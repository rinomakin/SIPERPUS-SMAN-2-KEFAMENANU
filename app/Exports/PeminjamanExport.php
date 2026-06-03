<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeminjamanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $peminjaman;

    public function __construct($peminjaman)
    {
        $this->peminjaman = $peminjaman;
    }

    public function collection()
    {
        return $this->peminjaman;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Peminjaman',
            'Nama Anggota',
            'NIS/NIK',
            'Kelas/Jurusan',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Jumlah Buku',
            'Daftar Buku',
            'Status',
            'Petugas',
            'Keterangan'
        ];
    }

    public function map($peminjaman): array
    {
        static $no = 1;
        
        $daftarBuku = $peminjaman->detailPeminjaman->map(function($detail) {
            return $detail->buku->judul_buku . ' (' . $detail->buku->isbn . ')';
        })->implode(', ');

        $kelasJurusan = $peminjaman->anggota->kelas 
            ? $peminjaman->anggota->kelas->nama_kelas . ' - ' . $peminjaman->anggota->kelas->jurusan->nama_jurusan
            : '-';

        return [
            $no++,
            $peminjaman->nomor_peminjaman,
            $peminjaman->anggota->nama_lengkap,
            $peminjaman->anggota->nis ?: $peminjaman->anggota->nomor_anggota,
            $kelasJurusan,
            $peminjaman->tanggal_pinjam ? $peminjaman->tanggal_pinjam->format('d/m/Y') : '-',
            $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->format('d/m/Y') : '-',
            $peminjaman->detailPeminjaman->count(),
            $daftarBuku,
            ucfirst($peminjaman->status),
            $peminjaman->user->name ?? '-',
            $peminjaman->keterangan ?: '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ]
        ];
    }
}