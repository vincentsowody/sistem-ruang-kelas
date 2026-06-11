@extends('layouts.app')
@section('title', 'Manajemen Jadwal Tetap')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Jadwal Tetap</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola jadwal kuliah rutin per semester</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.jadwal.import') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </a>
                <a href="{{ route('admin.jadwal.excel-import') }}"
        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
            <i class="fa-solid fa-file-excel"></i> Import Excel
        </a>
        <a href="{{ route('admin.jadwal.alokasi') }}"
           class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Alokasi Greedy
        </a>
        <a href="{{ route('admin.jadwal.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
            <i class="fa-solid fa-plus"></i> Tambah Jadwal
        </a>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 mb-5 flex items-start gap-3">
    <i class="fa-solid fa-circle-check text-green-500 mt-0.5 shrink-0"></i>
    <div>
        <p class="font-medium">{{ session('success') }}</p>
        @if(session('import_errors'))
        <details class="mt-2">
            <summary class="text-sm text-red-600 cursor-pointer font-medium">
                Lihat {{ count(session('import_errors')) }} baris yang gagal
            </summary>
            <ul class="mt-2 space-y-1">
                @foreach(session('import_errors') as $err)
                <li class="text-xs font-mono bg-red-50 text-red-700 px-3 py-1.5 rounded-lg">{{ $err }}</li>
                @endforeach
            </ul>
        </details>
        @endif
    </div>
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 mb-5 flex items-center gap-3">
    <i class="fa-solid fa-triangle-exclamation text-red-500 shrink-0"></i>
    <p>{{ session('error') }}</p>
</div>
@endif

{{-- Statistik --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label'=>'Total Jadwal', 'value'=>$stats['total'],    'color'=>'blue',  'icon'=>'fa-calendar-days'],
        ['label'=>'Aktif',        'value'=>$stats['aktif'],    'color'=>'green', 'icon'=>'fa-circle-check'],
        ['label'=>'Nonaktif',     'value'=>$stats['nonaktif'], 'color'=>'gray',  'icon'=>'fa-circle-xmark'],
    ] as $s)
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500">{{ $s['label'] }}</span>
            <i class="fa-solid {{ $s['icon'] }} text-{{ $s['color'] }}-400 text-sm"></i>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $s['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="{{ route('admin.jadwal.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[180px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari mata kuliah, kode MK..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="hari" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Hari</option>
            @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $h)
            <option value="{{ $h }}" {{ request('hari')==$h?'selected':'' }}>{{ ucfirst($h) }}</option>
            @endforeach
        </select>
        <select name="tahun_akademik" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Tahun</option>
            @foreach($tahunAkademikList as $t)
            <option value="{{ $t }}" {{ request('tahun_akademik')==$t?'selected':'' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="semester_ganjil_genap" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Ganjil/Genap</option>
            <option value="ganjil" {{ request('semester_ganjil_genap')=='ganjil'?'selected':'' }}>Ganjil</option>
            <option value="genap"  {{ request('semester_ganjil_genap')=='genap'?'selected':'' }}>Genap</option>
        </select>
        <select name="dosen_id" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Dosen</option>
            @foreach($dosenList as $d)
            <option value="{{ $d->id }}" {{ request('dosen_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-filter mr-1"></i> Filter
        </button>
        @if(request()->anyFilled(['search','hari','tahun_akademik','semester_ganjil_genap','dosen_id','status']))
        <a href="{{ route('admin.jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Hari & Jam</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Mata Kuliah</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Dosen</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Ruang</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kelas & Prodi</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Tahun / Sem</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($jadwalList as $jadwal)
                @php
                    $hariWarna = match($jadwal->hari) {
                        'senin'  => 'bg-blue-100 text-blue-700',
                        'selasa' => 'bg-purple-100 text-purple-700',
                        'rabu'   => 'bg-teal-100 text-teal-700',
                        'kamis'  => 'bg-orange-100 text-orange-700',
                        'jumat'  => 'bg-green-100 text-green-700',
                        'sabtu'  => 'bg-pink-100 text-pink-700',
                        default  => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $hariWarna }} capitalize">
                            {{ $jadwal->hari }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1.5 font-mono">
                            {{ substr($jadwal->jam_mulai,0,5) }} – {{ substr($jadwal->jam_selesai,0,5) }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $jadwal->mata_kuliah }}</p>
                        @if($jadwal->kode_mk)
                        <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $jadwal->kode_mk }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $jadwal->sks }} SKS</p>
                    </td>
                    <td class="px-5 py-4 text-gray-600 text-sm max-w-[150px]">
                        <p class="truncate">{{ $jadwal->dosen->name }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg text-xs">
                            {{ $jadwal->ruangKelas->kode_ruang }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $jadwal->ruangKelas->kapasitas }} kursi</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-700 font-medium">Kelas {{ $jadwal->kelas }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $jadwal->program_studi }}</p>
                        <p class="text-xs text-gray-400">Semester {{ $jadwal->semester }}</p>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">
                        <p>{{ $jadwal->tahun_akademik }}</p>
                        <span class="text-xs capitalize {{ $jadwal->semester_ganjil_genap == 'ganjil' ? 'text-orange-500' : 'text-blue-500' }}">
                            {{ $jadwal->semester_ganjil_genap }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full capitalize
                            {{ $jadwal->status == 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $jadwal->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.jadwal.edit', $jadwal) }}"
                               class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                                  onsubmit="return confirm('Hapus jadwal {{ $jadwal->mata_kuliah }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <i class="fa-solid fa-calendar-xmark text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-400">Belum ada jadwal tetap</p>
                        <a href="{{ route('admin.jadwal.create') }}" class="text-blue-600 text-sm hover:underline mt-1 inline-block">
                            Tambah jadwal pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($jadwalList->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $jadwalList->links() }}</div>
    @endif
</div>

@endsection
