@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola data admin, dosen, dan mahasiswa</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.dosen-import.manual') }}"
           class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 font-medium px-4 py-2.5 rounded-xl transition text-sm border border-green-200">
            <i class="fa-solid fa-file-import"></i> Import Data Dosen
        </a>
        <a href="{{ route('admin.dosen-import.form') }}"
           class="inline-flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium px-4 py-2.5 rounded-xl transition text-sm border border-emerald-200">
            <i class="fa-solid fa-file-excel"></i> Import dari Excel
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
        </a>
    </div>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['label'=>'Total',     'value'=>$stats['total'],     'color'=>'blue',   'icon'=>'fa-users'],
        ['label'=>'Admin',     'value'=>$stats['admin'],     'color'=>'purple', 'icon'=>'fa-user-shield'],
        ['label'=>'Dosen',     'value'=>$stats['dosen'],     'color'=>'green',  'icon'=>'fa-chalkboard-user'],
        ['label'=>'Mahasiswa', 'value'=>$stats['mahasiswa'], 'color'=>'indigo', 'icon'=>'fa-user-graduate'],
        ['label'=>'Nonaktif',  'value'=>$stats['nonaktif'],  'color'=>'gray',   'icon'=>'fa-user-slash'],
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

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, email, NIP/NIM..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="role" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Role</option>
            @foreach(['admin','dosen','mahasiswa'] as $r)
            <option value="{{ $r }}" {{ request('role')==$r?'selected':'' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            <option value="aktif"   {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
            <option value="nonaktif"{{ request('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-filter mr-1"></i> Filter
        </button>
        @if(request()->anyFilled(['search','role','status']))
        <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm transition">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Pengguna</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">NIP / NIM</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Program Studi</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Role</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($userList as $user)
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
                <tr class="{{ !$user->is_active ? 'bg-gray-50 opacity-60' : 'hover:bg-gray-50' }} transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                    <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full ml-1">Anda</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="font-mono text-xs text-gray-600">{{ $user->nip_nim ?? '-' }}</span>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500">{{ $user->program_studi ?? '-' }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleWarna }}">
                            <i class="fa-solid {{ $roleIkon }} text-xs"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full transition cursor-pointer
                                    {{ $user->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                title="{{ $user->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                                  onsubmit="return confirm('Kirim link reset password ke email {{ addslashes($user->email) }}?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition" title="Reset Password">
                                    <i class="fa-solid fa-key text-sm"></i>
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <i class="fa-solid fa-users text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-400">Tidak ada pengguna ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($userList->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $userList->links() }}</div>
    @endif
</div>

@endsection
