@extends('admin.laporan.pdf.layout')

@section('content')

{{-- Header --}}
<div class="header">
    <div class="header-inner">
        <div class="header-logo">
            <div class="header-logo-box">RK</div>
        </div>
        <div class="header-text">
            <h1>{{ config('app.name', 'Sistem Ruang Kelas') }}</h1>
            <p>Sistem Penjadwalan dan Reservasi Pengelolaan Ruang Kelas</p>
        </div>
        <div class="header-meta">
            Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}<br>
            Oleh: {{ auth()->user()->name ?? 'Admin' }}
        </div>
    </div>
</div>

{{-- Judul --}}
<div class="report-title">
    <h2>Laporan Utilisasi Ruang Kelas</h2>
    <p class="subtitle">Periode {{ $namaBulan }}</p>
</div>

{{-- Ringkasan --}}
@php
    $totalSesi = $utilisasi->sum('total_sesi');
    $totalJam  = $utilisasi->sum('total_jam');
    $ruangAktif = $utilisasi->where('total_sesi', '>', 0)->count();
    $avgUtil   = $utilisasi->count() > 0 ? round($utilisasi->avg('persen'), 1) : 0;
@endphp
<div class="summary-box">
    <div class="summary-title">Ringkasan Bulan {{ $namaBulan }}</div>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="num">{{ $utilisasi->count() }}</div>
            <div class="lbl">Total Ruang</div>
        </div>
        <div class="summary-item">
            <div class="num" style="color:#166534">{{ $ruangAktif }}</div>
            <div class="lbl">Ruang Dipakai</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ $totalSesi }}</div>
            <div class="lbl">Total Sesi</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ number_format($totalJam, 1) }}</div>
            <div class="lbl">Total Jam</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ $avgUtil }}%</div>
            <div class="lbl">Rata-rata Utilisasi</div>
        </div>
    </div>
</div>

{{-- Tabel Utilisasi --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:25px">No</th>
            <th style="width:60px">Kode</th>
            <th>Nama Ruang</th>
            <th style="width:70px">Gedung</th>
            <th style="width:40px">Kap.</th>
            <th style="width:40px">Jenis</th>
            <th style="width:40px">Sesi</th>
            <th style="width:45px">Jam</th>
            <th style="width:130px">Utilisasi</th>
            <th style="width:45px">%</th>
        </tr>
    </thead>
    <tbody>
        @forelse($utilisasi as $i => $item)
        @php
            $persen = $item['persen'];
            $barColor = $persen >= 70 ? '#16A34A'
                      : ($persen >= 40 ? '#2563EB'
                      : ($persen >= 10 ? '#F59E0B' : '#D1D5DB'));
            $kategori = $persen >= 70 ? 'badge-green'
                      : ($persen >= 40 ? 'badge-blue'
                      : ($persen >= 10 ? 'badge-amber' : 'badge-gray'));
        @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td><span class="badge badge-blue">{{ $item['ruang']->kode_ruang }}</span></td>
            <td class="fw-bold">{{ $item['ruang']->nama_ruang }}</td>
            <td>{{ $item['ruang']->gedung ?? '-' }}</td>
            <td class="text-center">{{ $item['ruang']->kapasitas }}</td>
            <td style="font-size:8px;text-transform:capitalize">{{ $item['ruang']->jenis_ruang ?? '-' }}</td>
            <td class="text-center fw-bold">{{ $item['total_sesi'] }}</td>
            <td class="text-center fw-bold">{{ $item['total_jam'] }} jam</td>
            <td style="padding-top:10px">
                <div class="progress-wrap">
                    <div class="progress-bar" style="width:{{ min($persen,100) }}%;background:{{ $barColor }}"></div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge {{ $kategori }}">{{ $persen }}%</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center" style="padding:20px;color:#9CA3AF">
                Tidak ada data utilisasi untuk periode ini
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Keterangan skala utilisasi --}}
<div style="margin-top:8px;font-size:8px;color:#9CA3AF">
    <span style="font-weight:bold;color:#374151">Keterangan utilisasi:</span>
    &nbsp;
    <span class="badge badge-green">&ge;70% Tinggi</span>&nbsp;
    <span class="badge badge-blue">40&ndash;69% Sedang</span>&nbsp;
    <span class="badge badge-amber">10&ndash;39% Rendah</span>&nbsp;
    <span class="badge badge-gray">&lt;10% Tidak aktif</span>
</div>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">{{ config('app.name') }} &mdash; Laporan Utilisasi Ruang</div>
    <div class="footer-right">Dokumen ini digenerate secara otomatis oleh sistem</div>
</div>

@endsection
