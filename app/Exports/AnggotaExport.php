<?php

namespace App\Exports;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class AnggotaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Anggota::with(['kelas.jurusan']);

        if ($this->request) {
            // Apply search filter
            if ($this->request->filled('search')) {
                $search = $this->request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nomor_anggota', 'like', "%{$search}%")
                      ->orWhere('barcode_anggota', 'like', "%{$search}%")

                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply kelas filter
            if ($this->request->filled('kelas_id')) {
                $query->where('kelas_id', $this->request->kelas_id);
            }

            // Apply jurusan filter
            if ($this->request->filled('jurusan_id')) {
                $query->whereHas('kelas', function($q) {
                    $q->where('jurusan_id', $this->request->jurusan_id);
                });
            }

            // Apply jenis anggota filter
            if ($this->request->filled('jenis_anggota')) {
                $query->where('jenis_anggota', $this->request->jenis_anggota);
            }

            // Apply status filter
            if ($this->request->filled('status')) {
                $query->where('status', $this->request->status);
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Anggota',
            'Barcode',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Alamat',
            'Nomor Telepon',
            'Email',
            'Kelas',
            'Jurusan',
            'Jabatan',
            'Jenis Anggota',
            'Status',
            'Tanggal Bergabung',
            'Tanggal Dibuat'
        ];
    }

    public function map($anggota): array
    {
        static $no = 1;
        
        return [
            $no++,
            $anggota->nomor_anggota,
            $anggota->barcode_anggota,
            $anggota->nama_lengkap,
            $anggota->jenis_kelamin ?? '-',
            $anggota->alamat,
            $anggota->nomor_telepon,
            $anggota->email ?? '-',
            $anggota->kelas ? $anggota->kelas->nama_kelas : '-',
            $anggota->kelas && $anggota->kelas->jurusan ? $anggota->kelas->jurusan->nama_jurusan : '-',
            $anggota->jabatan ?? '-',
            ucfirst($anggota->jenis_anggota),
            ucfirst($anggota->status),
            $anggota->tanggal_bergabung->format('d/m/Y'),
            $anggota->created_at->format('d/m/Y H:i:s')
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
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ],
        ];
    }
} 