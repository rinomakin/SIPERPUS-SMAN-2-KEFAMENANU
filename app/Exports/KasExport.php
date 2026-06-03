<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $kas;

    public function __construct($kas)
    {
        $this->kas = $kas;
    }

    public function collection()
    {
        return $this->kas;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Sumber Pemasukan',
            'Nomor Transaksi',
            'Nama Anggota',
            'NIS/NIK',
            'Kelas/Jurusan',
            'Keterangan',
            'Jumlah',
            'Petugas'
        ];
    }

    public function map($kas): array
    {
        static $no = 1;
        
        $kelasJurusan = $kas->peminjaman->anggota->kelas 
            ? $kas->peminjaman->anggota->kelas->nama_kelas . ' - ' . $kas->peminjaman->anggota->kelas->jurusan->nama_jurusan
            : '-';

        return [
            $no++,
            $kas->tanggal_bayar ? $kas->tanggal_bayar->format('d/m/Y') : '-',
            'Denda Keterlambatan',
            $kas->peminjaman->nomor_peminjaman,
            $kas->peminjaman->anggota->nama_lengkap,
            $kas->peminjaman->anggota->nis ?: $kas->peminjaman->anggota->nomor_anggota,
            $kelasJurusan,
            'Pembayaran denda keterlambatan ' . $kas->jumlah_hari_terlambat . ' hari',
            'Rp ' . number_format($kas->total_denda, 0, ',', '.'),
            $kas->user->name ?? '-'
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