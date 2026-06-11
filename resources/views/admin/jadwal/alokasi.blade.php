@extends('layouts.app')
@section('title', 'Alokasi Greedy — Jadwal Semester')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.jadwal.index') }}" class="hover:text-blue-600">Jadwal Tetap</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Alokasi Greedy</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Alokasi Jadwal Otomatis</h1>
    <p class="text-gray-500 text-sm mt-1">Sistem akan mengalokasikan ruang terbaik secara otomatis menggunakan algoritma Greedy Best-Fit</p>
</div>

{{-- Penjelasan Algoritma --}}
<div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-2xl p-5 mb-6">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
        </div>
        <div>
            <h3 class="font-semibold mb-1">Cara Kerja Algoritma Greedy Best-Fit</h3>
            <p class="text-purple-100 text-sm leading-relaxed">
                Untuk setiap permintaan jadwal, sistem memilih ruang dengan kapasitas <strong>paling pas</strong> (terkecil yang masih mencukupi jumlah peserta) dan tidak bentrok dengan jadwal lain pada slot yang sama.
                Hasilnya meminimalkan pemborosan kapasitas ruang.
            </p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.jadwal.proses-alokasi') }}" id="formAlokasi">
    @csrf

    {{-- Pengaturan semester --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-gear text-gray-400"></i> Pengaturan Semester
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Akademik</label>
                <input type="text" name="tahun_akademik" value="2024/2025"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Semester</label>
                <select name="semester_ganjil_genap" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel input jadwal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-list text-purple-500"></i>
                Daftar Kebutuhan Jadwal
            </h2>
            <button type="button" onclick="tambahBaris()"
                class="inline-flex items-center gap-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 font-medium px-3 py-2 rounded-xl transition text-sm">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Baris
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mata Kuliah</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dosen</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Prodi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sem</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">SKS</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Hari</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mulai</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Selesai</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Peserta</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="tabelJadwal" class="divide-y divide-gray-50">
                    {{-- Baris diisi oleh JavaScript --}}
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
            class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Jalankan Alokasi Greedy
        </button>
        <a href="{{ route('admin.jadwal.index') }}"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
            Batal
        </a>
    </div>
</form>

@endsection

@section('scripts')
<script>
const dosenList = @json($dosenList);
const hariList  = ['senin','selasa','rabu','kamis','jumat','sabtu'];
let rowCount    = 0;

function tambahBaris() {
    const tbody = document.getElementById('tabelJadwal');
    const i     = rowCount++;
    const tr    = document.createElement('tr');
    tr.className = 'hover:bg-gray-50';
    tr.id = `row-${i}`;

    tr.innerHTML = `
        <td class="px-4 py-3">
            <input type="text" name="jadwal[${i}][mata_kuliah]" required placeholder="Nama MK"
                class="w-36 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <select name="jadwal[${i}][dosen_id]" required class="w-36 border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                <option value="">-- Dosen --</option>
                ${dosenList.map(d => `<option value="${d.id}">${d.name}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="text" name="jadwal[${i}][kelas]" required placeholder="A" maxlength="5"
                class="w-12 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <input type="text" name="jadwal[${i}][program_studi]" required placeholder="Teknik Informatika"
                class="w-36 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <select name="jadwal[${i}][semester]" required class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                ${[1,2,3,4,5,6,7,8].map(s=>`<option value="${s}">${s}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <select name="jadwal[${i}][sks]" required class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                ${[1,2,3,4,5,6].map(s=>`<option value="${s}" ${s==2?'selected':''}>${s}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <select name="jadwal[${i}][hari]" required class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                ${hariList.map(h=>`<option value="${h}">${h.charAt(0).toUpperCase()+h.slice(1)}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="time" name="jadwal[${i}][jam_mulai]" required value="08:00"
                class="w-24 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <input type="time" name="jadwal[${i}][jam_selesai]" required value="10:30"
                class="w-24 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="jadwal[${i}][jumlah_peserta]" required value="30" min="1"
                class="w-16 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
        </td>
        <td class="px-4 py-3">
            <button type="button" onclick="hapusBaris('row-${i}')"
                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                <i class="fa-solid fa-trash text-xs"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function hapusBaris(id) {
    const row = document.getElementById(id);
    if (row) row.remove();
}

// Tambah 3 baris kosong saat halaman dimuat
tambahBaris(); tambahBaris(); tambahBaris();
</script>
@endsection
