@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Pengembalian')
@section('report-title', 'LAPORAN DATA PENGEMBALIAN BUKU')

@section('stats')
@php
    $totalPengembalian = $pengembalian->count();
    $tepatWaktu = $pengembalian->where('jumlah_hari_terlambat', '<=', 0)->count();
    $terlambat = $pengembalian->where('jumlah_hari_terlambat', '>', 0)->count();
    $totalDenda = $pengembalian->sum('total_denda');
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total</div><div class="stat-value">{{ $totalPengembalian }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Tepat Waktu</div><div class="stat-value" style="color:#059669;">{{ $tepatWaktu }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Terlambat</div><div class="stat-value" style="color:#dc2626;">{{ $terlambat }}</div></div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Denda</div><div class="stat-value" style="font-size:11px;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($pengembalian->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>No. Pengembalian</th>
            <th>Anggota</th>
            <th>Tgl Kembali</th>
            <th style="width:40px;">Buku</th>
            <th>Status</th>
            <th class="text-right">Denda</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pengembalian as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->nomor_pengembalian }}</td>
            <td>
                <strong>{{ $item->anggota->nama_lengkap }}</strong><br>
                <span style="font-size:9px; color:#666;">{{ $item->anggota->nomor_anggota }}</span>
            </td>
            <td>{{ $item->tanggal_pengembalian ? $item->tanggal_pengembalian->format('d/m/Y') : '-' }}</td>
            <td class="text-center">{{ $item->detailPengembalian->count() }}</td>
            <td>
                @if($item->jumlah_hari_terlambat > 0)
                    <span class="badge badge-red">Terlambat {{ $item->jumlah_hari_terlambat }} hari</span>
                @else
                    <span class="badge badge-green">Tepat Waktu</span>
                @endif
            </td>
            <td class="text-right {{ $item->total_denda > 0 ? 'text-red font-bold' : 'text-green' }}">
                Rp {{ number_format($item->total_denda, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-right">Total Denda:</td>
            <td class="text-right text-red font-bold">Rp {{ number_format($pengembalian->sum('total_denda'), 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@else
<div class="empty-state">Tidak ada data pengembalian yang sesuai dengan filter.</div>
@endif
@endsection
