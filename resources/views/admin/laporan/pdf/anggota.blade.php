@extends('admin.laporan.pdf.layout')

@section('title', 'Laporan Anggota')
@section('report-title', 'LAPORAN DATA ANGGOTA PERPUSTAKAAN')

@section('content')
@if($anggota->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th>Nama Lengkap</th>
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
