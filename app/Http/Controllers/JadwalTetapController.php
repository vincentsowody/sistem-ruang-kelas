<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\RuangKelas;
use App\Models\User;
use App\Services\GreedyScheduler;
use Illuminate\Http\Request;

class JadwalTetapController extends Controller
{
    protected $greedy;

    public function __construct(GreedyScheduler $greedy)
    {
        $this->greedy = $greedy;
    }

    public function index(Request $request)
    {
        $query = JadwalTetap::with(['ruangKelas', 'dosen']);

        if ($request->search) {
            $query->where('mata_kuliah', 'like', "%{$request->search}%");
        }

        if ($request->hari) {
            $query->where('hari', $request->hari);
        }

        $jadwalList = $query
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15);

        return view('admin.jadwal.index', [
            'jadwalList' => $jadwalList,
            'dosenList' => User::where('role', 'dosen')->get(),
            'tahunAkademikList' => JadwalTetap::distinct()->pluck('tahun_akademik'),
            'stats' => [
                'total' => JadwalTetap::count(),
                'aktif' => JadwalTetap::where('status', 'aktif')->count(),
                'nonaktif' => JadwalTetap::where('status', 'nonaktif')->count(),
            ]
        ]);
    }

    public function create()
    {
        return view('admin.jadwal.create', [
            'ruangList' => RuangKelas::where('status', 'aktif')->get(),
            'dosenList' => User::where('role', 'dosen')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if ($msg = $this->cekSemuaKonflik($data)) {
            return back()->withInput()->with('error', $msg);
        }

        JadwalTetap::create($data);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function show(JadwalTetap $jadwal)
    {
        $jadwal->load(['ruangKelas', 'dosen']);

        return view('admin.jadwal.show', compact('jadwal'));
    }

    public function edit(JadwalTetap $jadwal)
    {
        return view('admin.jadwal.edit', [
            'jadwal' => $jadwal,
            'ruangList' => RuangKelas::where('status', 'aktif')->get(),
            'dosenList' => User::where('role', 'dosen')->get()
        ]);
    }

    public function update(Request $request, JadwalTetap $jadwal)
    {
        $data = $request->validate($this->rules());

        if ($msg = $this->cekSemuaKonflik($data, $jadwal->id)) {
            return back()->withInput()->with('error', $msg);
        }

        $jadwal->update($data);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy(JadwalTetap $jadwal)
    {
        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    public function formAlokasi()
    {
        return view('admin.jadwal.alokasi', [
            'dosenList' => User::where('role', 'dosen')->get()
        ]);
    }

    public function prosesAlokasi(Request $request)
    {
        $hasil = $this->greedy->jadwalkanBatch($request->jadwal);

        foreach ($hasil['berhasil'] as $item) {
            JadwalTetap::create([
                'ruang_kelas_id' => $item['ruang_dialokasikan']->id,
                'dosen_id' => $item['dosen_id'],
                'mata_kuliah' => $item['mata_kuliah'],
                'kelas' => $item['kelas'],
                'program_studi' => $item['program_studi'],
                'semester' => $item['semester'],
                'tahun_akademik' => $request->tahun_akademik,
                'semester_ganjil_genap' => $request->semester_ganjil_genap,
                'hari' => $item['hari'],
                'jam_mulai' => $item['jam_mulai'],
                'jam_selesai' => $item['jam_selesai'],
                'sks' => $item['sks'],
                'status' => 'aktif',
            ]);
        }

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Alokasi jadwal berhasil');
    }

    public function formImport()
    {
        return view('admin.jadwal.import');
    }

    public function prosesImport(Request $request)
    {
        $request->validate([
            'file_jadwal' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file_jadwal')->getRealPath(), 'r');

        try {
            $header = fgetcsv($file);

            if (!$header) {
                return back()->with('error', 'File CSV kosong atau tidak valid.');
            }

            $berhasil = 0;
            $gagal    = 0;

            while (($row = fgetcsv($file)) !== false) {
                // Skip baris kosong atau tidak sesuai panjang header
                if (count($row) !== count($header)) {
                    $gagal++;
                    continue;
                }

                $data = array_combine($header, $row);

                $ruang = RuangKelas::where('kode_ruang', trim($data['kode_ruang'] ?? ''))->first();
                $dosen = User::where('name', 'like', '%'.trim($data['nama_dosen'] ?? '').'%')->first();

                if (!$ruang || !$dosen) {
                    $gagal++;
                    continue;
                }

                JadwalTetap::create([
                    'ruang_kelas_id'       => $ruang->id,
                    'dosen_id'             => $dosen->id,
                    'mata_kuliah'          => $data['mata_kuliah'],
                    'kode_mk'              => $data['kode_mk'] ?? null,
                    'kelas'                => $data['kelas'],
                    'program_studi'        => $data['program_studi'],
                    'semester'             => $data['semester'],
                    'tahun_akademik'       => $data['tahun_akademik'],
                    'semester_ganjil_genap'=> $data['semester_ganjil_genap'],
                    'hari'                 => strtolower(trim($data['hari'])),
                    'jam_mulai'            => $data['jam_mulai'],
                    'jam_selesai'          => $data['jam_selesai'],
                    'sks'                  => $data['sks'],
                    'status'               => 'aktif',
                ]);

                $berhasil++;
            }
        } finally {
            fclose($file);
        }

        $msg = "Import selesai: {$berhasil} berhasil" . ($gagal ? ", {$gagal} dilewati (ruang/dosen tidak ditemukan)." : '.');

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', $msg);
    }

    public function apiCekKonflik(Request $request)
    {
        $konflik = $this->cekKonflikJadwal(
            $request->ruang_kelas_id,
            $request->hari,
            $request->jam_mulai,
            $request->jam_selesai,
            $request->tahun_akademik,
            $request->semester_ganjil_genap,
            $request->kecuali_id
        );

        return response()->json([
            'konflik' => $konflik ? true : false
        ]);
    }

    private function rules()
    {
        return [
            'ruang_kelas_id' => 'required',
            'dosen_id' => 'required',
            'mata_kuliah' => 'required',
            'kelas' => 'required',
            'program_studi' => 'required',
            'semester' => 'required',
            'tahun_akademik' => 'required',
            'semester_ganjil_genap' => 'required',
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'sks' => 'required',
            'status' => 'required'
        ];
    }

    private function cekSemuaKonflik($data, $exceptId = null)
    {
        $konflikRuang = $this->cekKonflikJadwal(
            $data['ruang_kelas_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['tahun_akademik'],
            $data['semester_ganjil_genap'],
            $exceptId
        );

        if ($konflikRuang) {
            return 'Ruang bentrok dengan jadwal lain';
        }

        $konflikDosen = $this->cekKonflikDosen(
            $data['dosen_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['tahun_akademik'],
            $data['semester_ganjil_genap'],
            $exceptId
        );

        if ($konflikDosen) {
            return 'Dosen bentrok di jam yang sama';
        }

        return null;
    }

    private function cekKonflikJadwal($ruangId, $hari, $mulai, $selesai, $tahun, $semester, $exceptId = null)
    {
        return JadwalTetap::where('ruang_kelas_id', $ruangId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('jam_mulai', '<', $selesai)
                  ->where('jam_selesai', '>', $mulai);
            })
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }

    private function cekKonflikDosen($dosenId, $hari, $mulai, $selesai, $tahun, $semester, $exceptId = null)
    {
        return JadwalTetap::where('dosen_id', $dosenId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('jam_mulai', '<', $selesai)
                  ->where('jam_selesai', '>', $mulai);
            })
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }
}