<?php

namespace App\Http\Controllers;

use App\Models\RuangKelas;
use Illuminate\Http\Request;

class RuangKelasController extends Controller
{
    public function index(Request $request)
    {
        $query = RuangKelas::query();

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_ruang',  'like', '%' . $request->search . '%')
                  ->orWhere('nama_ruang', 'like', '%' . $request->search . '%')
                  ->orWhere('gedung',     'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('jenis'))  $query->where('jenis',  $request->jenis);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('gedung')) $query->where('gedung', $request->gedung);

        $ruangList = $query->orderBy('gedung')->orderBy('kode_ruang')->paginate(10)->withQueryString();

        $gedungList = RuangKelas::select('gedung')->distinct()->orderBy('gedung')->pluck('gedung');

        $stats = [
            'total'       => RuangKelas::count(),
            'aktif'       => RuangKelas::where('status', 'aktif')->count(),
            'nonaktif'    => RuangKelas::where('status', 'nonaktif')->count(),
            'perbaikan'   => RuangKelas::where('status', 'perbaikan')->count(),
        ];

        return view('admin.ruang.index', compact('ruangList', 'gedungList', 'stats'));
    }

    public function create()
    {
        $gedungList = RuangKelas::select('gedung')->distinct()->orderBy('gedung')->pluck('gedung');
        return view('admin.ruang.create', compact('gedungList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_ruang'  => 'required|string|max:20|unique:ruang_kelas,kode_ruang',
            'nama_ruang'  => 'required|string|max:100',
            'gedung'      => 'required|string|max:50',
            'lantai'      => 'required|integer|min:1|max:20',
            'kapasitas'   => 'required|integer|min:1|max:1000',
            'jenis'       => 'required|in:kelas,laboratorium,aula,seminar',
            'fasilitas'   => 'nullable|array',
            'fasilitas.*' => 'string',
            'status'      => 'required|in:aktif,nonaktif,perbaikan',
            'keterangan'  => 'nullable|string|max:500',
        ], [
            'kode_ruang.unique' => 'Kode ruang sudah digunakan.',
            'kapasitas.min'     => 'Kapasitas minimal 1 orang.',
        ]);

        RuangKelas::create($validated);

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang {$validated['kode_ruang']} berhasil ditambahkan.");
    }

    public function show(RuangKelas $ruang)
    {
        $ruang->load(['jadwalTetap.dosen', 'reservasi' => function ($q) {
            $q->where('status', 'disetujui')
              ->whereDate('tanggal', '>=', today())
              ->orderBy('tanggal')->orderBy('jam_mulai')
              ->take(10);
        }]);

        return view('admin.ruang.show', compact('ruang'));
    }

    public function edit(RuangKelas $ruang)
    {
        $gedungList = RuangKelas::select('gedung')->distinct()->orderBy('gedung')->pluck('gedung');
        return view('admin.ruang.edit', compact('ruang', 'gedungList'));
    }

    public function update(Request $request, RuangKelas $ruang)
    {
        $validated = $request->validate([
            'kode_ruang'  => 'required|string|max:20|unique:ruang_kelas,kode_ruang,' . $ruang->id,
            'nama_ruang'  => 'required|string|max:100',
            'gedung'      => 'required|string|max:50',
            'lantai'      => 'required|integer|min:1|max:20',
            'kapasitas'   => 'required|integer|min:1|max:1000',
            'jenis'       => 'required|in:kelas,laboratorium,aula,seminar',
            'fasilitas'   => 'nullable|array',
            'fasilitas.*' => 'string',
            'status'      => 'required|in:aktif,nonaktif,perbaikan',
            'keterangan'  => 'nullable|string|max:500',
        ]);

        $ruang->update($validated);

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang {$ruang->kode_ruang} berhasil diperbarui.");
    }

    public function destroy(RuangKelas $ruang)
    {
        // Cek apakah masih ada jadwal aktif
        if ($ruang->jadwalTetap()->where('status', 'aktif')->exists()) {
            return back()->with('error', "Ruang {$ruang->kode_ruang} tidak dapat dihapus karena masih memiliki jadwal aktif.");
        }

        // Cek reservasi mendatang yang disetujui
        if ($ruang->reservasi()->where('status', 'disetujui')->whereDate('tanggal', '>=', today())->exists()) {
            return back()->with('error', "Ruang {$ruang->kode_ruang} tidak dapat dihapus karena masih memiliki reservasi mendatang.");
        }

        $kode = $ruang->kode_ruang;
        $ruang->delete();

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang {$kode} berhasil dihapus.");
    }

    // API: cek ketersediaan ruang (dipakai form reservasi + greedy)
    public function cekKetersediaan(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i|after:jam_mulai',
        ]);

        $ruangList = RuangKelas::aktif()->get()->map(function ($ruang) use ($request) {
            return [
                'id'          => $ruang->id,
                'kode_ruang'  => $ruang->kode_ruang,
                'nama_ruang'  => $ruang->nama_ruang,
                'kapasitas'   => $ruang->kapasitas,
                'jenis'       => $ruang->jenis,
                'fasilitas'   => $ruang->fasilitas,
                'tersedia'    => $ruang->tersediaPada(
                    $request->tanggal,
                    $request->jam_mulai,
                    $request->jam_selesai
                ),
            ];
        });

        return response()->json($ruangList);
    }
}
