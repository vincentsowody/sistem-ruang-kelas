<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiRuang') — SiRuang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #1d4ed8;
            --sidebar-active-bg: rgba(29,78,216,.15);
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #e2e8f0;
            --accent: #3b82f6;
            --accent-glow: rgba(59,130,246,.25);
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ── Sidebar ── */
        #sidebar { background: var(--sidebar-bg); }
        .nav-label {
            font-size: 10px; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #475569;
            padding: 6px 14px 4px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-radius: 10px;
            font-size: .875rem; font-weight: 500;
            color: var(--sidebar-text);
            transition: all .15s ease;
            position: relative;
        }
        .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }
        .nav-link.active {
            background: var(--sidebar-active-bg);
            color: #60a5fa;
            font-weight: 600;
        }
        .nav-link.active::before {
            content: '';
            position: absolute; left: 0; top: 6px; bottom: 6px;
            width: 3px; border-radius: 0 3px 3px 0;
            background: var(--accent);
        }
        .nav-link .nav-icon { width: 18px; text-align: center; font-size: .85rem; }

        /* ── Cards & surfaces ── */
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        }
        .card-header {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 99px;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .stat-card .glow {
            position: absolute; top: -20px; right: -20px;
            width: 80px; height: 80px; border-radius: 50%;
            opacity: .12;
        }

        /* ── Buttons ── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            background: #2563eb; color: white;
            font-weight: 600; font-size: .875rem;
            padding: 10px 18px; border-radius: 12px;
            transition: all .15s; border: none; cursor: pointer;
            box-shadow: 0 1px 2px rgba(37,99,235,.3);
        }
        .btn-primary:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,.4); transform: translateY(-1px); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            background: #f1f5f9; color: #475569;
            font-weight: 500; font-size: .875rem;
            padding: 10px 18px; border-radius: 12px;
            transition: all .15s; border: none; cursor: pointer;
        }
        .btn-secondary:hover { background: #e2e8f0; }

        /* ── Form fields ── */
        .field {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-size: .875rem; font-family: 'Plus Jakarta Sans', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            background: white; color: #1e293b;
        }
        .field:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .field.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
        .field-icon { position: relative; }
        .field-icon .field { padding-left: 40px; }
        .field-icon .icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem; }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            background: #f8fafc; color: #64748b;
            font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            padding: 12px 16px; text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table tbody tr { border-bottom: 1px solid #f8fafc; transition: background .1s; }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody td { padding: 13px 16px; font-size: .875rem; color: #334155; vertical-align: middle; }

        /* ── Badge pulse ── */
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        .badge-live::after {
            content: ''; position: absolute; inset: 0; border-radius: 99px;
            background: #ef4444; animation: pulse-ring 1.5s ease-out infinite;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* ── Animations ── */
        @keyframes fadeSlideUp { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:translateY(0);} }
        .animate-in { animation: fadeSlideUp .25s ease both; }

        /* ── Page transition ── */
        #pageLoader {
            position: fixed; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #3b82f6);
            background-size: 200%;
            animation: loader 1s linear infinite;
            z-index: 9999;
            transition: opacity .3s;
        }
        @keyframes loader { from { background-position: 200%; } to { background-position: 0%; } }

        /* ── Toast ── */
        #toastContainer { position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; }
        .toast {
            display:flex; align-items:start; gap:12px;
            padding:14px 16px; border-radius:14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
            min-width:280px; max-width:380px;
            animation: fadeSlideUp .25s ease;
            backdrop-filter: blur(8px);
        }
        .toast-success { background:rgba(240,253,244,.97); border:1px solid #bbf7d0; color:#15803d; }
        .toast-error   { background:rgba(254,242,242,.97); border:1px solid #fecaca; color:#b91c1c; }
        .toast-warning { background:rgba(255,251,235,.97); border:1px solid #fde68a; color:#92400e; }

        @media (prefers-reduced-motion:reduce) { *, *::before, *::after { animation-duration:.01ms !important; transition-duration:.01ms !important; } }
    </style>
</head>
<body class="h-full">

{{-- Page loader --}}
<div id="pageLoader"></div>
<div id="toastContainer"></div>

@auth
@php $user = auth()->user(); $isAdmin = $user->isAdmin(); $isDosen = $user->isDosen(); @endphp

<div class="flex h-screen overflow-hidden">

    {{-- Mobile overlay --}}
    <div id="sidebarOverlay" onclick="closeSidebar()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity"></div>

    {{-- ── SIDEBAR ──────────────────────────────────────── --}}
    <aside id="sidebar"
           class="fixed lg:static inset-y-0 left-0 w-64 flex flex-col shrink-0
                  transition-transform duration-300 z-40 -translate-x-full lg:translate-x-0">

        {{-- Brand --}}
        <div class="px-5 py-5 border-b border-white/5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-900/40">
                <i class="fa-solid fa-school text-white text-sm"></i>
            </div>
            <div>
                <p class="font-extrabold text-white text-[15px] leading-tight tracking-tight">SiRuang</p>
                <p class="text-[11px] text-slate-500 leading-tight">Reservasi Ruang Kelas</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            @if($isAdmin)
            <p class="nav-label">Utama</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span> Dashboard
            </a>
            <p class="nav-label mt-3">Manajemen</p>
            <a href="{{ route('admin.ruang.index') }}" class="nav-link {{ request()->routeIs('admin.ruang.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-door-open"></i></span> Ruang Kelas
            </a>
            <a href="{{ route('admin.jadwal.index') }}" class="nav-link {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-calendar-days"></i></span> Jadwal Tetap
            </a>
            <a href="{{ route('admin.reservasi.index') }}" class="nav-link {{ request()->routeIs('admin.reservasi.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                Reservasi
                @php $pending = \App\Models\Reservasi::menunggu()->count(); @endphp
                @if($pending > 0)
                <span class="ml-auto relative flex h-5 w-5 shrink-0">
                    <span class="badge-live absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-60"></span>
                    <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-[10px] font-bold items-center justify-center">
                        {{ min($pending,9) }}{{ $pending>9?'+':'' }}
                    </span>
                </span>
                @endif
            </a>
            <a href="{{ route('kalender.index') }}" class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-calendar-week"></i></span> Kalender
            </a>
            <p class="nav-label mt-3">Sistem</p>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Pengguna
            </a>
            <a href="{{ route('admin.laporan.index') }}"
   class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="fa-solid fa-chart-bar"></i></span>
    Laporan
</a>

            @elseif($isDosen)
            <p class="nav-label">Menu</p>
            <a href="{{ route('dosen.dashboard') }}" class="nav-link {{ request()->routeIs('dosen.dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span> Dashboard
            </a>
            <a href="{{ route('kalender.index') }}" class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-calendar-week"></i></span> Kalender
            </a>
            <a href="{{ route('reservasi.create') }}" class="nav-link {{ request()->routeIs('reservasi.create') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-plus-circle"></i></span> Ajukan Reservasi
            </a>
            <a href="{{ route('reservasi.index') }}" class="nav-link {{ request()->routeIs('reservasi.index') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-list"></i></span> Riwayat Saya
            </a>

            @else
            <p class="nav-label">Menu</p>
            <a href="{{ route('mahasiswa.dashboard') }}" class="nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span> Dashboard
            </a>
            <a href="{{ route('kalender.index') }}" class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-calendar-week"></i></span> Kalender
            </a>
            <a href="{{ route('reservasi.create') }}" class="nav-link {{ request()->routeIs('reservasi.create') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-plus-circle"></i></span> Ajukan Reservasi
            </a>
            <a href="{{ route('reservasi.index') }}" class="nav-link {{ request()->routeIs('reservasi.index') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-list"></i></span> Riwayat Saya
            </a>
            @endif
        </nav>

        {{-- User --}}
        <div class="px-3 py-3 border-t border-white/5">
            <div class="relative">
                <button onclick="toggleDropdown()"
                    class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/5 transition text-left group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center shrink-0 shadow-sm">
                        <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-200 truncate leading-tight">{{ Str::limit($user->name,18) }}</p>
                        <p class="text-xs text-slate-500 capitalize">{{ $user->role }}</p>
                    </div>
                    <i class="fa-solid fa-ellipsis-vertical text-slate-600 text-sm group-hover:text-slate-400 transition"></i>
                </button>
                <div id="userDropdown"
                     class="hidden absolute bottom-full left-0 right-0 mb-2 bg-[#1e293b] rounded-2xl shadow-2xl border border-white/10 overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-white/5">
                        <p class="text-xs font-bold text-slate-200 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500 truncate mt-0.5">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-400 hover:text-slate-200 hover:bg-white/5 transition">
                        <i class="fa-solid fa-user-pen text-xs w-4 text-center"></i> Profil Saya
                    </a>
                    <a href="{{ route('notifikasi.index') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-400 hover:text-slate-200 hover:bg-white/5 transition">
                        <i class="fa-solid fa-bell text-xs w-4 text-center"></i> Notifikasi
                    </a>
                    <div class="border-t border-white/5 mt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition">
                                <i class="fa-solid fa-right-from-bracket text-xs w-4 text-center"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── MAIN ─────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-5 py-3 flex items-center justify-between shrink-0 sticky top-0 z-20"
                style="box-shadow:0 1px 0 rgba(0,0,0,.05)">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-500 transition">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
                <div>
                    <h1 class="text-sm font-bold text-slate-800 leading-tight">@yield('page_title','Dashboard')</h1>
                    <p class="text-xs text-slate-400 hidden sm:block">@yield('page_subtitle', now()->isoFormat('dddd, D MMMM Y'))</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Clock --}}
                <div class="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span id="liveClock" class="mono text-xs text-slate-600 font-medium">--:--</span>
                </div>

                {{-- Notif --}}
                <div class="relative" id="notifWrapper">
                    <button onclick="toggleNotif()"
                        class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 transition text-slate-500">
                        <i class="fa-solid fa-bell text-sm"></i>
                        <span id="notifBadge"
                            class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-white hidden"></span>
                    </button>
                    <div id="notifDropdown"
                         class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden animate-in">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                            <p class="font-bold text-slate-800 text-sm">Notifikasi</p>
                            <a href="{{ route('notifikasi.index') }}"
                               class="text-xs text-blue-600 hover:text-blue-700 font-semibold">Lihat semua</a>
                        </div>
                        <div id="notifList" class="max-h-72 overflow-y-auto">
                            <div class="flex flex-col items-center py-8 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-bell-slash text-slate-300 text-xl"></i>
                                </div>
                                <p class="text-sm text-slate-400">Tidak ada notifikasi baru</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Avatar --}}
                <button onclick="toggleDropdown()" class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center shadow-sm hover:shadow-md transition lg:hidden">
                    <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name,0,1)) }}</span>
                </button>
            </div>
        </header>

        {{-- Toast (flash) messages --}}
        @foreach(['success'=>['success','fa-circle-check'],'error'=>['error','fa-circle-xmark'],'warning'=>['warning','fa-triangle-exclamation']] as $type => [$cls, $ico])
        @if(session($type))
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast('{{ addslashes(session($type)) }}','{{ $cls }}'));
        </script>
        @endif
        @endforeach

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto">
            <div class="p-5 lg:p-6 animate-in">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@else
<main class="min-h-screen">@yield('content')</main>
@endauth

<script>
// Sidebar
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebarOverlay');
    const open = !sb.classList.contains('-translate-x-full');
    sb.classList.toggle('-translate-x-full', open);
    ov.classList.toggle('hidden', open);
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.add('hidden');
}

// Dropdowns
function toggleDropdown() { document.getElementById('userDropdown').classList.toggle('hidden'); }
function toggleNotif() {
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) loadNotif();
}
document.addEventListener('click', e => {
    if (!e.target.closest('#notifWrapper')) {
        const notifDD = document.getElementById('notifDropdown');
        if (notifDD) notifDD.classList.add('hidden');
    }
    if (!e.target.closest('[onclick="toggleDropdown()"]')) {
        const userDD = document.getElementById('userDropdown');
        if (userDD) userDD.classList.add('hidden');
    }
});

// Notif badge
function loadNotif() {
    fetch('{{ route("api.notifikasi.jumlah") }}')
        .then(r=>r.json())
        .then(d=>{ document.getElementById('notifBadge').classList.toggle('hidden', d.jumlah===0); })
        .catch(()=>{});
}
loadNotif(); setInterval(loadNotif, 30000);

// Clock
function tick() { document.getElementById('liveClock').textContent = new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }
tick(); setInterval(tick, 1000);

// Page loader
window.addEventListener('load', () => {
    const l = document.getElementById('pageLoader');
    l.style.opacity = '0';
    setTimeout(() => l.remove(), 400);
});

// Toast
function showToast(msg, type='success') {
    const tc = document.getElementById('toastContainer');
    const icons = { success:'fa-circle-check', error:'fa-circle-xmark', warning:'fa-triangle-exclamation' };
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `
        <i class="fa-solid ${icons[type]||'fa-info-circle'} text-base mt-0.5 shrink-0"></i>
        <div class="flex-1 text-sm font-medium leading-snug">${msg}</div>
        <button onclick="this.closest('.toast').remove()" class="shrink-0 opacity-50 hover:opacity-100 transition text-lg leading-none">&times;</button>`;
    tc.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(20px)'; t.style.transition='all .3s'; setTimeout(()=>t.remove(),300); }, 4000);
}
</script>
@yield('scripts')
</body>
</html>