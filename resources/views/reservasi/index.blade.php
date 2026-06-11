@extends('layouts.app')
@section('title', 'Reservasi Saya')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reservasi Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Riwayat pengajuan reservasi ruang Anda</p>
    </div>
    <a href="{{ route('reservasi.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
        <i class="fa-solid fa-plus"></i> Ajukan Baru
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kode</th>
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
                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">
                            {{ $rsv->kode_reservasi }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $rsv->keperluan }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 capitalize">
                            {{ str_replace('_', ' ', $rsv->jenis_kegiatan) }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg text-xs">
                            {{ $rsv->ruangKelas->kode_ruang }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-700">
                            {{ $rsv->tanggal->locale('id')->isoFormat('D MMM Y') }}
                        </p>
                        <p class="text-xs text-gray-400 font-mono">
                            {{ substr($rsv->jam_mulai,0,5) }} – {{ substr($rsv->jam_selesai,0,5) }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }} capitalize">
                            {{ $rsv->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('reservasi.show', $rsv) }}"
                           class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition inline-flex" title="Detail">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <i class="fa-solid fa-clipboard text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-400 font-medium">Belum ada riwayat reservasi</p>
                        <a href="{{ route('reservasi.create') }}"
                           class="text-blue-600 text-sm hover:underline mt-1 inline-block">
                            Ajukan reservasi pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservasiList->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $reservasiList->links() }}
    </div>
    @endif
</div>

@endsection
