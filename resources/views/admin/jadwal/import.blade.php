@extends('layouts.app')
@section('title', 'Import Jadwal dari CSV')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.jadwal.index') }}" class="hover:text-blue-600">Jadwal Tetap</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Import CSV</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Import Jadwal dari CSV</h1>
    <p class="text-gray-500 text-sm mt-1">Upload file CSV untuk mengimport data jadwal secara massal</p>
</div>

<div class="max-w-2xl space-y-5">

    {{-- Info Format --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"></i>
            <div class="flex-1">
                <p class="font-semibold text-blue-800 text-sm mb-2">Format File CSV</p>
                <p class="text-blue-700 text-xs mb-3">
                    File CSV harus menggunakan <strong>titik koma (;)</strong> sebagai pemisah.
                    Baris pertama adalah header. Unduh template untuk format yang benar.
                </p>
                <div class="overflow-x-auto">
                    <table class="text-xs text-blue-700 border-collapse">
                        <thead>
                            <tr class="bg-blue-100">
                                @foreach(['mata_kuliah','kode_mk','kelas','program_studi','semester','sks','hari','jam_mulai','jam_selesai','tahun_akademik','semester_ganjil_genap','email_dosen','kode_ruang'] as $col)
                                <th class="border border-blue-200 px-2 py-1 font-semibold whitespace-nowrap">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach(['Pemrograman Web','TI301','A','Teknik Informatika','5','3','senin','08:00','10:30','2024/2025','ganjil','dosen@kampus.ac.id','R.101'] as $val)
                                <td class="border border-blue-200 px-2 py-1 whitespace-nowrap text-blue-600">{{ $val }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.jadwal.template') }}"
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition">
                        <i class="fa-solid fa-download"></i> Unduh Template CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Aturan Penting --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <p class="font-semibold text-amber-800 text-sm mb-2 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Aturan Penting
        </p>
        <ul class="text-amber-700 text-xs space-y-1 list-disc list-inside">
            <li>Kolom <strong>email_dosen</strong> harus sesuai dengan email dosen yang sudah terdaftar</li>
            <li>Kolom <strong>kode_ruang</strong> harus sesuai dengan kode ruang yang sudah terdaftar</li>
            <li>Kolom <strong>hari</strong>: senin / selasa / rabu / kamis / jumat / sabtu (huruf kecil)</li>
            <li>Kolom <strong>jam_mulai & jam_selesai</strong>: format HH:MM (contoh: 08:00)</li>
            <li>Kolom <strong>semester_ganjil_genap</strong>: ganjil atau genap</li>
            <li>Jadwal yang bentrok (ruang atau dosen) akan dilewati dan dicatat sebagai gagal</li>
        </ul>
    </div>

    {{-- Form Upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-upload text-green-500"></i> Upload File CSV
        </h2>

        <form method="POST" action="{{ route('admin.jadwal.proses-import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Pilih File CSV <span class="text-red-500">*</span>
                </label>

                {{-- Drop area --}}
                <div id="dropArea"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer"
                    onclick="document.getElementById('fileCsv').click()">
                    <i class="fa-solid fa-file-csv text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 text-sm font-medium">Klik atau drag & drop file CSV di sini</p>
                    <p class="text-gray-400 text-xs mt-1">Format: .csv — Maksimal 2MB</p>
                    <p id="namaFile" class="text-blue-600 text-sm font-semibold mt-2 hidden"></p>
                </div>
                <input type="file" name="file_csv" id="fileCsv" accept=".csv,.txt"
                    class="hidden" onchange="tampilkanNamaFile(this)">

                @error('file_csv')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-file-import"></i> Import Sekarang
                </button>
                <a href="{{ route('admin.jadwal.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Ruang & Dosen yang Tersedia --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-blue-400"></i> Kode Ruang Tersedia
            </h3>
            <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                @foreach($ruangList as $r)
                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-lg font-mono">
                    {{ $r->kode_ruang }}
                </span>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-green-400"></i> Email Dosen Terdaftar
            </h3>
            <div class="space-y-1 max-h-32 overflow-y-auto">
                @foreach($dosenList as $d)
                <p class="text-xs text-gray-500 font-mono">{{ $d->email }}</p>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function tampilkanNamaFile(input) {
    const namaEl  = document.getElementById('namaFile');
    const dropEl  = document.getElementById('dropArea');
    if (input.files && input.files[0]) {
        namaEl.textContent = '✓ ' + input.files[0].name;
        namaEl.classList.remove('hidden');
        dropEl.classList.add('border-green-400', 'bg-green-50');
        dropEl.classList.remove('border-gray-300');
    }
}

// Drag & drop
const dropArea = document.getElementById('dropArea');
['dragenter','dragover'].forEach(evt => {
    dropArea.addEventListener(evt, e => {
        e.preventDefault();
        dropArea.classList.add('border-blue-400', 'bg-blue-50');
    });
});
['dragleave','drop'].forEach(evt => {
    dropArea.addEventListener(evt, e => {
        e.preventDefault();
        dropArea.classList.remove('border-blue-400', 'bg-blue-50');
    });
});
dropArea.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('fileCsv').files = e.dataTransfer.files;
        tampilkanNamaFile(document.getElementById('fileCsv'));
    }
});
</script>
@endsection
