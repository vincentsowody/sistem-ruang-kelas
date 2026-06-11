<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Services\GreedyScheduler;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GreedyLogController extends Controller
{
    protected GreedyScheduler $greedy;

    public function __construct(GreedyScheduler $greedy)
    {
        $this->greedy = $greedy;
    }

    /**
     * Halaman utama simulasi alokasi greedy.
     * Menjalankan Best-Fit pada semua ruang aktif untuk parameter yang diberikan,
     * lalu mengembalikan log langkah-langkah keputusan algoritma secara detail.
     */
    public function index(Request $request)
    {
        $ruangList = RuangKelas::aktif()->orderBy('kapasitas')->get();

        $log      = null;
        $input    = null;
        $hasilAkhir = null;

        if ($request->isMethod('POST')) {
            $request->validate([
                'tanggal'        => 'required|date',
                'jam_mulai'      => 'required|date_format:H:i',
                'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
                'jumlah_peserta' => 'required|integer|min:1|max:1000',
                'fasilitas'      => 'nullable|array',
            ]);

            $input = $request->only(['tanggal', 'jam_mulai', 'jam_selesai', 'jumlah_peserta', 'fasilitas']);
            $input['fasilitas'] = $input['fasilitas'] ?? [];

            // Jalankan algoritma dengan log detail
            [$log, $hasilAkhir] = $this->jalankanGreedyDenganLog(
                $input['tanggal'],
                $input['jam_mulai'],
                $input['jam_selesai'],
                $input['jumlah_peserta'],
                $input['fasilitas']
            );
        }

        return view('admin.greedy.log', compact('ruangList', 'log', 'input', 'hasilAkhir'));
    }

    /**
     * Menjalankan Greedy Best-Fit sambil merekam setiap langkah keputusan.
     * Ini adalah inti fitur "visualisasi algoritma".
     *
     * Mengembalikan: [array $log, ?RuangKelas $hasilAkhir]
     */
    private function jalankanGreedyDenganLog(
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        int $jumlahPeserta,
        array $fasilitasDibutuhkan = []
    ): array {
        $log = [];

        // STEP 1: Kumpulkan semua ruang aktif, urutkan kapasitas asc (best-fit)
        $kandidat = RuangKelas::aktif()
            ->orderBy('kapasitas', 'asc')
            ->get();

        $log[] = [
            'langkah' => 0,
            'tipe'    => 'inisialisasi',
            'pesan'   => "Algoritma dimulai. Ditemukan {$kandidat->count()} ruang aktif, diurutkan dari kapasitas terkecil ke terbesar.",
            'data'    => [
                'total_ruang'    => $kandidat->count(),
                'jumlah_peserta' => $jumlahPeserta,
                'tanggal'        => Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y'),
                'jam'            => "{$jamMulai}–{$jamSelesai}",
                'fasilitas'      => $fasilitasDibutuhkan,
            ],
        ];

        // STEP 2: Filter kapasitas — hapus ruang yang terlalu kecil
        $cukupKapasitas = $kandidat->where('kapasitas', '>=', $jumlahPeserta);
        $tidakCukup     = $kandidat->where('kapasitas', '<', $jumlahPeserta);

        if ($tidakCukup->isNotEmpty()) {
            $log[] = [
                'langkah' => 1,
                'tipe'    => 'filter_kapasitas',
                'pesan'   => "Filter kapasitas: {$tidakCukup->count()} ruang gugur karena kapasitas < {$jumlahPeserta} peserta.",
                'data'    => [
                    'gugur' => $tidakCukup->map(fn($r) => [
                        'kode'      => $r->kode_ruang,
                        'nama'      => $r->nama_ruang,
                        'kapasitas' => $r->kapasitas,
                        'selisih'   => $jumlahPeserta - $r->kapasitas,
                    ])->values()->toArray(),
                    'lolos' => $cukupKapasitas->count(),
                ],
            ];
        }

        // STEP 3: Filter fasilitas (jika ada kebutuhan)
        $lolosFasilitas = collect();
        $gagalFasilitas = collect();

        if (!empty($fasilitasDibutuhkan)) {
            foreach ($cukupKapasitas as $ruang) {
                $fasilitasRuang = $ruang->fasilitas ?? [];
                $kurang = array_diff($fasilitasDibutuhkan, $fasilitasRuang);
                if (empty($kurang)) {
                    $lolosFasilitas->push($ruang);
                } else {
                    $gagalFasilitas->push([
                        'ruang'  => $ruang,
                        'kurang' => array_values($kurang),
                    ]);
                }
            }

            $log[] = [
                'langkah' => 2,
                'tipe'    => 'filter_fasilitas',
                'pesan'   => "Filter fasilitas: {$gagalFasilitas->count()} ruang gugur karena tidak memiliki fasilitas yang dibutuhkan.",
                'data'    => [
                    'dibutuhkan' => $fasilitasDibutuhkan,
                    'gugur'      => $gagalFasilitas->map(fn($item) => [
                        'kode'   => $item['ruang']->kode_ruang,
                        'nama'   => $item['ruang']->nama_ruang,
                        'kurang' => $item['kurang'],
                    ])->values()->toArray(),
                    'lolos' => $lolosFasilitas->count(),
                ],
            ];
        } else {
            $lolosFasilitas = $cukupKapasitas;
        }

        if ($lolosFasilitas->isEmpty()) {
            $log[] = [
                'langkah' => 3,
                'tipe'    => 'tidak_ada_kandidat',
                'pesan'   => 'Tidak ada ruang yang memenuhi syarat kapasitas dan fasilitas. Algoritma berhenti.',
                'data'    => [],
            ];
            return [$log, null];
        }

        // STEP 4: Iterasi greedy — cek ketersediaan satu per satu
        $hariMap = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu'];
        $hari    = $hariMap[Carbon::parse($tanggal)->dayOfWeekIso] ?? '';

        $hasilAkhir = null;
        $iterasi    = [];

        foreach ($lolosFasilitas as $idx => $ruang) {
            $step = ['nomor' => $idx + 1, 'ruang' => $ruang, 'alasan_gugur' => null, 'dipilih' => false];

            // Cek jadwal tetap
            $jadwalBentrok = JadwalTetap::where('ruang_kelas_id', $ruang->id)
                ->where('hari', $hari)
                ->where('status', 'aktif')
                ->where(fn($q) => $q->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>', $jamMulai))
                ->first();

            if ($jadwalBentrok) {
                $step['alasan_gugur'] = "Bentrok jadwal tetap: {$jadwalBentrok->mata_kuliah} ({$jadwalBentrok->jam_mulai}–{$jadwalBentrok->jam_selesai})";
                $step['tipe_bentrok'] = 'jadwal_tetap';
                $step['bentrok_detail'] = $jadwalBentrok;
                $iterasi[] = $step;
                continue;
            }

            // Cek reservasi aktif
            $reservasiBentrok = Reservasi::where('ruang_kelas_id', $ruang->id)
                ->where('tanggal', $tanggal)
                ->where('status', 'disetujui')
                ->where(fn($q) => $q->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>', $jamMulai))
                ->first();

            if ($reservasiBentrok) {
                $step['alasan_gugur'] = "Bentrok reservasi {$reservasiBentrok->kode_reservasi} oleh {$reservasiBentrok->pemohon->name} ({$reservasiBentrok->jam_mulai}–{$reservasiBentrok->jam_selesai})";
                $step['tipe_bentrok'] = 'reservasi';
                $step['bentrok_detail'] = $reservasiBentrok;
                $iterasi[] = $step;
                continue;
            }

            // ✓ TERSEDIA — ini ruang terbaik (best-fit karena sudah urut kapasitas asc)
            $step['dipilih']     = true;
            $step['alasan_gugur'] = null;
            $iterasi[]           = $step;
            $hasilAkhir          = $ruang;
            break; // greedy: ambil yang pertama tersedia
        }

        $log[] = [
            'langkah' => 3,
            'tipe'    => 'iterasi_greedy',
            'pesan'   => $hasilAkhir
                ? "Greedy Best-Fit selesai. Ruang terpilih: {$hasilAkhir->kode_ruang} (kapasitas {$hasilAkhir->kapasitas}, sisa " . ($hasilAkhir->kapasitas - $jumlahPeserta) . " kursi)."
                : "Greedy Best-Fit selesai. Tidak ada ruang yang tersedia pada slot waktu tersebut.",
            'data'    => ['iterasi' => $iterasi],
        ];

        return [$log, $hasilAkhir];
    }

    /**
     * API endpoint — mengembalikan log greedy dalam JSON untuk keperluan AJAX.
     */
    public function api(Request $request)
    {
        $request->validate([
            'tanggal'        => 'required|date',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
            'jumlah_peserta' => 'required|integer|min:1',
        ]);

        [$log, $hasil] = $this->jalankanGreedyDenganLog(
            $request->tanggal,
            $request->jam_mulai,
            $request->jam_selesai,
            (int) $request->jumlah_peserta,
            $request->fasilitas ?? []
        );

        return response()->json([
            'log'   => $log,
            'hasil' => $hasil ? [
                'id'         => $hasil->id,
                'kode_ruang' => $hasil->kode_ruang,
                'nama_ruang' => $hasil->nama_ruang,
                'kapasitas'  => $hasil->kapasitas,
                'gedung'     => $hasil->gedung,
                'fasilitas'  => $hasil->fasilitas_list,
            ] : null,
        ]);
    }
}
