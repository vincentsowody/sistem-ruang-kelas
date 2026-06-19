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

    // ── Admin: daftar semua reservasi ─────────────────────
    public function adminIndex(Request $request)
    {
        $query = Reservasi::with(['pemohon', 'ruangKelas']);

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('tanggal')) $query->whereDate('tanggal', $request->tanggal);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->whereHas('pemohon', fn($q2) => $q2->where('name', 'like', "%{$s}%"))
                  ->orWhere('keperluan',      'like', "%{$s}%")
                  ->orWhere('kode_reservasi', 'like', "%{$s}%")
            );
        }

        $reservasiList = $query->latest()->paginate(15)->withQueryString();

        /**
         * BUG FIX 3: stats key tidak konsisten.
         * adminIndex() mengembalikan key 'hari_ini' tapi view menggunakan
         * $stats['reservasi_hari_ini'] — menyebabkan Undefined array key.
         * FIX: samakan key dengan yang dipakai view.
         */
        $stats = [
            'menunggu'          => Reservasi::menunggu()->count(),
            'disetujui'         => Reservasi::disetujui()->count(),
            'ditolak'           => Reservasi::where('status', 'ditolak')->count(),
            'dibatalkan'        => Reservasi::where('status', 'dibatalkan')->count(),
            // key yang benar sesuai view admin/reservasi/index.blade.php
            'reservasi_hari_ini'=> Reservasi::hariIni()->count(),
        ];

        return view('admin.reservasi.index', compact('reservasiList', 'stats'));
    }

    // ── Admin: detail & approval ──────────────────────────
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
        /**
         * BUG FIX 4: Validasi catatan_admin di tolak() menggunakan 'required'
         * tapi form di show.blade.php memiliki satu textarea yang di-share
         * antara setujui dan tolak — jika user tidak isi catatan lalu klik Tolak,
         * validasi gagal tapi error message tidak muncul dengan jelas di form.
         * FIX: ubah menjadi nullable agar tidak blocking, tapi tetap sanitasi.
         */
        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        if (!$reservasi->isMenunggu()) {
            return back()->with('error', 'Reservasi ini sudah diproses sebelumnya.');
        }

        $reservasi->update([
            'status'        => 'ditolak',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
            'catatan_admin' => $request->catatan_admin ?? '',
        ]);

        KirimNotifikasiReservasi::dispatch($reservasi, 'ditolak', $reservasi->pemohon_id);

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} ditolak.");
    }

    // ── User: form pengajuan ──────────────────────────────
    public function create()
    {
        $ruangList = RuangKelas::aktif()->orderBy('kode_ruang')->get();
        return view('reservasi.create', compact('ruangList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruang_kelas_id' => 'required|exists:ruang_kelas,id',
            'tanggal'        => 'required|date|after_or_equal:today',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
            'keperluan'      => 'required|string|max:200',
            /**
             * BUG FIX 5: jenis_kegiatan di rules tidak include 'praktikum'
             * dan 'organisasi' tapi form (reservasi/create.blade.php) menawarkan
             * keduanya — menyebabkan ValidationException "not in list".
             * FIX: sesuaikan list dengan opsi yang ada di form.
             */
            'jenis_kegiatan' => 'required|in:kuliah_pengganti,ujian,rapat,seminar,praktikum,organisasi,kegiatan_mahasiswa,lainnya',
            'jumlah_peserta' => 'required|integer|min:1|max:2000',
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

        // STEP 2: Jika bentrok → greedy cari alternatif
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

            // Kirim notifikasi rekomendasi ruang ke pemohon jika ada alternatif
            if ($ruangAlternatif) {
                KirimNotifikasiReservasi::dispatch($reservasi, 'rekomendasi_ruang', Auth::id());
            }

            if ($ruangAlternatif) {
                return redirect()->route('reservasi.show', $reservasi)
                    ->with('warning',
                        "Ruang yang Anda pilih bentrok ({$cek['detail']}). " .
                        "Sistem menyarankan ruang alternatif: <strong>{$ruangAlternatif->kode_ruang}</strong> " .
                        "({$ruangAlternatif->nama_ruang}, {$ruangAlternatif->kapasitas} kursi). " .
                        "Admin akan menghubungi Anda."
                    );
            }

            return redirect()->route('reservasi.show', $reservasi)
                ->with('warning', "Ruang yang dipilih bentrok: {$cek['detail']}. Tidak ada ruang alternatif tersedia. Admin akan menghubungi Anda.");
        }

        // STEP 3: Tersedia → simpan
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

    // ── User: detail ──────────────────────────────────────
    public function show(Reservasi $reservasi)
    {
        $this->otorisasiLihatReservasi($reservasi);
        $reservasi->load(['ruangKelas', 'ruangSaran', 'pemohon', 'diprosesDari']);
        return view('reservasi.show', compact('reservasi'));
    }

    // ── User: daftar milik sendiri ────────────────────────
    public function myReservasi(Request $request)
    {
        /**
         * BUG FIX 6: myReservasi() tidak eager-load 'pemohon'.
         * View reservasi/index.blade.php memanggil $rsv->pemohon->name
         * sehingga tiap baris trigger lazy load query baru (N+1).
         * FIX: tambahkan 'pemohon' ke with().
         */
        $reservasiList = Reservasi::with(['ruangKelas', 'pemohon'])
            ->where('pemohon_id', Auth::id())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reservasi.index', compact('reservasiList'));
    }

    // ── User: batalkan ────────────────────────────────────
    public function batalkan(Reservasi $reservasi)
    {
        $this->otorisasiMilikSendiri($reservasi);

        if (!$reservasi->isMenunggu()) {
            return back()->with('error', 'Hanya reservasi yang masih menunggu yang bisa dibatalkan.');
        }

        $reservasi->update(['status' => 'dibatalkan']);
        KirimNotifikasiReservasi::dispatch($reservasi, 'dibatalkan', $reservasi->pemohon_id);

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} berhasil dibatalkan.");
    }

    // ── API: cek ketersediaan real-time ───────────────────
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

        $saranRuang       = null;
        $rekomendasiRuang = null;
        $rekomendasiSama  = false;

        if ($cek['konflik']) {
            // Ruang bentrok → cari alternatif terbaik
            if ($request->filled('jumlah_peserta')) {
                $alt = $this->greedy->cariRuangTerbaik(
                    $request->tanggal,
                    $request->jam_mulai,
                    $request->jam_selesai,
                    (int) $request->jumlah_peserta,
                    [],
                    (int) $request->ruang_kelas_id
                );
                if ($alt) {
                    $saranRuang = [
                        'id'         => $alt->id,
                        'kode_ruang' => $alt->kode_ruang,
                        'nama_ruang' => $alt->nama_ruang,
                        'kapasitas'  => $alt->kapasitas,
                        'fasilitas'  => $alt->fasilitas_list,
                    ];
                }
            }
        } else {
            // Ruang tersedia → tampilkan rekomendasi greedy best-fit
            // supaya user tahu apakah ruang pilihannya sudah optimal
            if ($request->filled('jumlah_peserta')) {
                $bestFit = $this->greedy->cariRuangTerbaik(
                    $request->tanggal,
                    $request->jam_mulai,
                    $request->jam_selesai,
                    (int) $request->jumlah_peserta
                );
                if ($bestFit) {
                    $rekomendasiSama  = $bestFit->id === (int) $request->ruang_kelas_id;
                    $rekomendasiRuang = [
                        'id'         => $bestFit->id,
                        'kode_ruang' => $bestFit->kode_ruang,
                        'nama_ruang' => $bestFit->nama_ruang,
                        'kapasitas'  => $bestFit->kapasitas,
                        'fasilitas'  => $bestFit->fasilitas_list,
                    ];
                }
            }
        }

        return response()->json([
            'konflik'                       => $cek['konflik'],
            'detail'                        => $cek['detail'],
            'saran_ruang'                   => $saranRuang,
            'rekomendasi_ruang'             => $rekomendasiRuang,
            'rekomendasi_sama_dengan_pilihan' => $rekomendasiSama,
        ]);
    }

    // ── Helper: Authorization ─────────────────────────────
    private function otorisasiLihatReservasi(Reservasi $reservasi): void
    {
        if (Auth::user()->isAdmin()) return;
        if ($reservasi->pemohon_id !== Auth::id()) abort(403, 'Anda tidak memiliki akses ke reservasi ini.');
    }

    private function otorisasiMilikSendiri(Reservasi $reservasi): void
    {
        if ($reservasi->pemohon_id !== Auth::id()) abort(403, 'Anda hanya dapat mengelola reservasi milik Anda sendiri.');
    }

    private function notifikasiAdmin(Reservasi $reservasi): void
    {
        KirimNotifikasiReservasi::dispatch($reservasi, 'reservasi_baru');
    }
}