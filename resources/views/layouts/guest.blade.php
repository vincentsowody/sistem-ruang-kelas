<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Login') — SiRuang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; }
        .mono { font-family:'JetBrains Mono',monospace; }
        .field { width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:.875rem; font-family:'Plus Jakarta Sans',sans-serif; transition:border-color .15s,box-shadow .15s; background:white; color:#1e293b; }
        .field:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
        .field.error { border-color:#ef4444; }
        .field-icon { position:relative; }
        .field-icon .field { padding-left:40px; }
        .field-icon .icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.85rem; pointer-events:none; }
        .btn-primary { display:inline-flex; align-items:center; gap:8px; background:#2563eb; color:white; font-weight:700; font-size:.875rem; padding:11px 20px; border-radius:12px; transition:all .15s; border:none; cursor:pointer; box-shadow:0 2px 8px rgba(37,99,235,.3); font-family:'Plus Jakarta Sans',sans-serif; }
        .btn-primary:hover { background:#1d4ed8; box-shadow:0 4px 16px rgba(37,99,235,.4); transform:translateY(-1px); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .animate-float { animation:float 6s ease-in-out infinite; }
        .animate-up { animation:fadeUp .4s ease both; }
    </style>
</head>
<body class="min-h-screen flex" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#1d4ed8 100%)">

    {{-- Left decorative panel (hidden on small screens) --}}
    <div class="hidden lg:flex flex-1 flex-col items-center justify-center p-12 relative overflow-hidden">
        {{-- Blobs --}}
        <div class="absolute w-96 h-96 rounded-full opacity-10 -top-20 -left-20"
             style="background:radial-gradient(circle,#60a5fa,transparent)"></div>
        <div class="absolute w-64 h-64 rounded-full opacity-5 bottom-10 right-10"
             style="background:radial-gradient(circle,white,transparent)"></div>

        <div class="relative text-center animate-up">
            <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center mx-auto mb-6 animate-float">
                <i class="fa-solid fa-school text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-black text-white leading-tight tracking-tight">SiRuang</h1>
            <p class="text-blue-300 mt-2 text-lg">Sistem Reservasi Ruang Kelas</p>

            <div class="grid grid-cols-3 gap-4 mt-12 max-w-sm mx-auto">
                @foreach([
                    ['fa-door-open',     'Kelola Ruang',  'Pantau status setiap ruang'],
                    ['fa-calendar-days', 'Jadwal Otomatis','Import & alokasi cepat'],
                    ['fa-shield-check',  'Aman & Cepat',  'Persetujuan real-time'],
                ] as [$ico,$t,$s])
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-4 text-center">
                    <i class="fa-solid {{ $ico }} text-blue-400 text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-bold">{{ $t }}</p>
                    <p class="text-blue-400 text-[10px] mt-0.5">{{ $s }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Login card --}}
    <div class="w-full lg:w-[440px] flex items-center justify-center p-6">
        <div class="w-full max-w-sm animate-up" style="animation-delay:.1s">
            {{-- Mobile logo --}}
            <div class="flex items-center gap-3 mb-8 lg:hidden">
                <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-school text-white"></i>
                </div>
                <div>
                    <p class="font-black text-white text-lg leading-tight">SiRuang</p>
                    <p class="text-blue-300 text-xs">Reservasi Ruang Kelas</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-8 shadow-2xl">
                {{ $slot }}
            </div>
            <p class="text-center text-blue-400/50 text-xs mt-6">© {{ date('Y') }} SiRuang · All rights reserved</p>
        </div>
    </div>
</body>
</html>