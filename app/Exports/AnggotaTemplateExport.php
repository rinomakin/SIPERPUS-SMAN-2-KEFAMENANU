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
use App\Models\Kelas;

class AnggotaTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        $kelas = Kelas::with('jurusan')->get();

        return [
            [
                'John Doe',
                'Laki-laki',
                '1234567890123456',
                'Jl. Contoh No. 123, Kota Contoh',
                '081234567890',
                'john.doe@example.com',
                $kelas->first() ? $kelas->first()->id : '1',
                'siswa',
                'aktif',
                '2024-01-01'
            ],
            [
                'Jane Smith',
                'Perempuan',
                '9876543210987654',
                'Jl. Sample No. 456, Kota Sample',
                '089876543210',
                'jane.smith@example.com',
                $kelas->count() > 1 ? $kelas->get(1)->id : ($kelas->first() ? $kelas->first()->id : '1'),
                'siswa',
                'aktif',
                '2024-01-15'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nama_lengkap',
            'jenis_kelamin',
            'nisn_nik',
            'alamat',
            'nomor_telepon',
            'email',
            'kelas',
            'jenis_anggota',
            'status',
            'tanggal_bergabung'
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
                $kelas = Kelas::with('jurusan')->get();
                $listKelas = $kelas->map(fn($k) => "{$k->id} - {$k->nama_kelas} ({$k->jurusan->nama_jurusan})")->join(',');
                $maxRow = 100;
                $this->applyDropdownList($mainSheet, 'G', 2, $maxRow, $listKelas);
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
            $validation->setPromptTitle('Pilih kelas');
            $validation->setPrompt('Pilih kelas dari daftar yang sudah disediakan');
        }
    }
} 