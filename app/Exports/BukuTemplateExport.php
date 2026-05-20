<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\KategoriBuku;
use App\Models\JenisBuku;
use App\Models\SumberBuku;

class BukuTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        $kategoris = KategoriBuku::all();
        $jenis = JenisBuku::all();
        $sumber = SumberBuku::all();
        
        $sampleData = [
            [
                'Pemrograman Web dengan Laravel',
                '978-602-123456-7-8',
                'John Doe', // Nama Penulis (string)
                'Penerbit Teknologi', // Nama Penerbit (string)
                $kategoris->first() ? $kategoris->first()->id : '1', // ID Kategori
                $jenis->first() ? $jenis->first()->id : '1', // ID Jenis
                $sumber->first() ? $sumber->first()->id : '1', // ID Sumber
                '2024', // Tahun Terbit
                '300', // Jumlah Halaman
                'Indonesia', // Bahasa
                '5', // Jumlah Stok
                'Rak A-1', // Lokasi Rak
                'tersedia', // Status: tersedia/tidak_tersedia
                'Buku panduan lengkap pemrograman web dengan framework Laravel'
            ],
            [
                'Matematika Dasar untuk SMA',
                '978-602-987654-3-2',
                'Jane Smith',
                'Penerbit Pendidikan',
                $kategoris->count() > 1 ? $kategoris->get(1)->id : ($kategoris->first() ? $kategoris->first()->id : '1'),
                $jenis->count() > 1 ? $jenis->get(1)->id : ($jenis->first() ? $jenis->first()->id : '1'),
                $sumber->count() > 1 ? $sumber->get(1)->id : ($sumber->first() ? $sumber->first()->id : '1'),
                '2023',
                '250',
                'Indonesia',
                '3',
                'Rak B-2',
                'tersedia',
                'Buku matematika dasar untuk siswa SMA kelas X'
            ]
        ];

        // Tambahkan data referensi untuk setiap master data
        $referenceData = [];
        
        // Data Kategori
        $kategoriData = [];
        if ($kategoris->count() > 0) {
            foreach ($kategoris as $kat) {
                $kategoriData[] = [
                    '',
                    '',
                    '',
                    '',
                    'ID: ' . $kat->id . ' - ' . $kat->nama_kategori,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
        }

        // Data Jenis
        $jenisData = [];
        if ($jenis->count() > 0) {
            foreach ($jenis as $jen) {
                $jenisData[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    'ID: ' . $jen->id . ' - ' . $jen->nama_jenis,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
        }

        // Data Sumber
        $sumberData = [];
        if ($sumber->count() > 0) {
            foreach ($sumber as $sum) {
                $sumberData[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'ID: ' . $sum->id . ' - ' . $sum->nama_sumber,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }
        }

        return array_merge(
            $sampleData, 
            [[''], [''], ['DAFTAR KATEGORI UNTUK REFERENSI:'], ['']], 
            $kategoriData,
            [[''], [''], ['DAFTAR JENIS UNTUK REFERENSI:'], ['']], 
            $jenisData,
            [[''], [''], ['DAFTAR SUMBER UNTUK REFERENSI:'], ['']], 
            $sumberData
        );
    }

    public function headings(): array
    {
        return [
            'judul_buku',
            'isbn',
            'penulis',
            'penerbit',
            'kategori_id',
            'jenis_id',
            'sumber_id',
            'tahun_terbit',
            'jumlah_halaman',
            'bahasa',
            'jumlah_stok',
            'lokasi_rak',
            'status',
            'deskripsi'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ]
            ],
        ];
    }
}