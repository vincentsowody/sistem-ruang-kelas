<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Reservasi Ruang Kelas')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-50 font-sans">

<nav class="bg-blue-700 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-2">
                    <i class="fa-solid fa-school text-white text-lg"></i>
                </div>
                <div>
                    <span class="font-bold text-lg leading-tight block">SiRuang</span>
                    <span class="text-blue-100 text-xs">Sistem Reservasi Ruang Kelas</span>
                </div>
            </div>

            {{-- Menu --}}
            <div class="hidden md:flex items-center gap-1">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"       class="nav-link {{ request()->routeIs('admin.dashboard')    ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-gauge-high mr-1"></i> Dashboard</a>
                        <a href="{{ route('admin.ruang.index') }}"     class="nav-link {{ request()->routeIs('admin.ruang.*')      ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-door-open mr-1"></i> Ruang</a>
                        <a href="{{ route('admin.jadwal.index') }}"    class="nav-link {{ request()->routeIs('admin.jadwal.*')     ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-calendar-days mr-1"></i> Jadwal</a>
                        <a href="{{ route('admin.reservasi.index') }}" class="nav-link relative {{ request()->routeIs('admin.reservasi.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            <i class="fa-solid fa-clipboard-list mr-1"></i> Reservasi
                            @php $pending = \App\Models\Reservasi::menunggu()->count(); @endphp
                            @if($pending > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $pending > 9 ? '9+' : $pending }}</span>
                            @endif
                        </a>
                        <a href="{{ route('kalender.index') }}"        class="nav-link {{ request()->routeIs('kalender.*')        ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-calendar-week mr-1"></i> Kalender</a>

                    @elseif(auth()->user()->isDosen())
                        <a href="{{ route('dosen.dashboard') }}"    class="nav-link {{ request()->routeIs('dosen.dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-gauge-high mr-1"></i> Dashboard</a>
                        <a href="{{ route('kalender.index') }}"     class="nav-link {{ request()->routeIs('kalender.*')      ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-calendar-week mr-1"></i> Kalender</a>
                        <a href="{{ route('reservasi.create') }}"   class="nav-link {{ request()->routeIs('reservasi.create') ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-plus-circle mr-1"></i> Ajukan</a>
                        <a href="{{ route('reservasi.index') }}"    class="nav-link {{ request()->routeIs('reservasi.index')  ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-list mr-1"></i> Riwayat</a>

                    @else
                        <a href="{{ route('mahasiswa.dashboard') }}" class="nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-gauge-high mr-1"></i> Dashboard</a>
                        <a href="{{ route('kalender.index') }}"      class="nav-link {{ request()->routeIs('kalender.*')          ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-calendar-week mr-1"></i> Kalender</a>
                        <a href="{{ route('reservasi.create') }}"    class="nav-link {{ request()->routeIs('reservasi.create')    ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-plus-circle mr-1"></i> Ajukan</a>
                        <a href="{{ route('reservasi.index') }}"     class="nav-link {{ request()->routeIs('reservasi.index')     ? 'bg-white/20' : 'hover:bg-white/10' }}"><i class="fa-solid fa-list mr-1"></i> Riwayat</a>
                    @endif
                @endauth
            </div>

            {{-- User area --}}
            @auth
            <div class="flex items-center gap-2">

                {{-- Notifikasi dropdown --}}
                <div class="relative" id="notifWrapper">
                    <button onclick="toggleNotif()" class="relative p-2 rounded-lg hover:bg-white/10 transition">
                        <i class="fa-solid fa-bell text-white"></i>
                        <span id="notifBadge" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 items-center justify-center text-[10px] hidden"></span>
                    </button>

                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <p class="font-semibold text-gray-800 text-sm">Notifikasi</p>
                            <a href="{{ route('notifikasi.index') }}" class="text-blue-600 text-xs hover:underline">Lihat semua</a>
                        </div>
                        <div id="notifList" class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                            <div class="px-4 py-6 text-center">
                                <i class="fa-solid fa-spinner fa-spin text-gray-300 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Profile --}}
                <div class="relative">
                    <button onclick="toggleDropdown()"
                        class="flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2 transition">
                        <div class="w-7 h-7 bg-white/30 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-sm font-medium leading-tight">{{ Str::limit(auth()->user()->name, 15) }}</p>
                            <p class="text-blue-100 text-xs capitalize">{{ auth()->user()->role }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-blue-100"></i>
                    </button>

                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 z-50 border border-gray-100">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('notifikasi.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-bell w-4 text-gray-400"></i> Notifikasi
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>

{{-- Flash --}}
@foreach(['success'=>'green','error'=>'red','warning'=>'yellow'] as $type => $color)
@if(session($type))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-800 rounded-xl px-4 py-3 flex items-center gap-2">
        <i class="fa-solid {{ $type=='success' ? 'fa-circle-check' : ($type=='error' ? 'fa-circle-xmark' : 'fa-triangle-exclamation') }} text-{{ $color }}-500"></i>
        <span>{!! session($type) !!}</span>
    </div>
</div>
@endif
@endforeach

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @yield('content')
</main>

<footer class="border-t border-gray-200 mt-12 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-400">
        © {{ date('Y') }} Sistem Penjadwalan & Reservasi Ruang Kelas — Algoritma Greedy Best-Fit
    </div>
</footer>

<style>
.nav-link { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.5rem; font-size:.875rem; font-weight:500; color:white; transition:background-color .15s; }
</style>

<script>
// Dropdown user
function toggleDropdown() {
    document.getElementById('userDropdown').classList.toggle('hidden');
}

// Dropdown notifikasi
function toggleNotif() {
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) muatNotifikasi();
}

function muatNotifikasi() {
    fetch('{{ route("api.notifikasi.jumlah") }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.jumlah > 0) {
                badge.textContent = data.jumlah > 9 ? '9+' : data.jumlah;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
            }
        });

    // Muat preview notifikasi terbaru
    fetch('{{ route("notifikasi.index") }}', { headers: {'X-Requested-With': 'XMLHttpRequest'} });
}

// Polling badge notifikasi setiap 30 detik
setInterval(function() {
    fetch('{{ route("api.notifikasi.jumlah") }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.jumlah > 0) {
                badge.textContent = data.jumlah > 9 ? '9+' : data.jumlah;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }).catch(() => {});
}, 30000);

// Inisialisasi badge saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("api.notifikasi.jumlah") }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.jumlah > 0) {
                badge.textContent = data.jumlah > 9 ? '9+' : data.jumlah;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            }
        }).catch(() => {});
});

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('#notifWrapper'))
        document.getElementById('notifDropdown').classList.add('hidden');
    if (!e.target.closest('[onclick="toggleDropdown()"]'))
        document.getElementById('userDropdown').classList.add('hidden');
});
</script>

@yield('scripts')
</body>
</html>
