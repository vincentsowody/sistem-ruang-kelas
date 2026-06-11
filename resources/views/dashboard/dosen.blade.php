@extends('layouts.app')
@section('title', 'Dashboard Dosen')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Dosen</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ now()->isoFormat('dddd, D MMMM Y') }} —
            Selamat datang, <span class="font-medium text-blue-700">{{ auth()->user()->name }}</span>
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reservasi.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-plus"></i> Ajukan Reservasi
        </a>
    </div>
</div>

{{-- Statistik Utama --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Jadwal Mengajar</span>
            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-chalkboard-user text-blue-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['jadwal_aktif'] }}</div>
        <div class="text-xs text-gray-400 mt-1">jadwal aktif semester ini</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Reservasi Menunggu</span>
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold {{ $stats['reservasi_menunggu'] > 0 ? 'text-amber-600' : 'text-gray-800' }}">
            {{ $stats['reservasi_menunggu'] }}
        </div>
        <div class="text-xs text-gray-400 mt-1">menunggu persetujuan</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Reservasi Disetujui</span>
            <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-green-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['reservasi_disetujui'] }}</div>
        <div class="text-xs text-gray-400 mt-1">total disetujui</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Total Reservasi</span>
            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-calendar-days text-purple-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['reservasi_total'] }}</div>
        <div class="text-xs text-gray-400 mt-1">semua pengajuan</div>
    </div>

</div>

{{-- Baris Kedua: Jadwal Hari Ini + Notifikasi --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Jadwal Mengajar Hari Ini --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-blue-500"></i>
                Jadwal Mengajar Hari Ini
                <span class="text-xs font-normal text-gray-400 capitalize">({{ now()->isoFormat('dddd') }})</span>
            </h2>
        </div>

        @if($jadwalHariIni->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-mug-hot text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada jadwal mengajar hari ini</p>
            <p class="text-gray-300 text-xs mt-1">Selamat beristirahat!</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($jadwalHariIni as $jadwal)
            @php
                $now = now();
                $mulai = \Carbon\Carbon::today()->setTimeFromTimeString($jadwal->jam_mulai);
                $selesai = \Carbon\Carbon::today()->setTimeFromTimeString($jadwal->jam_selesai);
                $sedangBerlangsung = $now->between($mulai, $selesai);
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl border
                {{ $sedangBerlangsung ? 'bg-blue-50 border-blue-200' : 'border-gray-100' }}">
                <div class="text-center min-w-[52px]">
                    <p class="text-xs font-bold {{ $sedangBerlangsung ? 'text-blue-700' : 'text-gray-700' }}">
                        {{ substr($jadwal->jam_mulai, 0, 5) }}
                    </p>
                    <p class="text-xs {{ $sedangBerlangsung ? 'text-blue-400' : 'text-gray-400' }}">
                        {{ substr($jadwal->jam_selesai, 0, 5) }}
                    </p>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-medium text-sm text-gray-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                        @if($sedangBerlangsung)
                        <span class="shrink-0 text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full">
                            Berlangsung
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}
                        · Kelas {{ $jadwal->kelas }}
                        · {{ $jadwal->sks }} SKS
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Notifikasi Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-bell text-amber-500"></i>
                Notifikasi
                @if($notifikasiBaru->isNotEmpty())
                <span class="text-xs bg-red-500 text-white font-medium px-2 py-0.5 rounded-full">
                    {{ $notifikasiBaru->count() }} baru
                </span>
                @endif
            </h2>
            <a href="{{ route('notifikasi.index') }}"
               class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        @if($notifikasiBaru->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-bell-slash text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada notifikasi baru</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($notifikasiBaru as $notif)
            @php
                $icon = match($notif->tipe ?? '') {
                    'disetujui'      => ['fa-circle-check',  'text-green-500', 'bg-green-50'],
                    'ditolak'        => ['fa-circle-xmark',  'text-red-500',   'bg-red-50'],
                    'dibatalkan'     => ['fa-ban',            'text-gray-400',  'bg-gray-50'],
                    'reservasi_baru' => ['fa-clipboard-list', 'text-blue-500',  'bg-blue-50'],
                    default          => ['fa-bell',           'text-amber-500', 'bg-amber-50'],
                };
            @endphp
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full {{ $icon[2] }} flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid {{ $icon[0] }} {{ $icon[1] }} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $notif->judul }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notif->pesan }}</p>
                    <p class="text-xs text-gray-300 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Baris Ketiga: Jadwal Minggu Ini + Reservasi Terbaru --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Jadwal Mengajar Minggu Ini --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-week text-purple-500"></i>
                Jadwal Minggu Ini
            </h2>
            <span class="text-xs bg-purple-100 text-purple-700 font-medium px-2.5 py-1 rounded-full">
                {{ $jadwalMingguIni->count() }} jadwal
            </span>
        </div>

        @if($jadwalMingguIni->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-calendar-xmark text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Belum ada jadwal minggu ini</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($jadwalMingguIni as $jadwal)
            <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-purple-100 hover:bg-purple-50/30 transition">
                {{-- Badge hari --}}
                <div class="shrink-0">
                    <span class="text-xs font-bold uppercase px-2.5 py-1 rounded-lg bg-purple-100 text-purple-700 w-16 text-center inline-block">
                        {{ strtoupper(substr($jadwal->hari, 0, 3)) }}
                    </span>
                </div>
                <div class="text-center min-w-[52px]">
                    <p class="text-xs font-bold text-gray-700">{{ substr($jadwal->jam_mulai, 0, 5) }}</p>
                    <p class="text-xs text-gray-400">{{ substr($jadwal->jam_selesai, 0, 5) }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}
                        · Kelas {{ $jadwal->kelas }}
                        · {{ $jadwal->program_studi }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Reservasi Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-green-500"></i>
                Reservasi Terbaru
            </h2>
            <a href="{{ route('reservasi.index') }}"
               class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        @if($reservasiTerbaru->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-inbox text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Belum ada riwayat reservasi</p>
            <a href="{{ route('reservasi.create') }}"
               class="mt-3 inline-block text-xs text-blue-600 hover:underline">
                Ajukan reservasi pertama Anda →
            </a>
        </div>
        @else
        <div class="space-y-2">
            @foreach($reservasiTerbaru as $res)
            @php
                $badgeClass = match($res->status) {
                    'disetujui'  => 'bg-green-100 text-green-700',
                    'menunggu'   => 'bg-amber-100 text-amber-700',
                    'ditolak'    => 'bg-red-100 text-red-700',
                    'dibatalkan' => 'bg-gray-100 text-gray-500',
                    default      => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <a href="{{ route('reservasi.show', $res) }}"
               class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition group">
                <div class="text-center min-w-[52px]">
                    <p class="text-xs font-bold text-gray-700">
                        {{ \Carbon\Carbon::parse($res->tanggal)->format('d M') }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ substr($res->jam_mulai, 0, 5) }}
                    </p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $res->keperluan }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $res->ruangKelas->kode_ruang ?? '-' }}
                        · {{ substr($res->jam_mulai, 0, 5) }}–{{ substr($res->jam_selesai, 0, 5) }}
                    </p>
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-lg {{ $badgeClass }} shrink-0 capitalize">
                    {{ $res->status_label ?? $res->status }}
                </span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Akses Cepat --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-bolt text-yellow-500"></i> Akses Cepat
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('reservasi.create'),  'fa-plus',          'bg-blue-50 text-blue-700 border-blue-100',     'Ajukan Reservasi'],
            [route('reservasi.index'),   'fa-list-check',    'bg-green-50 text-green-700 border-green-100',  'Riwayat Reservasi'],
            [route('kalender.index'),    'fa-calendar-week', 'bg-purple-50 text-purple-700 border-purple-100','Lihat Kalender'],
            [route('notifikasi.index'),  'fa-bell',          'bg-amber-50 text-amber-700 border-amber-100',  'Notifikasi'],
        ] as [$url, $ico, $cls, $label])
        <a href="{{ $url }}"
           class="flex flex-col items-center gap-2 p-4 rounded-xl border {{ $cls }} hover:shadow-sm transition text-center">
            <i class="fa-solid {{ $ico }} text-xl"></i>
            <span class="text-sm font-medium">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</div>

@endsection