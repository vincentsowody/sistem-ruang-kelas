@extends('layouts.app')
@section('title', 'Detail Ruang — ' . $ruang->kode_ruang)

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.ruang.index') }}" class="hover:text-blue-600">Ruang Kelas</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">{{ $ruang->kode_ruang }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">{{ $ruang->nama_ruang }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.ruang.edit', $ruang) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-xl transition text-sm">
                <i class="fa-solid fa-pen"></i> Edit
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Ruang --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-blue-500"></i> Informasi Ruang
            </h2>
            <div class="space-y-3">
                @php
                    $statusWarna = match($ruang->status) {
                        'aktif'     => 'bg-green-100 text-green-700',
                        'nonaktif'  => 'bg-gray-100 text-gray-600',
                        'perbaikan' => 'bg-yellow-100 text-yellow-700',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                    $jenisWarna = match($ruang->jenis) {
                        'kelas'        => 'bg-blue-100 text-blue-700',
                        'laboratorium' => 'bg-purple-100 text-purple-700',
                        'aula'         => 'bg-orange-100 text-orange-700',
                        'seminar'      => 'bg-teal-100 text-teal-700',
                        default        => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Kode Ruang</span>
                    <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg text-sm">{{ $ruang->kode_ruang }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Gedung</span>
                    <span class="text-sm font-medium text-gray-800">{{ $ruang->gedung }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Lantai</span>
                    <span class="text-sm font-medium text-gray-800">{{ $ruang->lantai }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Kapasitas</span>
                    <span class="text-sm font-medium text-gray-800">{{ $ruang->kapasitas }} kursi</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Jenis</span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $jenisWarna }} capitalize">{{ $ruang->jenis }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusWarna }} capitalize">{{ $ruang->status }}</span>
                </div>
            </div>

            @if($ruang->keterangan)
            <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 font-medium mb-1">Keterangan</p>
                <p class="text-sm text-gray-600">{{ $ruang->keterangan }}</p>
            </div>
            @endif
        </div>

        {{-- Fasilitas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plug text-purple-500"></i> Fasilitas
            </h2>
            @if(!empty($ruang->fasilitas))
            <div class="flex flex-wrap gap-2">
                @php
                    $fasIkon = [
                        'proyektor'=>'fa-film','ac'=>'fa-snowflake','papan_tulis'=>'fa-chalkboard',
                        'wifi'=>'fa-wifi','komputer'=>'fa-computer','sound_system'=>'fa-volume-high',
                        'podium'=>'fa-person-chalkboard','kamera'=>'fa-camera',
                    ];
                @endphp
                @foreach($ruang->fasilitas as $fas)
                <span class="flex items-center gap-1.5 bg-purple-50 text-purple-700 text-xs font-medium px-3 py-1.5 rounded-full">
                    <i class="fa-solid {{ $fasIkon[$fas] ?? 'fa-check' }} text-xs"></i>
                    {{ ucfirst(str_replace('_',' ',$fas)) }}
                </span>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400">Belum ada fasilitas tercatat</p>
            @endif
        </div>
    </div>

    {{-- Jadwal & Reservasi --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Jadwal Tetap --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-blue-500"></i>
                <h2 class="font-semibold text-gray-800">Jadwal Tetap</h2>
                <span class="ml-auto text-xs bg-blue-50 text-blue-600 font-medium px-2.5 py-1 rounded-full">
                    {{ $ruang->jadwalTetap->count() }} jadwal
                </span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($ruang->jadwalTetap as $jadwal)
                <div class="px-6 py-3 flex items-center gap-4">
                    <div class="w-20 text-center flex-shrink-0">
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg capitalize">
                            {{ $jadwal->hari }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $jadwal->dosen->name }} · Kelas {{ $jadwal->kelas }} · {{ $jadwal->sks }} SKS
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-medium text-gray-700">{{ substr($jadwal->jam_mulai,0,5) }} – {{ substr($jadwal->jam_selesai,0,5) }}</p>
                        <span class="text-xs {{ $jadwal->status == 'aktif' ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $jadwal->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-gray-400 text-sm">Belum ada jadwal tetap di ruang ini</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Reservasi Mendatang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-green-500"></i>
                <h2 class="font-semibold text-gray-800">Reservasi Mendatang</h2>
                <span class="ml-auto text-xs bg-green-50 text-green-600 font-medium px-2.5 py-1 rounded-full">
                    yang disetujui
                </span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($ruang->reservasi as $rsv)
                <div class="px-6 py-3 flex items-center gap-4">
                    <div class="text-center flex-shrink-0 w-14">
                        <p class="text-xs font-bold text-gray-700">{{ $rsv->tanggal->format('d') }}</p>
                        <p class="text-xs text-gray-400">{{ $rsv->tanggal->locale('id')->isoFormat('MMM') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $rsv->keperluan }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $rsv->pemohon->name }} · {{ $rsv->jumlah_peserta }} peserta</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-medium text-gray-700">{{ substr($rsv->jam_mulai,0,5) }} – {{ substr($rsv->jam_selesai,0,5) }}</p>
                        <p class="text-xs text-gray-400">{{ $rsv->kode_reservasi }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-gray-400 text-sm">Tidak ada reservasi mendatang</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
