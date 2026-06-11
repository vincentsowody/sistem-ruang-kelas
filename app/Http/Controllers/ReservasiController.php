<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Jobs\KirimNotifikasiReservasi;
use App\Services\GreedyScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservasiController extends Controller
{
    protected GreedyScheduler $greedy;

    public function __construct(GreedyScheduler $greedy)
    {
        $this->greedy = $greedy;
    }

    // ── Admin: daftar semua reservasi ────────────────────
    public function adminIndex(Request $request)
    {
        $query = Reservasi::with(['pemohon', 'ruangKelas']);

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('tanggal')) $query->whereDate('tanggal', $request->tanggal);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('pemohon', fn($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                  ->orWhere('keperluan', 'like', '%' . $search . '%')
                  ->orWhere('kode_reservasi', 'like', '%' . $search . '%');
            });
        }

        $reservasiList = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'menunggu'  => Reservasi::menunggu()->count(),
            'disetujui' => Reservasi::disetujui()->count(),
            'ditolak'   => Reservasi::where('status', 'ditolak')->count(),
            'hari_ini'  => Reservasi::hariIni()->disetujui()->count(),
        ];

        return view('admin.reservasi.index', compact('reservasiList', 'stats'));
    }

    // ── Admin: detail & approval ─────────────────────────
    public function adminShow(Reservasi $reservasi)
    {
        $reservasi->load(['pemohon', 'ruangKelas', 'ruangSaran', 'diprosesDari']);
        return view('admin.reservasi.show', compact('reservasi'));
    }

    public function setujui(Request $request, Reservasi $reservasi)
    {
        if (!$reservasi->isMenunggu()) {
            return back()->with('error', 'Reservasi ini sudah diproses sebelumnya.');
        }

        // Cek konflik sekali lagi sebelum menyetujui
        $cek = $this->greedy->cekKonflik(
            $reservasi->ruang_kelas_id,
            $reservasi->tanggal->format('Y-m-d'),
            $reservasi->jam_mulai,
            $reservasi->jam_selesai,
            $reservasi->id
        );

        if ($cek['konflik']) {
            return back()->with('error', "Tidak dapat disetujui: {$cek['detail']}");
        }

        $reservasi->update([
            'status'        => 'disetujui',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
            'catatan_admin' => $request->catatan_admin,
        ]);

        KirimNotifikasiReservasi::dispatch($reservasi, 'disetujui', $reservasi->pemohon_id);

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} berhasil disetujui.");
    }

    public function tolak(Request $request, Reservasi $reservasi)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500'], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if (!$reservasi->isMenunggu()) {
            return back()->with('error', 'Reservasi ini sudah diproses sebelumnya.');
        }

        $reservasi->update([
            'status'        => 'ditolak',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
            'catatan_admin' => $request->catatan_admin,
        ]);

        KirimNotifikasiReservasi::dispatch($reservasi, 'ditolak', $reservasi->pemohon_id);

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} ditolak.");
    }

    // ── User (Dosen/Mahasiswa): form ajukan reservasi ────
    public function create()
    {
        $ruangList = RuangKelas::aktif()->orderBy('kode_ruang')->get();
        return view('reservasi.create', compact('ruangList'));
    }

    /**
     * INTI ALGORITMA GREEDY:
     * 1. Cek apakah ruang yang dipilih tersedia
     * 2. Jika BENTROK → greedy cari ruang alternatif terbaik (best-fit)
     * 3. Tampilkan saran ruang ke user
     * 4. Jika TERSEDIA → langsung simpan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruang_kelas_id' => 'required|exists:ruang_kelas,id',
            'tanggal'        => 'required|date|after_or_equal:today',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
            'keperluan'      => 'required|string|max:200',
            'jenis_kegiatan' => 'required|in:kuliah_pengganti,ujian,rapat,seminar,kegiatan_mahasiswa,lainnya',
            'jumlah_peserta' => 'required|integer|min:1',
            'keterangan'     => 'nullable|string|max:500',
            'gunakan_saran'  => 'nullable|boolean',
            'ruang_saran_id' => 'nullable|exists:ruang_kelas,id',
        ]);

        // Tentukan ruang yang akan dipakai
        $ruangId = $request->boolean('gunakan_saran') && $request->filled('ruang_saran_id')
            ? $request->ruang_saran_id
            : $validated['ruang_kelas_id'];

        // STEP 1: Cek konflik pada ruang yang dipilih
        $cek = $this->greedy->cekKonflik(
            $ruangId,
            $validated['tanggal'],
            $validated['jam_mulai'],
            $validated['jam_selesai']
        );

        // STEP 2: Jika bentrok, jalankan greedy cari alternatif
        if ($cek['konflik']) {
            $ruangAlternatif = $this->greedy->cariRuangTerbaik(
                $validated['tanggal'],
                $validated['jam_mulai'],
                $validated['jam_selesai'],
                $validated['jumlah_peserta'],
                [],
                $ruangId
            );

            $reservasi = Reservasi::create([
                ...$validated,
                'ruang_kelas_id' => $validated['ruang_kelas_id'],
                'pemohon_id'     => Auth::id(),
                'status'         => 'menunggu',
                'ruang_saran_id' => $ruangAlternatif?->id,
                'gunakan_saran'  => false,
            ]);

            $this->notifikasiAdmin($reservasi);

            if ($ruangAlternatif) {
                return redirect()->route('reservasi.show', $reservasi)
                    ->with('warning',
                        "Ruang yang Anda pilih bentrok ({$cek['detail']}). " .
                        "Sistem menyarankan ruang alternatif: <strong>{$ruangAlternatif->kode_ruang}</strong> " .
                        "({$ruangAlternatif->nama_ruang}, kapasitas {$ruangAlternatif->kapasitas} kursi). " .
                        "Anda dapat meminta admin untuk menggunakan ruang tersebut."
                    );
            }

            return redirect()->route('reservasi.show', $reservasi)
                ->with('warning', "Ruang yang dipilih bentrok: {$cek['detail']}. Tidak ada ruang alternatif tersedia. Admin akan menghubungi Anda.");
        }

        // STEP 3: Ruang tersedia → simpan langsung
        $reservasi = Reservasi::create([
            ...$validated,
            'ruang_kelas_id' => $ruangId,
            'pemohon_id'     => Auth::id(),
            'status'         => 'menunggu',
        ]);

        $this->notifikasiAdmin($reservasi);

        return redirect()->route('reservasi.show', $reservasi)
            ->with('success', "Reservasi {$reservasi->kode_reservasi} berhasil diajukan dan menunggu persetujuan.");
    }

    // ── User: detail reservasi milik sendiri ─────────────
    public function show(Reservasi $reservasi)
    {
        // PERBAIKAN: authorization yang lebih eksplisit dan aman
        $this->otorisasiLihatReservasi($reservasi);

        $reservasi->load(['ruangKelas', 'ruangSaran', 'pemohon', 'diprosesDari']);
        return view('reservasi.show', compact('reservasi'));
    }

    // ── User: daftar reservasi sendiri ───────────────────
    public function myReservasi(Request $request)
    {
        $reservasiList = Reservasi::with(['ruangKelas'])
            ->where('pemohon_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('reservasi.index', compact('reservasiList'));
    }

    public function batalkan(Reservasi $reservasi)
    {
        // PERBAIKAN: gunakan helper terpusat agar konsisten
        $this->otorisasiMilikSendiri($reservasi);

        if (!$reservasi->isMenunggu()) {
            return back()->with('error', 'Hanya reservasi yang masih menunggu yang bisa dibatalkan.');
        }

        $reservasi->update(['status' => 'dibatalkan']);

        KirimNotifikasiReservasi::dispatch($reservasi, 'dibatalkan', $reservasi->pemohon_id);

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} berhasil dibatalkan.");
    }

    // ── API: cek ketersediaan real-time (AJAX) ───────────
    public function apiCekKetersediaan(Request $request)
    {
        $request->validate([
            'ruang_kelas_id' => 'required|exists:ruang_kelas,id',
            'tanggal'        => 'required|date',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i',
            'jumlah_peserta' => 'nullable|integer|min:1',
        ]);

        $cek = $this->greedy->cekKonflik(
            $request->ruang_kelas_id,
            $request->tanggal,
            $request->jam_mulai,
            $request->jam_selesai
        );

        $saranRuang = null;

        if ($cek['konflik'] && $request->filled('jumlah_peserta')) {
            $alternatif = $this->greedy->cariRuangTerbaik(
                $request->tanggal,
                $request->jam_mulai,
                $request->jam_selesai,
                (int) $request->jumlah_peserta,
                [],
                (int) $request->ruang_kelas_id
            );

            if ($alternatif) {
                $saranRuang = [
                    'id'         => $alternatif->id,
                    'kode_ruang' => $alternatif->kode_ruang,
                    'nama_ruang' => $alternatif->nama_ruang,
                    'kapasitas'  => $alternatif->kapasitas,
                    'fasilitas'  => $alternatif->fasilitas_list,
                ];
            }
        }

        return response()->json([
            'konflik'     => $cek['konflik'],
            'detail'      => $cek['detail'],
            'saran_ruang' => $saranRuang,
        ]);
    }

    // ── Helper: Authorization ─────────────────────────────

    /**
     * Pastikan user boleh melihat reservasi:
     * hanya pemohon sendiri atau admin.
     */
    private function otorisasiLihatReservasi(Reservasi $reservasi): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) return;

        if ($reservasi->pemohon_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke reservasi ini.');
        }
    }

    /**
     * Pastikan reservasi benar-benar milik user yang sedang login.
     * Digunakan untuk aksi yang bersifat mutasi (batalkan, dll).
     */
    private function otorisasiMilikSendiri(Reservasi $reservasi): void
    {
        if ($reservasi->pemohon_id !== Auth::id()) {
            abort(403, 'Anda hanya dapat mengelola reservasi milik Anda sendiri.');
        }
    }

    // ── Helper: Notifikasi ────────────────────────────────
    private function notifikasiAdmin(Reservasi $reservasi): void
    {
        KirimNotifikasiReservasi::dispatch($reservasi, 'reservasi_baru');
    }
}
