<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DendaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $denda;

    public function __construct($denda)
    {
        $this->denda = $denda;
    }

    public function collection()
    {
        return $this->denda;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Peminjaman',
            'Nama Anggota',
            'NIS/NIK',
            'Kelas/Jurusan',
            'Daftar Buku',
            'Hari Terlambat',
            'Denda per Hari',
            'Total Denda',
            'Status Pembayaran',
            'Tanggal Bayar',
            'Petugas Pembayaran',
            'Keterangan'
        ];
    }

    public function map($denda): array
    {
        static $no = 1;
        
        $daftarBuku = $denda->peminjaman->detailPeminjaman->map(function($detail) {
            return $detail->buku->judul_buku . ' (' . $detail->buku->isbn . ')';
        })->implode(', ');

        $kelasJurusan = $denda->peminjaman->anggota->kelas 
            ? $denda->peminjaman->anggota->kelas->nama_kelas . ' - ' . $denda->peminjaman->anggota->kelas->jurusan->nama_jurusan
            : '-';

        return [
            $no++,
            $denda->peminjaman->nomor_peminjaman,
            $denda->peminjaman->anggota->nama_lengkap,
            $denda->peminjaman->anggota->nis ?: $denda->peminjaman->anggota->nomor_anggota,
            $kelasJurusan,
            $daftarBuku,
            $denda->jumlah_hari_terlambat,
            'Rp ' . number_format($denda->denda_per_hari, 0, ',', '.'),
            'Rp ' . number_format($denda->total_denda, 0, ',', '.'),
            $denda->status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar',
            $denda->tanggal_bayar ? $denda->tanggal_bayar->format('d/m/Y') : '-',
            $denda->user->name ?? '-',
            $denda->keterangan ?: '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF3E0']
                ]
            ]
        ];
    }
}