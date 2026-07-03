@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')
@section('page_subtitle', now()->isoFormat('dddd, D MMMM Y'))

@section('content')

{{-- ── Data Arrays (Mempersingkat Kode HTML) ──────────────── --}}
@php
    $pendingCount = $stats['reservasi_menunggu'];
    
    // Data Statistik
    $statCards = [
        ['url' => route('admin.ruang.index'), 'label' => 'Ruang', 'val' => $stats['ruang_aktif'], 'sub' => "dari {$stats['total_ruang']} total", 'icon' => 'fa-door-open', 'theme' => 'blue'],
        ['url' => route('admin.reservasi.index'), 'label' => 'Pending', 'val' => $pendingCount, 'sub' => 'perlu ditinjau', 'icon' => $pendingCount > 0 ? 'fa-hourglass-half' : 'fa-circle-check', 'theme' => $pendingCount > 0 ? 'amber' : 'emerald'],
        ['url' => route('admin.jadwal.index'), 'label' => 'Jadwal', 'val' => $stats['jadwal_aktif'], 'sub' => 'semester aktif', 'icon' => 'fa-calendar-check', 'theme' => 'emerald'],
        ['url' => route('admin.users.index'), 'label' => 'Pengguna', 'val' => $stats['total_user'], 'sub' => "{$stats['total_dosen']} dosen · {$stats['total_mahasiswa']} mhs", 'icon' => 'fa-users', 'theme' => 'purple'],
    ];

    // Data Akses Cepat
    $quickAccess = [
        ['url' => route('admin.ruang.create'), 'icon' => 'fa-plus', 'title' => 'Tambah Ruang', 'desc' => 'Daftarkan ruang baru', 'tx' => 'text-blue-600', 'bg' => 'bg-blue-100'],
        ['url' => route('admin.jadwal.excel-import'), 'icon' => 'fa-file-excel', 'title' => 'Import Jadwal', 'desc' => 'Upload file Excel', 'tx' => 'text-emerald-600', 'bg' => 'bg-emerald-100'],
        ['url' => route('admin.jadwal.alokasi'), 'icon' => 'fa-wand-magic-sparkles', 'title' => 'Alokasi Greedy', 'desc' => 'Otomatis alokasikan', 'tx' => 'text-purple-600', 'bg' => 'bg-purple-100'],
        ['url' => route('admin.users.create'), 'icon' => 'fa-user-plus', 'title' => 'Tambah Pengguna', 'desc' => 'Daftarkan akun baru', 'tx' => 'text-rose-600', 'bg' => 'bg-rose-100'],
    ];
@endphp

{{-- ── Hero Banner ─────────────────────────────────────────── --}}
<div class="relative bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-6 mb-6 overflow-hidden shadow-sm">
    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-10 right-20 w-28 h-28 bg-white/10 rounded-full"></div>
    
    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-blue-100 text-sm mb-1"><i class="fa-solid fa-calendar-day mr-2"></i>{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-2xl font-bold text-white">Halo, {{ Str::words(auth()->user()->name, 2, '') }}! 👋</h2>
            <p class="text-blue-100 text-sm mt-1">Berikut ringkasan aktivitas sistem SiRuang hari ini.</p>
        </div>
        
        @if($pendingCount > 0)
        <a href="{{ route('admin.reservasi.index', ['status'=>'menunggu']) }}" class="flex items-center gap-2 bg-white text-blue-700 text-sm font-bold px-5 py-2.5 rounded-xl hover:shadow-lg transition">
            <span class="w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
            Tinjau Reservasi
        </a>
        @else
        <div class="flex items-center gap-2 bg-white/20 text-white text-sm px-4 py-2 rounded-xl border border-white/30">
            <i class="fa-solid fa-circle-check text-green-300"></i> Semua reservasi ditinjau
        </div>
        @endif
    </div>
</div>

{{-- ── Kartu Statistik ────────────────────────────────────── --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach($statCards as $card)
    <a href="{{ $card['url'] }}" class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-{{ $card['theme'] }}-50 group-hover:bg-{{ $card['theme'] }}-100 transition">
                <i class="fa-solid {{ $card['icon'] }} text-{{ $card['theme'] }}-600"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50 px-2 py-1 rounded-lg">{{ $card['label'] }}</span>
        </div>
        <p class="text-3xl font-black text-slate-800">{{ $card['val'] }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $card['sub'] }}</p>
    </a>
    @endforeach
</div>

{{-- ── Grid Layout: Menunggu & Jadwal ────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">
    
    {{-- Menunggu Persetujuan --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-hourglass-half text-amber-500 mr-2"></i>Menunggu Persetujuan</h2>
            <a href="{{ route('admin.reservasi.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua &rarr;</a>
        </div>
        <div class="p-4">
            @forelse($reservasiPending as $res)
            <div class="flex items-center justify-between p-3 mb-2 rounded-xl bg-slate-50 border border-slate-100 hover:border-amber-200 transition">
                <div>
                    <p class="font-semibold text-sm text-slate-800">{{ $res->pemohon->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="font-mono font-bold text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                        · {{ \Carbon\Carbon::parse($res->tanggal)->isoFormat('d MMMM') }} · {{ substr($res->jam_mulai,0,5) }}-{{ substr($res->jam_selesai,0,5) }}
                    </p>
                </div>
                <a href="{{ route('admin.reservasi.show', $res) }}" class="bg-white border border-slate-200 text-slate-600 hover:text-amber-600 text-xs font-semibold px-3 py-1.5 rounded-lg transition">Tinjau</a>
            </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-sm"><i class="fa-solid fa-check-circle text-3xl mb-2 text-green-400 block"></i>Tidak ada antrean.</div>
            @endforelse
        </div>
    </div>

    {{-- Jadwal Hari Ini --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-calendar-day text-blue-500 mr-2"></i>Jadwal Hari Ini</h2>
            <a href="{{ route('admin.jadwal.index') }}" class="text-xs text-blue-600 hover:underline">Semua &rarr;</a>
        </div>
        <div class="p-4">
            @forelse($jadwalHariIni->take(5) as $jadwal)
                @php $isNow = $jadwal->jam_mulai <= now()->format('H:i') && $jadwal->jam_selesai > now()->format('H:i'); @endphp
                <div class="flex items-center gap-3 p-2 border-b border-slate-50 last:border-0 {{ $isNow ? 'bg-blue-50 rounded-lg' : '' }}">
                    <div class="text-center w-12 shrink-0">
                        <p class="text-xs font-bold {{ $isNow ? 'text-blue-600' : 'text-slate-600' }}">{{ substr($jadwal->jam_mulai,0,5) }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-xs text-slate-700 truncate">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }} · Kl.{{ $jadwal->kelas }}</p>
                    </div>
                    @if($isNow) <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse shrink-0"></span> @endif
                </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-sm">Libur mengajar hari ini.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Grid Layout: Reservasi Harian & Aktivitas ──────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    
    {{-- Reservasi Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="p-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-door-open text-emerald-500 mr-2"></i>Reservasi Digunakan Hari Ini</h2>
        </div>
        <div class="p-4">
            @forelse($reservasiHariIni as $res)
            <div class="flex items-center gap-3 p-2 border-b border-slate-50 last:border-0">
                <div class="text-center w-10 shrink-0">
                    <p class="text-xs font-bold text-slate-700">{{ substr($res->jam_mulai,0,5) }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-slate-700 truncate">{{ $res->pemohon->name ?? '-' }}</p>
                    <p class="text-xs text-slate-400 truncate"><span class="font-mono text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span> · {{ $res->keperluan }}</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 rounded bg-slate-100 text-slate-600 capitalize">{{ $res->status }}</span>
            </div>
            @empty
            <div class="text-center py-6 text-slate-400 text-sm">Tidak ada reservasi ruang hari ini.</div>
            @endforelse
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <div class="p-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-clock-rotate-left text-purple-500 mr-2"></i>Aktivitas Terbaru</h2>
        </div>
        <div class="p-4">
            @forelse($aktivitasTerbaru as $res)
            <div class="flex items-start gap-3 mb-4 last:mb-0">
                <div class="w-2 h-2 mt-1.5 rounded-full bg-slate-300 shrink-0"></div>
                <div>
                    <p class="text-sm text-slate-700 leading-snug">
                        <span class="font-bold">{{ Str::words($res->pemohon->name ?? '-', 2) }}</span> 
                        mengajukan ruang <span class="font-mono text-blue-600">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span>
                    </p>
                    <p class="text-xs text-slate-400">{{ $res->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-slate-400 text-sm">Belum ada aktivitas.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Akses Cepat ─────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <h2 class="font-bold text-slate-800 text-sm mb-4"><i class="fa-solid fa-bolt text-yellow-500 mr-2"></i>Akses Cepat</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach($quickAccess as $qa)
        <a href="{{ $qa['url'] }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:shadow-sm transition group">
            <div class="w-10 h-10 {{ $qa['bg'] }} rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid {{ $qa['icon'] }} {{ $qa['tx'] }}"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-700">{{ $qa['title'] }}</p>
                <p class="text-[10px] text-slate-400">{{ $qa['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>

@endsection