@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Absensi')
@section('report-title', 'LAPORAN ABSENSI PENGUNJUNG PERPUSTAKAAN')

@section('stats')
@php
    $totalKunjungan = $absensi->count();
    $kunjunganAnggota = $absensi->where('jenis_pengunjung', 'anggota')->count();
    $kunjunganTamu = $absensi->where('jenis_pengunjung', 'tamu')->count();
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Kunjungan</div><div class="stat-value">{{ $totalKunjungan }}</div></div>
            </td>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Anggota</div><div class="stat-value">{{ $kunjunganAnggota }}</div></div>
            </td>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Tamu</div><div class="stat-value">{{ $kunjunganTamu }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($absensi->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Instansi/Kelas</th>
            <th>Keperluan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($absensi as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->jam_masuk ?: '-' }}</td>
            <td>{{ $item->jam_keluar ?: '-' }}</td>
            <td>
                @if($item->jenis_pengunjung === 'anggota' && $item->anggota)
                    <strong>{{ $item->anggota->nama_lengkap }}</strong><br>
                    <span style="font-size:9px; color:#666;">{{ $item->anggota->nomor_anggota }}</span>
                @else
                    <strong>{{ $item->nama_tamu }}</strong><br>
                    <span style="font-size:9px; color:#666;">{{ $item->no_telepon }}</span>
                @endif
            </td>
            <td>
                @if($item->jenis_pengunjung === 'anggota')
                    <span class="badge badge-blue">Anggota</span>
                @else
                    <span class="badge badge-purple">Tamu</span>
                @endif
            </td>
            <td>
                @if($item->jenis_pengunjung === 'anggota' && $item->anggota && $item->anggota->kelas)
                    {{ $item->anggota->kelas->nama_kelas }} - {{ $item->anggota->kelas->jurusan->nama_jurusan ?? '' }}
                @else
                    {{ $item->instansi ?: '-' }}
                @endif
            </td>
            <td>{{ $item->keperluan ?: '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data absensi yang sesuai dengan filter.</div>
@endif
@endsection
