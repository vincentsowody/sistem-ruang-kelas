<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | SiRuang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md w-full">

        {{-- Error code big display --}}
        <div class="relative mb-8">
            <span class="text-[120px] font-black text-slate-100 leading-none select-none block">@yield('code')</span>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-lg @yield('icon-bg')">
                    <i class="@yield('icon') text-3xl text-white"></i>
                </div>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-3">@yield('title')</h1>
        <p class="text-slate-500 text-sm mb-8 leading-relaxed max-w-xs mx-auto">@yield('description')</p>

        <div class="flex gap-3 justify-center">
            <button onclick="history.back()"
               class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-5 py-2.5 rounded-xl transition text-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali
            </button>
            @auth
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-blue-200">
                <i class="fa-solid fa-house text-xs"></i> Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-blue-200">
                <i class="fa-solid fa-right-to-bracket text-xs"></i> Login
            </a>
            @endauth
        </div>

        <div class="flex items-center gap-3 justify-center mt-10">
            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-school text-white text-xs"></i>
            </div>
            <span class="text-xs text-slate-400 font-medium">SiRuang — Sistem Reservasi Ruang Kelas</span>
        </div>
    </div>
</body>
</html>