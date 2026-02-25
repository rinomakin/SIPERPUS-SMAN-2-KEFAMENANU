@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Buku')
@section('report-title', 'LAPORAN DATA KOLEKSI BUKU PERPUSTAKAAN')

@section('stats')
@php
    $totalBuku = $buku->count();
    $totalStok = $buku->sum('stok');
    $tersedia = $buku->where('stok', '>', 0)->count();
    $habis = $buku->where('stok', 0)->count();
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Judul Buku</div><div class="stat-value">{{ $totalBuku }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Eksemplar</div><div class="stat-value">{{ $totalStok }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Tersedia</div><div class="stat-value">{{ $tersedia }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Habis</div><div class="stat-value">{{ $habis }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($buku->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Judul Buku</th>
            <th>ISBN</th>
            <th>Pengarang</th>
            <th>Kategori</th>
            <th>Jenis</th>
            <th style="width:40px;">Stok</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($buku as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $item->judul_buku }}</strong><br>
                <span style="font-size:9px; color:#666;">{{ $item->penerbit }} ({{ $item->tahun_terbit }})</span>
            </td>
            <td>{{ $item->isbn }}</td>
            <td>{{ $item->pengarang }}</td>
            <td>{{ $item->kategoriBuku->nama_kategori ?? '-' }}</td>
            <td>{{ $item->jenisBuku->nama_jenis ?? '-' }}</td>
            <td class="text-center font-bold">{{ $item->stok }}</td>
            <td>
                @if($item->stok > 0)
                    <span class="badge badge-green">Tersedia</span>
                @else
                    <span class="badge badge-red">Habis</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data buku yang sesuai dengan filter.</div>
@endif
@endsection
