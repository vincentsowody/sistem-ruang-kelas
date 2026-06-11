@extends('layouts.app')
@section('title', 'Laporan & Statistik')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Laporan & Statistik</h1>
    <p class="text-gray-500 text-sm mt-1">Ringkasan data sistem dan ekspor laporan</p>
</div>

{{-- Statistik Ringkasan --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    @foreach([
        ['label'=>'Ruang Aktif',     'value'=>$stats['total_ruang'],         'color'=>'blue',   'icon'=>'fa-door-open'],
        ['label'=>'Jadwal Aktif',    'value'=>$stats['total_jadwal'],        'color'=>'indigo', 'icon'=>'fa-calendar-days'],
        ['label'=>'Total Reservasi', 'value'=>$stats['total_reservasi'],     'color'=>'purple', 'icon'=>'fa-clipboard-list'],
        ['label'=>'Disetujui',       'value'=>$stats['reservasi_disetujui'], 'color'=>'green',  'icon'=>'fa-circle-check'],
        ['label'=>'Ditolak',         'value'=>$stats['reservasi_ditolak'],   'color'=>'red',    'icon'=>'fa-circle-xmark'],
        ['label'=>'Bulan Ini',       'value'=>$stats['reservasi_bulan_ini'], 'color'=>'teal',   'icon'=>'fa-calendar-day'],
    ] as $s)
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500">{{ $s['label'] }}</span>
            <i class="fa-solid {{ $s['icon'] }} text-{{ $s['color'] }}-400 text-sm"></i>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $s['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form Laporan Jadwal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-calendar-days text-blue-600"></i>
            </div>
            <h2 class="font-semibold text-gray-800">Jadwal Per Semester</h2>
        </div>
        <form method="GET" action="{{ route('admin.laporan.jadwal') }}" class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Akademik</label>
                <select name="tahun_akademik" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                    <option value="2024/2025" {{ !$tahunList->contains('2024/2025') ? '' : '' }}>2024/2025</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Semester</label>
                <select name="semester_ganjil_genap" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" name="format" value="html"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-eye mr-1"></i> Lihat
                </button>
                <button type="submit" name="format" value="excel"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </button>
                <button type="submit" name="format" value="pdf"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Form Laporan Reservasi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-clipboard-list text-purple-600"></i>
            </div>
            <h2 class="font-semibold text-gray-800">Laporan Reservasi</h2>
        </div>
        <form method="GET" action="{{ route('admin.laporan.reservasi') }}" class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                    <option value="">Semua Status</option>
                    @foreach(['menunggu','disetujui','ditolak','dibatalkan'] as $s)
                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" name="format" value="html"
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-eye mr-1"></i> Lihat
                </button>
                <button type="submit" name="format" value="excel"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </button>
                <button type="submit" name="format" value="pdf"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Form Utilisasi Ruang --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-9 h-9 bg-teal-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-chart-bar text-teal-600"></i>
            </div>
            <h2 class="font-semibold text-gray-800">Utilisasi Ruang</h2>
        </div>
        <form method="GET" action="{{ route('admin.laporan.utilisasi') }}" class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" {{ now()->month == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null,$b)->locale('id')->isoFormat('MMMM') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    @foreach(range(now()->year, 2020) as $y)
                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1 mt-4">
                <button type="submit" name="format" value="html"
                    class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-eye mr-1"></i> Lihat
                </button>
                <button type="submit" name="format" value="excel"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </button>
                <button type="submit" name="format" value="pdf"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-xl transition">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Top 5 Utilisasi Bulan Ini --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-ranking-star text-yellow-500"></i>
        Top 5 Ruang Paling Sering Dipakai — {{ now()->locale('id')->isoFormat('MMMM Y') }}
    </h2>
    <div class="space-y-3">
        @forelse($utilisasi as $item)
        <div class="flex items-center gap-4">
            <div class="w-20 text-right flex-shrink-0">
                <span class="font-mono font-bold text-blue-600 text-sm">{{ $item['ruang']->kode_ruang }}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $item['ruang']->nama_ruang }}</span>
                    <span class="text-xs text-gray-500">{{ $item['total_jam'] }} jam · {{ $item['total_sesi'] }} sesi</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                         style="width: {{ max($item['persen'], 2) }}%"></div>
                </div>
            </div>
            <div class="w-12 text-right flex-shrink-0">
                <span class="text-sm font-semibold text-gray-700">{{ $item['persen'] }}%</span>
            </div>
        </div>
        @empty
        <p class="text-gray-400 text-sm text-center py-4">Belum ada data utilisasi bulan ini</p>
        @endforelse
    </div>
</div>

@endsection
