@extends('layouts.app')
@section('title', 'Laporan Reservasi')

@section('content')

<div class="flex items-center justify-between mb-6 print:hidden">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('admin.laporan.index') }}" class="hover:text-blue-600">Laporan</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span class="text-gray-800">Reservasi</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan Reservasi</h1>
        <p class="text-gray-500 text-sm mt-1">
            {{ \Carbon\Carbon::parse($request->tanggal_mulai)->locale('id')->isoFormat('D MMM Y') }} —
            {{ \Carbon\Carbon::parse($request->tanggal_selesai)->locale('id')->isoFormat('D MMM Y') }} ·
            {{ $reservasiList->count() }} data
        </p>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-xl transition text-sm">
            <i class="fa-solid fa-print"></i> Cetak
        </button>
        <a href="{{ route('admin.laporan.reservasi', array_merge(request()->all(), ['format'=>'excel'])) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-xl transition text-sm">
            <i class="fa-solid fa-file-excel"></i> Excel
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-purple-600 text-white">
                    <th class="text-left px-4 py-3 font-semibold">No</th>
                    <th class="text-left px-4 py-3 font-semibold">Kode</th>
                    <th class="text-left px-4 py-3 font-semibold">Tanggal</th>
                    <th class="text-left px-4 py-3 font-semibold">Jam</th>
                    <th class="text-left px-4 py-3 font-semibold">Keperluan</th>
                    <th class="text-left px-4 py-3 font-semibold">Pemohon</th>
                    <th class="text-left px-4 py-3 font-semibold">Ruang</th>
                    <th class="text-center px-4 py-3 font-semibold">Peserta</th>
                    <th class="text-left px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($reservasiList as $i => $rsv)
                @php
                    $badge = match($rsv->status) {
                        'disetujui'  => 'bg-green-100 text-green-700',
                        'menunggu'   => 'bg-yellow-100 text-yellow-700',
                        'ditolak'    => 'bg-red-100 text-red-700',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">
                            {{ $rsv->kode_reservasi }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">
                        {{ $rsv->tanggal->locale('id')->isoFormat('D MMM Y') }}
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                        {{ substr($rsv->jam_mulai,0,5) }}–{{ substr($rsv->jam_selesai,0,5) }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 text-sm">{{ Str::limit($rsv->keperluan, 30) }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ',$rsv->jenis_kegiatan) }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $rsv->pemohon->name }}</td>
                    <td class="px-4 py-3">
                        <span class="font-mono font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-lg text-xs">
                            {{ $rsv->ruangKelas->kode_ruang }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700 text-sm">{{ $rsv->jumlah_peserta }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }} capitalize">
                            {{ $rsv->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        Tidak ada data reservasi untuk periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    nav, footer    { display: none !important; }
    main           { padding: 0 !important; }
}
</style>

@endsection
