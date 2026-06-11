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
    <h2>Laporan Reservasi Ruang Kelas</h2>
    <p class="subtitle">
        Periode {{ \Carbon\Carbon::parse($request->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') }}
        s/d {{ \Carbon\Carbon::parse($request->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y') }}
        @if($request->filled('status')) &mdash; Status: {{ ucfirst($request->status) }} @endif
    </p>
</div>

{{-- Ringkasan statistik --}}
@php
    $totalDisetujui  = $reservasiList->where('status','disetujui')->count();
    $totalMenunggu   = $reservasiList->where('status','menunggu')->count();
    $totalDitolak    = $reservasiList->where('status','ditolak')->count();
    $totalDibatalkan = $reservasiList->where('status','dibatalkan')->count();
@endphp
<div class="summary-box">
    <div class="summary-title">Ringkasan</div>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="num">{{ $reservasiList->count() }}</div>
            <div class="lbl">Total</div>
        </div>
        <div class="summary-item">
            <div class="num" style="color:#166534">{{ $totalDisetujui }}</div>
            <div class="lbl">Disetujui</div>
        </div>
        <div class="summary-item">
            <div class="num" style="color:#92400E">{{ $totalMenunggu }}</div>
            <div class="lbl">Menunggu</div>
        </div>
        <div class="summary-item">
            <div class="num" style="color:#991B1B">{{ $totalDitolak }}</div>
            <div class="lbl">Ditolak</div>
        </div>
        <div class="summary-item">
            <div class="num" style="color:#6B7280">{{ $totalDibatalkan }}</div>
            <div class="lbl">Dibatalkan</div>
        </div>
    </div>
</div>

{{-- Tabel Reservasi --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:25px">No</th>
            <th style="width:95px">Kode</th>
            <th style="width:65px">Tanggal</th>
            <th style="width:70px">Jam</th>
            <th>Keperluan</th>
            <th style="width:80px">Jenis</th>
            <th>Pemohon</th>
            <th style="width:45px">Ruang</th>
            <th style="width:30px">Pst.</th>
            <th style="width:55px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reservasiList as $i => $res)
        @php
            $badgeClass = match($res->status) {
                'disetujui'  => 'badge-green',
                'menunggu'   => 'badge-amber',
                'ditolak'    => 'badge-red',
                'dibatalkan' => 'badge-gray',
                default      => 'badge-gray',
            };
            $labelStatus = match($res->status) {
                'disetujui'  => 'Disetujui',
                'menunggu'   => 'Menunggu',
                'ditolak'    => 'Ditolak',
                'dibatalkan' => 'Dibatalkan',
                default      => ucfirst($res->status),
            };
        @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td style="font-family:monospace;font-size:8px">{{ $res->kode_reservasi }}</td>
            <td>{{ $res->tanggal->locale('id')->isoFormat('D MMM Y') }}</td>
            <td class="fw-bold text-blue">
                {{ substr($res->jam_mulai,0,5) }}&ndash;{{ substr($res->jam_selesai,0,5) }}
            </td>
            <td class="fw-bold">{{ Str::limit($res->keperluan, 40) }}</td>
            <td style="font-size:8px">{{ ucwords(str_replace('_',' ',$res->jenis_kegiatan)) }}</td>
            <td>{{ $res->pemohon->name ?? '-' }}</td>
            <td class="text-center"><span class="badge badge-blue">{{ $res->ruangKelas->kode_ruang ?? '-' }}</span></td>
            <td class="text-center">{{ $res->jumlah_peserta }}</td>
            <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $labelStatus }}</span></td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center" style="padding:20px;color:#9CA3AF">
                Tidak ada data reservasi untuk periode ini
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">{{ config('app.name') }} &mdash; Laporan Reservasi</div>
    <div class="footer-right">Dokumen ini digenerate secara otomatis oleh sistem</div>
</div>

@endsection
