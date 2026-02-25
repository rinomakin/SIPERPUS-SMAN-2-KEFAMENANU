@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Peminjaman')
@section('report-title', 'LAPORAN DATA PEMINJAMAN BUKU')

@section('stats')
@php
    $totalPeminjaman = $peminjaman->count();
    $sedangDipinjam = $peminjaman->where('status', 'dipinjam')->count();
    $sudahDikembalikan = $peminjaman->where('status', 'dikembalikan')->count();
    $terlambat = $peminjaman->where('status', 'terlambat')->count();
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Peminjaman</div><div class="stat-value">{{ $totalPeminjaman }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Sedang Dipinjam</div><div class="stat-value">{{ $sedangDipinjam }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Dikembalikan</div><div class="stat-value">{{ $sudahDikembalikan }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Terlambat</div><div class="stat-value" style="color:#dc2626;">{{ $terlambat }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($peminjaman->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>No. Peminjaman</th>
            <th>Anggota</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th style="width:40px;">Buku</th>
            <th>Status</th>
            <th>Petugas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($peminjaman as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->nomor_peminjaman }}</td>
            <td>
                <strong>{{ $item->anggota->nama_lengkap }}</strong><br>
                <span style="font-size:9px; color:#666;">{{ $item->anggota->nomor_anggota }}
                @if($item->anggota->kelas) - {{ $item->anggota->kelas->nama_kelas }}@endif</span>
            </td>
            <td>{{ $item->tanggal_peminjaman ? $item->tanggal_peminjaman->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y') : '-' }}</td>
            <td class="text-center">{{ $item->detailPeminjaman->count() }}</td>
            <td>
                @if($item->status == 'dipinjam')
                    <span class="badge badge-yellow">Dipinjam</span>
                @elseif($item->status == 'dikembalikan')
                    <span class="badge badge-green">Dikembalikan</span>
                @elseif($item->status == 'terlambat')
                    <span class="badge badge-red">Terlambat</span>
                @else
                    <span class="badge badge-gray">{{ ucfirst($item->status) }}</span>
                @endif
            </td>
            <td>{{ $item->user->name ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data peminjaman yang sesuai dengan filter.</div>
@endif
@endsection
