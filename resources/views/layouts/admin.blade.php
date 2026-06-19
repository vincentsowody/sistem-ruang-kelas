@extends('layouts.app')
@section('title','Dashboard')
@section('page_title','Dashboard')
@section('page_subtitle', 'Selamat datang kembali, '.Str::words(auth()->user()->name,2,''))

@section('content')
@php
  $hour = now()->hour;
  $greet = $hour < 12 ? 'Selamat Pagi' : ($hour < 17 ? 'Selamat Siang' : 'Selamat Malam');
  $greetIco = $hour < 12 ? '🌤️' : ($hour < 17 ? '☀️' : '🌙');
@endphp

{{-- ── Hero ── --}}
<div class="relative rounded-3xl overflow-hidden mb-6 p-6 lg:p-8"
     style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 50%,#3b82f6 100%)">
    {{-- decorative blobs --}}
    <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10"
         style="background:radial-gradient(circle,white,transparent);transform:translate(30%,-30%)"></div>
    <div class="absolute bottom-0 left-1/2 w-48 h-48 rounded-full opacity-5"
         style="background:white;transform:translateY(40%)"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">{{ $greetIco }}</span>
                <span class="text-blue-300 text-sm font-semibold">{{ $greet }}</span>
            </div>
            <h2 class="text-2xl lg:text-3xl font-extrabold text-white leading-tight">
                {{ Str::words(auth()->user()->name, 2, '') }}
            </h2>
            <p class="text-blue-200 text-sm mt-1">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }} · Sistem berjalan normal
            </p>
        </div>
        @if($stats['reservasi_menunggu'] > 0)
        <a href="{{ route('admin.reservasi.index',['status'=>'menunggu']) }}"
           class="shrink-0 flex items-center gap-3 bg-white/15 hover:bg-white/25 backdrop-blur border border-white/20 text-white font-semibold px-5 py-3 rounded-2xl transition group">
            <div class="relative flex h-5 w-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[11px] font-black">
                    {{ $stats['reservasi_menunggu'] }}
                </span>
            </div>
            <span class="text-sm">Perlu Ditinjau</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
        </a>
        @else
        <div class="shrink-0 flex items-center gap-2 bg-green-500/20 border border-green-400/30 text-green-300 text-sm font-semibold px-4 py-2.5 rounded-2xl">
            <i class="fa-solid fa-circle-check"></i> Semua sudah ditinjau
        </div>
        @endif
    </div>

    {{-- Mini stats strip --}}
    <div class="relative grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-white/10">
        @foreach([
            [$stats['ruang_aktif'],       $stats['total_ruang'],    'Ruang Aktif'],
            [$stats['reservasi_menunggu'],'reservasi',              'Pending'],
            [$stats['jadwal_aktif'],      'jadwal',                 'Jadwal Aktif'],
            [$stats['total_user'],        $stats['total_dosen'].' dosen', 'Pengguna'],
        ] as [$val,$sub,$lbl])
        <div>
            <p class="text-2xl font-black text-white">{{ $val }}</p>
            <p class="text-blue-300 text-xs mt-0.5">{{ $lbl }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Stat cards ── --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @php
    $statCards = [
        ['ruang',   $stats['ruang_aktif'],         'dari '.$stats['total_ruang'].' total', 'Ruang Aktif',         'fa-door-open',         '#3b82f6', '#dbeafe', route('admin.ruang.index')],
        ['pending', $stats['reservasi_menunggu'],   'perlu ditinjau',                        'Menunggu',            'fa-hourglass-half',    '#f59e0b', '#fef3c7', route('admin.reservasi.index')],
        ['jadwal',  $stats['jadwal_aktif'],         'semester ini',                          'Jadwal',              'fa-calendar-check',    '#10b981', '#d1fae5', route('admin.jadwal.index')],
        ['users',   $stats['total_user'],           $stats['total_dosen'].' dosen · '.$stats['total_mahasiswa'].' mhs', 'Pengguna', 'fa-users', '#8b5cf6', '#ede9fe', route('admin.users.index')],
    ];
    @endphp
    @foreach($statCards as [$key,$val,$sub,$lbl,$ico,$color,$lightColor,$url])
    <a href="{{ $url }}" class="stat-card group">
        <div class="glow" style="background:{{ $color }}"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                 style="background:{{ $lightColor }}">
                <i class="fa-solid {{ $ico }} text-base" style="color:{{ $color }}"></i>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 text-xs group-hover:text-blue-400 transition-colors"></i>
        </div>
        <p class="text-3xl font-black text-slate-800 leading-none">{{ $val }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-1.5 uppercase tracking-wide">{{ $lbl }}</p>
        <p class="text-xs text-slate-400 mt-0.5">{{ $sub }}</p>
    </a>
    @endforeach
</div>

{{-- ── Pending + Jadwal ── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- Pending (lebar) --}}
    <div class="card lg:col-span-3 flex flex-col">
        <div class="card-header">
            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-hourglass-half text-amber-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-800 text-sm">Menunggu Persetujuan</p>
                <p class="text-xs text-slate-400">Reservasi yang perlu ditinjau</p>
            </div>
            @if($reservasiPending->isNotEmpty())
            <span class="chip bg-amber-100 text-amber-700">{{ $reservasiPending->count() }} pending</span>
            @endif
            <a href="{{ route('admin.reservasi.index') }}" class="text-xs text-blue-600 hover:underline font-semibold shrink-0">Semua →</a>
        </div>
        <div class="flex-1 p-4">
            @if($reservasiPending->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-circle-check text-green-400 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-600">Semua sudah beres!</p>
                <p class="text-xs text-slate-400 mt-1">Tidak ada reservasi yang menunggu</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($reservasiPending as $res)
                <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-amber-50 border border-amber-100 hover:border-amber-300 transition group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shrink-0 shadow-sm">
                        <span class="text-white font-black text-sm">{{ strtoupper(substr($res->pemohon->name??'?',0,1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="mono text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">
                                {{ $res->ruangKelas->kode_ruang ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($res->tanggal)->isoFormat('D MMM') }}</span>
                            <span class="text-xs text-slate-500 mono">{{ substr($res->jam_mulai,0,5) }}–{{ substr($res->jam_selesai,0,5) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.reservasi.show',$res) }}"
                       class="shrink-0 btn-primary !py-2 !px-3.5 !text-xs opacity-80 group-hover:opacity-100">
                        Tinjau
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Jadwal hari ini --}}
    <div class="card lg:col-span-2 flex flex-col">
        <div class="card-header">
            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calendar-day text-blue-600 text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Jadwal Hari Ini</p>
                <p class="text-xs text-slate-400 capitalize">{{ now()->locale('id')->isoFormat('dddd') }}</p>
            </div>
        </div>
        <div class="flex-1 p-4">
            @if($jadwalHariIni->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-mug-hot text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm text-slate-500">Tidak ada jadwal hari ini</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($jadwalHariIni->take(7) as $jadwal)
                @php
                    $now = now()->format('H:i');
                    $isNow = $jadwal->jam_mulai<=$now && $jadwal->jam_selesai>$now;
                    $isDone = $jadwal->jam_selesai<=$now;
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ $isNow?'bg-blue-50 border-2 border-blue-200':($isDone?'opacity-40 bg-slate-50':'hover:bg-slate-50 border border-transparent') }}">
                    <div class="text-center w-12 shrink-0">
                        <p class="text-xs font-black {{ $isNow?'text-blue-700':'text-slate-700' }} mono">{{ substr($jadwal->jam_mulai,0,5) }}</p>
                        <p class="text-[10px] text-slate-400 mono">{{ substr($jadwal->jam_selesai,0,5) }}</p>
                    </div>
                    <div class="w-px h-8 {{ $isNow?'bg-blue-300':'bg-slate-200' }} shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-xs text-slate-800 truncate">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 mono">{{ $jadwal->ruangKelas->kode_ruang??'-' }} · Kl.{{ $jadwal->kelas }}</p>
                    </div>
                    @if($isNow)<span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse shrink-0"></span>@endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Aktivitas timeline + Reservasi hari ini ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Reservasi hari ini --}}
    <div class="card flex flex-col">
        <div class="card-header">
            <div class="w-8 h-8 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calendar-check text-green-600 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-slate-800 text-sm">Reservasi Hari Ini</p>
                <p class="text-xs text-slate-400">{{ $stats['reservasi_hari_ini'] }} reservasi</p>
            </div>
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
                    [$bar,$bg,$txt] = match($res->status){
                        'disetujui'=>['bg-green-500','bg-green-50','text-green-700'],
                        'menunggu' =>['bg-amber-400','bg-amber-50','text-amber-700'],
                        'ditolak'  =>['bg-red-500',  'bg-red-50',  'text-red-700'],
                        default    =>['bg-slate-300','bg-slate-50','text-slate-500'],
                    };
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-slate-200 transition overflow-hidden">
                    <div class="w-1 h-10 {{ $bar }} rounded-full shrink-0"></div>
                    <div class="text-center w-11 shrink-0">
                        <p class="text-xs font-black text-slate-700 mono">{{ substr($res->jam_mulai,0,5) }}</p>
                        <p class="text-[10px] text-slate-400 mono">{{ substr($res->jam_selesai,0,5) }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $res->pemohon->name??'-' }}</p>
                        <p class="text-xs text-slate-400 truncate mono">{{ $res->ruangKelas->kode_ruang??'-' }} · {{ Str::limit($res->keperluan,25) }}</p>
                    </div>
                    <span class="chip {{ $bg }} {{ $txt }} capitalize shrink-0">{{ $res->status }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Timeline aktivitas --}}
    <div class="card flex flex-col">
        <div class="card-header">
            <div class="w-8 h-8 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-clock-rotate-left text-purple-600 text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Aktivitas Terbaru</p>
                <p class="text-xs text-slate-400">7 hari terakhir</p>
            </div>
        </div>
        <div class="flex-1 p-4">
            @if($aktivitasTerbaru->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="fa-solid fa-inbox text-slate-200 text-4xl mb-3"></i>
                <p class="text-sm text-slate-400">Belum ada aktivitas</p>
            </div>
            @else
            <div class="relative pl-8">
                <div class="absolute left-3 top-1 bottom-1 w-px bg-slate-100"></div>
                <div class="space-y-4">
                    @foreach($aktivitasTerbaru as $res)
                    @php
                        [$ico2,$cls2,$bg2] = match($res->status){
                            'disetujui' =>['fa-circle-check','text-green-600','bg-green-100'],
                            'menunggu'  =>['fa-clock',       'text-amber-500','bg-amber-100'],
                            'ditolak'   =>['fa-circle-xmark','text-red-500',  'bg-red-100'],
                            'dibatalkan'=>['fa-ban',         'text-slate-400','bg-slate-100'],
                            default     =>['fa-circle-info', 'text-blue-500', 'bg-blue-100'],
                        };
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-8 w-6 h-6 rounded-full {{ $bg2 }} flex items-center justify-center border-2 border-white shadow-sm">
                            <i class="fa-solid {{ $ico2 }} {{ $cls2 }} text-[9px]"></i>
                        </div>
                        <p class="text-sm text-slate-700 leading-snug">
                            <span class="font-semibold">{{ Str::words($res->pemohon->name??'-',2) }}</span>
                            ajukan <span class="mono font-bold text-blue-600 text-xs">{{ $res->ruangKelas->kode_ruang??'-' }}</span>
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

{{-- ── Akses Cepat ── --}}
<div class="card p-5">
    <div class="flex items-center gap-2.5 mb-4">
        <div class="w-8 h-8 rounded-xl bg-yellow-100 flex items-center justify-center">
            <i class="fa-solid fa-bolt text-yellow-600 text-sm"></i>
        </div>
        <p class="font-bold text-slate-800 text-sm">Akses Cepat</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('admin.ruang.create'),       'fa-plus',                '#3b82f6','#dbeafe','Tambah Ruang',   'Daftarkan ruang baru'],
            [route('admin.jadwal.excel-import'),'fa-file-excel',          '#10b981','#d1fae5','Import Jadwal',  'Upload file Excel'],
            [route('admin.jadwal.alokasi'),     'fa-wand-magic-sparkles', '#8b5cf6','#ede9fe','Alokasi Greedy', 'Otomatis alokasikan'],
            [route('admin.users.create'),       'fa-user-plus',           '#f59e0b','#fef3c7','Tambah User',    'Buat akun baru'],
        ] as [$url,$ico,$color,$light,$label,$sub])
        <a href="{{ $url }}"
           class="group flex items-center gap-3 p-4 rounded-2xl border border-slate-100 hover:border-slate-200 hover:shadow-md transition bg-white/60">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                 style="background:{{ $light }}">
                <i class="fa-solid {{ $ico }} text-sm" style="color:{{ $color }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-700 truncate">{{ $label }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $sub }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>

@endsection