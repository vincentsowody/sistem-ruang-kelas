@extends('layouts.app')
@section('title', 'Detail Reservasi — ' . $reservasi->kode_reservasi)

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('reservasi.index') }}" class="hover:text-blue-600">Reservasi Saya</a>
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

{{-- Flash saran greedy --}}
@if(session('warning'))
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
    <i class="fa-solid fa-wand-magic-sparkles text-amber-500 text-lg mt-0.5 flex-shrink-0"></i>
    <p class="text-amber-800 text-sm">{!! session('warning') !!}</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Detail Reservasi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clipboard text-blue-500"></i> Detail Reservasi
        </h2>
        <div class="space-y-3">
            @foreach([
                ['label' => 'Kode',          'value' => $reservasi->kode_reservasi],
                ['label' => 'Jenis Kegiatan','value' => ucwords(str_replace('_',' ',$reservasi->jenis_kegiatan))],
                ['label' => 'Tanggal',       'value' => $reservasi->tanggal->locale('id')->isoFormat('dddd, D MMMM Y')],
                ['label' => 'Waktu',         'value' => substr($reservasi->jam_mulai,0,5).' – '.substr($reservasi->jam_selesai,0,5).' ('.$reservasi->durasi_menit.' menit)'],
                ['label' => 'Jumlah Peserta','value' => $reservasi->jumlah_peserta.' orang'],
                ['label' => 'Diajukan',      'value' => $reservasi->created_at->locale('id')->isoFormat('D MMM Y, HH:mm')],
            ] as $row)
            <div class="flex justify-between items-start py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-500 flex-shrink-0">{{ $row['label'] }}</span>
                <span class="text-sm font-medium text-gray-800 text-right">{{ $row['value'] }}</span>
            </div>
            @endforeach

            @if($reservasi->keterangan)
            <div class="py-2">
                <p class="text-sm text-gray-500 mb-1">Keterangan</p>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $reservasi->keterangan }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Ruang & Status --}}
    <div class="space-y-4">

        {{-- Ruang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-green-500"></i> Ruang yang Dipesan
            </h2>
            <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-school text-blue-600"></i>
                </div>
                <div>
                    <p class="font-bold text-blue-700 text-lg">{{ $reservasi->ruangKelas->kode_ruang }}</p>
                    <p class="text-sm text-gray-600">{{ $reservasi->ruangKelas->nama_ruang }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $reservasi->ruangKelas->gedung }} · Lantai {{ $reservasi->ruangKelas->lantai }} ·
                        {{ $reservasi->ruangKelas->kapasitas }} kursi
                    </p>
                </div>
            </div>

            {{-- Saran Greedy --}}
            @if($reservasi->ruangSaran)
            <div class="mt-3 bg-amber-50 rounded-xl border border-amber-200 p-4">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Saran Ruang dari Greedy
                </p>
                <p class="font-bold text-gray-800">{{ $reservasi->ruangSaran->kode_ruang }}</p>
                <p class="text-sm text-gray-500">{{ $reservasi->ruangSaran->nama_ruang }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Kapasitas: {{ $reservasi->ruangSaran->kapasitas }} kursi
                </p>
            </div>
            @endif
        </div>

        {{-- Status & Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-timeline text-purple-500"></i> Status Proses
            </h2>

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-paper-plane text-blue-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Diajukan</p>
                        <p class="text-xs text-gray-400">
                            {{ $reservasi->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                        </p>
                    </div>
                </div>

                @if($reservasi->diproses_pada)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 {{ $reservasi->isDisetujui() ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid {{ $reservasi->isDisetujui() ? 'fa-check text-green-600' : 'fa-xmark text-red-600' }} text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 capitalize">{{ $reservasi->status }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $reservasi->diproses_pada->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                            @if($reservasi->diprosesDari)
                                · oleh {{ $reservasi->diprosesDari->name }}
                            @endif
                        </p>
                        @if($reservasi->catatan_admin)
                        <p class="text-xs text-gray-600 mt-1 bg-gray-50 rounded-lg p-2 italic">
                            "{{ $reservasi->catatan_admin }}"
                        </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Tombol Batalkan --}}
            @if($reservasi->isMenunggu() && $reservasi->pemohon_id === auth()->id())
            <form method="POST" action="{{ route('reservasi.batalkan', $reservasi) }}"
                  onsubmit="return confirm('Batalkan reservasi ini?')" class="mt-4">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 border border-red-200">
                    <i class="fa-solid fa-ban"></i> Batalkan Reservasi
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@endsection
