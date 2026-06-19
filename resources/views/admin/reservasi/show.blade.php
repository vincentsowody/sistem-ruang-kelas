@extends('layouts.app')
@section('title', 'Detail Reservasi')

@section('content')

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.reservasi.index') }}" class="hover:text-blue-600">Reservasi</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">{{ $reservasi->kode_reservasi }}</span>
    </div>
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-gray-800">{{ $reservasi->keperluan }}</h1>
        @php
            $badge = match($reservasi->status) {
                'menunggu'   => 'bg-yellow-100 text-yellow-700',
                'disetujui'  => 'bg-green-100 text-green-700',
                'ditolak'    => 'bg-red-100 text-red-700',
                'dibatalkan' => 'bg-gray-100 text-gray-600',
                default      => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <span class="text-sm font-semibold px-4 py-1.5 rounded-full {{ $badge }} capitalize">
            {{ $reservasi->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Lengkap --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clipboard text-blue-500"></i> Detail Reservasi
            </h2>
            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                @foreach([
                    ['label'=>'Kode Reservasi', 'value'=>$reservasi->kode_reservasi],
                    ['label'=>'Pemohon',         'value'=>$reservasi->pemohon->name.' ('.$reservasi->pemohon->role.')'],
                    ['label'=>'Jenis Kegiatan',  'value'=>ucwords(str_replace('_',' ',$reservasi->jenis_kegiatan))],
                    ['label'=>'Jumlah Peserta',  'value'=>$reservasi->jumlah_peserta.' orang'],
                    ['label'=>'Tanggal',         'value'=>$reservasi->tanggal->locale('id')->isoFormat('dddd, D MMMM Y')],
                    ['label'=>'Waktu',           'value'=>substr($reservasi->jam_mulai,0,5).' – '.substr($reservasi->jam_selesai,0,5).' ('.($reservasi->durasi_menit).' menit)'],
                ] as $row)
                <div class="py-2 border-b border-gray-50">
                    <p class="text-xs text-gray-400 mb-0.5">{{ $row['label'] }}</p>
                    <p class="text-sm font-medium text-gray-800">{{ $row['value'] }}</p>
                </div>
                @endforeach
            </div>
            @if($reservasi->keterangan)
            <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 mb-1">Keterangan</p>
                <p class="text-sm text-gray-700">{{ $reservasi->keterangan }}</p>
            </div>
            @endif
        </div>

        {{-- Ruang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-green-500"></i> Ruang yang Diminta
            </h2>
            <div class="flex items-center gap-4 bg-blue-50 rounded-xl p-4 mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-school text-blue-600"></i>
                </div>
                <div>
                    <p class="font-bold text-blue-700 text-lg">{{ $reservasi->ruangKelas->kode_ruang }}</p>
                    <p class="text-sm text-gray-600">{{ $reservasi->ruangKelas->nama_ruang }}</p>
                    <p class="text-xs text-gray-400">{{ $reservasi->ruangKelas->gedung }} · {{ $reservasi->ruangKelas->kapasitas }} kursi</p>
                    <p class="text-xs text-gray-400">{{ $reservasi->ruangKelas->fasilitas_list }}</p>
                </div>
            </div>

            {{-- Saran Greedy --}}
            @if($reservasi->ruangSaran)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Saran Ruang Alternatif (Greedy Best-Fit)
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-door-open text-amber-600"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $reservasi->ruangSaran->kode_ruang }}</p>
                        <p class="text-sm text-gray-600">{{ $reservasi->ruangSaran->nama_ruang }}</p>
                        <p class="text-xs text-gray-400">{{ $reservasi->ruangSaran->kapasitas }} kursi · {{ $reservasi->ruangSaran->fasilitas_list }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel Approval --}}
    <div class="space-y-4">

        @if($reservasi->isMenunggu())

        {{-- ✨ PANEL PILIHKAN RUANG TERBAIK --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-blue-500"></i> Pilihkan Ruang Terbaik
            </h2>
            <p class="text-xs text-gray-400 mb-4">Jalankan Greedy Best-Fit untuk menemukan ruang paling sesuai, lalu tetapkan & setujui langsung.</p>

            <button type="button" onclick="scanRuangTersedia()"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm mb-4">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span id="btn-scan-label">Jalankan Greedy &amp; Cari Ruang</span>
            </button>

            <div id="scan-loading" class="hidden text-center py-3">
                <div class="inline-flex items-center gap-2 text-blue-600 text-sm">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Menjalankan algoritma greedy...
                </div>
            </div>

            <div id="hasil-scan" class="hidden">
                <div id="info-konflik" class="mb-3 p-3 rounded-xl text-sm hidden"></div>

                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    Ruang Tersedia <span class="text-blue-500 font-bold">(urutan Greedy Best-Fit)</span>
                </p>
                <div id="list-ruang-tersedia" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>

                <div id="section-bentrok" class="mt-3 hidden">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wide mb-2">Tidak Tersedia (Bentrok)</p>
                    <div id="list-ruang-bentrok" class="space-y-1.5"></div>
                </div>

                <form id="form-pilihkan-ruang" method="POST"
                    action="{{ route('admin.reservasi.pilihkan-ruang', $reservasi) }}"
                    class="mt-4 hidden">
                    @csrf
                    <input type="hidden" name="ruang_dipilih_id" id="input-ruang-dipilih">
                    <div id="preview-ruang-dipilih"
                        class="p-3 bg-blue-50 border border-blue-200 rounded-xl mb-3 text-sm text-blue-800 font-medium"></div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan untuk pemohon (opsional)</label>
                        <textarea name="catatan_admin" rows="2"
                            placeholder="Misal: Ruang asli bentrok, dipindahkan ke ruang alternatif..."
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-check-double"></i> Setujui &amp; Tetapkan Ruang Ini
                    </button>
                </form>
            </div>
        </div>

        {{-- Setujui Tanpa Ganti Ruang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-check text-green-500"></i> Setujui Tanpa Ganti Ruang
            </h2>
            <form method="POST" action="{{ route('admin.reservasi.setujui', $reservasi) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan_admin" rows="2" placeholder="Catatan untuk pemohon..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm border border-green-200">
                    <i class="fa-solid fa-check"></i> Setujui (Ruang Tetap)
                </button>
            </form>
        </div>

        {{-- Tolak --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-xmark text-red-500"></i> Tolak Reservasi
            </h2>
            <form method="POST" action="{{ route('admin.reservasi.tolak', $reservasi) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan_admin" rows="2" required placeholder="Masukkan alasan penolakan..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2 text-sm border border-red-200">
                    <i class="fa-solid fa-xmark"></i> Tolak Reservasi
                </button>
            </form>
        </div>

        @else
        {{-- Sudah diproses --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Hasil Proses</h2>
            <div class="text-center py-4">
                <div class="w-16 h-16 {{ $reservasi->isDisetujui() ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid {{ $reservasi->isDisetujui() ? 'fa-check text-green-600' : 'fa-xmark text-red-600' }} text-2xl"></i>
                </div>
                <p class="font-semibold text-gray-800 capitalize">{{ $reservasi->status }}</p>
                @if($reservasi->diproses_pada)
                <p class="text-xs text-gray-400 mt-1">
                    {{ $reservasi->diproses_pada->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                    @if($reservasi->diprosesDari) · {{ $reservasi->diprosesDari->name }} @endif
                </p>
                @endif
                @if($reservasi->catatan_admin)
                <p class="text-sm text-gray-600 mt-3 bg-gray-50 rounded-xl p-3 text-left">
                    {{ $reservasi->catatan_admin }}
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- Info Pemohon --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-user text-purple-500"></i> Pemohon
            </h2>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-user text-purple-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $reservasi->pemohon->name }}</p>
                    <p class="text-xs text-gray-500">{{ $reservasi->pemohon->email }}</p>
                    <p class="text-xs text-gray-400 capitalize mt-0.5">{{ $reservasi->pemohon->role }}
                        @if($reservasi->pemohon->program_studi) · {{ $reservasi->pemohon->program_studi }} @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const SARAN_URL = '{{ route("admin.reservasi.saran-ruang", $reservasi) }}';

async function scanRuangTersedia() {
    document.getElementById('scan-loading').classList.remove('hidden');
    document.getElementById('hasil-scan').classList.add('hidden');
    document.getElementById('btn-scan-label').textContent = 'Memindai...';
    document.getElementById('form-pilihkan-ruang').classList.add('hidden');

    try {
        const res = await fetch(SARAN_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        renderHasilScan(data);
    } catch (e) {
        alert('Gagal menghubungi server. Coba lagi.');
    } finally {
        document.getElementById('scan-loading').classList.add('hidden');
        document.getElementById('btn-scan-label').textContent = 'Scan Ulang';
    }
}

function renderHasilScan(data) {
    document.getElementById('hasil-scan').classList.remove('hidden');

    // Info ruang asli
    const infoEl = document.getElementById('info-konflik');
    infoEl.classList.remove('hidden');
    if (data.ruang_asli_konflik) {
        infoEl.className = 'mb-3 p-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';
        infoEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i><strong>Ruang asli bentrok:</strong> ' + data.ruang_asli_detail;
    } else {
        infoEl.className = 'mb-3 p-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700';
        infoEl.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i><strong>Ruang asli tersedia</strong> dan tidak ada konflik.';
    }

    // Render tersedia
    const listEl = document.getElementById('list-ruang-tersedia');
    listEl.innerHTML = '';
    if (data.tersedia.length === 0) {
        listEl.innerHTML = '<p class="text-sm text-gray-400 text-center py-3">Tidak ada ruang yang tersedia.</p>';
    } else {
        data.tersedia.forEach((r, i) => {
            const badgeHtml = r.adalah_asli
                ? '<span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1">Pilihan Asli</span>'
                : r.adalah_saran
                    ? '<span class="text-xs bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full ml-1">Saran Greedy</span>'
                    : i === 0
                        ? '<span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full ml-1">⭐ Best-Fit</span>'
                        : '';
            const kapHtml = r.cukup
                ? '<span class="text-xs text-green-600"><i class="fa-solid fa-users mr-0.5"></i>' + r.kapasitas + ' kursi</span>'
                : '<span class="text-xs text-orange-500"><i class="fa-solid fa-users mr-0.5"></i>' + r.kapasitas + ' kursi (kurang)</span>';

            const div = document.createElement('div');
            div.className = 'cursor-pointer border-2 rounded-xl p-3 transition hover:border-blue-400 hover:bg-blue-50 ' + (r.adalah_asli ? 'border-blue-300 bg-blue-50' : 'border-gray-200 bg-white');
            div.dataset.ruangId = r.id;
            div.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">${r.kode_ruang} ${badgeHtml}</p>
                        <p class="text-xs text-gray-500 truncate">${r.nama_ruang} · ${r.gedung}</p>
                        <div class="flex flex-wrap gap-3 mt-1">${kapHtml}<span class="text-xs text-gray-400">${r.fasilitas}</span></div>
                    </div>
                    <div class="shrink-0 w-5 h-5 rounded-full border-2 border-gray-300 mt-0.5 flex items-center justify-center" id="radio-${r.id}"></div>
                </div>`;
            div.addEventListener('click', () => pilihRuang(r, div));
            listEl.appendChild(div);
        });
    }

    // Render bentrok
    const bentrokEl = document.getElementById('section-bentrok');
    const listBentrok = document.getElementById('list-ruang-bentrok');
    listBentrok.innerHTML = '';
    if (data.bentrok.length > 0) {
        bentrokEl.classList.remove('hidden');
        data.bentrok.forEach(r => {
            listBentrok.innerHTML += `
                <div class="border border-red-100 rounded-xl p-2.5 bg-red-50 flex items-center gap-2 opacity-70">
                    <i class="fa-solid fa-ban text-red-400 text-xs shrink-0"></i>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-700 truncate">${r.kode_ruang} · ${r.nama_ruang}</p>
                        <p class="text-xs text-red-500">${r.detail}</p>
                    </div>
                </div>`;
        });
    } else {
        bentrokEl.classList.add('hidden');
    }

    document.getElementById('form-pilihkan-ruang').classList.add('hidden');
}

function pilihRuang(ruang, cardEl) {
    // Reset semua card
    document.querySelectorAll('#list-ruang-tersedia > div').forEach(c => {
        c.classList.remove('border-blue-500', 'bg-blue-100');
        c.classList.add('border-gray-200', 'bg-white');
        const r = document.getElementById('radio-' + c.dataset.ruangId);
        if (r) r.innerHTML = '';
    });

    // Aktifkan card terpilih
    cardEl.classList.remove('border-gray-200', 'bg-white');
    cardEl.classList.add('border-blue-500', 'bg-blue-100');
    const radio = document.getElementById('radio-' + ruang.id);
    if (radio) radio.innerHTML = '<div class="w-3 h-3 rounded-full bg-blue-600"></div>';

    document.getElementById('input-ruang-dipilih').value = ruang.id;
    document.getElementById('preview-ruang-dipilih').innerHTML =
        '<i class="fa-solid fa-circle-check text-green-600 mr-1"></i>' +
        '<strong>' + ruang.kode_ruang + '</strong> — ' + ruang.nama_ruang +
        ' · <span class="font-normal text-gray-600">' + ruang.kapasitas + ' kursi · ' + ruang.gedung + '</span>' +
        ' akan ditetapkan untuk pemohon.';
    document.getElementById('form-pilihkan-ruang').classList.remove('hidden');
}
</script>
@endpush

@endsection