@extends('layouts.app')
@section('title', 'Kalender Jadwal')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kalender Jadwal</h1>
        <p class="text-gray-500 text-sm mt-1">Tampilan jadwal tetap dan reservasi yang disetujui</p>
    </div>
    {{-- Filter Ruang --}}
    <div class="flex items-center gap-2">
        <select id="filterRuang"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Ruang</option>
            @foreach($ruangList as $r)
            <option value="{{ $r->id }}">{{ $r->kode_ruang }} — {{ $r->nama_ruang }}</option>
            @endforeach
        </select>
        <a href="{{ route('reservasi.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-xl transition text-sm">
            <i class="fa-solid fa-plus"></i> Reservasi
        </a>
    </div>
</div>

{{-- Legenda --}}
<div class="flex flex-wrap gap-4 mb-4">
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 rounded bg-blue-500"></div>
        <span class="text-sm text-gray-600">Jadwal Tetap</span>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 rounded bg-green-500"></div>
        <span class="text-sm text-gray-600">Reservasi Disetujui</span>
    </div>
</div>

{{-- Kalender --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6">
    <div id="kalender"></div>
</div>

{{-- Modal Detail Event --}}
<div id="modalEvent" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div id="modalIkon" class="w-10 h-10 rounded-xl flex items-center justify-center">
                    <i id="modalIkonIcon" class="fa-solid fa-calendar text-white"></i>
                </div>
                <div>
                    <p id="modalTipe" class="text-xs font-semibold uppercase tracking-wide text-gray-400"></p>
                    <h3 id="modalJudul" class="font-bold text-gray-800 text-lg leading-tight"></h3>
                </div>
            </div>
            <button onclick="tutupModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="modalKonten" class="space-y-2.5"></div>

        <div class="mt-5 flex gap-2">
            <button onclick="tutupModal()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-xl transition text-sm">
                Tutup
            </button>
            <a id="modalLink" href="#"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl transition text-sm text-center hidden">
                Lihat Detail
            </a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- FullCalendar CDN --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/id.global.min.js'></script>

<script>
let kalender;

document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('kalender');

    kalender = new FullCalendar.Calendar(el, {
        locale: 'id',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today:    'Hari Ini',
            month:    'Bulan',
            week:     'Minggu',
            day:      'Hari',
            list:     'Daftar',
        },
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot:  false,
        height:      'auto',
        nowIndicator: true,
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime:  '07:00',
            endTime:    '20:00',
        },
        events: function (info, successCallback, failureCallback) {
            const ruangId = document.getElementById('filterRuang').value;
            const params  = new URLSearchParams({
                start: info.startStr,
                end:   info.endStr,
            });
            if (ruangId) params.append('ruang_id', ruangId);

            fetch('{{ route("api.kalender.events") }}?' + params)
                .then(r => r.json())
                .then(successCallback)
                .catch(failureCallback);
        },
        eventClick: function (info) {
            tampilModal(info.event);
        },
        eventDidMount: function (info) {
            // Tooltip singkat
            info.el.title = info.event.title;
        },
    });

    kalender.render();

    // Filter ruang
    document.getElementById('filterRuang').addEventListener('change', function () {
        kalender.refetchEvents();
    });
});

function tampilModal(event) {
    const p   = event.extendedProps;
    const modal = document.getElementById('modalEvent');
    const ikon  = document.getElementById('modalIkon');
    const link  = document.getElementById('modalLink');

    // Tentukan warna & ikon berdasarkan tipe
    if (p.tipe === 'jadwal_tetap') {
        ikon.className  = 'w-10 h-10 rounded-xl flex items-center justify-center bg-blue-500';
        document.getElementById('modalIkonIcon').className = 'fa-solid fa-calendar-days text-white';
        document.getElementById('modalTipe').textContent   = 'Jadwal Tetap';
        link.classList.add('hidden');
    } else {
        ikon.className  = 'w-10 h-10 rounded-xl flex items-center justify-center bg-green-500';
        document.getElementById('modalIkonIcon').className = 'fa-solid fa-clipboard-check text-white';
        document.getElementById('modalTipe').textContent   = 'Reservasi';
        link.classList.remove('hidden');
    }

    document.getElementById('modalJudul').textContent = event.title.split(' (')[0];

    // Build konten
    const rows = p.tipe === 'jadwal_tetap' ? [
        ['Mata Kuliah', p.mata_kuliah],
        ['Dosen',       p.dosen],
        ['Ruang',       p.ruang],
        ['Kelas',       p.kelas],
        ['SKS',         p.sks],
    ] : [
        ['Keperluan',   p.keperluan],
        ['Pemohon',     p.pemohon],
        ['Ruang',       p.ruang],
        ['Peserta',     p.peserta],
        ['Kode',        p.kode],
    ];

    // Tambahkan waktu
    const mulai   = event.start ? event.start.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'}) : '';
    const selesai = event.end   ? event.end.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})   : '';
    rows.unshift(['Waktu', mulai + ' – ' + selesai]);

    document.getElementById('modalKonten').innerHTML = rows.map(([label, val]) => `
        <div class="flex justify-between items-start py-1.5 border-b border-gray-50 last:border-0">
            <span class="text-xs text-gray-400 flex-shrink-0 w-24">${label}</span>
            <span class="text-sm font-medium text-gray-800 text-right">${val || '-'}</span>
        </div>
    `).join('');

    modal.classList.remove('hidden');
}

function tutupModal() {
    document.getElementById('modalEvent').classList.add('hidden');
}

// Tutup modal saat klik di luar
document.getElementById('modalEvent').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});
</script>
@endsection
