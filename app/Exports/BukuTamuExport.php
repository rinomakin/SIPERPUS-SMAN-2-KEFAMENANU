<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BukuTamuExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $bukuTamu;

    public function __construct($bukuTamu)
    {
        $this->bukuTamu = $bukuTamu;
    }

    public function collection()
    {
        return $this->bukuTamu;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Waktu Datang',
            'Waktu Pulang',
            'Nama',
            'Tipe',
            'Kelas/Instansi',
            'Keperluan',
            'No. Telepon',
            'Status',
            'Petugas',
        ];
    }

    public function map($item): array
    {
        static $no = 1;

        $isAnggota = !is_null($item->anggota_id);
        $nama = $item->nama_tamu;
        $tipe = $isAnggota ? 'Anggota' : 'Tamu Umum';
        $kelasInstansi = $isAnggota && $item->anggota && $item->anggota->kelas
            ? $item->anggota->kelas->nama_kelas . ' - ' . ($item->anggota->kelas->jurusan->nama_jurusan ?? '')
            : ($item->instansi ?: '-');

        return [
            $no++,
            $item->waktu_datang ? $item->waktu_datang->format('d/m/Y') : '-',
            $item->waktu_datang ? $item->waktu_datang->format('H:i') : '-',
            $item->waktu_pulang ? $item->waktu_pulang->format('H:i') : '-',
            $nama,
            $tipe,
            $kelasInstansi,
            $item->keperluan ?: '-',
            $item->no_telepon ?: '-',
            $item->waktu_pulang ? 'Sudah Pulang' : 'Sedang Berkunjung',
            $item->petugas->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8EAF6']
                ]
            ]
        ];
    }
}
