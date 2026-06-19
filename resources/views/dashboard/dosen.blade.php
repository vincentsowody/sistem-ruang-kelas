@extends('layouts.app')
@section('title', 'Dashboard Dosen')
@section('page_title', 'Dashboard')
@section('page_subtitle', now()->isoFormat('dddd, D MMMM Y'))

@section('content')

{{-- ── Hero Banner ─────────────────────────────────────────── --}}
<div class="relative bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-600 rounded-3xl p-6 mb-6 overflow-hidden shadow-lg shadow-indigo-200">
    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-10 right-20 w-28 h-28 bg-white/5 rounded-full"></div>
    <div class="absolute top-4 right-36 w-10 h-10 bg-white/10 rounded-full"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-indigo-200 text-sm font-medium mb-1">
                <i class="fa-solid fa-chalkboard-user mr-1.5"></i>Portal Dosen
            </p>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Selamat datang, {{ Str::words(auth()->user()->name, 2, '') }}! 👋
            </h2>
            <p class="text-indigo-200 text-sm mt-1">
                Anda memiliki
                <span class="font-bold text-white">{{ $stats['jadwal_aktif'] }} jadwal mengajar</span>
                semester ini.
            </p>
        </div>
        <a href="{{ route('reservasi.create') }}"
           class="shrink-0 inline-flex items-center gap-2 bg-white text-indigo-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-indigo-50 transition shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i> Ajukan Reservasi
        </a>
    </div>
</div>

{{-- ── Kartu Statistik ────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
    $statCards = [
        ['Jadwal Mengajar',      $stats['jadwal_aktif'],          'fa-chalkboard-user', 'indigo', 'semester ini',       route('kalender.index')],
        ['Reservasi Menunggu',   $stats['reservasi_menunggu'],    'fa-hourglass-half',  'amber',  'perlu diproses',     route('reservasi.index')],
        ['Reservasi Disetujui',  $stats['reservasi_disetujui'],   'fa-circle-check',    'green',  'total disetujui',    route('reservasi.index')],
        ['Total Reservasi',      $stats['reservasi_total'],       'fa-calendar-days',   'purple', 'semua pengajuan',    route('reservasi.index')],
    ];
    @endphp
    @foreach($statCards as [$label, $val, $ico, $color, $sub, $url])
    <a href="{{ $url }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-{{ $color }}-200 transition">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 bg-{{ $color }}-100 rounded-xl flex items-center justify-center group-hover:bg-{{ $color }}-200 transition">
                <i class="fa-solid {{ $ico }} text-{{ $color }}-600"></i>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 text-xs mt-1 group-hover:text-{{ $color }}-400 transition"></i>
        </div>
        <p class="text-3xl font-black {{ $label === 'Reservasi Menunggu' && $val > 0 ? 'text-amber-600' : 'text-slate-800' }}">
            {{ $val }}
        </p>
        <p class="text-xs text-slate-400 mt-1 font-medium">{{ $label }}</p>
        <p class="text-[11px] text-slate-300 mt-0.5">{{ $sub }}</p>
    </a>
    @endforeach
</div>

{{-- ── Baris 2: Jadwal Hari Ini + Notifikasi ───────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- Jadwal Mengajar Hari Ini (wider) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-indigo-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Jadwal Mengajar Hari Ini</h2>
                    <p class="text-[11px] text-slate-400 capitalize">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>
        </div>
        <div class="flex-1 p-4">
            @if($jadwalHariIni->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-mug-hot text-indigo-300 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-600">Tidak ada jadwal hari ini</p>
                <p class="text-xs text-slate-400 mt-1">Selamat menikmati waktu istirahat! ☕</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($jadwalHariIni as $jadwal)
                @php
                    $now = now();
                    $mulai   = \Carbon\Carbon::today()->setTimeFromTimeString($jadwal->jam_mulai);
                    $selesai = \Carbon\Carbon::today()->setTimeFromTimeString($jadwal->jam_selesai);
                    $isNow   = $now->between($mulai, $selesai);
                    $isDone  = $now->gt($selesai);
                @endphp
                <div class="flex items-center gap-4 p-4 rounded-2xl
                    {{ $isNow ? 'bg-indigo-50 border-2 border-indigo-200 shadow-sm' : ($isDone ? 'bg-slate-50 border border-slate-100 opacity-60' : 'border border-slate-100 hover:bg-slate-50') }} transition">
                    {{-- Jam --}}
                    <div class="text-center shrink-0">
                        <p class="text-base font-black {{ $isNow ? 'text-indigo-700' : 'text-slate-700' }}">
                            {{ substr($jadwal->jam_mulai,0,5) }}
                        </p>
                        <div class="w-px h-3 bg-slate-300 mx-auto my-0.5"></div>
                        <p class="text-xs text-slate-400">{{ substr($jadwal->jam_selesai,0,5) }}</p>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-sm text-slate-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                            @if($isNow)
                            <span class="shrink-0 text-[10px] bg-indigo-600 text-white px-2 py-0.5 rounded-full font-bold animate-pulse">
                                BERLANGSUNG
                            </span>
                            @elseif($isDone)
                            <span class="shrink-0 text-[10px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded-full">Selesai</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="inline-flex items-center gap-1 text-xs bg-white border border-slate-200 px-2 py-0.5 rounded-lg text-slate-600">
                                <i class="fa-solid fa-door-open text-blue-500 text-[10px]"></i>
                                {{ $jadwal->ruangKelas->kode_ruang ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-400">Kelas {{ $jadwal->kelas }}</span>
                            <span class="text-xs text-slate-400">{{ $jadwal->sks }} SKS</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Notifikasi (narrower) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-bell text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Notifikasi</h2>
                    @if($notifikasiBaru->isNotEmpty())
                    <p class="text-[11px] text-amber-500 font-semibold">{{ $notifikasiBaru->count() }} belum dibaca</p>
                    @else
                    <p class="text-[11px] text-slate-400">Semua sudah dibaca</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('notifikasi.index') }}" class="text-xs text-blue-600 hover:underline shrink-0">Semua →</a>
        </div>
        <div class="flex-1 p-4">
            @if($notifikasiBaru->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-bell-slash text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-500">Tidak ada notifikasi baru</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($notifikasiBaru as $notif)
                @php
                    [$ico, $cls, $bg] = match($notif->tipe ?? '') {
                        'disetujui'      => ['fa-circle-check',  'text-green-600', 'bg-green-100'],
                        'ditolak'        => ['fa-circle-xmark',  'text-red-600',   'bg-red-100'],
                        'dibatalkan'     => ['fa-ban',           'text-slate-500', 'bg-slate-100'],
                        'reservasi_baru' => ['fa-clipboard-list','text-blue-600',  'bg-blue-100'],
                        default          => ['fa-bell',          'text-amber-600', 'bg-amber-100'],
                    };
                @endphp
                <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-xl {{ $bg }} flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $ico }} {{ $cls }} text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $notif->judul }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">{{ $notif->pesan }}</p>
                        <p class="text-[10px] text-slate-300 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Baris 3: Jadwal Minggu Ini + Reservasi Terbaru ───────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Jadwal Semua Hari --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar-week text-purple-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Jadwal Mingguan</h2>
                    <p class="text-[11px] text-slate-400">Semua hari mengajar</p>
                </div>
            </div>
            <span class="text-xs bg-purple-100 text-purple-700 font-semibold px-2.5 py-1 rounded-full">
                {{ $jadwalMingguIni->count() }} jadwal
            </span>
        </div>
        <div class="flex-1 p-4">
            @if($jadwalMingguIni->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="fa-solid fa-calendar-xmark text-slate-200 text-4xl mb-3"></i>
                <p class="text-sm text-slate-400">Belum ada jadwal terdaftar</p>
            </div>
            @else
            @php $hariColors = ['senin'=>'blue','selasa'=>'indigo','rabu'=>'purple','kamis'=>'violet','jumat'=>'fuchsia','sabtu'=>'pink']; @endphp
            <div class="space-y-2">
                @foreach($jadwalMingguIni as $jadwal)
                @php $hColor = $hariColors[strtolower($jadwal->hari)] ?? 'slate'; @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-{{ $hColor }}-100 hover:bg-{{ $hColor }}-50/30 transition">
                    <span class="text-[10px] font-black uppercase w-9 text-center py-1.5 rounded-lg bg-{{ $hColor }}-100 text-{{ $hColor }}-700 shrink-0">
                        {{ strtoupper(substr($jadwal->hari, 0, 3)) }}
                    </span>
                    <div class="text-center w-12 shrink-0">
                        <p class="text-xs font-bold text-slate-700">{{ substr($jadwal->jam_mulai,0,5) }}</p>
                        <p class="text-[10px] text-slate-400">{{ substr($jadwal->jam_selesai,0,5) }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-xs text-slate-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            <span class="font-mono text-blue-600">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}</span>
                            · Kelas {{ $jadwal->kelas }} · {{ $jadwal->program_studi }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Reservasi Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-green-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Reservasi Terbaru</h2>
                    <p class="text-[11px] text-slate-400">Riwayat pengajuan Anda</p>
                </div>
            </div>
            <a href="{{ route('reservasi.index') }}" class="text-xs text-blue-600 hover:underline shrink-0">Semua →</a>
        </div>
        <div class="flex-1 p-4">
            @if($reservasiTerbaru->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="fa-solid fa-inbox text-slate-200 text-4xl mb-3"></i>
                <p class="text-sm text-slate-500 font-medium">Belum ada riwayat</p>
                <a href="{{ route('reservasi.create') }}"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus text-[10px]"></i> Ajukan sekarang
                </a>
            </div>
            @else
            <div class="space-y-2">
                @foreach($reservasiTerbaru as $res)
                @php
                    [$bg, $text, $ico2] = match($res->status) {
                        'disetujui'  => ['bg-green-100',  'text-green-700',  'fa-circle-check'],
                        'menunggu'   => ['bg-amber-100',  'text-amber-700',  'fa-clock'],
                        'ditolak'    => ['bg-red-100',    'text-red-700',    'fa-circle-xmark'],
                        'dibatalkan' => ['bg-slate-100',  'text-slate-500',  'fa-ban'],
                        default      => ['bg-slate-100',  'text-slate-600',  'fa-circle'],
                    };
                @endphp
                <a href="{{ route('reservasi.show', $res) }}"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition group">
                    <div class="w-10 h-10 {{ $bg }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $ico2 }} {{ $text }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $res->keperluan }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <span class="font-mono text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                            · {{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}
                            · {{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $bg }} {{ $text }} capitalize shrink-0">
                        {{ $res->status_label ?? $res->status }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Akses Cepat ─────────────────────────────────────────── --}}
<div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-bolt text-yellow-600 text-sm"></i>
        </div>
        <h2 class="font-semibold text-slate-800 text-sm">Akses Cepat</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('reservasi.create'),  'fa-plus',          'indigo',  'Ajukan Reservasi',  'Pesan ruang baru'],
            [route('reservasi.index'),   'fa-list-check',    'green',   'Riwayat Reservasi', 'Semua pengajuan'],
            [route('kalender.index'),    'fa-calendar-week', 'purple',  'Lihat Kalender',    'Jadwal & reservasi'],
            [route('notifikasi.index'),  'fa-bell',          'amber',   'Notifikasi',        'Pemberitahuan baru'],
        ] as [$url, $ico, $color, $label, $sub])
        <a href="{{ $url }}"
           class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 hover:border-{{ $color }}-200 hover:bg-{{ $color }}-50 transition group">
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