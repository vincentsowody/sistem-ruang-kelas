@extends('layouts.app')
@section('title', 'Dashboard Mahasiswa')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Mahasiswa</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ now()->isoFormat('dddd, D MMMM Y') }} —
            Selamat datang, <span class="font-medium text-blue-700">{{ auth()->user()->name }}</span>
        </p>
    </div>
    <a href="{{ route('reservasi.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
        <i class="fa-solid fa-plus"></i> Ajukan Reservasi
    </a>
</div>

{{-- Notifikasi belum dibaca --}}
@if($notifikasiBaru->isNotEmpty())
<div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-5">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-bell text-blue-500"></i>
            <p class="font-semibold text-blue-800 text-sm">{{ $notifikasiBaru->count() }} notifikasi baru</p>
        </div>
    </div>
    <div class="space-y-2">
        @foreach($notifikasiBaru as $notif)
        <div class="flex items-start gap-2 text-sm text-blue-700">
            <i class="fa-solid {{ $notif->ikon }} mt-0.5 shrink-0"></i>
            <span>{{ $notif->pesan }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Reservasi',      $stats['reservasi_total'],    'fa-clipboard-list', 'bg-blue-100 text-blue-600',   'semua pengajuan'],
        ['Menunggu',             $stats['reservasi_menunggu'], 'fa-clock',          'bg-amber-100 text-amber-600', 'sedang diproses'],
        ['Disetujui',            $stats['reservasi_disetujui'],'fa-circle-check',   'bg-green-100 text-green-600', 'reservasi aktif'],
        ['Ditolak',              $stats['reservasi_ditolak'],  'fa-circle-xmark',   'bg-red-100 text-red-600',     'tidak disetujui'],
    ] as [$label, $val, $ico, $cls, $sub])
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">{{ $label }}</span>
            <div class="w-9 h-9 {{ $cls }} rounded-xl flex items-center justify-center bg-opacity-20">
                <i class="fa-solid {{ $ico }} text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $val }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $sub }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Reservasi Mendatang --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-green-500"></i>
                Reservasi Mendatang
            </h2>
            <span class="text-xs bg-green-100 text-green-700 font-medium px-2.5 py-1 rounded-full">
                Disetujui
            </span>
        </div>

        @if($reservasiMendatang->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-calendar-plus text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-500 text-sm">Tidak ada reservasi mendatang</p>
            <a href="{{ route('reservasi.create') }}"
               class="text-blue-600 text-sm hover:underline mt-2 inline-block">
                Ajukan sekarang →
            </a>
        </div>
        @else
        <div class="space-y-3">
            @foreach($reservasiMendatang as $res)
            @php
                $isHariIni = \Carbon\Carbon::parse($res->tanggal)->isToday();
                $isBesok   = \Carbon\Carbon::parse($res->tanggal)->isTomorrow();
                $labelTgl  = $isHariIni ? 'Hari ini' : ($isBesok ? 'Besok' : \Carbon\Carbon::parse($res->tanggal)->format('d M Y'));
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl border {{ $isHariIni ? 'border-green-300 bg-green-50' : 'border-gray-100' }}">
                <div class="text-center min-w-[58px]">
                    <p class="text-xs font-bold {{ $isHariIni ? 'text-green-700' : 'text-gray-700' }}">
                        {{ $labelTgl }}
                    </p>
                    <p class="text-xs text-gray-400">{{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $res->keperluan }}</p>
                    <p class="text-xs text-gray-500">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $res->ruangKelas->nama_ruang ?? $res->ruangKelas->kode_ruang ?? '-' }}
                    </p>
                </div>
                @if($isHariIni)
                <span class="text-xs bg-green-600 text-white px-2 py-1 rounded-lg shrink-0">Hari ini</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Menunggu Persetujuan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                Menunggu Persetujuan
            </h2>
            @if($reservasiMenunggu->isNotEmpty())
            <span class="text-xs bg-amber-100 text-amber-700 font-medium px-2.5 py-1 rounded-full">
                {{ $reservasiMenunggu->count() }} pengajuan
            </span>
            @endif
        </div>

        @if($reservasiMenunggu->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-circle-check text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada pengajuan yang menunggu</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($reservasiMenunggu as $res)
            <div class="flex items-start justify-between gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $res->keperluan }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $res->ruangKelas->kode_ruang ?? '-' }}
                        · {{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}
                        · {{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}
                    </p>
                    <p class="text-xs text-amber-600 mt-1">
                        <i class="fa-solid fa-clock mr-1"></i>Diajukan {{ $res->created_at->diffForHumans() }}
                    </p>
                </div>
                <a href="{{ route('reservasi.show', $res) }}"
                   class="shrink-0 text-xs text-blue-600 hover:underline mt-1">Detail</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Riwayat & Ruang Tersedia --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Riwayat Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-gray-500"></i>
                Riwayat Terbaru
            </h2>
            <a href="{{ route('reservasi.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        @if($riwayat->isEmpty())
        <div class="text-center py-6">
            <p class="text-gray-400 text-sm">Belum ada riwayat reservasi</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($riwayat as $res)
            @php
                $badge = match($res->status) {
                    'disetujui'  => ['bg-green-100 text-green-700', 'Disetujui'],
                    'menunggu'   => ['bg-amber-100 text-amber-700', 'Menunggu'],
                    'ditolak'    => ['bg-red-100 text-red-700',     'Ditolak'],
                    'dibatalkan' => ['bg-gray-100 text-gray-500',   'Dibatalkan'],
                    default      => ['bg-gray-100 text-gray-600',   ucfirst($res->status)],
                };
            @endphp
            <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-700 truncate font-medium">{{ $res->keperluan }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $res->ruangKelas->kode_ruang ?? '-' }} ·
                        {{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}
                    </p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-lg {{ $badge[0] }} shrink-0">
                    {{ $badge[1] }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Ruang Tersedia --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-blue-500"></i>
                Ruang Tersedia
            </h2>
            <a href="{{ route('kalender.index') }}" class="text-xs text-blue-600 hover:underline">Cek kalender</a>
        </div>

        @if($ruangTersedia->isEmpty())
        <div class="text-center py-6">
            <p class="text-gray-400 text-sm">Tidak ada data ruang</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($ruangTersedia as $ruang)
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-sm text-gray-800">{{ $ruang->kode_ruang }}</p>
                    <p class="text-xs text-gray-500">{{ $ruang->nama_ruang }} · {{ $ruang->kapasitas }} kursi</p>
                </div>
                <a href="{{ route('reservasi.create') }}?ruang_id={{ $ruang->id }}"
                   class="text-xs bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition font-medium">
                    Ajukan
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection
