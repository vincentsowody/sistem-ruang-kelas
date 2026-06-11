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
    <h2>Laporan Jadwal Perkuliahan</h2>
    <p class="subtitle">
        Tahun Akademik {{ $tahun }} &mdash; Semester {{ ucfirst($semester) }}
    </p>
</div>

{{-- Ringkasan --}}
@php
    $perHari = $jadwalList->groupBy('hari');
    $hariOrder = ['senin','selasa','rabu','kamis','jumat','sabtu'];
@endphp
<div class="summary-box">
    <div class="summary-title">Ringkasan</div>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="num">{{ $jadwalList->count() }}</div>
            <div class="lbl">Total Jadwal</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ $jadwalList->pluck('ruang_kelas_id')->unique()->count() }}</div>
            <div class="lbl">Ruang Dipakai</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ $jadwalList->pluck('dosen_id')->unique()->count() }}</div>
            <div class="lbl">Dosen Mengajar</div>
        </div>
        <div class="summary-item">
            <div class="num">{{ $jadwalList->pluck('mata_kuliah')->unique()->count() }}</div>
            <div class="lbl">Mata Kuliah</div>
        </div>
    </div>
</div>

{{-- Tabel Jadwal --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:25px">No</th>
            <th style="width:55px">Hari</th>
            <th style="width:70px">Jam</th>
            <th>Mata Kuliah</th>
            <th style="width:45px">Kode MK</th>
            <th>Dosen</th>
            <th style="width:35px">Kelas</th>
            <th style="width:70px">Prodi</th>
            <th style="width:25px">Smt</th>
            <th style="width:25px">SKS</th>
            <th style="width:45px">Ruang</th>
        </tr>
    </thead>
    <tbody>
        @forelse($jadwalList as $i => $jadwal)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="fw-bold" style="text-transform:capitalize">{{ $jadwal->hari }}</td>
            <td class="fw-bold text-blue">
                {{ substr($jadwal->jam_mulai, 0, 5) }}&ndash;{{ substr($jadwal->jam_selesai, 0, 5) }}
            </td>
            <td class="fw-bold">{{ $jadwal->mata_kuliah }}</td>
            <td style="font-size:8px;font-family:monospace">{{ $jadwal->kode_mk ?? '-' }}</td>
            <td>{{ $jadwal->dosen->name ?? '-' }}</td>
            <td class="text-center">{{ $jadwal->kelas }}</td>
            <td style="font-size:8px">{{ Str::limit($jadwal->program_studi, 20) }}</td>
            <td class="text-center">{{ $jadwal->semester }}</td>
            <td class="text-center">{{ $jadwal->sks }}</td>
            <td class="text-center"><span class="badge badge-blue">{{ $jadwal->ruangKelas->kode_ruang ?? '-' }}</span></td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="text-center" style="padding:20px;color:#9CA3AF">
                Tidak ada data jadwal untuk tahun akademik dan semester yang dipilih
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">{{ config('app.name') }} &mdash; Laporan Jadwal TA {{ $tahun }} Semester {{ ucfirst($semester) }}</div>
    <div class="footer-right">Dokumen ini digenerate secara otomatis oleh sistem</div>
</div>

@endsection
