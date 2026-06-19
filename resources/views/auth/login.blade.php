<x-guest-layout>
<x-auth-session-status class="mb-4" :status="session('status')"/>

<div class="mb-7">
    <p class="text-slate-400 text-sm font-medium mb-1">Portal Akademik</p>
    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Masuk ke SiRuang</h2>
    <p class="text-slate-500 text-sm mt-1">Kelola reservasi ruang kelas dengan mudah</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Email</label>
        <div class="field-icon">
            <i class="fa-solid fa-envelope icon"></i>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                placeholder="nama@kampus.ac.id"
                class="field {{ $errors->has('email')?'error':'' }}">
        </div>
        @error('email')<p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Password</label>
        <div class="field-icon">
            <i class="fa-solid fa-lock icon"></i>
            <input id="pwdInput" type="password" name="password" required
                placeholder="••••••••"
                class="field pr-12 {{ $errors->has('password')?'error':'' }}">
            <button type="button" onclick="togglePwd()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1">
                <i id="pwdEye" class="fa-solid fa-eye text-sm"></i>
            </button>
        </div>
        @error('password')<p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-slate-600">Ingat saya</span>
        </label>
    </div>
    <button type="submit" class="btn-primary w-full justify-center py-3 mt-2 text-base rounded-2xl">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
    </button>
</form>

<p class="text-center text-xs text-slate-400 mt-6">
    Belum punya akun? Hubungi <span class="font-semibold text-slate-500">administrator</span>.
</p>

<script>
function togglePwd(){
    const i=document.getElementById('pwdInput'),e=document.getElementById('pwdEye');
    i.type=i.type==='password'?'text':'password';
    e.classList.toggle('fa-eye'); e.classList.toggle('fa-eye-slash');
}
</script>
</x-guest-layout>