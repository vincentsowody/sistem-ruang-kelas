@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ now()->isoFormat('dddd, D MMMM Y') }} —
            Selamat datang, <span class="font-medium text-blue-700">{{ auth()->user()->name }}</span>
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reservasi.index') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-clipboard-list"></i> Kelola Reservasi
            @if($stats['reservasi_menunggu'] > 0)
            <span class="bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $stats['reservasi_menunggu'] }}</span>
            @endif
        </a>
    </div>
</div>

{{-- Statistik Utama --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Ruang Aktif</span>
            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-door-open text-blue-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['ruang_aktif'] }}</div>
        <div class="text-xs text-gray-400 mt-1">dari {{ $stats['total_ruang'] }} total ruang</div>
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
        <div class="text-xs text-gray-400 mt-1">perlu ditinjau</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Jadwal Aktif</span>
            <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-calendar-check text-green-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['jadwal_aktif'] }}</div>
        <div class="text-xs text-gray-400 mt-1">jadwal semester ini</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500">Total Pengguna</span>
            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-users text-purple-600 text-sm"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['total_user'] }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $stats['total_dosen'] }} dosen · {{ $stats['total_mahasiswa'] }} mahasiswa</div>
    </div>
</div>

{{-- Baris Kedua: Pending Reservasi + Jadwal Hari Ini --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Reservasi Menunggu Persetujuan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                Menunggu Persetujuan
            </h2>
            <a href="{{ route('admin.reservasi.index') }}"
               class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        @if($reservasiPending->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-circle-check text-4xl text-green-300 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada reservasi yang menunggu</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($reservasiPending as $res)
            <div class="flex items-start justify-between gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i>{{ $res->ruangKelas->kode_ruang ?? '-' }}
                        · {{ \Carbon\Carbon::parse($res->tanggal)->format('d M') }}
                        · {{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $res->keperluan }}</p>
                </div>
                <a href="{{ route('admin.reservasi.show', $res) }}"
                   class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Tinjau
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Jadwal Hari Ini --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-blue-500"></i>
                Jadwal Hari Ini
                <span class="text-xs font-normal text-gray-400 capitalize">({{ now()->isoFormat('dddd') }})</span>
            </h2>
            <a href="{{ route('admin.jadwal.index') }}"
               class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        @if($jadwalHariIni->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-calendar-xmark text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada jadwal hari ini</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($jadwalHariIni as $jadwal)
            <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                <div class="text-center min-w-[52px]">
                    <p class="text-xs font-bold text-blue-700">{{ substr($jadwal->jam_mulai,0,5) }}</p>
                    <p class="text-xs text-blue-400">{{ substr($jadwal->jam_selesai,0,5) }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $jadwal->ruangKelas->kode_ruang ?? '-' }} · {{ $jadwal->dosen->name ?? '-' }} · Kelas {{ $jadwal->kelas }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Baris Ketiga: Reservasi Hari Ini + Aktivitas Terbaru --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Reservasi Hari Ini --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-green-500"></i>
                Reservasi Hari Ini
            </h2>
            <span class="text-xs bg-green-100 text-green-700 font-medium px-2.5 py-1 rounded-full">
                {{ $stats['reservasi_hari_ini'] }} reservasi
            </span>
        </div>

        @if($reservasiHariIni->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-calendar-xmark text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Tidak ada reservasi hari ini</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($reservasiHariIni as $res)
            @php
                $badgeClass = match($res->status) {
                    'disetujui'  => 'bg-green-100 text-green-700',
                    'menunggu'   => 'bg-amber-100 text-amber-700',
                    'ditolak'    => 'bg-red-100 text-red-700',
                    default      => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100">
                <div class="text-center min-w-[52px]">
                    <p class="text-xs font-bold text-gray-700">{{ substr($res->jam_mulai,0,5) }}</p>
                    <p class="text-xs text-gray-400">{{ substr($res->jam_selesai,0,5) }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $res->ruangKelas->kode_ruang ?? '-' }} · {{ $res->keperluan }}</p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-lg {{ $badgeClass }} shrink-0 capitalize">
                    {{ $res->status }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-500"></i>
                Aktivitas Terbaru
            </h2>
            <span class="text-xs text-gray-400">7 hari terakhir</span>
        </div>

        @if($aktivitasTerbaru->isEmpty())
        <div class="text-center py-8">
            <i class="fa-solid fa-inbox text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 text-sm">Belum ada aktivitas</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($aktivitasTerbaru as $res)
            @php
                $icon = match($res->status) {
                    'disetujui'  => ['fa-circle-check',   'text-green-500',  'bg-green-50'],
                    'menunggu'   => ['fa-clock',           'text-amber-500',  'bg-amber-50'],
                    'ditolak'    => ['fa-circle-xmark',    'text-red-500',    'bg-red-50'],
                    'dibatalkan' => ['fa-ban',              'text-gray-400',   'bg-gray-50'],
                    default      => ['fa-circle-info',     'text-blue-500',   'bg-blue-50'],
                };
            @endphp
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full {{ $icon[2] }} flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid {{ $icon[0] }} {{ $icon[1] }} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">{{ $res->pemohon->name ?? '-' }}</span>
                        mengajukan reservasi ruang <span class="font-medium">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $res->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Akses Cepat --}}
<div class="mt-5 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-bolt text-yellow-500"></i> Akses Cepat
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('admin.ruang.create'),   'fa-plus',          'bg-blue-50 text-blue-700 border-blue-100',   'Tambah Ruang'],
            [route('admin.jadwal.import'),  'fa-file-import',   'bg-green-50 text-green-700 border-green-100', 'Import Jadwal'],
            [route('admin.users.index'),    'fa-users-gear',    'bg-purple-50 text-purple-700 border-purple-100','Kelola User'],
            [route('kalender.index'),       'fa-calendar-week', 'bg-amber-50 text-amber-700 border-amber-100', 'Lihat Kalender'],
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
