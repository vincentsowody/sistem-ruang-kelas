@extends('layouts.app')
@section('title', 'Import Data Diri Dosen')

@push('head')
<style>
  /* ── Custom styles untuk halaman import dosen ── */
  .step-connector {
    position: absolute;
    top: 20px;
    left: calc(50% + 24px);
    width: calc(100% - 48px);
    height: 2px;
    background: #e5e7eb;
    z-index: 0;
  }
  .step-connector.done { background: #22c55e; }
  .step-connector.active { background: linear-gradient(90deg,#22c55e,#3b82f6); }

  .dosen-card {
    transition: box-shadow .15s, border-color .15s;
  }
  .dosen-card:focus-within {
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    border-color: #93c5fd;
  }
  .dosen-card.disabled {
    opacity: .45;
    pointer-events: none;
  }

  input.invalid { border-color:#ef4444!important; }
  input.valid   { border-color:#22c55e!important; }

  @keyframes slideIn {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
  }
  .anim-in { animation: slideIn .2s ease both; }

  .mode-tab { transition: all .15s; }
  .mode-tab.active {
    background: white;
    color: #1d4ed8;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    font-weight:600;
  }
</style>
@endpush

@section('content')

{{-- ── Header ─────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
  <div>
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
      <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600 transition">Pengguna</a>
      <i class="fa-solid fa-chevron-right text-xs"></i>
      <span class="text-gray-700 font-medium">Import Data Diri Dosen</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
      <span class="bg-blue-100 text-blue-600 rounded-xl p-2 text-base">
        <i class="fa-solid fa-chalkboard-user"></i>
      </span>
      Import Data Diri Dosen
    </h1>
    <p class="text-gray-400 text-sm mt-1">
      Tambahkan dosen baru ke sistem — satu per satu atau sekaligus lewat Excel.
    </p>
  </div>
  <div class="flex gap-2 shrink-0">
    <a href="{{ route('admin.dosen-import.form') }}"
       class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 font-medium px-4 py-2 rounded-xl transition text-sm border border-green-200">
      <i class="fa-solid fa-file-excel"></i> Import dari Excel
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium px-4 py-2 rounded-xl transition text-sm">
      <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

{{-- ── Alert session ───────────────────────────────── --}}
@if(session('success'))
  <div class="mb-5 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-3 anim-in">
    <i class="fa-solid fa-circle-check text-green-500 mt-0.5 text-lg shrink-0"></i>
    <div class="text-sm text-green-800">{!! session('success') !!}</div>
  </div>
@endif
@if(session('warning'))
  <div class="mb-5 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 anim-in">
    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 text-lg shrink-0"></i>
    <div class="text-sm text-amber-800">{!! session('warning') !!}</div>
  </div>
@endif
@if(session('error'))
  <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3 anim-in">
    <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5 text-lg shrink-0"></i>
    <div class="text-sm text-red-800">{!! session('error') !!}</div>
  </div>
@endif

{{-- ── Mode tabs ───────────────────────────────────── --}}
<div class="bg-gray-100 rounded-2xl p-1 inline-flex gap-1 mb-6">
  <button onclick="setMode('manual')" id="tab-manual"
    class="mode-tab active px-5 py-2 rounded-xl text-sm text-gray-500">
    <i class="fa-solid fa-keyboard mr-1.5"></i>Input Manual
  </button>
  <button onclick="setMode('csv')" id="tab-csv"
    class="mode-tab px-5 py-2 rounded-xl text-sm text-gray-500">
    <i class="fa-solid fa-file-csv mr-1.5"></i>Paste CSV / Teks
  </button>
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- MODE 1: Input Manual                              --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="panel-manual">

  <form method="POST" action="{{ route('admin.dosen-import.simpan') }}" id="formManual" novalidate>
    @csrf

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-2xl p-4">
      <p class="text-sm font-semibold text-red-700 mb-2 flex items-center gap-1.5">
        <i class="fa-solid fa-circle-xmark"></i> Ada kesalahan pada data:
      </p>
      <ul class="text-xs text-red-600 list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
      </ul>
    </div>
    @endif

    {{-- Daftar kartu dosen --}}
    <div id="dosenList" class="space-y-4 mb-5">
      {{-- Kartu pertama selalu ada --}}
      <div class="dosen-card bg-white rounded-2xl border border-gray-200 p-5 anim-in" id="card-0" data-index="0">
        @include('admin.dosen._card', ['i' => 0, 'nama' => old('dosen.0.nama', ''), 'email' => old('dosen.0.email',''), 'nip' => old('dosen.0.nip',''), 'prodi' => old('dosen.0.program_studi','')])
      </div>
    </div>

    {{-- Tombol tambah kartu --}}
    <button type="button" onclick="tambahKartu()"
      class="w-full border-2 border-dashed border-blue-200 hover:border-blue-400 hover:bg-blue-50 rounded-2xl py-4 flex items-center justify-center gap-2 text-blue-500 hover:text-blue-700 transition text-sm font-medium mb-6">
      <i class="fa-solid fa-plus text-lg"></i>
      Tambah Dosen Lainnya
    </button>

    {{-- Info password default --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 mb-6">
      <i class="fa-solid fa-key text-amber-500 mt-0.5 shrink-0"></i>
      <div class="text-sm">
        <p class="font-semibold text-amber-800 mb-0.5">Password default: <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">dosen123</code></p>
        <p class="text-amber-700 text-xs">Dosen dapat menggantinya setelah login pertama. Pastikan NIP/NIM atau email dikomunikasikan ke dosen yang bersangkutan.</p>
      </div>
    </div>

    {{-- Tombol submit --}}
    <div class="flex items-center gap-3">
      <button type="submit" id="btnSimpan"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition text-sm shadow-sm shadow-blue-200">
        <i class="fa-solid fa-user-plus"></i>
        Simpan Dosen
        <span id="jumlahLabel" class="bg-white/25 text-white text-xs px-2 py-0.5 rounded-full font-medium">1</span>
      </button>
      <a href="{{ route('admin.users.index') }}"
        class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium px-6 py-3 rounded-xl transition text-sm">
        Batal
      </a>
      <span class="text-xs text-gray-400 ml-auto hidden sm:block">
        <i class="fa-solid fa-shield-halved mr-1"></i>Data tersimpan terenkripsi
      </span>
    </div>
  </form>

</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- MODE 2: Paste CSV / Teks                          --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="panel-csv" class="hidden">

  <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
    <h3 class="font-semibold text-gray-700 mb-1 flex items-center gap-2">
      <i class="fa-solid fa-paste text-indigo-500"></i>Paste Data Dosen
    </h3>
    <p class="text-xs text-gray-400 mb-4">
      Tempel tabel dari Excel atau ketik langsung. Format tiap baris:
      <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs font-mono">Nama ; Email ; NIP ; Program Studi</code>
      (dipisah titik-koma atau tab)
    </p>

    <div class="relative">
      <textarea id="csvInput" rows="8" placeholder="Contoh:
Dr. Budi Santoso, M.Kom ; budi@unsrat.ac.id ; 197001012000031001 ; Teknik Informatika
Sari Paturusi, ST, M.Eng ; sari@unsrat.ac.id ; ; Teknik Sipil
Ahmad Widjaya ; ahmad@kampus.ac.id"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono resize-y focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400"
        oninput="parseCsv()"></textarea>
      <div class="absolute bottom-3 right-3 text-xs text-gray-300" id="csvCounter">0 baris</div>
    </div>

    <div class="mt-3 flex gap-2">
      <button type="button" onclick="parseCsv()"
        class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i> Parse & Preview
      </button>
      <button type="button" onclick="clearCsv()"
        class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fa-solid fa-eraser text-xs"></i> Hapus
      </button>
    </div>
  </div>

  {{-- Preview hasil parse --}}
  <div id="csvPreview" class="hidden mb-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold text-gray-700 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye text-blue-400"></i>
        Preview Data
        <span id="csvPreviewCount" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">0</span>
      </h3>
      <button type="button" onclick="konfirmasiCsv()"
        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition">
        <i class="fa-solid fa-arrow-right"></i> Pindah ke Form Manual
      </button>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-6">#</th>
            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">NIP</th>
            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Program Studi</th>
            <th class="w-8"></th>
          </tr>
        </thead>
        <tbody id="csvPreviewBody" class="divide-y divide-gray-50"></tbody>
      </table>
    </div>
  </div>

</div>

{{-- ── Template kartu (hidden, untuk JS clone) ─────── --}}
<template id="cardTemplate">
  <div class="dosen-card bg-white rounded-2xl border border-gray-200 p-5 anim-in" id="card-__IDX__" data-index="__IDX__">
    @include('admin.dosen._card', ['i' => '__IDX__', 'nama' => '', 'email' => '', 'nip' => '', 'prodi' => ''])
  </div>
</template>

@endsection

@section('scripts')
<script>
// ── State ──────────────────────────────────────────────
let totalKartu = 1;
let csvData    = [];

// ── Mode tabs ──────────────────────────────────────────
function setMode(mode) {
  ['manual','csv'].forEach(m => {
    document.getElementById('panel-'+m).classList.toggle('hidden', m !== mode);
    document.getElementById('tab-'+m).classList.toggle('active', m === mode);
  });
}

// ── Tambah kartu dosen baru ────────────────────────────
function tambahKartu(data = {}) {
  const idx  = totalKartu++;
  const tmpl = document.getElementById('cardTemplate').innerHTML
    .replaceAll('__IDX__', idx);

  const wrapper = document.createElement('div');
  wrapper.innerHTML = tmpl;
  const card = wrapper.firstElementChild;
  document.getElementById('dosenList').appendChild(card);

  // Isi nilai jika ada data dari CSV
  if (data.nama)  card.querySelector(`[name="dosen[${idx}][nama]"]`).value  = data.nama;
  if (data.email) card.querySelector(`[name="dosen[${idx}][email]"]`).value = data.email;
  if (data.nip)   card.querySelector(`[name="dosen[${idx}][nip]"]`).value   = data.nip;
  if (data.prodi) card.querySelector(`[name="dosen[${idx}][program_studi]"]`).value = data.prodi;

  updateJumlah();
  card.querySelector('input').focus();
}

function hapusKartu(idx) {
  const card = document.getElementById('card-'+idx);
  if (!card) return;
  // Minimal 1 kartu
  const semua = document.querySelectorAll('.dosen-card');
  if (semua.length <= 1) {
    card.querySelectorAll('input').forEach(i => i.value = '');
    return;
  }
  card.style.transition = 'opacity .15s, transform .15s';
  card.style.opacity = '0';
  card.style.transform = 'scale(.97)';
  setTimeout(() => { card.remove(); updateJumlah(); }, 150);
}

function updateJumlah() {
  const n = document.querySelectorAll('.dosen-card').length;
  const el = document.getElementById('jumlahLabel');
  if (el) el.textContent = n + ' dosen';
}

// ── Validasi email real-time ───────────────────────────
function validasiEmail(inp) {
  const val = inp.value.trim();
  if (!val) { inp.className = inp.className.replace(' valid',' ').replace(' invalid',' '); return; }
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
  inp.classList.toggle('valid', ok);
  inp.classList.toggle('invalid', !ok);
}

// ── Parse CSV / teks ───────────────────────────────────
function parseCsv() {
  const raw  = document.getElementById('csvInput').value.trim();
  const lines = raw.split('\n').filter(l => l.trim().length > 0);
  document.getElementById('csvCounter').textContent = lines.length + ' baris';

  csvData = lines.map(line => {
    // Deteksi separator: ; atau \t
    const sep = line.includes(';') ? ';' : '\t';
    const cols = line.split(sep).map(c => c.trim());
    return {
      nama  : cols[0] || '',
      email : cols[1] || '',
      nip   : cols[2] || '',
      prodi : cols[3] || '',
    };
  }).filter(d => d.nama.length > 0);

  renderPreview();
}

function renderPreview() {
  const body    = document.getElementById('csvPreviewBody');
  const panel   = document.getElementById('csvPreview');
  const counter = document.getElementById('csvPreviewCount');

  body.innerHTML = '';
  counter.textContent = csvData.length;

  if (csvData.length === 0) { panel.classList.add('hidden'); return; }
  panel.classList.remove('hidden');

  csvData.forEach((d, i) => {
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email);
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50';
    tr.innerHTML = `
      <td class="px-4 py-3 text-xs text-gray-400">${i+1}</td>
      <td class="px-4 py-3">
        <span class="font-medium text-gray-800 text-sm">${esc(d.nama)}</span>
      </td>
      <td class="px-4 py-3">
        <span class="text-sm ${emailOk ? 'text-gray-700' : 'text-red-500 font-medium'}">${esc(d.email) || '<span class="text-gray-300 italic text-xs">kosong</span>'}</span>
        ${!emailOk && d.email ? '<i class="fa-solid fa-triangle-exclamation text-red-400 text-xs ml-1"></i>' : ''}
      </td>
      <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">${esc(d.nip) || '—'}</td>
      <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">${esc(d.prodi) || '—'}</td>
      <td class="px-4 py-3">
        <button type="button" onclick="hapusCsvRow(${i})" class="text-red-400 hover:text-red-600 transition text-xs">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </td>
    `;
    body.appendChild(tr);
  });
}

function hapusCsvRow(i) {
  csvData.splice(i, 1);
  renderPreview();
}

function clearCsv() {
  document.getElementById('csvInput').value = '';
  csvData = [];
  document.getElementById('csvPreview').classList.add('hidden');
  document.getElementById('csvCounter').textContent = '0 baris';
}

function konfirmasiCsv() {
  if (csvData.length === 0) return;

  // Kosongkan kartu manual yang ada, ganti dengan data CSV
  document.getElementById('dosenList').innerHTML = '';
  totalKartu = 0;

  // Kartu pertama
  const d0 = csvData[0];
  totalKartu = 1;
  const first = `<div class="dosen-card bg-white rounded-2xl border border-gray-200 p-5 anim-in" id="card-0" data-index="0">
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-2">
        <span class="bg-blue-100 text-blue-600 text-xs font-bold px-2.5 py-1 rounded-lg">Dosen #1</span>
      </div>
      <button type="button" onclick="hapusKartu(0)" class="text-gray-300 hover:text-red-500 transition" title="Hapus">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" name="dosen[0][nama]" value="${esc(d0.nama)}" placeholder="Nama lengkap dengan gelar" required
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Email <span class="text-red-500">*</span></label>
        <input type="email" name="dosen[0][email]" value="${esc(d0.email)}" placeholder="email@kampus.ac.id" required
          oninput="validasiEmail(this)"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">NIP / NIDN</label>
        <input type="text" name="dosen[0][nip]" value="${esc(d0.nip)}" placeholder="Nomor Induk Pegawai"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Program Studi</label>
      <input type="text" name="dosen[0][program_studi]" value="${esc(d0.prodi)}" placeholder="Teknik Informatika"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
  </div>`;
  document.getElementById('dosenList').innerHTML = first;

  csvData.slice(1).forEach(d => tambahKartu(d));
  setMode('manual');
}

function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
updateJumlah();
</script>
@endsection
