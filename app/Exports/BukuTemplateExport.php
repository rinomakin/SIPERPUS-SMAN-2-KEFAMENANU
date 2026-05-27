<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\KategoriBuku;
use App\Models\JenisBuku;
use App\Models\SumberBuku;
use App\Models\RakBuku;

class BukuTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        $kategoris = KategoriBuku::all();
        $jenis = JenisBuku::all();
        $sumber = SumberBuku::all();
        $rakBuku = RakBuku::all();

        $sampleData = [
            [
                'Pemrograman Web dengan Laravel',
                '978-602-123456-7-8',
                'John Doe',
                'Penerbit Teknologi',
                $kategoris->first() ? $kategoris->first()->id : '1',
                $jenis->first() ? $jenis->first()->id : '1',
                $sumber->first() ? $sumber->first()->id : '1',
                $rakBuku->first() ? $rakBuku->first()->id : '1',
                '2024',
                '300',
                'Indonesia',
                '5',
                'tersedia',
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
                $rakBuku->count() > 1 ? $rakBuku->get(1)->id : ($rakBuku->first() ? $rakBuku->first()->id : '1'),
                '2023',
                '250',
                'Indonesia',
                '3',
                'tersedia',
                'Buku matematika dasar untuk siswa SMA kelas X'
            ]
        ];

        return $sampleData;
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
            'rak_id',
            'tahun_terbit',
            'jumlah_halaman',
            'bahasa',
            'jumlah_stok',
            'status',
            'deskripsi'
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
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $mainSheet = $event->sheet->getDelegate();

                $kategoris = KategoriBuku::all();
                $jenis = JenisBuku::all();
                $sumber = SumberBuku::all();
                $rakBuku = RakBuku::all();

                $listKategori = $kategoris->map(fn($k) => "{$k->id} - {$k->nama_kategori}")->join(',');
                $listJenis    = $jenis->map(fn($j)    => "{$j->id} - {$j->nama_jenis}")->join(',');
                $listSumber   = $sumber->map(fn($s)   => "{$s->id} - {$s->nama_sumber}")->join(',');
                $listRak      = $rakBuku->map(fn($r)  => "{$r->id} - {$r->nama_rak}")->join(',');

                $maxRow = 100;
                $this->applyDropdownList($mainSheet, 'E', 2, $maxRow, $listKategori);
                $this->applyDropdownList($mainSheet, 'F', 2, $maxRow, $listJenis);
                $this->applyDropdownList($mainSheet, 'G', 2, $maxRow, $listSumber);
                $this->applyDropdownList($mainSheet, 'H', 2, $maxRow, $listRak);
            }
        ];
    }

    private function applyDropdownList($sheet, string $column, int $startRow, int $endRow, string $list): void
    {
        if (empty($list)) return;

        for ($row = $startRow; $row <= $endRow; $row++) {
            $cell = $column . $row;
            $validation = $sheet->getCell($cell)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setFormula1('"' . $list . '"');
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan tidak valid');
            $validation->setError('Silakan pilih dari daftar yang tersedia');
            $validation->setPromptTitle('Pilih data');
            $validation->setPrompt('Pilih dari daftar yang sudah disediakan');
        }
    }
}
