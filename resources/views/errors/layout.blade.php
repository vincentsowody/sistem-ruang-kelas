<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md w-full">
        <div class="mb-6">
            <span class="text-8xl font-black text-gray-200 leading-none">@yield('code')</span>
        </div>
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5 @yield('icon-bg')">
            <i class="@yield('icon') text-2xl text-white"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">@yield('title')</h1>
        <p class="text-gray-500 text-sm mb-8 leading-relaxed">@yield('description')</p>
        <div class="flex gap-3 justify-center">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-xl transition text-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali
            </a>
            @auth
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm">
                <i class="fa-solid fa-house text-xs"></i> Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm">
                <i class="fa-solid fa-right-to-bracket text-xs"></i> Login
            </a>
            @endauth
        </div>
        <p class="text-xs text-gray-300 mt-10">{{ config('app.name') }}</p>
    </div>
</body>
</html>
