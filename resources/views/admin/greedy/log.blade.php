@extends('layouts.app')
@section('title', 'Visualisasi Algoritma Greedy')

@push('styles')
<style>
/* ── Animasi masuk tiap langkah ── */
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.step-card { animation: fadeSlideIn .35s ease both; }
.step-card:nth-child(1) { animation-delay: .05s; }
.step-card:nth-child(2) { animation-delay: .12s; }
.step-card:nth-child(3) { animation-delay: .19s; }
.step-card:nth-child(4) { animation-delay: .26s; }

/* ── Baris iterasi ruang ── */
@keyframes rowIn {
    from { opacity: 0; transform: translateX(-8px); }
    to   { opacity: 1; transform: translateX(0); }
}
.row-iter { animation: rowIn .28s ease both; }

/* ── Bar kapasitas ── */
.cap-bar { transition: width .6s cubic-bezier(.4,0,.2,1); }

/* ── Connector line ── */
.connector { position: relative; }
.connector::before {
    content: '';
    position: absolute;
    left: 19px; top: 40px;
    width: 2px; bottom: -8px;
    background: #e5e7eb;
    z-index: 0;
}
.connector:last-child::before { display: none; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-medium">Visualisasi Greedy</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-diagram-project text-purple-600 text-sm"></i>
            </span>
            Visualisasi Algoritma Greedy
        </h1>
        <p class="text-gray-500 text-sm mt-1">Lihat setiap langkah keputusan algoritma Best-Fit secara transparan</p>
    </div>
    <a href="{{ route('admin.jadwal.alokasi') }}"
       class="inline-flex items-center gap-2 text-sm text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-4 py-2 rounded-xl transition">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Ke Alokasi Batch
    </a>
</div>

{{-- Penjelasan singkat --}}
<div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-700 text-white rounded-2xl p-5 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-arrow-down-1-9 text-sm"></i>
            </div>
            <div>
                <p class="font-semibold text-sm">Langkah 1: Urutkan</p>
                <p class="text-purple-200 text-xs mt-0.5">Ruang diurutkan berdasarkan kapasitas dari terkecil ke terbesar</p>
            </div>
        </div>
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-filter text-sm"></i>
            </div>
            <div>
                <p class="font-semibold text-sm">Langkah 2: Filter</p>
                <p class="text-purple-200 text-xs mt-0.5">Gugurkan ruang yang kapasitas atau fasilitasnya tidak memenuhi syarat</p>
            </div>
        </div>
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-bolt text-sm"></i>
            </div>
            <div>
                <p class="font-semibold text-sm">Langkah 3: Pilih Pertama</p>
                <p class="text-purple-200 text-xs mt-0.5">Iterasi dari ruang terkecil, pilih yang pertama kali tersedia (Best-Fit)</p>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- FORM INPUT SIMULASI --}}
{{-- ======================================================== --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-sliders text-purple-500"></i> Parameter Simulasi
    </h2>

    <form method="POST" action="{{ route('admin.greedy.log') }}" id="formSimulasi">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal"
                       value="{{ old('tanggal', $input['tanggal'] ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jam Mulai <span class="text-red-500">*</span>
                </label>
                <input type="time" name="jam_mulai"
                       value="{{ old('jam_mulai', $input['jam_mulai'] ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jam Selesai <span class="text-red-500">*</span>
                </label>
                <input type="time" name="jam_selesai"
                       value="{{ old('jam_selesai', $input['jam_selesai'] ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jumlah Peserta <span class="text-red-500">*</span>
                </label>
                <input type="number" name="jumlah_peserta" min="1" max="1000"
                       value="{{ old('jumlah_peserta', $input['jumlah_peserta'] ?? '') }}"
                       placeholder="cth: 35"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        {{-- Fasilitas --}}
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Fasilitas Dibutuhkan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach(['proyektor','ac','wifi','papan_tulis','microphone','komputer','lcd','sound_system'] as $fas)
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="fasilitas[]" value="{{ $fas }}"
                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                           {{ in_array($fas, $input['fasilitas'] ?? []) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $fas)) }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-play"></i> Jalankan Simulasi
        </button>
    </form>
</div>

{{-- ======================================================== --}}
{{-- HASIL SIMULASI --}}
{{-- ======================================================== --}}
@if($log !== null)

{{-- Banner hasil akhir --}}
<div class="mb-5">
    @if($hasilAkhir)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-green-800 text-lg">Ruang ditemukan!</p>
            <p class="text-green-700 mt-0.5">
                Algoritma memilih <strong>{{ $hasilAkhir->kode_ruang }} — {{ $hasilAkhir->nama_ruang }}</strong>
                (Gedung {{ $hasilAkhir->gedung }}, kapasitas {{ $hasilAkhir->kapasitas }} kursi).
            </p>
            @php $sisa = $hasilAkhir->kapasitas - $input['jumlah_peserta']; @endphp
            <p class="text-green-600 text-sm mt-1">
                Sisa kapasitas: <strong>{{ $sisa }} kursi</strong>
                ({{ $input['jumlah_peserta'] }} / {{ $hasilAkhir->kapasitas }} terpakai).
                @if($sisa === 0)
                    <span class="ml-1 text-xs bg-green-200 text-green-800 px-2 py-0.5 rounded-full">Pas Sempurna!</span>
                @elseif($sisa <= 5)
                    <span class="ml-1 text-xs bg-green-200 text-green-800 px-2 py-0.5 rounded-full">Sangat Optimal</span>
                @endif
            </p>
        </div>
    </div>
    @else
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 flex items-start gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-xmark text-red-500 text-xl"></i>
        </div>
        <div>
            <p class="font-semibold text-red-800 text-lg">Tidak ada ruang tersedia</p>
            <p class="text-red-600 mt-0.5">Semua ruang yang memenuhi syarat telah terpakai pada slot waktu tersebut. Coba ubah jam atau tanggal.</p>
        </div>
    </div>
    @endif
</div>

{{-- Log langkah-langkah algoritma --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-terminal text-purple-500"></i>
            Log Eksekusi Algoritma
        </h2>
        <span class="text-xs text-gray-400">{{ count($log) }} langkah dieksekusi</span>
    </div>

    <div class="divide-y divide-gray-50 p-6 space-y-5">

    @foreach($log as $step)

        {{-- ── LANGKAH 0: Inisialisasi ── --}}
        @if($step['tipe'] === 'inisialisasi')
        <div class="step-card">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 border-2 border-blue-300 flex items-center justify-center shrink-0 z-10">
                    <i class="fa-solid fa-play text-blue-600 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Inisialisasi</p>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $step['pesan'] }}</p>
                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            <p class="text-lg font-bold text-blue-700">{{ $step['data']['total_ruang'] }}</p>
                            <p class="text-xs text-blue-500">ruang aktif</p>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-3 text-center">
                            <p class="text-lg font-bold text-purple-700">{{ $step['data']['jumlah_peserta'] }}</p>
                            <p class="text-xs text-purple-500">peserta</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center sm:col-span-2">
                            <p class="text-sm font-bold text-gray-700">{{ $step['data']['jam'] }}</p>
                            <p class="text-xs text-gray-500">{{ $step['data']['tanggal'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LANGKAH 1: Filter Kapasitas ── --}}
        @elseif($step['tipe'] === 'filter_kapasitas')
        <div class="step-card">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-amber-100 border-2 border-amber-300 flex items-center justify-center shrink-0 z-10">
                    <i class="fa-solid fa-ruler text-amber-600 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Filter Kapasitas</p>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $step['pesan'] }}</p>

                    @if(!empty($step['data']['gugur']))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($step['data']['gugur'] as $r)
                        <div class="inline-flex items-center gap-1.5 bg-red-50 border border-red-100 text-red-700 text-xs px-3 py-1.5 rounded-xl">
                            <i class="fa-solid fa-xmark"></i>
                            {{ $r['kode'] }} ({{ $r['kapasitas'] }} kursi, kurang {{ $r['selisih'] }})
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <p class="text-xs text-green-600 mt-2 font-medium">
                        <i class="fa-solid fa-check mr-1"></i>{{ $step['data']['lolos'] }} ruang lolos ke tahap berikutnya
                    </p>
                </div>
            </div>
        </div>

        {{-- ── LANGKAH 2: Filter Fasilitas ── --}}
        @elseif($step['tipe'] === 'filter_fasilitas')
        <div class="step-card">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-100 border-2 border-indigo-300 flex items-center justify-center shrink-0 z-10">
                    <i class="fa-solid fa-plug text-indigo-600 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Filter Fasilitas</p>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $step['pesan'] }}</p>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($step['data']['dibutuhkan'] as $fas)
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-check mr-1"></i>{{ ucwords(str_replace('_', ' ', $fas)) }}
                        </span>
                        @endforeach
                    </div>

                    @if(!empty($step['data']['gugur']))
                    <div class="mt-3 space-y-1.5">
                        @foreach($step['data']['gugur'] as $r)
                        <div class="flex items-center gap-2 text-xs text-red-600 bg-red-50 rounded-lg px-3 py-1.5">
                            <i class="fa-solid fa-xmark"></i>
                            <span><strong>{{ $r['kode'] }}</strong> tidak punya: {{ implode(', ', $r['kurang']) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <p class="text-xs text-green-600 mt-2 font-medium">
                        <i class="fa-solid fa-check mr-1"></i>{{ $step['data']['lolos'] }} ruang lolos ke iterasi greedy
                    </p>
                </div>
            </div>
        </div>

        {{-- ── LANGKAH 3: Tidak Ada Kandidat ── --}}
        @elseif($step['tipe'] === 'tidak_ada_kandidat')
        <div class="step-card">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-red-100 border-2 border-red-300 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-ban text-red-500 text-xs"></i>
                </div>
                <div>
                    <p class="font-semibold text-red-700 text-sm">Algoritma Berhenti</p>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $step['pesan'] }}</p>
                </div>
            </div>
        </div>

        {{-- ── LANGKAH 3: Iterasi Greedy ── --}}
        @elseif($step['tipe'] === 'iterasi_greedy')
        <div class="step-card">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-purple-100 border-2 border-purple-400 flex items-center justify-center shrink-0 z-10">
                    <i class="fa-solid fa-bolt text-purple-600 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Iterasi Greedy Best-Fit</p>
                    <p class="text-gray-500 text-sm mt-0.5 mb-4">{{ $step['pesan'] }}</p>

                    {{-- Tabel iterasi --}}
                    <div class="space-y-2">
                        @foreach($step['data']['iterasi'] as $idx => $iter)
                        @php
                            $ruang = $iter['ruang'];
                            $pctPeserta = min(round(($input['jumlah_peserta'] / $ruang->kapasitas) * 100), 100);
                        @endphp
                        <div class="row-iter flex items-center gap-3 p-3 rounded-xl border
                            {{ $iter['dipilih']
                                ? 'bg-green-50 border-green-200'
                                : 'bg-gray-50 border-gray-100 opacity-80' }}"
                            style="animation-delay: {{ $idx * 0.06 }}s">

                            {{-- Nomor iterasi --}}
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                {{ $iter['dipilih'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $iter['nomor'] }}
                            </div>

                            {{-- Info ruang --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-sm {{ $iter['dipilih'] ? 'text-green-800' : 'text-gray-700' }}">
                                        {{ $ruang->kode_ruang }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $ruang->nama_ruang }}</span>
                                    <span class="text-xs {{ $iter['dipilih'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 rounded-full">
                                        {{ $ruang->kapasitas }} kursi
                                    </span>
                                </div>

                                {{-- Bar kapasitas --}}
                                <div class="mt-1.5 flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden max-w-[120px]">
                                        <div class="cap-bar h-full rounded-full {{ $iter['dipilih'] ? 'bg-green-500' : 'bg-gray-400' }}"
                                             style="width: {{ $pctPeserta }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $pctPeserta }}% terpakai</span>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="shrink-0 text-right">
                                @if($iter['dipilih'])
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 border border-green-200 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-circle-check"></i> DIPILIH
                                </span>
                                @else
                                <div>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 border border-red-100 px-2.5 py-1 rounded-lg">
                                        <i class="fa-solid fa-xmark"></i> LEWATI
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1 max-w-[180px] text-right leading-snug">
                                        {{ $iter['alasan_gugur'] }}
                                    </p>
                                </div>
                                @endif
                            </div>

                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        @endif

    @endforeach

    </div>{{-- end space-y-5 --}}
</div>

@endif
{{-- end if $log --}}

@endsection
