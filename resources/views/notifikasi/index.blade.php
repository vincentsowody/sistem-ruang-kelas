@extends('layouts.app')
@section('title', 'Notifikasi')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Notifikasi</h1>
        <p class="text-gray-500 text-sm mt-1">Semua pemberitahuan sistem untuk Anda</p>
    </div>
    @if($notifikasi->total() > 0)
    <form method="POST" action="{{ route('notifikasi.hapus-semua') }}"
          onsubmit="return confirm('Hapus semua notifikasi?')">
        @csrf @method('DELETE')
        <button type="submit"
            class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 font-medium px-4 py-2 rounded-xl transition text-sm border border-red-200">
            <i class="fa-solid fa-trash"></i> Hapus Semua
        </button>
    </form>
    @endif
</div>

<div class="space-y-3 max-w-2xl">
    @forelse($notifikasi as $notif)
    @php
        $ikonWarna = match($notif->tipe) {
            'reservasi_baru' => ['bg-blue-100',   'text-blue-600',   'fa-clipboard-list'],
            'disetujui'      => ['bg-green-100',  'text-green-600',  'fa-circle-check'],
            'ditolak'        => ['bg-red-100',     'text-red-600',    'fa-circle-xmark'],
            'dibatalkan'     => ['bg-gray-100',    'text-gray-500',   'fa-ban'],
            'pengingat'      => ['bg-yellow-100',  'text-yellow-600', 'fa-bell'],
            default          => ['bg-indigo-100',  'text-indigo-600', 'fa-circle-info'],
        };
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border {{ $notif->sudah_dibaca ? 'border-gray-100' : 'border-blue-200' }} p-5 flex items-start gap-4">

        {{-- Ikon --}}
        <div class="w-10 h-10 {{ $ikonWarna[0] }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid {{ $ikonWarna[2] }} {{ $ikonWarna[1] }}"></i>
        </div>

        {{-- Konten --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">
                        {{ $notif->judul }}
                        @if(!$notif->sudah_dibaca)
                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-1 align-middle"></span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $notif->pesan }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $notif->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} ·
                        {{ $notif->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($notif->reservasi)
                    @php
                        $linkReservasi = auth()->user()->isAdmin()
                            ? route('admin.reservasi.show', $notif->reservasi)
                            : route('reservasi.show', $notif->reservasi);
                    @endphp
                    <a href="{{ $linkReservasi }}"
                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                    @endif

                    <form method="POST" action="{{ route('notifikasi.destroy', $notif) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 text-center">
        <i class="fa-solid fa-bell-slash text-gray-200 text-6xl mb-4"></i>
        <p class="text-gray-500 font-medium">Tidak ada notifikasi</p>
        <p class="text-gray-400 text-sm mt-1">Anda akan menerima notifikasi ketika ada aktivitas baru</p>
    </div>
    @endforelse
</div>

@if($notifikasi->hasPages())
<div class="mt-4 max-w-2xl">{{ $notifikasi->links() }}</div>
@endif

@endsection
