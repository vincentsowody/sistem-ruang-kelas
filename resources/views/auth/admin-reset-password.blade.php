@extends('layouts.guest')
@section('title', 'Buat Password Baru')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-lock-open text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Buat Password Baru</h1>
            <p class="text-gray-500 text-sm mt-1">Untuk akun <strong>{{ $user->email }}</strong></p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
            <form method="POST" action="{{ route('password.admin-reset.update', $token) }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autofocus
                               minlength="8"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
                               placeholder="Minimal 8 karakter">
                        <button type="button" onclick="togglePw('password','eye1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye text-sm" id="eye1"></i>
                        </button>
                    </div>

                    {{-- Strength indicator --}}
                    <div class="mt-2 flex gap-1" id="strength-bars">
                        <div class="h-1 flex-1 rounded bg-gray-200" id="bar1"></div>
                        <div class="h-1 flex-1 rounded bg-gray-200" id="bar2"></div>
                        <div class="h-1 flex-1 rounded bg-gray-200" id="bar3"></div>
                        <div class="h-1 flex-1 rounded bg-gray-200" id="bar4"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1" id="strength-label">Masukkan password</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password2" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
                               placeholder="Ulangi password baru">
                        <button type="button" onclick="togglePw('password2','eye2')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye text-sm" id="eye2"></i>
                        </button>
                    </div>
                    <p class="text-xs mt-1 hidden" id="match-msg"></p>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                    <i class="fa-solid fa-check mr-2"></i> Simpan Password Baru
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-5">
            Sudah ingat password? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login di sini</a>
        </p>
    </div>
</div>

<script>
function togglePw(id, eyeId) {
    const inp = document.getElementById(id);
    const eye = document.getElementById(eyeId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    eye.className = inp.type === 'password' ? 'fa-solid fa-eye text-sm' : 'fa-solid fa-eye-slash text-sm';
}

const pw  = document.getElementById('password');
const pw2 = document.getElementById('password2');
const bars = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
const label = document.getElementById('strength-label');
const matchMsg = document.getElementById('match-msg');

function checkStrength(val) {
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/\d/.test(val) && /[^a-zA-Z0-9]/.test(val)) score++;

    const colors = ['#EF4444','#F97316','#EAB308','#22C55E'];
    const labels = ['Lemah','Cukup','Kuat','Sangat kuat'];
    bars.forEach((b, i) => { b.style.background = i < score ? colors[score - 1] : '#E5E7EB'; });
    label.textContent = val.length === 0 ? 'Masukkan password' : labels[score - 1] || 'Lemah';
    label.style.color = val.length === 0 ? '#9CA3AF' : colors[score - 1] || '#EF4444';
}

function checkMatch() {
    if (!pw2.value) { matchMsg.classList.add('hidden'); return; }
    matchMsg.classList.remove('hidden');
    if (pw.value === pw2.value) {
        matchMsg.textContent = '✓ Password cocok'; matchMsg.style.color = '#16A34A';
    } else {
        matchMsg.textContent = '✗ Password tidak cocok'; matchMsg.style.color = '#DC2626';
    }
}

pw.addEventListener('input',  () => { checkStrength(pw.value); checkMatch(); });
pw2.addEventListener('input', checkMatch);
</script>
@endsection
