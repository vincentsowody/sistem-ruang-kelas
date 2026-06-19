<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiRuang — Sistem Reservasi Ruang Kelas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-school text-white text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-[15px] leading-tight">SiRuang</p>
                <p class="text-xs text-slate-400 leading-tight">Reservasi Ruang Kelas</p>
            </div>
        </div>
        @auth
        <a href="{{ url('/dashboard') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-xl text-sm transition">
            <i class="fa-solid fa-gauge-high text-xs"></i> Dashboard
        </a>
        @else
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-xl text-sm transition">
            <i class="fa-solid fa-right-to-bracket text-xs"></i> Masuk
        </a>
        @endauth
    </nav>

    {{-- Hero --}}
    <section class="max-w-7xl mx-auto px-6 py-20 text-center">
        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs font-semibold px-4 py-2 rounded-full border border-blue-100 mb-6">
            <i class="fa-solid fa-star text-blue-500"></i>
            Sistem Informasi Akademik Kampus
        </div>
        <h1 class="text-5xl font-extrabold text-slate-900 leading-tight mb-5 max-w-3xl mx-auto">
            Reservasi Ruang Kelas<br>
            <span class="text-blue-600">Lebih Mudah & Efisien</span>
        </h1>
        <p class="text-slate-500 text-lg mb-10 max-w-xl mx-auto leading-relaxed">
            Kelola jadwal kuliah, ajukan reservasi ruang, dan pantau ketersediaan ruang kelas secara real-time.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-2xl text-sm transition shadow-lg shadow-blue-200">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </a>
            <a href="{{ route('kalender.index') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-medium px-6 py-3 rounded-2xl text-sm transition border border-slate-200">
                <i class="fa-solid fa-calendar-week text-blue-500"></i> Lihat Kalender
            </a>
        </div>
    </section>

    {{-- Fitur --}}
    <section class="max-w-7xl mx-auto px-6 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['fa-calendar-days','blue','Jadwal Otomatis','Lihat jadwal kuliah semester ini secara otomatis, lengkap dengan informasi ruang dan dosen.'],
                ['fa-clipboard-check','green','Reservasi Mudah','Ajukan reservasi ruang kapan saja, lacak status persetujuan secara real-time.'],
                ['fa-wand-magic-sparkles','purple','Alokasi Cerdas','Algoritma greedy otomatis mengalokasikan ruang terbaik sesuai kebutuhan mata kuliah.'],
            ] as [$ico, $color, $title, $desc])
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <div class="w-12 h-12 bg-{{ $color }}-100 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid {{ $ico }} text-{{ $color }}-600 text-lg"></i>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">{{ $title }}</h3>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>

</body>
</html>