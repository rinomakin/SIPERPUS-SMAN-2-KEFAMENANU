@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Buku Tamu')
@section('report-title', 'LAPORAN BUKU TAMU PERPUSTAKAAN')

@section('stats')
@php
    $totalKunjungan = $bukuTamu->count();
    $kunjunganAnggota = $bukuTamu->whereNotNull('anggota_id')->count();
    $kunjunganTamu = $bukuTamu->whereNull('anggota_id')->count();
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
                <div class="stat-box"><div class="stat-label">Tamu Umum</div><div class="stat-value">{{ $kunjunganTamu }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($bukuTamu->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Tanggal</th>
            <th>Waktu Datang</th>
            <th>Waktu Pulang</th>
            <th>Nama</th>
            <th>Tipe</th>
            <th>Kelas/Instansi</th>
            <th>Keperluan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bukuTamu as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->waktu_datang ? $item->waktu_datang->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->waktu_datang ? $item->waktu_datang->format('H:i') : '-' }}</td>
            <td>{{ $item->waktu_pulang ? $item->waktu_pulang->format('H:i') : '-' }}</td>
            <td>
                <strong>{{ $item->nama_tamu }}</strong><br>
                @if($item->anggota)
                    <span style="font-size:9px; color:#666;">{{ $item->anggota->nomor_anggota }}</span>
                @elseif($item->no_telepon)
                    <span style="font-size:9px; color:#666;">{{ $item->no_telepon }}</span>
                @endif
            </td>
            <td>
                @if($item->anggota_id)
                    <span class="badge badge-blue">Anggota</span>
                @else
                    <span class="badge badge-purple">Tamu Umum</span>
                @endif
            </td>
            <td>
                @if($item->anggota && $item->anggota->kelas)
                    {{ $item->anggota->kelas->nama_kelas }} - {{ $item->anggota->kelas->jurusan->nama_jurusan ?? '' }}
                @else
                    {{ $item->instansi ?: '-' }}
                @endif
            </td>
            <td>{{ $item->keperluan ?: '-' }}</td>
            <td>
                @if($item->waktu_pulang)
                    <span class="badge badge-green">Sudah Pulang</span>
                @else
                    <span class="badge badge-yellow">Berkunjung</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data buku tamu yang sesuai dengan filter.</div>
@endif
@endsection
