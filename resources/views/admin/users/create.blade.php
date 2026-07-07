@extends('layouts.app')
@section('title', 'Tambah Pengguna')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">Pengguna</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Tambah Pengguna</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h1>
</div>

<div class="max-w-xl">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-user text-blue-500"></i> Informasi Pengguna
            </h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Dr. Budi Santoso, M.Kom"
                    class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@kampus.ac.id"
                    class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Min. 8 karakter"
                            class="w-full border rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        <button type="button" onclick="togglePwd('password','eye1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye text-sm" id="eye1"></i>
                        </button>
                    </div>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password2" placeholder="Ulangi password"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="togglePwd('password2','eye2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye text-sm" id="eye2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach([
                        ['value'=>'admin',     'label'=>'Admin',     'icon'=>'fa-user-shield',     'color'=>'purple'],
                        ['value'=>'dosen',     'label'=>'Dosen',     'icon'=>'fa-chalkboard-user', 'color'=>'green'],
                        ['value'=>'mahasiswa', 'label'=>'Mahasiswa', 'icon'=>'fa-user-graduate',   'color'=>'blue'],
                    ] as $role)
                    <label class="flex flex-col items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-{{ $role['color'] }}-50 hover:border-{{ $role['color'] }}-300 transition
                                  {{ old('role') == $role['value'] ? 'bg-'.$role['color'].'-50 border-'.$role['color'].'-300' : 'border-gray-200' }}">
                        <input type="radio" name="role" value="{{ $role['value'] }}"
                            {{ old('role','dosen') == $role['value'] ? 'checked' : '' }}
                            class="sr-only">
                        <i class="fa-solid {{ $role['icon'] }} text-{{ $role['color'] }}-500 text-xl"></i>
                        <span class="text-sm font-medium text-gray-700">{{ $role['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-green-500"></i> Informasi Tambahan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">NIP / NIM</label>
                    <input type="text" name="nip_nim" value="{{ old('nip_nim') }}" placeholder="Nomor Induk"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('nip_nim') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('nip_nim')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Program Studi</label>
                <input type="text" name="program_studi" value="{{ old('program_studi') }}" placeholder="Contoh: Teknik Informatika"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div id="fieldMahasiswa" class="grid grid-cols-2 gap-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="semester" min="1" max="14" value="{{ old('semester') }}" placeholder="Contoh: 1, 3, 5"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('semester')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Dipakai agar mahasiswa hanya melihat jadwal semesternya sendiri.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kelas" maxlength="5" value="{{ old('kelas') }}" placeholder="Contoh: A, B, C"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('kelas')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                <input type="checkbox" name="is_active" value="1" id="isActive" checked
                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="isActive" class="text-sm text-gray-700 cursor-pointer">
                    Akun aktif (pengguna dapat login)
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
                <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
            </button>
            <a href="{{ route('admin.users.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Highlight radio role saat diklik
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="role"]').forEach(r => {
            r.closest('label').className = r.closest('label').className
                .replace(/bg-\w+-50|border-\w+-300/g, '')
                .trim() + ' border-gray-200';
        });
        const colors = {admin:'purple', dosen:'green', mahasiswa:'blue'};
        const c = colors[this.value];
        this.closest('label').classList.remove('border-gray-200');
        this.closest('label').classList.add(`bg-${c}-50`, `border-${c}-300`);
        toggleFieldMahasiswa();
    });
});

// Tampilkan field Semester & Kelas hanya untuk role mahasiswa
function toggleFieldMahasiswa() {
    const dipilih = document.querySelector('input[name="role"]:checked');
    const fieldMhs = document.getElementById('fieldMahasiswa');
    if (dipilih && dipilih.value === 'mahasiswa') {
        fieldMhs.classList.remove('hidden');
    } else {
        fieldMhs.classList.add('hidden');
    }
}
toggleFieldMahasiswa();
</script>
@endsection