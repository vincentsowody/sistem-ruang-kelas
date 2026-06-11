@extends('layouts.app')
@section('title', 'Tambah Ruang Kelas')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.ruang.index') }}" class="hover:text-blue-600">Ruang Kelas</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Tambah Ruang</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Tambah Ruang Kelas</h1>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.ruang.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-blue-500"></i> Informasi Dasar
            </h2>

            {{-- Kode & Nama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Kode Ruang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode_ruang" value="{{ old('kode_ruang') }}"
                        placeholder="Contoh: R.101, LAB-A"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('kode_ruang') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('kode_ruang')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nama Ruang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_ruang" value="{{ old('nama_ruang') }}"
                        placeholder="Contoh: Ruang Kelas 101"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('nama_ruang') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('nama_ruang')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Gedung & Lantai --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Gedung <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="gedung" value="{{ old('gedung') }}"
                        placeholder="Contoh: Gedung A"
                        list="gedung-list"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('gedung') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    <datalist id="gedung-list">
                        @foreach($gedungList as $g)
                            <option value="{{ $g }}">
                        @endforeach
                    </datalist>
                    @error('gedung')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Lantai <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="lantai" value="{{ old('lantai', 1) }}" min="1" max="20"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('lantai') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('lantai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Kapasitas & Jenis --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Kapasitas (kursi) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="kapasitas" value="{{ old('kapasitas') }}" min="1"
                        placeholder="Contoh: 40"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('kapasitas') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('kapasitas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jenis Ruang <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white {{ $errors->has('jenis') ? 'border-red-400' : 'border-gray-200' }}">
                        @foreach(['kelas','laboratorium','aula','seminar'] as $j)
                        <option value="{{ $j }}" {{ old('jenis') == $j ? 'selected' : '' }}>{{ ucfirst($j) }}</option>
                        @endforeach
                    </select>
                    @error('jenis')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Status <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    @foreach(['aktif'=>'green','nonaktif'=>'gray','perbaikan'=>'yellow'] as $s => $c)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="{{ $s }}"
                            {{ old('status', 'aktif') == $s ? 'checked' : '' }}
                            class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 capitalize">{{ $s }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Fasilitas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2 mb-4">
                <i class="fa-solid fa-plug text-purple-500"></i> Fasilitas
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @php
                    $fasilitasOptions = [
                        'proyektor'    => ['icon'=>'fa-film',         'label'=>'Proyektor'],
                        'ac'           => ['icon'=>'fa-snowflake',    'label'=>'AC'],
                        'papan_tulis'  => ['icon'=>'fa-chalkboard',   'label'=>'Papan Tulis'],
                        'wifi'         => ['icon'=>'fa-wifi',         'label'=>'WiFi'],
                        'komputer'     => ['icon'=>'fa-computer',     'label'=>'Komputer'],
                        'sound_system' => ['icon'=>'fa-volume-high',  'label'=>'Sound System'],
                        'podium'       => ['icon'=>'fa-person-chalkboard', 'label'=>'Podium'],
                        'kamera'       => ['icon'=>'fa-camera',       'label'=>'Kamera CCTV'],
                    ];
                    $oldFasilitas = old('fasilitas', []);
                @endphp
                @foreach($fasilitasOptions as $value => $opt)
                <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition
                              {{ in_array($value, $oldFasilitas) ? 'bg-blue-50 border-blue-300' : 'border-gray-200' }}">
                    <input type="checkbox" name="fasilitas[]" value="{{ $value }}"
                        {{ in_array($value, $oldFasilitas) ? 'checked' : '' }}
                        class="text-blue-600 rounded focus:ring-blue-500">
                    <i class="fa-solid {{ $opt['icon'] }} text-gray-400 text-sm w-4"></i>
                    <span class="text-sm text-gray-700">{{ $opt['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Keterangan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Keterangan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea name="keterangan" rows="3" placeholder="Catatan tambahan tentang ruang ini..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Ruang
            </button>
            <a href="{{ route('admin.ruang.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
