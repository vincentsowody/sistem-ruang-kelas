<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Jumlah jam operasional per hari kerja.
     * Ubah konstanta ini sesuai jadwal kampus (default 08:00–21:00 = 13 jam).
     */
    const JAM_OPERASIONAL_PER_HARI = 13;

    /**
     * Estimasi hari kerja efektif per bulan.
     * Ubah sesuai kalender akademik kampus.
     */
    const HARI_KERJA_PER_BULAN = 26;

    // ── Halaman laporan utama ────────────────────────────
    public function index()
    {
        $ruangList = RuangKelas::aktif()->orderBy('kode_ruang')->get();
        $tahunList = JadwalTetap::select('tahun_akademik')
            ->distinct()
            ->orderBy('tahun_akademik', 'desc')
            ->pluck('tahun_akademik');

        // Statistik ringkasan
        $stats = [
            'total_ruang'         => RuangKelas::aktif()->count(),
            'total_jadwal'        => JadwalTetap::aktif()->count(),
            'total_reservasi'     => Reservasi::count(),
            'reservasi_disetujui' => Reservasi::disetujui()->count(),
            'reservasi_ditolak'   => Reservasi::where('status', 'ditolak')->count(),
            'reservasi_bulan_ini' => Reservasi::disetujui()
                ->whereMonth('tanggal', now()->month)
                ->count(),
        ];

        // PERBAIKAN N+1: gunakan satu query agregasi DB
        // Hitung total jam reservasi bulan ini per ruang dalam satu query
        $utilisasiRaw = Reservasi::select(
                'ruang_kelas_id',
                DB::raw('COUNT(*) as total_sesi'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, jam_mulai, jam_selesai)) as total_menit')
            )
            ->where('status', 'disetujui')
            ->whereMonth('tanggal', now()->month)
            ->groupBy('ruang_kelas_id')
            ->pluck('total_menit', 'ruang_kelas_id'); // key = ruang_kelas_id

        $utilisasi = RuangKelas::aktif()->get()->map(function ($ruang) use ($utilisasiRaw) {
            $totalJam = round(($utilisasiRaw[$ruang->id] ?? 0) / 60, 1);
            $jamTersedia = self::JAM_OPERASIONAL_PER_HARI * self::HARI_KERJA_PER_BULAN;
            $persen = $jamTersedia > 0 ? round(($totalJam / $jamTersedia) * 100, 1) : 0;

            return [
                'ruang'     => $ruang,
                'total_jam' => $totalJam,
                'persen'    => min($persen, 100),
            ];
        })->sortByDesc('total_jam')->take(5);

        return view('admin.laporan.index', compact('ruangList', 'tahunList', 'stats', 'utilisasi'));
    }

    // ── Laporan Jadwal per Semester ──────────────────────
    public function jadwalSemester(Request $request)
    {
        $request->validate([
            'tahun_akademik'        => 'required|string',
            'semester_ganjil_genap' => 'required|in:ganjil,genap',
        ]);

        $jadwalList = JadwalTetap::with(['ruangKelas', 'dosen'])
            ->where('tahun_akademik',        $request->tahun_akademik)
            ->where('semester_ganjil_genap', $request->semester_ganjil_genap)
            ->aktif()
            ->orderByRaw("FIELD(hari,'senin','selasa','rabu','kamis','jumat','sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        if ($request->format === 'pdf') {
            return $this->exportJadwalPdf($jadwalList, $request->tahun_akademik, $request->semester_ganjil_genap);
        }

        if ($request->format === 'excel') {
            return $this->exportJadwalExcel($jadwalList, $request->tahun_akademik, $request->semester_ganjil_genap);
        }

        return view('admin.laporan.jadwal', compact('jadwalList', 'request'));
    }

    // ── Laporan Reservasi ────────────────────────────────
    public function reservasi(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $query = Reservasi::with(['pemohon', 'ruangKelas'])
            ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('ruang_id')) $query->where('ruang_kelas_id', $request->ruang_id);

        $reservasiList = $query->get();

        if ($request->format === 'pdf') {
            return $this->exportReservasiPdf($reservasiList, $request);
        }

        if ($request->format === 'excel') {
            return $this->exportReservasiExcel($reservasiList, $request);
        }

        return view('admin.laporan.reservasi', compact('reservasiList', 'request'));
    }

    // ── Laporan Utilisasi Ruang ──────────────────────────
    public function utilisasiRuang(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // PERBAIKAN N+1: satu query agregasi untuk semua ruang sekaligus
        $reservasiRaw = Reservasi::select(
                'ruang_kelas_id',
                DB::raw('COUNT(*) as total_sesi'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, jam_mulai, jam_selesai)) as total_menit')
            )
            ->where('status', 'disetujui')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('ruang_kelas_id')
            ->get()
            ->keyBy('ruang_kelas_id');

        // PERBAIKAN hardcoded: gunakan konstanta yang terdokumentasi
        $jamTersedia = self::JAM_OPERASIONAL_PER_HARI * self::HARI_KERJA_PER_BULAN;

        $utilisasi = RuangKelas::aktif()->get()->map(function ($ruang) use ($reservasiRaw, $jamTersedia) {
            $data      = $reservasiRaw->get($ruang->id);
            $totalSesi = $data->total_sesi ?? 0;
            $totalJam  = round(($data->total_menit ?? 0) / 60, 1);
            $persen    = $jamTersedia > 0 ? round(($totalJam / $jamTersedia) * 100, 1) : 0;

            return [
                'ruang'      => $ruang,
                'total_sesi' => $totalSesi,
                'total_jam'  => $totalJam,
                'persen'     => min($persen, 100),
            ];
        })->sortByDesc('total_jam');

        if ($request->format === 'pdf') {
            return $this->exportUtilisasiPdf($utilisasi, $bulan, $tahun);
        }

        if ($request->format === 'excel') {
            return $this->exportUtilisasiExcel($utilisasi, $bulan, $tahun);
        }

        $namaBulan = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');
        return view('admin.laporan.utilisasi', compact('utilisasi', 'bulan', 'tahun', 'namaBulan'));
    }

    // ── Export PDF (barryvdh/laravel-dompdf) ────────────
    private function exportJadwalPdf($jadwalList, $tahun, $semester)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.laporan.pdf.jadwal',
            compact('jadwalList', 'tahun', 'semester')
        )->setPaper('a4', 'landscape');

        return $pdf->download("Jadwal-{$tahun}-{$semester}.pdf");
    }

    private function exportReservasiPdf($reservasiList, $request)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.laporan.pdf.reservasi',
            compact('reservasiList', 'request')
        )->setPaper('a4', 'landscape');

        return $pdf->download("Reservasi-{$request->tanggal_mulai}-sd-{$request->tanggal_selesai}.pdf");
    }

    private function exportUtilisasiPdf($utilisasi, $bulan, $tahun)
    {
        $namaBulan = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.laporan.pdf.utilisasi',
            compact('utilisasi', 'namaBulan', 'bulan', 'tahun')
        )->setPaper('a4', 'portrait');

        return $pdf->download("Utilisasi-Ruang-{$namaBulan}.pdf");
    }

    // ── Export Excel (CSV format) ────────────────────────
    private function exportJadwalExcel($jadwalList, $tahun, $semester)
    {
        $rows   = [];
        $rows[] = ['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Mata Kuliah', 'Kode MK', 'Dosen', 'Kelas', 'Prodi', 'Semester', 'SKS', 'Ruang', 'Kapasitas'];

        foreach ($jadwalList as $i => $j) {
            $rows[] = [
                $i + 1,
                ucfirst($j->hari),
                substr($j->jam_mulai, 0, 5),
                substr($j->jam_selesai, 0, 5),
                $j->mata_kuliah,
                $j->kode_mk ?? '-',
                $j->dosen->name,
                'Kelas ' . $j->kelas,
                $j->program_studi,
                $j->semester,
                $j->sks,
                $j->ruangKelas->kode_ruang,
                $j->ruangKelas->kapasitas,
            ];
        }

        return $this->downloadCsv($rows, "jadwal-{$tahun}-{$semester}.csv");
    }

    private function exportReservasiExcel($reservasiList, $request)
    {
        $rows   = [];
        $rows[] = ['No', 'Kode', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Keperluan', 'Jenis', 'Pemohon', 'Ruang', 'Peserta', 'Status'];

        foreach ($reservasiList as $i => $r) {
            $rows[] = [
                $i + 1,
                $r->kode_reservasi,
                $r->tanggal->format('d/m/Y'),
                substr($r->jam_mulai, 0, 5),
                substr($r->jam_selesai, 0, 5),
                $r->keperluan,
                ucwords(str_replace('_', ' ', $r->jenis_kegiatan)),
                $r->pemohon->name,
                $r->ruangKelas->kode_ruang,
                $r->jumlah_peserta,
                ucfirst($r->status),
            ];
        }

        return $this->downloadCsv($rows, "reservasi-{$request->tanggal_mulai}-{$request->tanggal_selesai}.csv");
    }

    private function exportUtilisasiExcel($utilisasi, $bulan, $tahun)
    {
        $rows   = [];
        $rows[] = ['No', 'Kode Ruang', 'Nama Ruang', 'Gedung', 'Kapasitas', 'Total Sesi', 'Total Jam', 'Utilisasi (%)'];

        foreach ($utilisasi->values() as $i => $item) {
            $rows[] = [
                $i + 1,
                $item['ruang']->kode_ruang,
                $item['ruang']->nama_ruang,
                $item['ruang']->gedung,
                $item['ruang']->kapasitas,
                $item['total_sesi'],
                $item['total_jam'],
                $item['persen'] . '%',
            ];
        }

        return $this->downloadCsv($rows, "utilisasi-{$bulan}-{$tahun}.csv");
    }

    private function downloadCsv(array $rows, string $filename)
    {
        $output = fopen('php://temp', 'r+');
        // BOM untuk Excel agar bisa baca UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
