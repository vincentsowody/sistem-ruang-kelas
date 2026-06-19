@extends('layouts.app')
@section('title', 'Manajemen Reservasi')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Reservasi</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola semua pengajuan reservasi ruang</p>
    </div>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label'=>'Menunggu',  'value'=>$stats['menunggu'],  'color'=>'yellow', 'icon'=>'fa-hourglass-half'],
        ['label'=>'Disetujui', 'value'=>$stats['disetujui'], 'color'=>'green',  'icon'=>'fa-circle-check'],
        ['label'=>'Ditolak',   'value'=>$stats['ditolak'],   'color'=>'red',    'icon'=>'fa-circle-xmark'],
        ['label'=>'Hari Ini', 'value'=>$stats['reservasi_hari_ini'] ?? 0, 'color'=>'blue', 'icon'=>'fa-calendar-day'],
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
    <form method="GET" action="{{ route('admin.reservasi.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[180px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pemohon, kegiatan, kode..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            @foreach(['menunggu','disetujui','ditolak','dibatalkan'] as $s)
            <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
            class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-filter mr-1"></i> Filter
        </button>
        @if(request()->anyFilled(['search','status','tanggal']))
        <a href="{{ route('admin.reservasi.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm transition">
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
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Pemohon</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kegiatan</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Ruang</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Tanggal & Waktu</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reservasiList as $rsv)
                @php
                    $badge = match($rsv->status) {
                        'menunggu'   => 'bg-yellow-100 text-yellow-700',
                        'disetujui'  => 'bg-green-100 text-green-700',
                        'ditolak'    => 'bg-red-100 text-red-700',
                        'dibatalkan' => 'bg-gray-100 text-gray-600',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-lg text-gray-600">
                            {{ $rsv->kode_reservasi }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $rsv->pemohon->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $rsv->pemohon->role }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ Str::limit($rsv->keperluan, 30) }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ',$rsv->jenis_kegiatan) }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg text-xs">
                            {{ $rsv->ruangKelas->kode_ruang }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $rsv->jumlah_peserta }} peserta</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-700">{{ $rsv->tanggal->locale('id')->isoFormat('D MMM Y') }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ substr($rsv->jam_mulai,0,5) }} – {{ substr($rsv->jam_selesai,0,5) }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }} capitalize">
                            {{ $rsv->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.reservasi.show', $rsv) }}"
                               class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            @if($rsv->isMenunggu())
                            <form method="POST" action="{{ route('admin.reservasi.setujui', $rsv) }}"
                                  onsubmit="return confirm('Setujui reservasi {{ addslashes($rsv->kode_reservasi) }} dari {{ addslashes($rsv->pemohon->name ?? '') }}?')">
                                @csrf
                                <button type="submit"
                                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Setujui">
                                    <i class="fa-solid fa-check text-sm"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <i class="fa-solid fa-clipboard text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-400">Belum ada data reservasi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservasiList->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $reservasiList->links() }}</div>
    @endif
</div>

@endsection
