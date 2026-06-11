<x-guest-layout>
    {{-- Pesan info saat user mencoba akses /register --}}
    @if(session('info'))
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('info') }}</span>
    </div>
    @endif

    {{-- Status session (misal: setelah reset password) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            {{-- Link lupa password dihapus — reset dilakukan oleh admin --}}
            <x-primary-button class="ml-3">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>

    {{-- Informasi: tidak ada registrasi mandiri --}}
    <div class="mt-6 text-center text-xs text-gray-400">
        Belum punya akun? Hubungi administrator kampus.
    </div>
</x-guest-layout>
