<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\Notifikasi;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin())     return redirect()->route('admin.dashboard');
        if ($user->isDosen())     return redirect()->route('dosen.dashboard');
        if ($user->isMahasiswa()) return redirect()->route('mahasiswa.dashboard');

        abort(403);
    }

    /* ── ADMIN ─────────────────────────────────────────────── */
public function admin()
{
    $hariInggris = now()->format('l');

    $hariIndo = match ($hariInggris) {
        'Monday'    => 'senin',
        'Tuesday'   => 'selasa',
        'Wednesday' => 'rabu',
        'Thursday'  => 'kamis',
        'Friday'    => 'jumat',
        'Saturday'  => 'sabtu',
        default     => null,
    };

    // Statistik utama
    $stats = [
        'total_ruang'         => RuangKelas::count(),
        'ruang_aktif'         => RuangKelas::aktif()->count(),
        'total_user'          => User::count(),
        'total_dosen'         => User::dosen()->count(),
        'total_mahasiswa'     => User::mahasiswa()->count(),
        'reservasi_menunggu'  => Reservasi::menunggu()->count(),
        'reservasi_disetujui' => Reservasi::disetujui()->count(),
        'reservasi_hari_ini'  => Reservasi::hariIni()->count(),
        'jadwal_aktif'        => JadwalTetap::aktif()->count(),
    ];

    // Reservasi menunggu persetujuan
    $reservasiPending = Reservasi::menunggu()
        ->with(['pemohon', 'ruangKelas'])
        ->latest()
        ->limit(5)
        ->get();

    // Jadwal hari ini
    $jadwalHariIni = $hariIndo
        ? JadwalTetap::aktif()
            ->where('hari', $hariIndo)
            ->with(['ruangKelas', 'dosen'])
            ->orderBy('jam_mulai')
            ->limit(8)
            ->get()
        : collect();

    // Reservasi hari ini
    $reservasiHariIni = Reservasi::hariIni()
        ->with(['pemohon', 'ruangKelas'])
        ->orderBy('jam_mulai')
        ->limit(6)
        ->get();

    // Aktivitas terbaru
    $aktivitasTerbaru = Reservasi::with(['pemohon', 'ruangKelas'])
        ->where('created_at', '>=', now()->subDays(7))
        ->latest()
        ->limit(8)
        ->get();

    // Notifikasi admin
    $notifikasiBaru = auth()->user()
        ->notifikasi()
        ->where('sudah_dibaca', false)
        ->latest()
        ->limit(5)
        ->get();

    return view('dashboard.admin', compact(
        'stats',
        'reservasiPending',
        'jadwalHariIni',
        'reservasiHariIni',
        'aktivitasTerbaru',
        'notifikasiBaru'
    ));
}
    /* ── DOSEN ─────────────────────────────────────────────── */
    public function dosen()
    {
        $dosen   = auth()->user();
        $hariInggris = now()->format('l');
        $hariIndo    = match($hariInggris) {
            'Monday'    => 'senin',   'Tuesday'  => 'selasa',
            'Wednesday' => 'rabu',    'Thursday' => 'kamis',
            'Friday'    => 'jumat',   'Saturday' => 'sabtu',
            default     => null,
        };

        // Statistik personal
        $stats = [
            'jadwal_aktif'          => $dosen->jadwalTetap()->aktif()->count(),
            'reservasi_total'       => $dosen->reservasi()->count(),
            'reservasi_menunggu'    => $dosen->reservasi()->menunggu()->count(),
            'reservasi_disetujui'   => $dosen->reservasi()->disetujui()->count(),
        ];

        // Jadwal mengajar hari ini
        $jadwalHariIni = $hariIndo
            ? $dosen->jadwalTetap()->aktif()
                ->where('hari', $hariIndo)
                ->with('ruangKelas')
                ->orderBy('jam_mulai')
                ->get()
            : collect();

        // Jadwal mengajar minggu ini (semua hari)
        $jadwalMingguIni = $dosen->jadwalTetap()->aktif()
            ->with('ruangKelas')
            ->orderByRaw("FIELD(hari,'senin','selasa','rabu','kamis','jumat','sabtu')")
            ->orderBy('jam_mulai')
            ->limit(10)
            ->get();

        // Reservasi terbaru milik dosen ini
        $reservasiTerbaru = $dosen->reservasi()
            ->with('ruangKelas')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifikasi belum dibaca
        $notifikasiBaru = $dosen->notifikasi()
            ->where('sudah_dibaca', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.dosen', compact(
            'stats', 'jadwalHariIni', 'jadwalMingguIni',
            'reservasiTerbaru', 'notifikasiBaru'
        ));
    }

    /* ── MAHASISWA ──────────────────────────────────────────── */
    public function mahasiswa()
    {
        $mahasiswa = auth()->user();

        // Statistik personal
        $stats = [
            'reservasi_total'       => $mahasiswa->reservasi()->count(),
            'reservasi_menunggu'    => $mahasiswa->reservasi()->menunggu()->count(),
            'reservasi_disetujui'   => $mahasiswa->reservasi()->disetujui()->count(),
            'reservasi_ditolak'     => $mahasiswa->reservasi()->where('status','ditolak')->count(),
        ];

        // Reservasi aktif mendatang
        $reservasiMendatang = $mahasiswa->reservasi()
            ->disetujui()
            ->where('tanggal', '>=', today())
            ->with('ruangKelas')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(5)
            ->get();

        // Reservasi menunggu persetujuan
        $reservasiMenunggu = $mahasiswa->reservasi()
            ->menunggu()
            ->with('ruangKelas')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Riwayat reservasi terbaru
        $riwayat = $mahasiswa->reservasi()
            ->with('ruangKelas')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
        $reservasiTerbaru = $riwayat; // alias untuk view baru

        // Notifikasi belum dibaca
        $notifikasiBaru = $mahasiswa->notifikasi()
            ->where('sudah_dibaca', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Ruang tersedia hari ini (untuk inspirasi pengajuan)
        $ruangTersedia = RuangKelas::aktif()
            ->orderBy('kapasitas', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard.mahasiswa', compact(
            'stats', 'reservasiMendatang', 'reservasiMenunggu',
            'riwayat', 'reservasiTerbaru', 'notifikasiBaru', 'ruangTersedia'
        ));
    }
}