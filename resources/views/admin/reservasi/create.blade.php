@extends('layouts.app')
@section('title', 'Ajukan Reservasi Ruang')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('reservasi.index') }}" class="hover:text-blue-600">Reservasi Saya</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Ajukan Baru</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">Ajukan Reservasi Ruang</h1>
    <p class="text-gray-500 text-sm mt-1">Sistem akan otomatis menyarankan ruang alternatif jika ruang pilihan tidak tersedia</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('reservasi.store') }}" class="space-y-5" id="formReservasi">
        @csrf

        {{-- Informasi Kegiatan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-blue-500"></i> Informasi Kegiatan
            </h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama / Judul Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="keperluan" value="{{ old('keperluan') }}"
                    placeholder="Contoh: Kuliah Pengganti Pemrograman Web"
                    class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                           {{ $errors->has('keperluan') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('keperluan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jenis Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kegiatan"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @foreach([
                            'kuliah_pengganti'   => 'Kuliah Pengganti',
                            'ujian'              => 'Ujian',
                            'rapat'              => 'Rapat',
                            'seminar'            => 'Seminar',
                            'kegiatan_mahasiswa' => 'Kegiatan Mahasiswa',
                            'lainnya'            => 'Lainnya',
                        ] as $val => $label)
                        <option value="{{ $val }}" {{ old('jenis_kegiatan') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jumlah Peserta <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah_peserta"
                        value="{{ old('jumlah_peserta', 30) }}" min="1" id="jumlahPeserta"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cekKetersediaan()">
                    @error('jumlah_peserta')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Keterangan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="keterangan" rows="2"
                    placeholder="Informasi tambahan tentang kegiatan..."
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('keterangan') }}</textarea>
            </div>
        </div>

        {{-- Waktu & Ruangan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-clock text-green-500"></i> Waktu & Ruangan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" value="{{ old('tanggal') }}"
                        id="tanggalInput" min="{{ date('Y-m-d') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cekKetersediaan()">
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}"
                        id="jamMulaiInput"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cekKetersediaan()">
                    @error('jam_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}"
                        id="jamSelesaiInput" max="17:00"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cekKetersediaan()">
                    <p class="text-xs text-gray-400 mt-1">Reservasi ruangan hanya bisa sampai jam 17:00.</p>
                    @error('jam_selesai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Pilih Ruang <span class="text-red-500">*</span>
                </label>
                <select name="ruang_kelas_id" id="ruangInput"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white
                           {{ $errors->has('ruang_kelas_id') ? 'border-red-400' : '' }}"
                    onchange="cekKetersediaan()">
                    <option value="">-- Pilih Ruang --</option>
                    @foreach($ruangList as $r)
                    <option value="{{ $r->id }}" {{ old('ruang_kelas_id') == $r->id ? 'selected' : '' }}>
                        {{ $r->kode_ruang }} — {{ $r->nama_ruang }} ({{ $r->kapasitas }} kursi)
                    </option>
                    @endforeach
                </select>
                @error('ruang_kelas_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Panel Greedy — muncul saat ada hasil cek ── --}}
            <div id="panelGreedy" class="hidden space-y-2">

                {{-- Loading --}}
                <div id="panelLoading" class="hidden bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-gray-400 text-sm"></i>
                        <p class="text-gray-500 text-sm">Memeriksa ketersediaan ruang...</p>
                    </div>
                </div>

                {{-- Tersedia --}}
                <div id="panelTersedia" class="hidden bg-green-50 border border-green-200 rounded-xl p-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-green-500"></i>
                        <p class="text-green-700 text-sm font-medium">Ruang tersedia pada waktu yang dipilih</p>
                    </div>
                </div>

                {{-- Konflik tanpa saran --}}
                <div id="panelKonflikSaja" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-red-700 text-sm">Ruang Tidak Tersedia</p>
                            <p id="detailKonflik" class="text-red-600 text-xs mt-1"></p>
                            <p class="text-red-500 text-xs mt-1">Tidak ada ruang alternatif tersedia. Coba pilih waktu lain.</p>
                        </div>
                    </div>
                </div>

                {{-- Saran Greedy --}}
                <div id="panelSaran" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mt-0.5 text-lg"></i>
                        <div class="flex-1">
                            <p id="judulSaran" class="font-semibold text-amber-800 text-sm">
                                Ruang Pilihan Bentrok — Sistem Menyarankan Ruang Alternatif
                            </p>
                            <p id="detailSaran" class="text-amber-700 text-xs mt-1"></p>

                            <div class="mt-3 bg-white rounded-xl border border-amber-200 p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm" id="saranKode"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" id="saranNama"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" id="saranKapasitas"></p>
                                        <p class="text-xs text-gray-400" id="saranFasilitas"></p>
                                    </div>
                                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full flex-shrink-0">
                                        <i class="fa-solid fa-check mr-1"></i>Tersedia
                                    </span>
                                </div>
                            </div>

                            <label id="pilihanSaran" class="flex items-center gap-3 mt-3 cursor-pointer bg-white rounded-xl border border-amber-200 p-3 hover:bg-amber-50 transition">
                                <input type="checkbox" name="gunakan_saran" id="gunakanSaran" value="1"
                                    class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500">
                                <input type="hidden" name="ruang_saran_id" id="ruangSaranId" value="">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Gunakan ruang saran dari sistem</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Centang untuk mengajukan dengan ruang alternatif ini
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Reservasi
            </button>
            <a href="{{ route('reservasi.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
let cekTimer = null;

function cekKetersediaan() {
    clearTimeout(cekTimer);
    cekTimer = setTimeout(_doCheck, 600);
}

function tampilkanRuangSaran(ruang, judul, detail, bisaDipakai) {
    document.getElementById('judulSaran').textContent = judul;
    document.getElementById('detailSaran').textContent = detail;
    document.getElementById('saranKode').textContent = ruang.kode_ruang;
    document.getElementById('saranNama').textContent = ruang.nama_ruang;
    document.getElementById('saranKapasitas').textContent = 'Kapasitas: ' + ruang.kapasitas + ' kursi';
    document.getElementById('saranFasilitas').textContent = 'Fasilitas: ' + (ruang.fasilitas || '-');

    const pilihanSaran = document.getElementById('pilihanSaran');
    const gunakanSaran = document.getElementById('gunakanSaran');
    const ruangSaranId = document.getElementById('ruangSaranId');

    gunakanSaran.checked = false;
    ruangSaranId.value = bisaDipakai ? ruang.id : '';
    pilihanSaran.classList.toggle('hidden', !bisaDipakai);

    document.getElementById('panelSaran').classList.remove('hidden');
}

function _doCheck() {
    const ruangId    = document.getElementById('ruangInput').value;
    const tanggal    = document.getElementById('tanggalInput').value;
    const jamMulai   = document.getElementById('jamMulaiInput').value;
    const jamSelesai = document.getElementById('jamSelesaiInput').value;
    const peserta    = document.getElementById('jumlahPeserta').value;

    const panel      = document.getElementById('panelGreedy');
    const pLoading   = document.getElementById('panelLoading');
    const pTersedia  = document.getElementById('panelTersedia');
    const pSaran     = document.getElementById('panelSaran');
    const pKonflik   = document.getElementById('panelKonflikSaja');
    const gunakanSaran = document.getElementById('gunakanSaran');
    const ruangSaranId = document.getElementById('ruangSaranId');

    // Sembunyikan semua panel dulu
    [panel, pLoading, pTersedia, pSaran, pKonflik].forEach(el => {
        if (el) el.classList.add('hidden');
    });
    gunakanSaran.checked = false;
    ruangSaranId.value = '';

    if (!ruangId || !tanggal || !jamMulai || !jamSelesai) return;

    // Tampilkan loading
    panel.classList.remove('hidden');
    pLoading.classList.remove('hidden');

    fetch('{{ route("api.reservasi.cek") }}?' + new URLSearchParams({
        ruang_kelas_id: ruangId,
        tanggal:        tanggal,
        jam_mulai:      jamMulai,
        jam_selesai:    jamSelesai,
        jumlah_peserta: peserta,
    }), { method: 'GET' })
    .then(r => r.json())
    .then(data => {
        pLoading.classList.add('hidden');

        if (!data.konflik) {
            pTersedia.classList.remove('hidden');
            if (data.rekomendasi_ruang) {
                tampilkanRuangSaran(
                    data.rekomendasi_ruang,
                    'Rekomendasi Ruang Terbaik',
                    data.rekomendasi_sama_dengan_pilihan
                        ? 'Ruang pilihan Anda sudah menjadi pilihan terbaik berdasarkan kapasitas dan ketersediaan.'
                        : 'Sistem menemukan ruang yang lebih pas berdasarkan kapasitas dan ketersediaan.',
                    !data.rekomendasi_sama_dengan_pilihan
                );
            }
            return;
        }

        if (data.saran_ruang) {
            tampilkanRuangSaran(
                data.saran_ruang,
                'Ruang Pilihan Bentrok - Sistem Menyarankan Ruang Alternatif',
                data.detail,
                true
            );
        } else {
            document.getElementById('detailKonflik').textContent = data.detail;
            pKonflik.classList.remove('hidden');
        }
    })
    .catch(() => {
        pLoading.classList.add('hidden');
    });
}
</script>
@endsection