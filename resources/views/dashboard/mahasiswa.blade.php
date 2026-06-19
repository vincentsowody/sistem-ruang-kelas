@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', now()->isoFormat('dddd, D MMMM Y'))

@section('content')

{{-- ── Hero Banner ─────────────────────────────────────────── --}}
<div class="relative bg-gradient-to-r from-teal-600 via-teal-500 to-cyan-500 rounded-3xl p-6 mb-6 overflow-hidden shadow-lg shadow-teal-200">
    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-10 right-20 w-28 h-28 bg-white/5 rounded-full"></div>
    <div class="absolute top-4 right-36 w-10 h-10 bg-white/10 rounded-full"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-teal-100 text-sm font-medium mb-1">
                <i class="fa-solid fa-graduation-cap mr-1.5"></i>Portal Mahasiswa
            </p>
            <h2 class="text-2xl font-bold text-white leading-tight">
                Halo, {{ Str::words(auth()->user()->name, 2, '') }}! 👋
            </h2>
            <p class="text-teal-100 text-sm mt-1">
                Kamu punya
                <span class="font-bold text-white">{{ $stats['reservasi_menunggu'] }} reservasi menunggu</span>
                · <span class="font-bold text-white">{{ $stats['reservasi_disetujui'] }} disetujui</span>
            </p>
        </div>
        <a href="{{ route('reservasi.create') }}"
           class="shrink-0 inline-flex items-center gap-2 bg-white text-teal-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-teal-50 transition shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i> Ajukan Reservasi
        </a>
    </div>
</div>

{{-- ── Kartu Statistik ────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
    $cards = [
        ['Total Reservasi',    $stats['reservasi_total'],      'fa-calendar-days',  'teal',   'semua pengajuan'],
        ['Menunggu',           $stats['reservasi_menunggu'],   'fa-hourglass-half', 'amber',  'sedang diproses'],
        ['Disetujui',          $stats['reservasi_disetujui'],  'fa-circle-check',   'green',  'sudah disetujui'],
        ['Ditolak',            $stats['reservasi_ditolak'],    'fa-circle-xmark',   'red',    'tidak disetujui'],
    ];
    @endphp
    @foreach($cards as [$label, $val, $ico, $color, $sub])
    <a href="{{ route('reservasi.index') }}"
       class="group bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-{{ $color }}-200 transition">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 bg-{{ $color }}-100 rounded-xl flex items-center justify-center group-hover:bg-{{ $color }}-200 transition">
                <i class="fa-solid {{ $ico }} text-{{ $color }}-600"></i>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square text-slate-200 text-xs mt-1 group-hover:text-{{ $color }}-400 transition"></i>
        </div>
        <p class="text-3xl font-black {{ $label === 'Menunggu' && $val > 0 ? 'text-amber-600' : 'text-slate-800' }}">
            {{ $val }}
        </p>
        <p class="text-xs font-medium text-slate-500 mt-1">{{ $label }}</p>
        <p class="text-[11px] text-slate-300 mt-0.5">{{ $sub }}</p>
    </a>
    @endforeach
</div>

{{-- ── Baris 2: Ketersediaan Ruang + Jadwal Hari Ini ─────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- Ketersediaan Ruang Sekarang (wider) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-teal-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-door-open text-teal-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Ruang Tersedia Sekarang</h2>
                    <p class="text-[11px] text-slate-400">Jam {{ now()->format('H:i') }} · Hari ini</p>
                </div>
            </div>
            <a href="{{ route('kalender.index') }}" class="text-xs text-blue-600 hover:underline shrink-0 font-medium">Kalender →</a>
        </div>
        <div class="flex-1 p-4">
            @if($ruangTersedia->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-door-closed text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-500">Semua ruang sedang digunakan</p>
                <p class="text-xs text-slate-400 mt-1">Coba cek lagi beberapa saat</p>
            </div>
            @else
            <div class="grid grid-cols-2 gap-2">
                @foreach($ruangTersedia->take(8) as $ruang)
                <a href="{{ route('reservasi.create', ['ruang_id' => $ruang->id]) }}"
                   class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-100 hover:border-teal-200 hover:bg-teal-50/50 transition group">
                    <div class="w-8 h-8 bg-teal-100 group-hover:bg-teal-200 rounded-lg flex items-center justify-center shrink-0 transition">
                        <i class="fa-solid fa-door-open text-teal-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-800 font-mono truncate">{{ $ruang->kode_ruang }}</p>
                        <p class="text-[10px] text-slate-400">{{ $ruang->kapasitas }} kursi
                            @if($ruang->jenis === 'laboratorium')
                            <span class="text-purple-500">· Lab</span>
                            @endif
                        </p>
                    </div>
                    <i class="fa-solid fa-plus text-teal-400 text-[10px] ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </a>
                @endforeach
            </div>
            @if($ruangTersedia->count() > 8)
            <p class="text-center text-xs text-slate-400 mt-3">
                +{{ $ruangTersedia->count() - 8 }} ruang lainnya tersedia
            </p>
            @endif
            @endif
        </div>
    </div>

    {{-- Notifikasi --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-bell text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800 text-sm leading-tight">Notifikasi</h2>
                    @if(isset($notifikasiBaru) && $notifikasiBaru->isNotEmpty())
                    <p class="text-[11px] text-amber-500 font-semibold">{{ $notifikasiBaru->count() }} belum dibaca</p>
                    @else
                    <p class="text-[11px] text-slate-400">Sudah terbaca semua</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('notifikasi.index') }}" class="text-xs text-blue-600 hover:underline shrink-0">Semua →</a>
        </div>
        <div class="flex-1 p-4">
            @php $notifs = $notifikasiBaru ?? collect(); @endphp
            @if($notifs->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-bell-slash text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-500">Tidak ada notifikasi baru</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($notifs->take(5) as $notif)
                @php
                    [$ico, $cls, $bg] = match($notif->tipe ?? '') {
                        'disetujui'  => ['fa-circle-check',  'text-green-600',  'bg-green-100'],
                        'ditolak'    => ['fa-circle-xmark',  'text-red-600',    'bg-red-100'],
                        'dibatalkan' => ['fa-ban',           'text-slate-500',  'bg-slate-100'],
                        default      => ['fa-bell',          'text-amber-600',  'bg-amber-100'],
                    };
                @endphp
                <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                    <div class="w-8 h-8 {{ $bg }} rounded-xl flex items-center justify-center shrink-0">
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

{{-- ── Reservasi Terbaru ────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col mb-5">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left text-blue-600 text-sm"></i>
            </div>
            <div>
                <h2 class="font-semibold text-slate-800 text-sm leading-tight">Reservasi Terbaru</h2>
                <p class="text-[11px] text-slate-400">Riwayat pengajuan ruang kamu</p>
            </div>
        </div>
        <a href="{{ route('reservasi.index') }}" class="text-xs text-blue-600 hover:underline shrink-0">Lihat semua →</a>
    </div>
    <div class="p-4">
        @if($reservasiTerbaru->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mb-3">
                <i class="fa-solid fa-clipboard-list text-teal-300 text-2xl"></i>
            </div>
            <p class="text-sm font-semibold text-slate-600">Belum ada reservasi</p>
            <p class="text-xs text-slate-400 mt-1 mb-4">Mulai ajukan reservasi ruang untuk kegiatanmu</p>
            <a href="{{ route('reservasi.create') }}"
               class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition shadow-sm shadow-teal-200">
                <i class="fa-solid fa-plus text-xs"></i> Ajukan Sekarang
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($reservasiTerbaru as $res)
            @php
                [$bg, $text, $ico2, $barColor] = match($res->status) {
                    'disetujui'  => ['bg-green-100',  'text-green-700',  'fa-circle-check',  'bg-green-500'],
                    'menunggu'   => ['bg-amber-100',  'text-amber-700',  'fa-clock',         'bg-amber-400'],
                    'ditolak'    => ['bg-red-100',    'text-red-700',    'fa-circle-xmark',  'bg-red-500'],
                    'dibatalkan' => ['bg-slate-100',  'text-slate-500',  'fa-ban',           'bg-slate-300'],
                    default      => ['bg-blue-100',   'text-blue-700',   'fa-circle-info',   'bg-blue-400'],
                };
            @endphp
            <a href="{{ route('reservasi.show', $res) }}"
               class="flex items-stretch gap-0 rounded-xl border border-slate-100 hover:border-slate-200 hover:shadow-sm transition overflow-hidden group">
                {{-- Color bar --}}
                <div class="w-1.5 {{ $barColor }} shrink-0"></div>
                <div class="flex items-center gap-3 p-3.5 flex-1 min-w-0">
                    <div class="w-10 h-10 {{ $bg }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $ico2 }} {{ $text }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $res->keperluan }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono text-blue-600 font-bold">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                            <span>·</span>
                            <span>{{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}</span>
                            <span>·</span>
                            <span>{{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}</span>
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $bg }} {{ $text }} capitalize shrink-0">
                        {{ $res->status }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Akses Cepat ─────────────────────────────────────────── --}}
<div class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-2xl border border-teal-100 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-teal-200 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-bolt text-teal-700 text-sm"></i>
        </div>
        <div>
            <h2 class="font-semibold text-slate-800 text-sm leading-tight">Akses Cepat</h2>
            <p class="text-[11px] text-slate-400">Fitur yang sering digunakan</p>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('reservasi.create'),  'fa-plus',           'teal',   'Ajukan Reservasi',   'Pesan ruang baru'],
            [route('reservasi.index'),   'fa-list-check',     'blue',   'Riwayat Reservasi',  'Semua pengajuan'],
            [route('kalender.index'),    'fa-calendar-week',  'purple', 'Lihat Kalender',     'Jadwal & ketersediaan'],
            [route('notifikasi.index'),  'fa-bell',           'amber',  'Notifikasi',         'Update reservasi'],
        ] as [$url, $ico, $color, $label, $sub])
        <a href="{{ $url }}"
           class="flex items-center gap-3 p-4 rounded-xl bg-white border border-white hover:border-{{ $color }}-200 hover:bg-{{ $color }}-50 transition group shadow-sm">
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