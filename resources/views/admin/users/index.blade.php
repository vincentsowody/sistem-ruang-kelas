@extends('layouts.app')
@section('title', 'Pengguna')
@section('page_title', 'Pengguna')
@section('page_subtitle', 'Kelola akun dosen dan mahasiswa')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Manajemen Pengguna</h1>
        <p class="text-sm text-slate-400 mt-0.5">{{ $userList->total() }} pengguna terdaftar</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl transition text-sm shadow-sm shadow-blue-200 w-full sm:w-auto">
        <i class="fa-solid fa-user-plus text-xs"></i> Tambah Pengguna
    </a>
</div>

{{-- Stat chips --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @foreach([
        ['Admin',      $stats['admin'],      'blue',   'fa-shield-halved'],
        ['Dosen',      $stats['dosen'],      'purple', 'fa-chalkboard-user'],
        ['Mahasiswa',  $stats['mahasiswa'],  'teal',   'fa-graduation-cap'],
        ['Nonaktif',   $stats['nonaktif'],   'red',    'fa-user-slash'],
    ] as [$lbl, $val, $col, $ico])
    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 bg-{{ $col }}-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid {{ $ico }} text-{{ $col }}-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xl font-black text-slate-800">{{ $val }}</p>
            <p class="text-[11px] text-slate-400">{{ $lbl }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[180px]">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau email..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <select name="role" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Role</option>
            @foreach(['admin','dosen','mahasiswa'] as $r)
            <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        {{-- FIX: filter status sudah didukung controller ($request->filled('status'))
             tapi belum ada di form — admin tidak bisa filter user nonaktif. --}}
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-filter text-xs"></i> Filter
        </button>
        @if(request()->anyFilled(['search','role','status']))
        <a href="{{ route('admin.users.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm transition">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>
</div>

{{-- Desktop table --}}
@if($userList->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
    <i class="fa-solid fa-users-slash text-slate-200 text-5xl mb-4"></i>
    <p class="text-slate-500 font-semibold">Tidak ada pengguna ditemukan</p>
</div>
@else
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Pengguna</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Email</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Role</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Terdaftar</th>
                    <th class="text-center px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($userList as $user)
                @php
                    [$roleBg, $roleText, $roleIco] = match($user->role) {
                        'admin'      => ['bg-blue-100',   'text-blue-700',   'fa-shield-halved'],
                        'dosen'      => ['bg-purple-100', 'text-purple-700', 'fa-chalkboard-user'],
                        'mahasiswa'  => ['bg-teal-100',   'text-teal-700',   'fa-graduation-cap'],
                        default      => ['bg-slate-100',  'text-slate-600',  'fa-user'],
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-gradient-to-br from-slate-400 to-slate-600 rounded-xl flex items-center justify-center shrink-0">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 text-sm">{{ $user->email }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleBg }} {{ $roleText }} capitalize">
                            <i class="fa-solid {{ $roleIco }} text-[10px]"></i> {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($user->is_active)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                            <i class="fa-solid fa-circle text-[6px]"></i> Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-red-100 text-red-600">
                            <i class="fa-solid fa-circle text-[6px]"></i> Nonaktif
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-400 text-sm">{{ $user->created_at->isoFormat('D MMM Y') }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.users.show', $user) }}" title="Detail"
                               class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" title="Edit"
                               class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            {{-- FIX: tombol toggle status hilang sebelumnya — route sudah
                                 ada di controller tapi tidak pernah bisa dipicu dari UI. --}}
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                                  onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="p-2 text-slate-400 {{ $user->is_active ? 'hover:text-amber-600 hover:bg-amber-50' : 'hover:text-green-600 hover:bg-green-50' }} rounded-lg transition">
                                    <i class="fa-solid {{ $user->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} text-sm"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($userList->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $userList->links() }}</div>
    @endif
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @foreach($userList as $user)
    @php
        [$roleBg, $roleText, $roleIco] = match($user->role) {
            'admin'     => ['bg-blue-100',   'text-blue-700',   'fa-shield-halved'],
            'dosen'     => ['bg-purple-100', 'text-purple-700', 'fa-chalkboard-user'],
            'mahasiswa' => ['bg-teal-100',   'text-teal-700',   'fa-graduation-cap'],
            default     => ['bg-slate-100',  'text-slate-600',  'fa-user'],
        };
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-gradient-to-br from-slate-400 to-slate-600 rounded-xl flex items-center justify-center shrink-0">
            <span class="text-white font-black text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-slate-800 text-sm truncate">{{ $user->name }}</p>
            <p class="text-xs text-slate-400 truncate mt-0.5">{{ $user->email }}</p>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $roleBg }} {{ $roleText }}">
                    <i class="fa-solid {{ $roleIco }} text-[9px]"></i> {{ ucfirst($user->role) }}
                </span>
                @if($user->is_active)
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                    <i class="fa-solid fa-circle text-[5px]"></i> Aktif
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600">
                    <i class="fa-solid fa-circle text-[5px]"></i> Nonaktif
                </span>
                @endif
            </div>
        </div>
        <div class="flex flex-col gap-1 shrink-0">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-green-100 text-slate-400 hover:text-green-600 rounded-lg transition">
                <i class="fa-solid fa-pen text-xs"></i>
            </a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                  onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-8 h-8 flex items-center justify-center bg-slate-100 {{ $user->is_active ? 'hover:bg-amber-100 hover:text-amber-600' : 'hover:bg-green-100 hover:text-green-600' }} text-slate-400 rounded-lg transition">
                    <i class="fa-solid {{ $user->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} text-xs"></i>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Hapus akun ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-red-100 text-slate-400 hover:text-red-600 rounded-lg transition">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
    @if($userList->hasPages())
    <div class="pt-2">{{ $userList->links() }}</div>
    @endif
</div>
@endif

@endsection