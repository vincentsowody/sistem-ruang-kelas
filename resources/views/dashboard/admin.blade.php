@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')
@section('page_subtitle', now()->isoFormat('dddd, D MMMM Y'))

@section('content')

{{-- ── Hero Banner ─────────────────────────────────────────── --}}
<div class="relative bg-gradient-to-r from-blue-700 via-blue-600 to-blue-500 rounded-3xl p-6 mb-6 overflow-hidden shadow-lg shadow-blue-200">
    {{-- decorative circles --}}
    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-10 right-20 w-28 h-28 bg-white/5 rounded-full"></div>
    <div class="absolute top-4 right-36 w-10 h-10 bg-white/10 rounded-full"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-blue-200 text-sm font-medium mb-1">
                <i class="fa-solid fa-sun mr-1.5"></i>{{ now()->isoFormat('dddd, D MMMM Y') }}
            </p>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Halo, {{ Str::words(auth()->user()->name, 2, '') }}! 👋
            </h2>
            <p class="text-blue-200 text-sm mt-1">Berikut ringkasan aktivitas sistem SiRuang hari ini.</p>
        </div>
        @if($stats['reservasi_menunggu'] > 0)
        <a href="{{ route('admin.reservasi.index', ['status'=>'menunggu']) }}"
           class="shrink-0 inline-flex items-center gap-2 bg-white text-blue-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-blue-50 transition shadow-sm">
            <span class="w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                {{ $stats['reservasi_menunggu'] > 9 ? '9+' : $stats['reservasi_menunggu'] }}
            </span>
            Tinjau Reservasi
        </a>
        @else
        <div class="shrink-0 inline-flex items-center gap-2 bg-white/20 text-white text-sm px-4 py-2 rounded-xl border border-white/30">
            <i class="fa-solid fa-circle-check text-green-300"></i>
            Semua reservasi ditinjau
        </div>
        @endif
    </div>
</div>

{{-- ── Kartu Statistik ────────────────────────────────────── --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Ruang Aktif --}}
    <a href="{{ route('admin.ruang.index') }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition">
                <i class="fa-solid fa-door-open text-blue-600"></i>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide bg-slate-50 px-2 py-1 rounded-lg">Ruang</span>
        </div>
        <p class="text-3xl font-black text-slate-800">{{ $stats['ruang_aktif'] }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-slate-400">dari {{ $stats['total_ruang'] }} total ruang</p>
            <i class="fa-solid fa-arrow-right text-blue-400 text-xs group-hover:translate-x-0.5 transition-transform"></i>
        </div>
    </a>

    {{-- Reservasi Pending --}}
    <a href="{{ route('admin.reservasi.index') }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md {{ $stats['reservasi_menunggu'] > 0 ? 'hover:border-amber-200' : 'hover:border-green-200' }} transition">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 {{ $stats['reservasi_menunggu'] > 0 ? 'bg-amber-100' : 'bg-green-100' }} rounded-xl flex items-center justify-center transition">
                <i class="fa-solid {{ $stats['reservasi_menunggu'] > 0 ? 'fa-hourglass-half text-amber-600' : 'fa-circle-check text-green-600' }}"></i>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide bg-slate-50 px-2 py-1 rounded-lg">Pending</span>
        </div>
        <p class="text-3xl font-black {{ $stats['reservasi_menunggu'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
            {{ $stats['reservasi_menunggu'] }}
        </p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-slate-400">perlu ditinjau</p>
            <i class="fa-solid fa-arrow-right text-slate-300 text-xs group-hover:translate-x-0.5 transition-transform"></i>
        </div>
    </a>

    {{-- Jadwal Aktif --}}
    <a href="{{ route('admin.jadwal.index') }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-green-200 transition">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition">
                <i class="fa-solid fa-calendar-check text-green-600"></i>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide bg-slate-50 px-2 py-1 rounded-lg">Jadwal</span>
        </div>
        <p class="text-3xl font-black text-slate-800">{{ $stats['jadwal_aktif'] }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-slate-400">semester aktif</p>
            <i class="fa-solid fa-arrow-right text-green-400 text-xs group-hover:translate-x-0.5 transition-transform"></i>
        </div>
    </a>

    {{-- Total Pengguna --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-purple-200 transition">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition">
                <i class="fa-solid fa-users text-purple-600"></i>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide bg-slate-50 px-2 py-1 rounded-lg">Pengguna</span>
        </div>
        <p class="text-3xl font-black text-slate-800">{{ $stats['total_user'] }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-slate-400">{{ $stats['total_dosen'] }} dosen · {{ $stats['total_mahasiswa'] }} mhs</p>
            <i class="fa-solid fa-arrow-right text-purple-400 text-xs group-hover:translate-x-0.5 transition-transform"></i>
        </div>
    </a>

</div>

{{-- ── Baris 2: Menunggu + Jadwal Hari Ini ─────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- Reservasi Menunggu (wider) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-hourglass-half text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Menunggu Persetujuan</h2>
                    <p class="text-[11px] text-slate-400">Reservasi yang perlu ditinjau admin</p>
                </div>
                @if($reservasiPending->isNotEmpty())
                <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-1">
                    {{ $reservasiPending->count() }}
                </span>
                @endif
            </div>
            <a href="{{ route('admin.reservasi.index') }}"
               class="text-xs text-blue-600 hover:underline font-medium shrink-0">Lihat semua →</a>
        </div>
        <div class="flex-1 p-4">
            @if($reservasiPending->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-circle-check text-green-400 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-600">Semua beres!</p>
                <p class="text-xs text-slate-400 mt-1">Tidak ada reservasi yang menunggu persetujuan</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($reservasiPending as $res)
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-100 hover:border-amber-300 hover:bg-amber-50 transition group">
                    {{-- Avatar --}}
                    <div class="w-9 h-9 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                        <span class="text-white font-bold text-sm">{{ strtoupper(substr($res->pemohon->name ?? '?', 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono bg-white px-1.5 py-0.5 rounded-md text-blue-700 font-bold border border-blue-100">
                                {{ $res->ruangKelas->kode_ruang ?? '-' }}
                            </span>
                            <span>·</span>
                            <span>{{ \Carbon\Carbon::parse($res->tanggal)->isoFormat('ddd, D MMM') }}</span>
                            <span>·</span>
                            <span>{{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}</span>
                        </p>
                    </div>
                    <a href="{{ route('admin.reservasi.show', $res) }}"
                       class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow-sm shadow-amber-200">
                        Tinjau
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Jadwal Hari Ini (narrower) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Jadwal Hari Ini</h2>
                    <p class="text-[11px] text-slate-400 capitalize">{{ now()->isoFormat('dddd') }}</p>
                </div>
            </div>
            <a href="{{ route('admin.jadwal.index') }}" class="text-xs text-blue-600 hover:underline shrink-0">Semua →</a>
        </div>
        <div class="flex-1 p-4">
            @if($jadwalHariIni->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-calendar-xmark text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm text-slate-500 font-medium">Tidak ada jadwal</p>
                <p class="text-xs text-slate-400 mt-1">Hari ini libur mengajar</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($jadwalHariIni->take(6) as $jadwal)
                @php
                    $now = now()->format('H:i');
                    $isNow = $jadwal->jam_mulai <= $now && $jadwal->jam_selesai > $now;
                    $isDone = $jadwal->jam_selesai <= $now;
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl
                    {{ $isNow ? 'bg-blue-50 border border-blue-200' : ($isDone ? 'opacity-50 bg-slate-50' : 'border border-transparent hover:bg-slate-50') }} transition">
                    <div class="text-center w-12 shrink-0">
                        <p class="text-xs font-bold {{ $isNow ? 'text-blue-700' : 'text-slate-600' }}">{{ substr($jadwal->jam_mulai,0,5) }}</p>
                        <p class="text-[10px] text-slate-400">{{ substr($jadwal->jam_selesai,0,5) }}</p>
                    </div>
                    <div class="w-px h-7 {{ $isNow ? 'bg-blue-300' : 'bg-slate-200' }} shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-xs text-slate-700 truncate">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-[11px] text-slate-400 truncate">
                            <span class="font-mono text-blue-600">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}</span>
                            · Kl.{{ $jadwal->kelas }}
                        </p>
                    </div>
                    @if($isNow)
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse shrink-0"></span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Baris 3: Reservasi Hari Ini + Aktivitas ─────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Reservasi Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar-check text-green-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Reservasi Hari Ini</h2>
                    <p class="text-[11px] text-slate-400">Semua reservasi untuk hari ini</p>
                </div>
            </div>
            <span class="text-xs bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-full">
                {{ $stats['reservasi_hari_ini'] }}
            </span>
        </div>
        <div class="flex-1 p-4">
            @if($reservasiHariIni->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="fa-solid fa-calendar-xmark text-slate-200 text-4xl mb-3"></i>
                <p class="text-sm text-slate-400">Belum ada reservasi hari ini</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($reservasiHariIni as $res)
                @php
                    [$badgeBg, $badgeText, $dotColor] = match($res->status) {
                        'disetujui'  => ['bg-green-100',  'text-green-700',  'bg-green-500'],
                        'menunggu'   => ['bg-amber-100',  'text-amber-700',  'bg-amber-400'],
                        'ditolak'    => ['bg-red-100',    'text-red-700',    'bg-red-500'],
                        default      => ['bg-slate-100',  'text-slate-600',  'bg-slate-400'],
                    };
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                    <div class="w-1.5 h-10 {{ $dotColor }} rounded-full shrink-0"></div>
                    <div class="text-center w-11 shrink-0">
                        <p class="text-xs font-bold text-slate-700">{{ substr($res->jam_mulai,0,5) }}</p>
                        <p class="text-[10px] text-slate-400">{{ substr($res->jam_selesai,0,5) }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-700 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            <span class="font-mono text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                            · {{ Str::limit($res->keperluan, 25) }}
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $badgeBg }} {{ $badgeText }} capitalize shrink-0">
                        {{ $res->status }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-purple-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Aktivitas Terbaru</h2>
                    <p class="text-[11px] text-slate-400">7 hari terakhir</p>
                </div>
            </div>
        </div>
        <div class="flex-1 p-4">
            @if($aktivitasTerbaru->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="fa-solid fa-inbox text-slate-200 text-4xl mb-3"></i>
                <p class="text-sm text-slate-400">Belum ada aktivitas</p>
            </div>
            @else
            <div class="relative">
                {{-- Timeline line --}}
                <div class="absolute left-3.5 top-0 bottom-0 w-px bg-slate-100"></div>
                <div class="space-y-4 pl-10">
                    @foreach($aktivitasTerbaru as $res)
                    @php
                        [$ico, $cls, $bg] = match($res->status) {
                            'disetujui'  => ['fa-circle-check',  'text-green-600', 'bg-green-100'],
                            'menunggu'   => ['fa-clock',          'text-amber-500', 'bg-amber-100'],
                            'ditolak'    => ['fa-circle-xmark',   'text-red-500',   'bg-red-100'],
                            'dibatalkan' => ['fa-ban',            'text-slate-400', 'bg-slate-100'],
                            default      => ['fa-circle-info',    'text-blue-500',  'bg-blue-100'],
                        };
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-10 w-7 h-7 rounded-full {{ $bg }} flex items-center justify-center border-2 border-white">
                            <i class="fa-solid {{ $ico }} {{ $cls }} text-[10px]"></i>
                        </div>
                        <p class="text-sm text-slate-700 leading-snug">
                            <span class="font-semibold">{{ Str::words($res->pemohon->name ?? '-', 2) }}</span>
                            mengajukan reservasi ruang
                            <span class="font-mono font-bold text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $res->created_at->diffForHumans() }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Akses Cepat ─────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-bolt text-yellow-600 text-sm"></i>
        </div>
        <div>
            <h2 class="font-semibold text-slate-800 text-sm leading-tight">Akses Cepat</h2>
            <p class="text-[11px] text-slate-400">Fitur yang sering digunakan</p>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('admin.ruang.create'),        'fa-plus',                'blue',   'Tambah Ruang',      'Daftarkan ruang baru'],
            [route('admin.jadwal.excel-import'), 'fa-file-excel',          'green',  'Import Jadwal',     'Upload file Excel'],
            [route('admin.jadwal.alokasi'),      'fa-wand-magic-sparkles', 'purple', 'Alokasi Greedy',    'Otomatis alokasikan'],
            [route('admin.users.create'),        'fa-user-plus',           'rose',   'Tambah Pengguna',   'Daftarkan akun baru'],
        ] as [$url, $ico, $color, $label, $sub])
        <a href="{{ $url }}"
           class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 hover:border-{{ $color }}-200 hover:bg-{{ $color }}-50/60 transition group">
            <div class="w-10 h-10 bg-{{ $color }}-100 group-hover:bg-{{ $color }}-200 rounded-xl flex items-center justify-center shrink-0 transition">
                <i class="fa-solid {{ $ico }} text-{{ $color }}-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                <p class="text-xs text-slate-400">{{ $sub }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>

@endsection