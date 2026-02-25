@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Denda')
@section('report-title', 'LAPORAN DATA DENDA PERPUSTAKAAN')

@section('stats')
@php
    $totalDenda = $denda->sum('jumlah_denda');
    $dendaSudahBayar = $denda->where('status_pembayaran', 'sudah_dibayar')->sum('jumlah_denda');
    $dendaBelumBayar = $denda->where('status_pembayaran', 'belum_dibayar')->sum('jumlah_denda');
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Denda</div><div class="stat-value" style="font-size:11px;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div></div>
            </td>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Sudah Bayar</div><div class="stat-value" style="font-size:11px; color:#059669;">Rp {{ number_format($dendaSudahBayar, 0, ',', '.') }}</div></div>
            </td>
            <td style="width:33.33%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Belum Bayar</div><div class="stat-value" style="font-size:11px; color:#dc2626;">Rp {{ number_format($dendaBelumBayar, 0, ',', '.') }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($denda->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Anggota</th>
            <th>No. Peminjaman</th>
            <th>Hari Terlambat</th>
            <th class="text-right">Total Denda</th>
            <th>Status</th>
            <th>Tgl Bayar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($denda as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $item->peminjaman->anggota->nama_lengkap }}</strong><br>
                <span style="font-size:9px; color:#666;">{{ $item->peminjaman->anggota->nomor_anggota }}</span>
            </td>
            <td>{{ $item->peminjaman->nomor_peminjaman }}</td>
            <td class="text-center">{{ $item->jumlah_hari_terlambat }} hari</td>
            <td class="text-right text-red font-bold">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</td>
            <td>
                @if($item->status_pembayaran == 'sudah_dibayar')
                    <span class="badge badge-green">Sudah Bayar</span>
                @else
                    <span class="badge badge-red">Belum Bayar</span>
                @endif
            </td>
            <td>{{ $item->tanggal_pembayaran ? $item->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right">Total:</td>
            <td class="text-right text-red font-bold">Rp {{ number_format($denda->sum('jumlah_denda'), 0, ',', '.') }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@else
<div class="empty-state">Tidak ada data denda yang sesuai dengan filter.</div>
@endif
@endsection
