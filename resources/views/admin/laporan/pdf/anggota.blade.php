@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Anggota')
@section('report-title', 'LAPORAN DATA ANGGOTA PERPUSTAKAAN')

@section('stats')
@php
    $totalAnggota = $anggota->count();
    $siswa = $anggota->where('jenis_anggota', 'siswa')->count();
    $guru = $anggota->where('jenis_anggota', 'guru')->count();
    $aktif = $anggota->where('status', 'aktif')->count();
@endphp
<div class="stats">
    <table>
        <tr>
            <td style="width:25%; padding:3px;">
                <div class="stat-box">
                    <div class="stat-label">Total Anggota</div>
                    <div class="stat-value">{{ $totalAnggota }}</div>
                </div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box">
                    <div class="stat-label">Siswa</div>
                    <div class="stat-value">{{ $siswa }}</div>
                </div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box">
                    <div class="stat-label">Guru</div>
                    <div class="stat-value">{{ $guru }}</div>
                </div>
            </td>
            <td style="width:25%; padding:3px;">
                <div class="stat-box">
                    <div class="stat-label">Aktif</div>
                    <div class="stat-value">{{ $aktif }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('content')
@if($anggota->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Nama Lengkap</th>
            <th>NIS/NIK</th>
            <th>No. Anggota</th>
            <th style="width:30px;">L/P</th>
            <th>Kelas/Jurusan</th>
            <th>Jenis</th>
            <th>Status</th>
            <th>Tgl Daftar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($anggota as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="font-bold">{{ $item->nama_lengkap }}</td>
            <td>{{ $item->nis ?: $item->nomor_anggota }}</td>
            <td>{{ $item->nomor_anggota }}</td>
            <td class="text-center">{{ $item->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
            <td>
                @if($item->kelas)
                    {{ $item->kelas->nama_kelas }} - {{ $item->kelas->jurusan->nama_jurusan ?? '' }}
                @else
                    -
                @endif
            </td>
            <td>
                <span class="badge {{ $item->jenis_anggota == 'siswa' ? 'badge-blue' : ($item->jenis_anggota == 'guru' ? 'badge-green' : 'badge-purple') }}">
                    {{ ucfirst($item->jenis_anggota) }}
                </span>
            </td>
            <td>
                <span class="badge {{ $item->status == 'aktif' ? 'badge-green' : ($item->status == 'nonaktif' ? 'badge-red' : 'badge-yellow') }}">
                    {{ ucfirst($item->status) }}
                </span>
            </td>
            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data anggota yang sesuai dengan filter.</div>
@endif
@endsection
