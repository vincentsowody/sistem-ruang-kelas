@extends('layouts.app')
@section('title', 'Detail Pengguna')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">Pengguna</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">{{ $user->name }}</span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-3 text-sm">
        <i class="fa-solid fa-circle-check text-green-500 shrink-0"></i>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-4 text-sm">
        <div class="flex items-center gap-2 mb-1">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 shrink-0"></i>
            <span class="font-semibold">Perhatian</span>
        </div>
        <p>{!! session('warning') !!}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-3 text-sm">
        <i class="fa-solid fa-triangle-exclamation text-red-500 shrink-0"></i>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-xl transition text-sm">
                <i class="fa-solid fa-pen"></i> Edit
            </a>

            {{-- Tombol reset password: kirim link via email --}}
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                  onsubmit="return confirm('Kirim link reset password ke email {{ $user->email }}?')">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded-xl transition text-sm">
                    <i class="fa-solid fa-key"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Profil --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            {{-- Avatar --}}
            <div class="flex flex-col items-center text-center mb-5">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mb-3">
                    <span class="text-white font-bold text-3xl">{{ strtoupper(substr($user->name,0,1)) }}</span>
                </div>
                <h2 class="font-bold text-gray-800 text-lg">{{ $user->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                @php
                    $roleWarna = match($user->role) {
                        'admin'     => 'bg-purple-100 text-purple-700',
                        'dosen'     => 'bg-green-100 text-green-700',
                        'mahasiswa' => 'bg-blue-100 text-blue-700',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                    $roleIkon = match($user->role) {
                        'admin'     => 'fa-user-shield',
                        'dosen'     => 'fa-chalkboard-user',
                        'mahasiswa' => 'fa-user-graduate',
                        default     => 'fa-user',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $roleWarna }} mt-2">
                    <i class="fa-solid {{ $roleIkon }}"></i> {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="space-y-2.5">
                @foreach([
                    ['label'=>'NIP / NIM',      'value'=>$user->nip_nim ?? '-',       'icon'=>'fa-id-card'],
                    ['label'=>'Program Studi',   'value'=>$user->program_studi ?? '-', 'icon'=>'fa-graduation-cap'],
                    ['label'=>'No. HP',          'value'=>$user->no_hp ?? '-',         'icon'=>'fa-phone'],
                    ['label'=>'Bergabung',       'value'=>$user->created_at->locale('id')->isoFormat('D MMM Y'), 'icon'=>'fa-calendar'],
                ] as $row)
                <div class="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
                    <i class="fa-solid {{ $row['icon'] }} text-gray-300 text-sm mt-0.5 w-4"></i>
                    <div>
                        <p class="text-xs text-gray-400">{{ $row['label'] }}</p>
                        <p class="text-sm font-medium text-gray-700">{{ $row['value'] }}</p>
                    </div>
                </div>
                @endforeach

                <div class="flex items-center gap-3 py-2">
                    <i class="fa-solid fa-circle text-sm w-4 {{ $user->is_active ? 'text-green-400' : 'text-gray-300' }}"></i>
                    <div>
                        <p class="text-xs text-gray-400">Status</p>
                        <p class="text-sm font-medium {{ $user->is_active ? 'text-green-700' : 'text-gray-500' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Statistik Aktivitas</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Reservasi</span>
                    <span class="font-bold text-gray-800">{{ $stats['total_reservasi'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Disetujui</span>
                    <span class="font-bold text-green-600">{{ $stats['reservasi_disetujui'] }}</span>
                </div>
                @if($user->isDosen())
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Jadwal Aktif</span>
                    <span class="font-bold text-blue-600">{{ $stats['total_jadwal'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat Reservasi --}}
    <div class="lg:col-span-2 space-y-4">

        @if($user->isDosen() && $user->jadwalTetap->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-blue-500"></i>
                <h2 class="font-semibold text-gray-800">Jadwal Mengajar</h2>
                <span class="ml-auto text-xs bg-blue-50 text-blue-600 font-medium px-2.5 py-1 rounded-full">
                    {{ $user->jadwalTetap->count() }} jadwal
                </span>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($user->jadwalTetap->take(5) as $jadwal)
                <div class="px-6 py-3 flex items-center gap-4">
                    @php
                        $hariWarna = match($jadwal->hari) {
                            'senin'  => 'bg-blue-100 text-blue-700',
                            'selasa' => 'bg-purple-100 text-purple-700',
                            'rabu'   => 'bg-teal-100 text-teal-700',
                            'kamis'  => 'bg-orange-100 text-orange-700',
                            'jumat'  => 'bg-green-100 text-green-700',
                            'sabtu'  => 'bg-pink-100 text-pink-700',
                            default  => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $hariWarna }} capitalize w-20 text-center flex-shrink-0">
                        {{ $jadwal->hari }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $jadwal->mata_kuliah }}</p>
                        <p class="text-xs text-gray-400">Kelas {{ $jadwal->kelas }} · {{ $jadwal->ruangKelas->kode_ruang }}</p>
                    </div>
                    <p class="text-xs font-mono text-gray-500 flex-shrink-0">
                        {{ substr($jadwal->jam_mulai,0,5) }}–{{ substr($jadwal->jam_selesai,0,5) }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reservasi Terbaru --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-purple-500"></i>
                <h2 class="font-semibold text-gray-800">Riwayat Reservasi</h2>
                <span class="ml-auto text-xs bg-purple-50 text-purple-600 font-medium px-2.5 py-1 rounded-full">
                    {{ $user->reservasi->count() }} total
                </span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($user->reservasi->take(8) as $rsv)
                @php
                    $badge = match($rsv->status) {
                        'menunggu'   => 'bg-yellow-100 text-yellow-700',
                        'disetujui'  => 'bg-green-100 text-green-700',
                        'ditolak'    => 'bg-red-100 text-red-700',
                        'dibatalkan' => 'bg-gray-100 text-gray-600',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <div class="px-6 py-3 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $rsv->keperluan }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $rsv->ruangKelas->kode_ruang }} ·
                            {{ $rsv->tanggal->locale('id')->isoFormat('D MMM Y') }} ·
                            {{ substr($rsv->jam_mulai,0,5) }}–{{ substr($rsv->jam_selesai,0,5) }}
                        </p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }} capitalize flex-shrink-0">
                        {{ $rsv->status }}
                    </span>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <p class="text-gray-400 text-sm">Belum ada riwayat reservasi</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
