<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\RuangKelas;
use App\Models\User;
use App\Services\GreedyScheduler;
use Illuminate\Http\Request;

class JadwalTetapController extends Controller
{
    protected GreedyScheduler $greedy;

    public function __construct(GreedyScheduler $greedy)
    {
        $this->greedy = $greedy;
    }

    public function index(Request $request)
    {
        /**
         * BUG FIX 7: index() tidak filter berdasarkan tahun_akademik
         * meski form filter punya dropdown tahun_akademik —
         * menyebabkan filter tidak bekerja sama sekali.
         * FIX: tambahkan filter tahun_akademik dan semester_ganjil_genap.
         */
        $query = JadwalTetap::with(['ruangKelas', 'dosen']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('mata_kuliah', 'like', "%{$s}%")
                  ->orWhere('kode_mk',    'like', "%{$s}%")
            );
        }
        if ($request->filled('hari'))                 $query->where('hari', $request->hari);
        if ($request->filled('tahun_akademik'))       $query->where('tahun_akademik', $request->tahun_akademik);
        if ($request->filled('semester_ganjil_genap')) $query->where('semester_ganjil_genap', $request->semester_ganjil_genap);
        if ($request->filled('dosen_id'))             $query->where('dosen_id', $request->dosen_id);
        if ($request->filled('status'))               $query->where('status', $request->status);

        $jadwalList = $query
            ->orderByRaw("FIELD(hari,'senin','selasa','rabu','kamis','jumat','sabtu')")
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jadwal.index', [
            'jadwalList'        => $jadwalList,
            'dosenList'         => User::dosen()->orderBy('name')->get(),
            'tahunAkademikList' => JadwalTetap::distinct()->orderBy('tahun_akademik','desc')->pluck('tahun_akademik'),
            'stats' => [
                'total'    => JadwalTetap::count(),
                'aktif'    => JadwalTetap::where('status', 'aktif')->count(),
                'nonaktif' => JadwalTetap::where('status', 'nonaktif')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.jadwal.create', [
            'ruangList' => RuangKelas::aktif()->orderBy('kode_ruang')->get(),
            'dosenList' => User::dosen()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if ($msg = $this->cekSemuaKonflik($data)) {
            return back()->withInput()->with('error', $msg);
        }

        JadwalTetap::create($data);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function show(JadwalTetap $jadwal)
    {
        $jadwal->load(['ruangKelas', 'dosen']);
        return view('admin.jadwal.show', compact('jadwal'));
    }

    public function edit(JadwalTetap $jadwal)
    {
        return view('admin.jadwal.edit', [
            'jadwal'    => $jadwal,
            'ruangList' => RuangKelas::aktif()->orderBy('kode_ruang')->get(),
            'dosenList' => User::dosen()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, JadwalTetap $jadwal)
    {
        $data = $request->validate($this->rules());

        if ($msg = $this->cekSemuaKonflik($data, $jadwal->id)) {
            return back()->withInput()->with('error', $msg);
        }

        $jadwal->update($data);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(JadwalTetap $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    public function formAlokasi()
    {
        return view('admin.jadwal.alokasi', [
            'dosenList' => User::dosen()->orderBy('name')->get(),
        ]);
    }

    public function prosesAlokasi(Request $request)
    {
        /**
         * BUG FIX 8: prosesAlokasi() tidak validasi input sama sekali.
         * Jika $request->jadwal null atau bukan array, jadwalkanBatch()
         * mengembalikan array kosong tanpa error yang informatif.
         * FIX: tambahkan validasi input dan redirect dengan pesan lengkap.
         */
        $request->validate([
            'tahun_akademik'        => 'required|string',
            'semester_ganjil_genap' => 'required|in:ganjil,genap',
            'jadwal'                => 'required|array|min:1',
            'jadwal.*.mata_kuliah'  => 'required|string',
            'jadwal.*.hari'         => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jadwal.*.jam_mulai'    => 'required|date_format:H:i',
            'jadwal.*.jam_selesai'  => 'required|date_format:H:i',
            'jadwal.*.dosen_id'     => 'required|exists:users,id',
        ]);

        $hasil = $this->greedy->jadwalkanBatch($request->jadwal);

        foreach ($hasil['berhasil'] as $item) {
            JadwalTetap::create([
                'ruang_kelas_id'        => $item['ruang_dialokasikan']->id,
                'dosen_id'              => $item['dosen_id'],
                'mata_kuliah'           => $item['mata_kuliah'],
                'kelas'                 => $item['kelas'] ?? 'A',
                'program_studi'         => $item['program_studi'] ?? 'Teknik Informatika',
                'semester'              => $item['semester'] ?? 1,
                'tahun_akademik'        => $request->tahun_akademik,
                'semester_ganjil_genap' => $request->semester_ganjil_genap,
                'hari'                  => $item['hari'],
                'jam_mulai'             => $item['jam_mulai'],
                'jam_selesai'           => $item['jam_selesai'],
                'sks'                   => $item['sks'] ?? 2,
                'status'                => 'aktif',
            ]);
        }

        $berhasil = count($hasil['berhasil']);
        $gagal    = count($hasil['gagal']);
        $pesan    = "Alokasi selesai: {$berhasil} jadwal berhasil dialokasikan.";
        if ($gagal > 0) {
            $pesan .= " {$gagal} jadwal gagal (tidak ada ruang tersedia).";
            return redirect()->route('admin.jadwal.index')->with('warning', $pesan);
        }

        return redirect()->route('admin.jadwal.index')->with('success', $pesan);
    }

    public function formImport()
    {
        return view('admin.jadwal.import', [
            'ruangList' => RuangKelas::aktif()->orderBy('kode_ruang')->get(),
            'dosenList' => User::dosen()->orderBy('name')->get(),
        ]);
    }

    public function prosesImport(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048',
        ], [
            'file_csv.required' => 'File CSV wajib diunggah.',
            'file_csv.mimes'    => 'File harus berformat .csv.',
        ]);

        $file = fopen($request->file('file_csv')->getRealPath(), 'r');

        try {
            $barisPertama = fgets($file);
            rewind($file);
            $delimiter = (substr_count($barisPertama, ';') >= substr_count($barisPertama, ',')) ? ';' : ',';

            $header = fgetcsv($file, 0, $delimiter);
            if (!$header) {
                return back()->with('error', 'File CSV kosong atau tidak valid.');
            }
            $header = array_map(fn($h) => strtolower(trim($h)), $header);

            $berhasil  = 0;
            $gagal     = 0;
            $gagalList = [];
            $noRow     = 1;

            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $noRow++;
                if (empty(array_filter(array_map('trim', $row)))) continue;

                while (count($row) < count($header)) $row[] = '';
                $data = array_combine($header, array_slice($row, 0, count($header)));

                $kodeRuang = trim($data['kode_ruang'] ?? '');
                $kodeNorm  = strtoupper(preg_replace('/\s*[-–]\s*/', '-', $kodeRuang));
                $ruang     = RuangKelas::get()->first(
                    fn($r) => strtoupper(preg_replace('/\s*[-–]\s*/', '-', $r->kode_ruang)) === $kodeNorm
                );

                $emailDosen = trim($data['email_dosen'] ?? '');
                $dosen = null;
                if ($emailDosen) {
                    $dosen = User::where('email', $emailDosen)->first();
                }
                if (!$dosen && !empty($data['nama_dosen'] ?? '')) {
                    $dosen = User::where('name', 'like', '%'.trim($data['nama_dosen']).'%')->first();
                }

                if (!$ruang || !$dosen) {
                    $gagal++;
                    $alasan = [];
                    if (!$ruang)  $alasan[] = "Ruang '{$kodeRuang}' tidak ditemukan";
                    if (!$dosen)  $alasan[] = "Dosen '{$emailDosen}' tidak ditemukan";
                    $gagalList[] = "Baris {$noRow}: ".($data['mata_kuliah']??'?')." — ".implode(', ', $alasan).".";
                    continue;
                }

                $hari       = strtolower(trim($data['hari'] ?? ''));
                $jamMulai   = trim($data['jam_mulai'] ?? '');
                $jamSelesai = trim($data['jam_selesai'] ?? '');
                $tahun      = trim($data['tahun_akademik'] ?? '');
                $semGG      = strtolower(trim($data['semester_ganjil_genap'] ?? 'ganjil'));

                $hariMap = ['senin','selasa','rabu','kamis','jumat','sabtu'];
                if (!in_array($hari, $hariMap)) {
                    $gagal++;
                    $gagalList[] = "Baris {$noRow}: ".($data['mata_kuliah']??'?')." — Hari '{$hari}' tidak valid.";
                    continue;
                }

                $konflikRuang = JadwalTetap::where('ruang_kelas_id', $ruang->id)
                    ->where('hari', $hari)->where('tahun_akademik', $tahun)
                    ->where('semester_ganjil_genap', $semGG)->where('status', 'aktif')
                    ->where(fn($q) => $q->where('jam_mulai','<',$jamSelesai)->where('jam_selesai','>',$jamMulai))
                    ->exists();

                if ($konflikRuang) {
                    $gagal++;
                    $gagalList[] = "Baris {$noRow}: ".($data['mata_kuliah']??'?')." — Ruang {$ruang->kode_ruang} bentrok pada {$hari} {$jamMulai}–{$jamSelesai}.";
                    continue;
                }

                JadwalTetap::create([
                    'ruang_kelas_id'        => $ruang->id,
                    'dosen_id'              => $dosen->id,
                    'mata_kuliah'           => trim($data['mata_kuliah'] ?? ''),
                    'kode_mk'               => trim($data['kode_mk']     ?? '') ?: null,
                    'kelas'                 => trim($data['kelas']        ?? 'A'),
                    'program_studi'         => trim($data['program_studi']?? 'Teknik Informatika'),
                    'semester'              => max(1, (int)($data['semester'] ?? 1)),
                    'tahun_akademik'        => $tahun,
                    'semester_ganjil_genap' => $semGG,
                    'hari'                  => $hari,
                    'jam_mulai'             => $jamMulai,
                    'jam_selesai'           => $jamSelesai,
                    'sks'                   => max(1, (int)($data['sks'] ?? 2)),
                    'status'                => 'aktif',
                ]);
                $berhasil++;
            }
        } finally {
            fclose($file);
        }

        $pesan = "Import selesai: <strong>{$berhasil}</strong> jadwal berhasil.";
        if ($gagal > 0) {
            $pesan .= " <strong>{$gagal}</strong> baris gagal.";
            return redirect()->route('admin.jadwal.index')
                ->with('warning', $pesan)->with('import_errors', $gagalList);
        }
        return redirect()->route('admin.jadwal.index')->with('success', $pesan);
    }

    public function apiCekKonflik(Request $request)
    {
        // BUG FIX A: response harus include 'detail' karena JS form memakai data.detail
        // Sebelumnya hanya return {'konflik': bool} tanpa pesan → tampil 'undefined'
        $jadwalBentrok = $this->cekKonflikJadwal(
            $request->ruang_kelas_id, $request->hari,
            $request->jam_mulai, $request->jam_selesai,
            $request->tahun_akademik, $request->semester_ganjil_genap,
            $request->kecuali_id
        );

        if ($jadwalBentrok) {
            $detail = "Ruang sudah dipakai untuk <strong>{$jadwalBentrok->mata_kuliah}</strong>"
                    . " Kelas {$jadwalBentrok->kelas}"
                    . " ({$jadwalBentrok->jam_mulai}–{$jadwalBentrok->jam_selesai}).";
            return response()->json(['konflik' => true, 'detail' => $detail]);
        }

        // Cek juga konflik dosen jika dosen_id dikirim
        if ($request->filled('dosen_id')) {
            $dosenBentrok = $this->cekKonflikDosen(
                $request->dosen_id, $request->hari,
                $request->jam_mulai, $request->jam_selesai,
                $request->tahun_akademik, $request->semester_ganjil_genap,
                $request->kecuali_id
            );
            if ($dosenBentrok) {
                $detail = "Dosen sudah mengajar <strong>{$dosenBentrok->mata_kuliah}</strong>"
                        . " ({$dosenBentrok->jam_mulai}–{$dosenBentrok->jam_selesai}).";
                return response()->json(['konflik' => true, 'detail' => $detail]);
            }
        }

        $jamMulai   = $request->jam_mulai;
        $jamSelesai = $request->jam_selesai;
        return response()->json([
            'konflik' => false,
            'detail'  => "Ruang tersedia pada {$jamMulai}–{$jamSelesai}. Silakan simpan jadwal.",
        ]);
    }

    // ── Private helpers ───────────────────────────────────

    private function rules(): array
    {
        /**
         * BUG FIX 9: rules() tidak ada type validation sama sekali —
         * 'required' saja tidak cukup, integer bisa dikirim sebagai string kosong.
         * FIX: tambahkan tipe data, range, dan exists check.
         */
        return [
            'ruang_kelas_id'        => 'required|exists:ruang_kelas,id',
            'dosen_id'              => 'required|exists:users,id',
            'mata_kuliah'           => 'required|string|max:200',
            'kode_mk'               => 'nullable|string|max:20',
            'kelas'                 => 'required|string|max:5',
            'program_studi'         => 'required|string|max:100',
            'semester'              => 'required|integer|min:1|max:14',
            'tahun_akademik'        => 'required|string|max:10',
            'semester_ganjil_genap' => 'required|in:ganjil,genap',
            'hari'                  => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai'             => 'required|date_format:H:i',
            'jam_selesai'           => 'required|date_format:H:i|after:jam_mulai',
            'sks'                   => 'required|integer|min:1|max:6',
            'status'                => 'required|in:aktif,nonaktif',
        ];
    }

    private function cekSemuaKonflik(array $data, ?int $exceptId = null): ?string
    {
        $konflikRuang = $this->cekKonflikJadwal(
            $data['ruang_kelas_id'], $data['hari'],
            $data['jam_mulai'], $data['jam_selesai'],
            $data['tahun_akademik'], $data['semester_ganjil_genap'],
            $exceptId
        );
        if ($konflikRuang) {
            return "Ruang {$konflikRuang->ruangKelas->kode_ruang} sudah dipakai untuk {$konflikRuang->mata_kuliah} pada {$data['hari']} {$data['jam_mulai']}–{$data['jam_selesai']}.";
        }

        $konflikDosen = $this->cekKonflikDosen(
            $data['dosen_id'], $data['hari'],
            $data['jam_mulai'], $data['jam_selesai'],
            $data['tahun_akademik'], $data['semester_ganjil_genap'],
            $exceptId
        );
        if ($konflikDosen) {
            return "Dosen sudah mengajar {$konflikDosen->mata_kuliah} pada {$data['hari']} {$data['jam_mulai']}–{$data['jam_selesai']}.";
        }

        return null;
    }

    private function cekKonflikJadwal($ruangId, $hari, $mulai, $selesai, $tahun, $semester, $exceptId = null): ?JadwalTetap
    {
        return JadwalTetap::with('ruangKelas')
            ->where('ruang_kelas_id', $ruangId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where(fn($q) => $q->where('jam_mulai', '<', $selesai)->where('jam_selesai', '>', $mulai))
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }

    private function cekKonflikDosen($dosenId, $hari, $mulai, $selesai, $tahun, $semester, $exceptId = null): ?JadwalTetap
    {
        return JadwalTetap::where('dosen_id', $dosenId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where(fn($q) => $q->where('jam_mulai', '<', $selesai)->where('jam_selesai', '>', $mulai))
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }
}