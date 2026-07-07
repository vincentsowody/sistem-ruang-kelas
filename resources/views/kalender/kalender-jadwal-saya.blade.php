@extends('layouts.app')
@section('title', 'Jadwal Saya')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Jadwal Saya</h1>
        <p class="text-gray-500 text-sm mt-1">
            @if(!$belumLengkap)
                {{ $mahasiswa->program_studi }} — Semester {{ $mahasiswa->semester }} — Kelas {{ $mahasiswa->kelas }}
            @else
                Jadwal kuliah mingguan sesuai semester dan kelas Anda
            @endif
        </p>
    </div>
    <a href="{{ route('kalender.index') }}"
       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-xl transition text-sm">
        <i class="fa-solid fa-calendar-week"></i> Lihat Kalender Umum
    </a>
</div>

@if($belumLengkap)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-5 flex items-start gap-3">
    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
    <div>
        <p class="font-semibold">Data semester/kelas Anda belum diisi</p>
        <p class="text-sm mt-1">
            Untuk menampilkan jadwal kuliah Anda secara otomatis, silakan minta admin
            melengkapi data <strong>program studi, semester, dan kelas</strong> pada akun Anda.
            Sementara itu Anda tetap bisa melihat semua jadwal lewat menu
            <a href="{{ route('kalender.index') }}" class="underline font-medium">Kalender</a>.
        </p>
    </div>
</div>
@else

    @php
        $labelHari = [
            'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu',
            'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu',
        ];
        $totalJadwal = $jadwalPerHari->flatten()->count();
    @endphp

    @if($totalJadwal === 0)
    <div class="bg-white border border-gray-100 rounded-xl p-8 text-center text-gray-500">
        <i class="fa-solid fa-calendar-xmark text-3xl text-gray-300 mb-3"></i>
        <p>Belum ada jadwal kuliah tetap untuk {{ $mahasiswa->program_studi }} semester
            {{ $mahasiswa->semester }} kelas {{ $mahasiswa->kelas }}.</p>
        <p class="text-sm mt-1">Hubungi admin jika seharusnya sudah ada jadwal yang tayang di sini.</p>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($jadwalPerHari as $hari => $list)
            @if($list->isNotEmpty())
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">{{ $labelHari[$hari] }}</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($list as $j)
                    <div class="px-4 py-3 flex items-start gap-3">
                        <div class="w-16 shrink-0 text-xs font-medium text-blue-600 pt-0.5">
                            {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 text-sm truncate">{{ $j->mata_kuliah }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <i class="fa-solid fa-door-open mr-1"></i>{{ $j->ruangKelas->kode_ruang ?? '-' }} — {{ $j->ruangKelas->nama_ruang ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                <i class="fa-solid fa-user mr-1"></i>{{ $j->dosen->name ?? '-' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif
@endif

@endsection
