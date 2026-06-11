@extends('layouts.app')
@section('title', 'Import Dosen dari Excel Jadwal')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">Pengguna</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Import Dosen dari Excel</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Import Dosen dari File Excel Jadwal</h1>
    <p class="text-gray-500 text-sm mt-1">
        Scan file Excel jadwal kuliah — sistem akan mendeteksi dosen yang belum terdaftar secara otomatis.
    </p>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- TAHAP 1: Form Upload (tampil jika belum scan)          --}}
{{-- ══════════════════════════════════════════════════════ --}}
@if(!isset($namaDariExcel))

<div class="max-w-2xl space-y-5">

    {{-- Info cara kerja --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-semibold text-blue-800 text-sm mb-2">Cara kerja fitur ini</p>
                <ol class="text-blue-700 text-xs space-y-1 list-decimal list-inside">
                    <li>Upload file Excel jadwal kuliah (format apapun yang punya kolom <strong>Pengajar</strong>)</li>
                    <li>Sistem akan membaca semua nama dosen di kolom tersebut</li>
                    <li>Nama yang <strong>sudah terdaftar</strong> di sistem ditandai hijau</li>
                    <li>Nama yang <strong>belum ada</strong> ditampilkan untuk didaftarkan — lengkapi email lalu simpan</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Form upload --}}
    <form method="POST" action="{{ route('admin.dosen-import.scan') }}" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-green-500"></i> Upload File Excel
            </h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    File Excel Jadwal <span class="text-red-500">*</span>
                </label>
                <div id="dropzone"
                    class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition"
                    onclick="document.getElementById('fileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium text-gray-600" id="dropLabel">Klik atau seret file ke sini</p>
                    <p class="text-xs text-gray-400 mt-1">Format: .xlsx atau .xls — Maksimal 5MB</p>
                    <input type="file" name="file_excel" id="fileInput" class="hidden"
                        accept=".xlsx,.xls" onchange="tampilkanNamaFile(this)">
                </div>
                @error('file_excel')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0 text-sm"></i>
                <p class="text-xs text-amber-700">
                    File Excel harus memiliki kolom dengan header <strong>Pengajar</strong> atau <strong>Dosen</strong>.
                    Format nama dosen di Excel harus jelas — bukan singkatan.
                    Jika satu baris memiliki 2 dosen, pisahkan dengan tanda garis miring <strong>(/)</strong>.
                </p>
            </div>
        </div>

        <div class="mt-4 flex gap-3">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Scan File Excel
            </button>
            <a href="{{ route('admin.users.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- TAHAP 2: Hasil Scan + Form Konfirmasi                  --}}
{{-- ══════════════════════════════════════════════════════ --}}
@else

{{-- Ringkasan statistik --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <div class="text-2xl font-bold text-gray-800">{{ count($namaDariExcel) }}</div>
        <div class="text-xs text-gray-500 mt-1">Total dosen di Excel</div>
    </div>
    <div class="bg-green-50 rounded-2xl border border-green-100 p-4 text-center">
        <div class="text-2xl font-bold text-green-700">{{ count($sudahAda) }}</div>
        <div class="text-xs text-green-600 mt-1">Sudah terdaftar</div>
    </div>
    <div class="bg-amber-50 rounded-2xl border border-amber-100 p-4 text-center">
        <div class="text-2xl font-bold text-amber-700">{{ count($belumAda) + count($mirip) }}</div>
        <div class="text-xs text-amber-600 mt-1">Perlu ditinjau</div>
    </div>
</div>

{{-- ── Dosen yang sudah terdaftar ──────────────────────── --}}
@if(!empty($sudahAda))
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
    <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSection('sectionSudahAda', 'ikonSudahAda')">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            Sudah Terdaftar
            <span class="text-xs font-normal bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ count($sudahAda) }}</span>
        </h2>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform" id="ikonSudahAda"></i>
    </div>
    <div id="sectionSudahAda" class="hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($sudahAda as $namaExcel => $user)
            <div class="flex items-center gap-2 p-2.5 bg-green-50 rounded-xl border border-green-100">
                <i class="fa-solid fa-user-check text-green-500 text-xs flex-shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-green-800 truncate">{{ $namaExcel }}</p>
                    @if($namaExcel !== $user->name)
                    <p class="text-xs text-green-600 truncate">→ dicocokkan ke: {{ $user->name }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Nama mirip (perlu konfirmasi manual) ────────────── --}}
@if(!empty($mirip))
<div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5 mb-5">
    <h2 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
        Nama Mirip — Perlu Konfirmasi
        <span class="text-xs font-normal bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ count($mirip) }}</span>
    </h2>
    <p class="text-xs text-gray-500 mb-4">
        Nama-nama berikut memiliki kemiripan dengan dosen yang sudah ada.
        Jika sudah sama orangnya, tidak perlu didaftarkan ulang.
        Jika memang orang berbeda, daftarkan di bagian bawah.
    </p>
    <div class="space-y-3">
        @foreach($mirip as $namaExcel => $kandidat)
        <div class="border border-amber-100 rounded-xl p-3 bg-amber-50">
            <p class="text-sm font-medium text-gray-800 mb-2">
                <span class="text-xs bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded mr-1">Di Excel</span>
                {{ $namaExcel }}
            </p>
            <div class="space-y-1">
                @foreach($kandidat as $k)
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <i class="fa-solid fa-arrow-right text-amber-400"></i>
                    <span>Mungkin sama dengan: <strong>{{ $k->name }}</strong> ({{ $k->email ?? '-' }})</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Dosen belum ada → Form daftar ──────────────────── --}}
@if(!empty($belumAda))

<form method="POST" action="{{ route('admin.dosen-import.simpan') }}">
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-blue-500"></i>
                Dosen Baru — Belum Terdaftar
                <span class="text-xs font-normal bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ count($belumAda) }}</span>
            </h2>
            <div class="flex gap-2">
                <button type="button" onclick="pilihSemua(true)"
                    class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg transition">
                    Pilih Semua
                </button>
                <button type="button" onclick="pilihSemua(false)"
                    class="text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg transition">
                    Batal Semua
                </button>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4 flex items-start gap-2">
            <i class="fa-solid fa-circle-info text-blue-400 text-sm mt-0.5 flex-shrink-0"></i>
            <p class="text-xs text-blue-700">
                Centang dosen yang ingin didaftarkan, lalu lengkapi email masing-masing.
                Password default <strong>dosen123</strong> — dosen dapat menggantinya setelah login.
                Email harus unik dan belum pernah digunakan.
            </p>
        </div>

        <div class="space-y-3" id="listDosenBaru">
            @php $idx = 0; @endphp
            @foreach($belumAda as $nama)
            @php
                // Generate email otomatis dari nama (tanpa gelar)
                $namaDepan = strtolower(preg_replace('/[^a-zA-Z\s]/', '', explode(',', $nama)[0]));
                $namaDepan = preg_replace('/\s+/', '.', trim($namaDepan));
                $emailSuggest = $namaDepan . '@kampus.ac.id';
            @endphp
            <div class="border border-gray-100 rounded-xl p-4 dosen-row" id="row-{{ $idx }}">
                <div class="flex items-start gap-3">
                    {{-- Checkbox --}}
                    <div class="pt-1">
                        <input type="checkbox" name="dosen[{{ $idx }}][aktif]" value="1"
                            id="chk-{{ $idx }}" checked
                            onchange="toggleRow({{ $idx }}, this.checked)"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    </div>

                    <div class="flex-1 min-w-0">
                        {{-- Nama (readonly, dari Excel) --}}
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Dosen (dari Excel)</label>
                            <input type="text" name="dosen[{{ $idx }}][nama]"
                                value="{{ $nama }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Email --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="dosen[{{ $idx }}][email]"
                                    value="{{ $emailSuggest }}"
                                    placeholder="email@kampus.ac.id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            {{-- NIP --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">NIP (opsional)</label>
                                <input type="text" name="dosen[{{ $idx }}][nip]"
                                    placeholder="Nomor Induk Pegawai"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        {{-- Program Studi --}}
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Program Studi (opsional)</label>
                            <input type="text" name="dosen[{{ $idx }}][program_studi]"
                                placeholder="Contoh: Teknik Informatika"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>
            @php $idx++; @endphp
            @endforeach
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" id="btnSimpan"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-user-plus"></i>
            Daftarkan Dosen Terpilih
            <span id="jumlahDipilih" class="bg-white/30 text-white text-xs px-2 py-0.5 rounded-full">
                {{ count($belumAda) }}
            </span>
        </button>
        <a href="{{ route('admin.dosen-import.form') }}"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm flex items-center gap-2">
            <i class="fa-solid fa-arrow-rotate-left"></i> Scan Ulang
        </a>
    </div>

</form>

@else

{{-- Semua dosen sudah terdaftar --}}
<div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
    <i class="fa-solid fa-circle-check text-5xl text-green-400 mb-3"></i>
    <h2 class="font-semibold text-green-800 text-lg mb-1">Semua Dosen Sudah Terdaftar</h2>
    <p class="text-green-700 text-sm mb-4">
        Semua {{ count($namaDariExcel) }} dosen yang ditemukan di file Excel sudah ada di database.
        Anda bisa langsung melanjutkan import jadwal.
    </p>
    <div class="flex justify-center gap-3">
        <a href="{{ route('admin.jadwal.excel-import') }}"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition text-sm flex items-center gap-2">
            <i class="fa-solid fa-file-import"></i> Lanjut Import Jadwal
        </a>
        <a href="{{ route('admin.dosen-import.form') }}"
            class="bg-white border border-green-300 hover:bg-green-50 text-green-700 font-medium px-5 py-2.5 rounded-xl transition text-sm">
            Scan File Lain
        </a>
    </div>
</div>

@endif

{{-- Link lanjut ke import jadwal --}}
@if(!empty($belumAda))
<div class="mt-4 bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-arrow-right text-gray-400"></i>
        <p class="text-sm text-gray-600">
            Setelah mendaftarkan dosen, lanjutkan import jadwal Excel
        </p>
    </div>
    <a href="{{ route('admin.jadwal.excel-import') }}"
        class="text-sm text-blue-600 hover:underline font-medium flex items-center gap-1 whitespace-nowrap">
        Import Jadwal <i class="fa-solid fa-external-link text-xs"></i>
    </a>
</div>
@endif

@endif {{-- end isset($namaDariExcel) --}}

@endsection

@section('scripts')
<script>
// Toggle section sudah ada
function toggleSection(id, iconId) {
    const el   = document.getElementById(id);
    const icon = document.getElementById(iconId);
    el.classList.toggle('hidden');
    icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// Enable/disable row dosen
function toggleRow(idx, aktif) {
    const row    = document.getElementById('row-' + idx);
    const inputs = row.querySelectorAll('input[type="text"], input[type="email"]');

    inputs.forEach(inp => {
        inp.disabled = !aktif;
        inp.required = aktif;
        inp.classList.toggle('opacity-40', !aktif);
        inp.classList.toggle('bg-gray-50', !aktif);
    });

    row.classList.toggle('opacity-50', !aktif);
    updateJumlahDipilih();
}

// Pilih semua / batal semua
function pilihSemua(state) {
    document.querySelectorAll('.dosen-row input[type="checkbox"]').forEach(chk => {
        const idx = chk.id.replace('chk-', '');
        chk.checked = state;
        toggleRow(parseInt(idx), state);
    });
}

// Update counter di tombol simpan
function updateJumlahDipilih() {
    const total = document.querySelectorAll('.dosen-row input[type="checkbox"]:checked').length;
    const el = document.getElementById('jumlahDipilih');
    if (el) el.textContent = total;
}

// Drag & drop file
const dropzone = document.getElementById('dropzone');
if (dropzone) {
    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('border-blue-400', 'bg-blue-50');
    });
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-blue-400', 'bg-blue-50');
    });
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('border-blue-400', 'bg-blue-50');
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('fileInput').files = e.dataTransfer.files;
            tampilkanNamaFile({ files: [file] });
        }
    });
}

function tampilkanNamaFile(input) {
    const file = input.files[0];
    if (file) {
        document.getElementById('dropLabel').textContent = '✓ ' + file.name;
        document.getElementById('dropzone').classList.add('border-green-400', 'bg-green-50');
    }
}
</script>
@endsection
