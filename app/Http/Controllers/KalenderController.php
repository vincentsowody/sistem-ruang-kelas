<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalenderController extends Controller
{
    // Halaman kalender utama
    public function index()
    {
        $ruangList = RuangKelas::aktif()->orderBy('kode_ruang')->get();
        return view('kalender.index', compact('ruangList'));
    }

    /**
     * API: ambil semua event untuk FullCalendar
     * Format: array of { id, title, start, end, color, extendedProps }
     */
    public function apiEvents(Request $request)
    {
        $request->validate([
            'start'      => 'required|date',
            'end'        => 'required|date',
            'ruang_id'   => 'nullable|exists:ruang_kelas,id',
        ]);

        $mulai  = Carbon::parse($request->start);
        $selesai = Carbon::parse($request->end);
        $events  = [];

        // ── Jadwal Tetap ─────────────────────────────────
        // Mapping hari ke Carbon dayOfWeek (0=Minggu, 1=Senin, ..., 6=Sabtu)
        $hariKeAngka = [
            'minggu' => 0,
            'senin'  => 1,
            'selasa' => 2,
            'rabu'   => 3,
            'kamis'  => 4,
            'jumat'  => 5,
            'sabtu'  => 6,
        ];

        $queryJadwal = JadwalTetap::with(['ruangKelas','dosen'])->aktif();
        if ($request->filled('ruang_id')) {
            $queryJadwal->where('ruang_kelas_id', $request->ruang_id);
        }
        $jadwalList = $queryJadwal->get();

        // Generate event untuk setiap minggu dalam rentang tanggal
        // startOfWeek default Carbon = Senin (ISO); gunakan Carbon::SUNDAY untuk konsistensi
        $current = $mulai->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        while ($current->lte($selesai)) {
            foreach ($jadwalList as $jadwal) {
                $hariNum = $hariKeAngka[$jadwal->hari] ?? 1;
                $tanggal = $current->copy()->dayOfWeek($hariNum);

                if ($tanggal->between($mulai, $selesai)) {
                    $events[] = [
                        'id'    => 'jadwal-'.$jadwal->id.'-'.$tanggal->format('Ymd'),
                        'title' => $jadwal->mata_kuliah.' ('.$jadwal->ruangKelas->kode_ruang.')',
                        'start' => $tanggal->format('Y-m-d').'T'.$jadwal->jam_mulai,
                        'end'   => $tanggal->format('Y-m-d').'T'.$jadwal->jam_selesai,
                        'color' => '#3B82F6', // biru
                        'extendedProps' => [
                            'tipe'          => 'jadwal_tetap',
                            'mata_kuliah'   => $jadwal->mata_kuliah,
                            'dosen'         => $jadwal->dosen->name,
                            'ruang'         => $jadwal->ruangKelas->kode_ruang,
                            'kelas'         => 'Kelas '.$jadwal->kelas,
                            'sks'           => $jadwal->sks.' SKS',
                        ],
                    ];
                }
            }
            $current->addWeek();
        }

        // ── Reservasi Disetujui ───────────────────────────
        $queryReservasi = Reservasi::with(['ruangKelas','pemohon'])
            ->disetujui()
            ->whereBetween('tanggal', [$mulai->format('Y-m-d'), $selesai->format('Y-m-d')]);

        if ($request->filled('ruang_id')) {
            $queryReservasi->where('ruang_kelas_id', $request->ruang_id);
        }

        foreach ($queryReservasi->get() as $rsv) {
            $events[] = [
                'id'    => 'reservasi-'.$rsv->id,
                'title' => $rsv->keperluan.' ('.$rsv->ruangKelas->kode_ruang.')',
                'start' => $rsv->tanggal->format('Y-m-d').'T'.$rsv->jam_mulai,
                'end'   => $rsv->tanggal->format('Y-m-d').'T'.$rsv->jam_selesai,
                'color' => '#10B981', // hijau
                'extendedProps' => [
                    'tipe'      => 'reservasi',
                    'keperluan' => $rsv->keperluan,
                    'pemohon'   => $rsv->pemohon->name,
                    'ruang'     => $rsv->ruangKelas->kode_ruang,
                    'peserta'   => $rsv->jumlah_peserta.' orang',
                    'kode'      => $rsv->kode_reservasi,
                ],
            ];
        }

        return response()->json($events);
    }

    // Kalender per ruang — cek ketersediaan spesifik
    public function ruang(RuangKelas $ruang)
    {
        return view('kalender.ruang', compact('ruang'));
    }
}
