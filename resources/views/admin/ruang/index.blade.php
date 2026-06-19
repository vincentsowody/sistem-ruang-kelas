@extends('layouts.app')
@section('title', 'Manajemen Ruang Kelas')
@section('page_title', 'Ruang Kelas')
@section('page_subtitle', 'Kelola ruang kelas dan laboratorium')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Manajemen Ruang Kelas</h1>
        <p class="text-sm text-slate-400 mt-0.5">{{ $ruangList->total() }} ruang terdaftar</p>
    </div>
    <a href="{{ route('admin.ruang.create') }}"
       class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl transition text-sm shadow-sm shadow-blue-200 w-full sm:w-auto">
        <i class="fa-solid fa-plus text-xs"></i> Tambah Ruang
    </a>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @foreach([
        ['Total Ruang',  $stats['total'],     'blue',   'fa-door-open'],
        ['Aktif',        $stats['aktif'],     'green',  'fa-circle-check'],
        ['Nonaktif',     $stats['nonaktif'],  'slate',  'fa-circle-xmark'],
        ['Perbaikan',    $stats['perbaikan'], 'amber',  'fa-wrench'],
    ] as [$lbl, $val, $col, $ico])
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
    <form method="GET" action="{{ route('admin.ruang.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[160px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode, nama, gedung..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <select name="jenis" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Jenis</option>
            @foreach(['kelas','laboratorium','aula','seminar'] as $j)
            <option value="{{ $j }}" {{ request('jenis')==$j?'selected':'' }}>{{ ucfirst($j) }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            @foreach(['aktif','nonaktif','perbaikan'] as $s)
            <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="gedung" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Gedung</option>
            @foreach($gedungList as $g)
            <option value="{{ $g }}" {{ request('gedung')==$g?'selected':'' }}>{{ $g }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-filter text-xs"></i> Filter
        </button>
        @if(request()->anyFilled(['search','jenis','status','gedung']))
        <a href="{{ route('admin.ruang.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm transition">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>
</div>

@if($ruangList->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-door-open text-slate-300 text-3xl"></i>
    </div>
    <p class="font-semibold text-slate-500">Tidak ada data ruang</p>
    <a href="{{ route('admin.ruang.create') }}" class="text-blue-600 text-sm hover:underline mt-2 inline-block">+ Tambah ruang pertama</a>
</div>
@else

{{-- Desktop table --}}
<div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    @foreach(['Kode','Nama Ruang','Gedung','Kapasitas','Jenis','Fasilitas','Status','Aksi'] as $h)
                    <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide {{ $h==='Aksi'?'text-center':'' }}">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($ruangList as $ruang)
                @php
                    [$jBg,$jTxt] = match($ruang->jenis) {
                        'kelas'        => ['bg-blue-100',   'text-blue-700'],
                        'laboratorium' => ['bg-purple-100', 'text-purple-700'],
                        'aula'         => ['bg-orange-100', 'text-orange-700'],
                        'seminar'      => ['bg-teal-100',   'text-teal-700'],
                        default        => ['bg-slate-100',  'text-slate-600'],
                    };
                    [$sBg,$sTxt] = match($ruang->status) {
                        'aktif'     => ['bg-green-100', 'text-green-700'],
                        'perbaikan' => ['bg-amber-100', 'text-amber-700'],
                        default     => ['bg-slate-100', 'text-slate-500'],
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-4 py-3.5">
                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg text-xs">{{ $ruang->kode_ruang }}</span>
                    </td>
                    <td class="px-4 py-3.5 font-medium text-slate-800">{{ $ruang->nama_ruang }}</td>
                    <td class="px-4 py-3.5">
                        <p class="text-sm text-slate-700">{{ $ruang->gedung }}</p>
                        <p class="text-xs text-slate-400">Lt. {{ $ruang->lantai }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-slate-300 text-xs"></i>
                            <span class="font-semibold text-slate-700">{{ $ruang->kapasitas }}</span>
                            <span class="text-xs text-slate-400">kursi</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $jBg }} {{ $jTxt }} capitalize">{{ $ruang->jenis }}</span>
                    </td>
                    <td class="px-4 py-3.5 max-w-[160px]">
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($ruang->fasilitas ?? [], 0, 3) as $fas)
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">{{ ucfirst(str_replace('_',' ',$fas)) }}</span>
                            @endforeach
                            @if(count($ruang->fasilitas ?? []) > 3)
                            <span class="text-[10px] bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded">+{{ count($ruang->fasilitas)-3 }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $sBg }} {{ $sTxt }} capitalize">{{ $ruang->status }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.ruang.show', $ruang) }}" title="Detail"
                               class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('admin.ruang.edit', $ruang) }}" title="Edit"
                               class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.ruang.destroy', $ruang) }}"
                                  onsubmit="return confirm('Hapus ruang {{ $ruang->kode_ruang }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
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
    @if($ruangList->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $ruangList->links() }}</div>
    @endif
</div>

{{-- Mobile & tablet cards --}}
<div class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-3">
    @foreach($ruangList as $ruang)
    @php
        [$jBg,$jTxt] = match($ruang->jenis) {
            'kelas'        => ['bg-blue-100',   'text-blue-700'],
            'laboratorium' => ['bg-purple-100', 'text-purple-700'],
            'aula'         => ['bg-orange-100', 'text-orange-700'],
            'seminar'      => ['bg-teal-100',   'text-teal-700'],
            default        => ['bg-slate-100',  'text-slate-600'],
        };
        [$sBg,$sTxt] = match($ruang->status) {
            'aktif'     => ['bg-green-100', 'text-green-700'],
            'perbaikan' => ['bg-amber-100', 'text-amber-700'],
            default     => ['bg-slate-100', 'text-slate-500'],
        };
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        {{-- Top row --}}
        <div class="flex items-start justify-between gap-2 mb-3">
            <div>
                <span class="font-mono font-black text-blue-700 text-base">{{ $ruang->kode_ruang }}</span>
                <p class="text-sm text-slate-600 mt-0.5">{{ $ruang->nama_ruang }}</p>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $sBg }} {{ $sTxt }} capitalize shrink-0">{{ $ruang->status }}</span>
        </div>
        {{-- Info chips --}}
        <div class="flex items-center gap-2 flex-wrap mb-3">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $jBg }} {{ $jTxt }} capitalize">{{ $ruang->jenis }}</span>
            <span class="text-xs text-slate-500 flex items-center gap-1">
                <i class="fa-solid fa-building text-slate-300 text-[10px]"></i> {{ $ruang->gedung }} Lt.{{ $ruang->lantai }}
            </span>
            <span class="text-xs text-slate-500 flex items-center gap-1">
                <i class="fa-solid fa-users text-slate-300 text-[10px]"></i> {{ $ruang->kapasitas }} kursi
            </span>
        </div>
        {{-- Fasilitas --}}
        @if(!empty($ruang->fasilitas))
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach(array_slice($ruang->fasilitas, 0, 4) as $fas)
            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">{{ ucfirst(str_replace('_',' ',$fas)) }}</span>
            @endforeach
            @if(count($ruang->fasilitas) > 4)
            <span class="text-[10px] bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded">+{{ count($ruang->fasilitas)-4 }}</span>
            @endif
        </div>
        @endif
        {{-- Actions --}}
        <div class="flex gap-2 pt-3 border-t border-slate-50">
            <a href="{{ route('admin.ruang.show', $ruang) }}"
               class="flex-1 flex items-center justify-center gap-1.5 bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 text-xs font-medium py-2 rounded-lg transition">
                <i class="fa-solid fa-eye text-[11px]"></i> Detail
            </a>
            <a href="{{ route('admin.ruang.edit', $ruang) }}"
               class="flex-1 flex items-center justify-center gap-1.5 bg-slate-50 hover:bg-green-50 text-slate-600 hover:text-green-600 text-xs font-medium py-2 rounded-lg transition">
                <i class="fa-solid fa-pen text-[11px]"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.ruang.destroy', $ruang) }}"
                  onsubmit="return confirm('Hapus ruang ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="flex items-center justify-center gap-1.5 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-600 text-xs font-medium px-3 py-2 rounded-lg transition">
                    <i class="fa-solid fa-trash text-[11px]"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
    @if($ruangList->hasPages())
    <div class="sm:col-span-2 pt-2">{{ $ruangList->links() }}</div>
    @endif
</div>
@endif

@endsection