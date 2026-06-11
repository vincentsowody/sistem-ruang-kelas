@extends('layouts.app')
@section('title', 'Manajemen Ruang Kelas')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Ruang Kelas</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola data ruang kelas, laboratorium, dan aula</p>
    </div>
    <a href="{{ route('admin.ruang.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
        <i class="fa-solid fa-plus"></i> Tambah Ruang
    </a>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label'=>'Total Ruang',  'value'=>$stats['total'],     'color'=>'blue',   'icon'=>'fa-door-open'],
        ['label'=>'Aktif',        'value'=>$stats['aktif'],     'color'=>'green',  'icon'=>'fa-circle-check'],
        ['label'=>'Nonaktif',     'value'=>$stats['nonaktif'],  'color'=>'gray',   'icon'=>'fa-circle-xmark'],
        ['label'=>'Perbaikan',    'value'=>$stats['perbaikan'], 'color'=>'yellow', 'icon'=>'fa-wrench'],
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
    <form method="GET" action="{{ route('admin.ruang.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode, nama, atau gedung..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="jenis" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Jenis</option>
            @foreach(['kelas','laboratorium','aula','seminar'] as $j)
            <option value="{{ $j }}" {{ request('jenis') == $j ? 'selected' : '' }}>{{ ucfirst($j) }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            @foreach(['aktif','nonaktif','perbaikan'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="gedung" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Gedung</option>
            @foreach($gedungList as $g)
            <option value="{{ $g }}" {{ request('gedung') == $g ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-filter mr-1"></i> Filter
        </button>
        @if(request()->anyFilled(['search','jenis','status','gedung']))
        <a href="{{ route('admin.ruang.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-medium transition">
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
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kode</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Nama Ruang</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Gedung / Lantai</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kapasitas</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Jenis</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Fasilitas</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($ruangList as $ruang)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <span class="font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg text-xs">
                            {{ $ruang->kode_ruang }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $ruang->nama_ruang }}</p>
                    </td>
                    <td class="px-5 py-4 text-gray-500">
                        {{ $ruang->gedung }}<br>
                        <span class="text-xs text-gray-400">Lantai {{ $ruang->lantai }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-gray-300 text-xs"></i>
                            <span class="font-medium text-gray-700">{{ $ruang->kapasitas }}</span>
                            <span class="text-gray-400 text-xs">kursi</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $jenisWarna = match($ruang->jenis) {
                                'kelas'        => 'bg-blue-100 text-blue-700',
                                'laboratorium' => 'bg-purple-100 text-purple-700',
                                'aula'         => 'bg-orange-100 text-orange-700',
                                'seminar'      => 'bg-teal-100 text-teal-700',
                                default        => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $jenisWarna }} capitalize">
                            {{ $ruang->jenis }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-1 max-w-[160px]">
                            @foreach(($ruang->fasilitas ?? []) as $fas)
                            <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">
                                {{ ucfirst(str_replace('_',' ',$fas)) }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $statusWarna = match($ruang->status) {
                                'aktif'      => 'bg-green-100 text-green-700',
                                'nonaktif'   => 'bg-gray-100 text-gray-600',
                                'perbaikan'  => 'bg-yellow-100 text-yellow-700',
                                default      => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusWarna }} capitalize">
                            {{ $ruang->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.ruang.show', $ruang) }}"
                               class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('admin.ruang.edit', $ruang) }}"
                               class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.ruang.destroy', $ruang) }}"
                                  onsubmit="return confirm('Hapus ruang {{ $ruang->kode_ruang }}? Tindakan ini tidak dapat dibatalkan.')">
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
                        <i class="fa-solid fa-door-open text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-400">Tidak ada data ruang kelas</p>
                        <a href="{{ route('admin.ruang.create') }}" class="text-blue-600 text-sm hover:underline mt-1 inline-block">
                            Tambah ruang pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ruangList->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $ruangList->links() }}
    </div>
    @endif
</div>

@endsection
