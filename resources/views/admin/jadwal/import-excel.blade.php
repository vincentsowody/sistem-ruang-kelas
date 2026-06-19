@extends('layouts.app')
@section('title', 'Import Jadwal dari Excel')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.jadwal.index') }}" class="hover:text-blue-600">Jadwal Tetap</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Import Excel</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Import Jadwal dari Excel</h1>
    <p class="text-gray-500 text-sm mt-1">Upload file Excel (.xlsx) dengan format jadwal kampus — mendukung slot waktu (1-2, 4-5, dst)</p>
</div>

{{-- Tampilkan error import --}}
@if(session('import_errors'))
<div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-5">
    <div class="flex items-center gap-2 mb-3">
        <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        <p class="font-semibold text-red-700 text-sm">{{ count(session('import_errors')) }} baris gagal diimport:</p>
    </div>
    <ul class="space-y-1 max-h-48 overflow-y-auto">
        @foreach(session('import_errors') as $err)
        <li class="text-red-600 text-xs flex items-start gap-2">
            <i class="fa-solid fa-xmark mt-0.5 flex-shrink-0"></i>{{ $err }}
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl space-y-5">

    {{-- Preview format --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-table text-blue-500"></i> Format Excel yang Didukung
            </h2>
            <a href="{{ route('admin.jadwal.excel-template') }}"
               class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-xl transition">
                <i class="fa-solid fa-download"></i> Unduh Template .xlsx
            </a>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr>
                        @foreach(['Kode MK','Nama MK','SKS','Semester','Kelas','Pengajar','Hari','Slot Waktu','Ruang','Program Studi'] as $h)
                        <th class="bg-blue-700 text-white px-3 py-2 border border-blue-600 whitespace-nowrap text-left">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['TIK1021','Pancasila','2','1','A','Ir. S. T. G. Kaunang, MT, Ph.D.','KAMIS','4 - 5','JTE-04','Teknik Informatika'],
                        ['TIK1021','Pancasila','2','1','B','Nancy J. Tuturoong, ST, M.Kom.','KAMIS','4 - 5','JTE-05','Teknik Informatika'],
                        ['TIK1031','Bahasa Indonesia','2','1','A','Dr.Eng. Sary D. E. Paturusi / Pujo H. Saputro','SELASA','3 - 4','JTE-04','Teknik Informatika'],
                        ['TIK1101','Pendidikan Agama','2','1','A','Pdt. Dina Pontoh, MTh.','JUMAT','1 - 2','TBA','Teknik Informatika'],
                    ] as $i => $row)
                    <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-blue-50' }}">
                        @foreach($row as $j => $val)
                        @php
                            $isSkip = ($j == 8 && strtoupper($val) == 'TBA');
                        @endphp
                        <td class="px-3 py-2 border border-gray-100 whitespace-nowrap {{ $isSkip ? 'text-red-400 line-through' : 'text-gray-700' }}">
                            {{ $val }}
                            @if($isSkip)<span class="text-red-400 text-[10px] no-underline ml-1">(dilewati)</span>@endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 bg-amber-50 border-t border-amber-100">
            <p class="text-xs text-amber-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Ruang <strong>TBA</strong> atau kosong akan dilewati otomatis.
                Pengajar dengan 2 dosen dipisahkan dengan <strong>/</strong> — sistem akan memakai dosen pertama.
            </p>
        </div>
    </div>

    {{-- Referensi Slot Waktu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <button onclick="toggleSlot()" class="w-full px-5 py-4 flex items-center justify-between text-left">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock text-purple-500"></i>
                <span class="font-semibold text-gray-800">Referensi Slot Waktu</span>
                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">{{ count($slots) }} slot</span>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-sm transition-transform" id="slotChevron"></i>
        </button>
        <div id="slotPanel" class="hidden px-5 pb-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach($slots as $no => $jam)
                <div class="bg-purple-50 rounded-xl p-3 text-center">
                    <p class="font-bold text-purple-700 text-lg">{{ $no }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $jam['mulai'] }} – {{ $jam['selesai'] }}</p>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Gunakan format <span class="font-mono bg-gray-100 px-1 rounded">1 - 2</span> atau
                <span class="font-mono bg-gray-100 px-1 rounded">4-5</span> di kolom Slot Waktu
            </p>
        </div>
    </div>

    {{-- Form Upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-upload text-green-500"></i> Upload File Excel
        </h2>

        <form method="POST" action="{{ route('admin.jadwal.proses-excel-import') }}"
              enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Pengaturan Semester --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tahun Akademik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tahun_akademik" value="{{ old('tahun_akademik','2024/2025') }}"
                        placeholder="2024/2025"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('tahun_akademik')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <select name="semester_ganjil_genap"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Program Studi Default
                    <span class="text-gray-400 font-normal">(dipakai jika kolom Program Studi kosong di Excel)</span>
                </label>
                <input type="text" name="program_studi" value="{{ old('program_studi','Teknik Informatika') }}"
                    placeholder="Teknik Informatika"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Opsi tambahan --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 space-y-3">
                <p class="text-xs font-semibold text-amber-800 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-amber-500"></i> Opsi Import
                </p>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="auto_create_ruang" value="1" id="autoCreateRuang"
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Auto-buat ruang yang belum terdaftar</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Jika ruang di Excel belum ada di database, sistem akan otomatis menambahkannya
                            dengan kapasitas default 40 kursi. Cocok saat pertama kali import.
                        </p>
                    </div>
                </label>
            </div>

            {{-- Drop area file --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    File Excel (.xlsx) <span class="text-red-500">*</span>
                </label>
                <div id="dropArea"
                    class="border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer"
                    onclick="document.getElementById('fileExcel').click()">
                    <i class="fa-solid fa-file-excel text-green-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 font-medium">Klik atau drag & drop file Excel di sini</p>
                    <p class="text-gray-400 text-xs mt-1">Format: .xlsx atau .xls — Maksimal 5MB</p>
                    <div id="namaFile" class="hidden mt-3 inline-flex items-center gap-2 bg-green-100 text-green-700 text-sm font-semibold px-4 py-2 rounded-xl">
                        <i class="fa-solid fa-check-circle"></i>
                        <span id="namaFileText"></span>
                    </div>
                </div>
                <input type="file" name="file_excel" id="fileExcel" accept=".xlsx,.xls"
                    class="hidden" onchange="tampilkanFile(this)">
                @error('file_excel')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Dosen & Ruang Tersedia --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <details class="bg-gray-50 rounded-xl p-3">
                    <summary class="text-xs font-semibold text-gray-600 cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-door-open text-blue-400"></i>
                        Kode Ruang Tersedia ({{ $ruangList->count() }})
                    </summary>
                    <div class="flex flex-wrap gap-1 mt-2 max-h-24 overflow-y-auto">
                        @foreach($ruangList as $r)
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-mono">{{ $r->kode_ruang }}</span>
                        @endforeach
                    </div>
                </details>
                <details class="bg-gray-50 rounded-xl p-3">
                    <summary class="text-xs font-semibold text-gray-600 cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-chalkboard-user text-green-400"></i>
                        Dosen Terdaftar ({{ $dosenList->count() }})
                    </summary>
                    <div class="mt-2 max-h-24 overflow-y-auto space-y-0.5">
                        @foreach($dosenList as $d)
                        <p class="text-xs text-gray-600 truncate">{{ $d->name }}</p>
                        @endforeach
                    </div>
                </details>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" id="btnImport"
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
</div>

@endsection

@section('scripts')
<script>
function tampilkanFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('namaFileText').textContent = file.name;
        document.getElementById('namaFile').classList.remove('hidden');
        document.getElementById('dropArea').classList.add('border-green-400','bg-green-50');
        document.getElementById('dropArea').classList.remove('border-gray-300');
    }
}

function toggleSlot() {
    const panel   = document.getElementById('slotPanel');
    const chevron = document.getElementById('slotChevron');
    panel.classList.toggle('hidden');
    chevron.style.transform = panel.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// Drag & drop
const dropArea = document.getElementById('dropArea');
['dragenter','dragover'].forEach(e => dropArea.addEventListener(e, ev => {
    ev.preventDefault();
    dropArea.classList.add('border-blue-400','bg-blue-50');
}));
['dragleave','drop'].forEach(e => dropArea.addEventListener(e, ev => {
    ev.preventDefault();
    dropArea.classList.remove('border-blue-400','bg-blue-50');
}));
dropArea.addEventListener('drop', e => {
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('fileExcel').files = files;
        tampilkanFile(document.getElementById('fileExcel'));
    }
});

// Loading state saat submit
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.getElementById('btnImport');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;
});
</script>
@endsection