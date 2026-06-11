@extends('layouts.app')
@section('title', 'Detail Reservasi')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.reservasi.index') }}" class="hover:text-blue-600">Reservasi</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">{{ $reservasi->kode_reservasi }}</span>
    </div>
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-gray-800">{{ $reservasi->keperluan }}</h1>
        @php
            $badge = match($reservasi->status) {
                'menunggu'   => 'bg-yellow-100 text-yellow-700',
                'disetujui'  => 'bg-green-100 text-green-700',
                'ditolak'    => 'bg-red-100 text-red-700',
                'dibatalkan' => 'bg-gray-100 text-gray-600',
                default      => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <span class="text-sm font-semibold px-4 py-1.5 rounded-full {{ $badge }} capitalize">
            {{ $reservasi->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Lengkap --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clipboard text-blue-500"></i> Detail Reservasi
            </h2>
            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                @foreach([
                    ['label'=>'Kode Reservasi', 'value'=>$reservasi->kode_reservasi],
                    ['label'=>'Pemohon',         'value'=>$reservasi->pemohon->name.' ('.$reservasi->pemohon->role.')'],
                    ['label'=>'Jenis Kegiatan',  'value'=>ucwords(str_replace('_',' ',$reservasi->jenis_kegiatan))],
                    ['label'=>'Jumlah Peserta',  'value'=>$reservasi->jumlah_peserta.' orang'],
                    ['label'=>'Tanggal',         'value'=>$reservasi->tanggal->locale('id')->isoFormat('dddd, D MMMM Y')],
                    ['label'=>'Waktu',           'value'=>substr($reservasi->jam_mulai,0,5).' – '.substr($reservasi->jam_selesai,0,5).' ('.($reservasi->durasi_menit).' menit)'],
                ] as $row)
                <div class="py-2 border-b border-gray-50">
                    <p class="text-xs text-gray-400 mb-0.5">{{ $row['label'] }}</p>
                    <p class="text-sm font-medium text-gray-800">{{ $row['value'] }}</p>
                </div>
                @endforeach
            </div>
            @if($reservasi->keterangan)
            <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 mb-1">Keterangan</p>
                <p class="text-sm text-gray-700">{{ $reservasi->keterangan }}</p>
            </div>
            @endif
        </div>

        {{-- Ruang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-green-500"></i> Ruang yang Diminta
            </h2>
            <div class="flex items-center gap-4 bg-blue-50 rounded-xl p-4 mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-school text-blue-600"></i>
                </div>
                <div>
                    <p class="font-bold text-blue-700 text-lg">{{ $reservasi->ruangKelas->kode_ruang }}</p>
                    <p class="text-sm text-gray-600">{{ $reservasi->ruangKelas->nama_ruang }}</p>
                    <p class="text-xs text-gray-400">{{ $reservasi->ruangKelas->gedung }} · {{ $reservasi->ruangKelas->kapasitas }} kursi</p>
                    <p class="text-xs text-gray-400">{{ $reservasi->ruangKelas->fasilitas_list }}</p>
                </div>
            </div>

            {{-- Saran Greedy --}}
            @if($reservasi->ruangSaran)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Saran Ruang Alternatif (Greedy Best-Fit)
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-door-open text-amber-600"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $reservasi->ruangSaran->kode_ruang }}</p>
                        <p class="text-sm text-gray-600">{{ $reservasi->ruangSaran->nama_ruang }}</p>
                        <p class="text-xs text-gray-400">{{ $reservasi->ruangSaran->kapasitas }} kursi · {{ $reservasi->ruangSaran->fasilitas_list }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel Approval --}}
    <div class="space-y-4">

        @if($reservasi->isMenunggu())
        {{-- Setujui --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-gavel text-blue-500"></i> Proses Persetujuan
            </h2>

            {{-- Setujui --}}
            <form method="POST" action="{{ route('admin.reservasi.setujui', $reservasi) }}" class="mb-3">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan (opsional)</label>
                    <textarea name="catatan_admin" rows="2" placeholder="Catatan untuk pemohon..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-check"></i> Setujui Reservasi
                </button>
            </form>

            {{-- Tolak --}}
            <form method="POST" action="{{ route('admin.reservasi.tolak', $reservasi) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan_admin" rows="2" required placeholder="Masukkan alasan penolakan..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm border border-red-200">
                    <i class="fa-solid fa-xmark"></i> Tolak Reservasi
                </button>
            </form>
        </div>

        @else
        {{-- Sudah diproses --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Hasil Proses</h2>
            <div class="text-center py-4">
                <div class="w-16 h-16 {{ $reservasi->isDisetujui() ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid {{ $reservasi->isDisetujui() ? 'fa-check text-green-600' : 'fa-xmark text-red-600' }} text-2xl"></i>
                </div>
                <p class="font-semibold text-gray-800 capitalize">{{ $reservasi->status }}</p>
                @if($reservasi->diproses_pada)
                <p class="text-xs text-gray-400 mt-1">
                    {{ $reservasi->diproses_pada->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                    @if($reservasi->diprosesDari) · {{ $reservasi->diprosesDari->name }} @endif
                </p>
                @endif
                @if($reservasi->catatan_admin)
                <p class="text-sm text-gray-600 mt-3 bg-gray-50 rounded-xl p-3 text-left">
                    {{ $reservasi->catatan_admin }}
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- Info Pemohon --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-user text-purple-500"></i> Pemohon
            </h2>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-user text-purple-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $reservasi->pemohon->name }}</p>
                    <p class="text-xs text-gray-500">{{ $reservasi->pemohon->email }}</p>
                    <p class="text-xs text-gray-400 capitalize mt-0.5">{{ $reservasi->pemohon->role }}
                        @if($reservasi->pemohon->program_studi) · {{ $reservasi->pemohon->program_studi }} @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
