<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengembalianExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $pengembalian;

    public function __construct($pengembalian)
    {
        $this->pengembalian = $pengembalian;
    }

    public function collection()
    {
        return $this->pengembalian;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Pengembalian',
            'Nomor Peminjaman',
            'Nama Anggota',
            'NIS/NIK',
            'Kelas/Jurusan',
            'Tanggal Pengembalian',
            'Jam Pengembalian',
            'Jumlah Buku',
            'Daftar Buku',
            'Hari Terlambat',
            'Total Denda',
            'Status',
            'Petugas',
            'Keterangan'
        ];
    }

    public function map($pengembalian): array
    {
        static $no = 1;
        
        $daftarBuku = $pengembalian->detailPengembalian->map(function($detail) {
            return $detail->buku->judul_buku . ' (' . $detail->buku->isbn . ')';
        })->implode(', ');

        $kelasJurusan = $pengembalian->anggota->kelas 
            ? $pengembalian->anggota->kelas->nama_kelas . ' - ' . $pengembalian->anggota->kelas->jurusan->nama_jurusan
            : '-';

        $status = $pengembalian->jumlah_hari_terlambat > 0 
            ? 'Terlambat (' . $pengembalian->jumlah_hari_terlambat . ' hari)'
            : 'Tepat Waktu';

        return [
            $no++,
            $pengembalian->nomor_pengembalian,
            $pengembalian->peminjaman->nomor_peminjaman ?? '-',
            $pengembalian->anggota->nama_lengkap,
            $pengembalian->anggota->nis ?: $pengembalian->anggota->nik,
            $kelasJurusan,
            $pengembalian->tanggal_pengembalian ? $pengembalian->tanggal_pengembalian->format('d/m/Y') : '-',
            $pengembalian->jam_pengembalian ?: '-',
            $pengembalian->detailPengembalian->sum('jumlah_dikembalikan'),
            $daftarBuku,
            $pengembalian->jumlah_hari_terlambat,
            'Rp ' . number_format($pengembalian->total_denda, 0, ',', '.'),
            $status,
            $pengembalian->user->name ?? '-',
            $pengembalian->keterangan ?: '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ]
            ]
        ];
    }
}