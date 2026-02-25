@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Kas')
@section('report-title', 'LAPORAN KAS PERPUSTAKAAN')

@section('stats')
<div class="stats">
    <table>
        <tr>
            <td style="width:50%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Total Pemasukan</div><div class="stat-value" style="color:#059669; font-size:12px;">Rp {{ number_format($kas->sum('jumlah_denda'), 0, ',', '.') }}</div></div>
            </td>
            <td style="width:50%; padding:3px;">
                <div class="stat-box"><div class="stat-label">Jumlah Transaksi</div><div class="stat-value">{{ $kas->count() }}</div></div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($kas->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Tanggal</th>
            <th>Anggota</th>
            <th>Sumber</th>
            <th>Keterangan</th>
            <th class="text-right">Jumlah</th>
            <th>Petugas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kas as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->tanggal_pembayaran ? $item->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
            <td>
                <strong>{{ $item->peminjaman->anggota->nama_lengkap }}</strong><br>
                <span style="font-size:9px; color:#666;">{{ $item->peminjaman->anggota->nomor_anggota }}</span>
            </td>
            <td><span class="badge badge-yellow">Denda Keterlambatan</span></td>
            <td>
                Pembayaran denda {{ $item->jumlah_hari_terlambat }} hari terlambat<br>
                <span style="font-size:9px; color:#666;">{{ $item->peminjaman->nomor_peminjaman }}</span>
            </td>
            <td class="text-right text-green font-bold">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</td>
            <td>-</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">Total Pemasukan:</td>
            <td class="text-right text-green font-bold">Rp {{ number_format($kas->sum('jumlah_denda'), 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@else
<div class="empty-state">Tidak ada transaksi kas yang sesuai dengan filter.</div>
@endif
@endsection
