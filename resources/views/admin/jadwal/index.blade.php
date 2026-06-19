@extends('layouts.app')
@section('title', 'Jadwal Tetap')
@section('page_title', 'Jadwal Tetap')
@section('page_subtitle', 'Kelola jadwal kuliah rutin per semester')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Jadwal Tetap</h1>
        <p class="text-sm text-slate-400 mt-0.5">{{ $jadwalList->total() }} jadwal terdaftar</p>
    </div>
    {{-- Tombol aksi — wrap di mobile --}}
    <div class="grid grid-cols-2 sm:flex gap-2">
        <a href="{{ route('admin.jadwal.import') }}"
           class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-3 py-2.5 rounded-xl transition text-xs sm:text-sm">
            <i class="fa-solid fa-file-csv"></i> <span>CSV</span>
        </a>
        <a href="{{ route('admin.jadwal.excel-import') }}"
           class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-3 py-2.5 rounded-xl transition text-xs sm:text-sm">
            <i class="fa-solid fa-file-excel"></i> <span>Excel</span>
        </a>
        <a href="{{ route('admin.jadwal.alokasi') }}"
           class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-medium px-3 py-2.5 rounded-xl transition text-xs sm:text-sm">
            <i class="fa-solid fa-wand-magic-sparkles"></i> <span class="hidden sm:inline">Alokasi</span> <span class="sm:hidden">Greedy</span>
        </a>
        <a href="{{ route('admin.jadwal.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-2.5 rounded-xl transition text-xs sm:text-sm shadow-sm shadow-blue-200">
            <i class="fa-solid fa-plus text-xs"></i> Tambah
        </a>
    </div>
</div>

{{-- ✨ Detail Error Import — sebelumnya hilang karena layout hanya menampilkan toast --}}
@if(session('import_errors') && count(session('import_errors')) > 0)
<div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-5" id="panel-import-errors">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
            </div>
            <div>
                <h3 class="font-bold text-red-800 text-sm">
                    {{ count(session('import_errors')) }} Baris Gagal Diimport
                </h3>
                <p class="text-xs text-red-500 mt-0.5">Perbaiki data di Excel sesuai catatan berikut, lalu import ulang.</p>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('panel-import-errors').remove()"
            class="text-red-400 hover:text-red-600 transition shrink-0">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="max-h-80 overflow-y-auto bg-white rounded-xl border border-red-100 divide-y divide-red-50">
        @foreach(session('import_errors') as $i => $err)
        <div class="px-4 py-2.5 text-sm text-slate-700 flex gap-2">
            <span class="text-red-400 font-mono text-xs shrink-0 mt-0.5">{{ $i + 1 }}.</span>
            <span>{{ $err }}</span>
        </div>
        @endforeach
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ route('admin.jadwal.excel-import') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
            <i class="fa-solid fa-arrow-rotate-left"></i> Import Ulang
        </a>
    </div>
</div>
@endif
<div class="grid grid-cols-3 gap-3 mb-5">
    @foreach([
        ['Total',    $stats['total'],    'blue',  'fa-calendar-days'],
        ['Aktif',    $stats['aktif'],    'green', 'fa-circle-check'],
        ['Nonaktif', $stats['nonaktif'], 'slate', 'fa-circle-xmark'],
    ] as [$lbl,$val,$col,$ico])
    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 bg-{{ $col }}-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid {{ $ico }} text-{{ $col }}-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xl font-black text-slate-800">{{ $val }}</p>
            <p class="text-[11px] text-slate-400">{{ $lbl }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.jadwal.index') }}" class="flex flex-wrap gap-3">
        <div class="w-full sm:flex-1 sm:min-w-[180px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari mata kuliah, kode MK..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <select name="hari" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Hari</option>
            @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $h)
            <option value="{{ $h }}" {{ request('hari')==$h?'selected':'' }}>{{ ucfirst($h) }}</option>
            @endforeach
        </select>
        <select name="tahun_akademik" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Tahun</option>
            @foreach($tahunAkademikList as $t)
            <option value="{{ $t }}" {{ request('tahun_akademik')==$t?'selected':'' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="semester_ganjil_genap" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Ganjil/Genap</option>
            <option value="ganjil" {{ request('semester_ganjil_genap')=='ganjil'?'selected':'' }}>Ganjil</option>
            <option value="genap"  {{ request('semester_ganjil_genap')=='genap'?'selected':'' }}>Genap</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-filter text-xs"></i> Filter
        </button>
        @if(request()->anyFilled(['search','hari','tahun_akademik','semester_ganjil_genap','dosen_id']))
        <a href="{{ route('admin.jadwal.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm transition">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>
</div>

@if($jadwalList->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-calendar-xmark text-slate-300 text-3xl"></i>
    </div>
    <p class="font-semibold text-slate-500">Belum ada jadwal tetap</p>
    <a href="{{ route('admin.jadwal.create') }}" class="text-blue-600 text-sm hover:underline mt-2 inline-block">+ Tambah jadwal pertama</a>
</div>
@else

{{-- Desktop table --}}
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    @foreach(['Hari & Jam','Mata Kuliah','Dosen','Ruang','Kelas & Prodi','Tahun / Sem','Status',''] as $h)
                    <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($jadwalList as $jadwal)
                @php
                    $hariColors = ['senin'=>'blue','selasa'=>'indigo','rabu'=>'teal','kamis'=>'orange','jumat'=>'green','sabtu'=>'pink'];
                    $hc = $hariColors[$jadwal->hari] ?? 'slate';
                @endphp
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-4 py-3.5">
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-{{ $hc }}-100 text-{{ $hc }}-700 capitalize">{{ $jadwal->hari }}</span>
                        <p class="text-xs text-slate-500 mt-1.5 font-mono">{{ substr($jadwal->jam_mulai,0,5) }}–{{ substr($jadwal->jam_selesai,0,5) }}</p>
                    </td>
                    <td class="px-4 py-3.5 max-w-[180px]">
                        <p class="font-semibold text-slate-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jadwal->kode_mk }}</p>
                        <p class="text-xs text-slate-400">{{ $jadwal->sks }} SKS</p>
                    </td>
                    <td class="px-4 py-3.5 max-w-[140px]">
                        <p class="text-sm text-slate-700 truncate">{{ $jadwal->dosen->name ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg text-xs">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}</span>
                        <p class="text-xs text-slate-400 mt-1">{{ $jadwal->ruangKelas->kapasitas ?? '-' }} kursi</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="text-sm font-medium text-slate-700">Kelas {{ $jadwal->kelas }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $jadwal->program_studi }}</p>
                        <p class="text-xs text-slate-400">Sem {{ $jadwal->semester }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="text-sm text-slate-600">{{ $jadwal->tahun_akademik }}</p>
                        <span class="text-xs font-medium capitalize {{ $jadwal->semester_ganjil_genap=='ganjil' ? 'text-orange-500' : 'text-blue-500' }}">
                            {{ $jadwal->semester_ganjil_genap }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize
                            {{ $jadwal->status=='aktif' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $jadwal->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.jadwal.edit', $jadwal) }}"
                               class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($jadwalList->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $jadwalList->links() }}</div>
    @endif
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @foreach($jadwalList as $jadwal)
    @php
        $hariColors = ['senin'=>'blue','selasa'=>'indigo','rabu'=>'teal','kamis'=>'orange','jumat'=>'green','sabtu'=>'pink'];
        $hc = $hariColors[$jadwal->hari] ?? 'slate';
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-{{ $hc }}-100 text-{{ $hc }}-700 capitalize">{{ $jadwal->hari }}</span>
                <span class="text-xs font-mono text-slate-600 font-semibold">{{ substr($jadwal->jam_mulai,0,5) }}–{{ substr($jadwal->jam_selesai,0,5) }}</span>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full capitalize shrink-0
                {{ $jadwal->status=='aktif' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $jadwal->status }}
            </span>
        </div>
        <p class="font-semibold text-slate-800 text-sm">{{ $jadwal->mata_kuliah }}</p>
        <div class="flex items-center gap-2 mt-2 flex-wrap text-xs text-slate-500">
            <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}</span>
            <span>Kelas {{ $jadwal->kelas }}</span>
            <span>{{ $jadwal->sks }} SKS</span>
            <span>{{ $jadwal->tahun_akademik }}</span>
        </div>
        <p class="text-xs text-slate-400 mt-1">{{ $jadwal->dosen->name ?? '-' }}</p>
        <div class="flex gap-2 mt-3 pt-3 border-t border-slate-50">
            <a href="{{ route('admin.jadwal.edit', $jadwal) }}"
               class="flex-1 flex items-center justify-center gap-1.5 bg-slate-50 hover:bg-green-50 text-slate-600 hover:text-green-600 text-xs font-medium py-2 rounded-lg transition">
                <i class="fa-solid fa-pen text-[11px]"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                  onsubmit="return confirm('Hapus jadwal ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="flex items-center justify-center gap-1.5 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-600 text-xs font-medium px-3 py-2 rounded-lg transition">
                    <i class="fa-solid fa-trash text-[11px]"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
    @if($jadwalList->hasPages())
    <div class="pt-2">{{ $jadwalList->links() }}</div>
    @endif
</div>
@endif

@endsection