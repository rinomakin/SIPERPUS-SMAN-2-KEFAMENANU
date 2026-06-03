<?php

namespace App\Imports;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\DB;

class AnggotaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts
{
    private $errors = [];
    private $imported = 0;
    private $errorTracker = []; // Track unique errors to prevent duplicates

    public function model(array $row)
    {
        try {
            // Debug: log the row data
            \Log::info('Import row data:', $row);
            
            // Check if this is a header row or empty row
            if (empty($row['nama_lengkap'])) {
                \Log::info('Skipping empty or header row');
                return null;
            }
            
            // Convert data types and clean up
            $namaLengkap = trim((string)($row['nama_lengkap'] ?? ''));
            $jenisKelamin = trim((string)($row['jenis_kelamin'] ?? ''));
            $alamat = trim((string)($row['alamat'] ?? ''));
            $nomorTelepon = trim((string)($row['nomor_telepon'] ?? ''));
            $email = trim((string)($row['email'] ?? ''));
            $kelasId = !empty($row['kelas']) ? (int)$row['kelas'] : (!empty($row['kelas_id']) ? (int)$row['kelas_id'] : null);
            $jabatan = trim((string)($row['jabatan'] ?? ''));
            $jenisAnggota = trim((string)($row['jenis_anggota'] ?? ''));
            $status = trim((string)($row['status'] ?? ''));
            $tanggalBergabung = now()->format('Y-m-d');
            
            // Manual validation
            if (empty($namaLengkap)) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Nama lengkap wajib diisi");
                return null;
            }

            // Validate email if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Format email tidak valid");
                return null;
            }

            // Validate jenis kelamin
            if (!empty($jenisKelamin) && !in_array($jenisKelamin, ['Laki-laki', 'Perempuan'])) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Jenis kelamin harus 'Laki-laki' atau 'Perempuan'");
                return null;
            }

            // Validate kelas_id if provided
            if (!empty($kelasId) && !\App\Models\Kelas::find($kelasId)) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": ID Kelas tidak valid");
                return null;
            }

            // Validate jenis_anggota if provided
            if (!empty($jenisAnggota) && !in_array($jenisAnggota, ['siswa', 'guru', 'staff'])) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Jenis anggota harus salah satu dari: siswa, guru, staff");
                return null;
            }

            // Validate status if provided
            if (!empty($status) && !in_array($status, ['aktif', 'nonaktif', 'ditangguhkan'])) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Status harus salah satu dari: aktif, nonaktif, ditangguhkan");
                return null;
            }

            // Generate unique nomor anggota dan barcode using the robust method
            $maxRetries = 5;
            $retryCount = 0;
            $generatedData = null;
            
            while ($retryCount < $maxRetries) {
                try {
                    $generatedData = Anggota::generateUniqueCodes();
                    break;
                } catch (\Exception $e) {
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        $this->addUniqueError("Baris " . ($this->imported + 1) . ": Gagal generate kode unik setelah {$maxRetries} percobaan");
                        return null;
                    }
                    // Wait a bit before retrying
                    usleep(100000); // 0.1 second
                }
            }

            if (!$generatedData) {
                $this->addUniqueError("Baris " . ($this->imported + 1) . ": Gagal generate kode unik");
                return null;
            }

            $this->imported = $this->imported + 1;

            return new Anggota([
                'nomor_anggota' => $generatedData['nomor_anggota'],
                'barcode_anggota' => $generatedData['barcode_anggota'],
                'nama_lengkap' => $namaLengkap,
                'jenis_kelamin' => !empty($jenisKelamin) ? $jenisKelamin : null,
                'alamat' => $alamat,
                'nomor_telepon' => $nomorTelepon,
                'email' => !empty($email) ? $email : null,
                'kelas_id' => $kelasId,
                'jabatan' => !empty($jabatan) ? $jabatan : null,
                'jenis_anggota' => !empty($jenisAnggota) ? $jenisAnggota : 'siswa',
                'status' => !empty($status) ? $status : 'aktif',
                'tanggal_bergabung' => $tanggalBergabung,
            ]);
        } catch (\Exception $e) {
            $this->addUniqueError("Error pada baris " . ($this->imported + 1) . ": " . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'nullable',
            'jenis_kelamin' => 'nullable',
            'alamat' => 'nullable',
            'nomor_telepon' => 'nullable',
            'email' => 'nullable',
            'kelas' => 'nullable',
            'jenis_anggota' => 'nullable',
            'status' => 'nullable',

        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'kelas.exists' => 'ID Kelas tidak valid',
            'jenis_anggota.in' => 'Jenis anggota harus salah satu dari: siswa, guru, staff',
            'status.in' => 'Status harus salah satu dari: aktif, nonaktif, ditangguhkan',

        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function batchSize(): int
    {
        return 50; // Reduced batch size for better performance and less locking conflicts
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImportedCount()
    {
        return $this->imported;
    }

    private function parseDate($value)
    {
        if (empty($value)) return now();

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)->format('Y-m-d');
            } catch (\Exception $e) {
                return now();
            }
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now();
        }
    }

    private function addUniqueError($errorMessage)
    {
        if (!in_array($errorMessage, $this->errorTracker)) {
            $this->errors[] = $errorMessage;
            $this->errorTracker[] = $errorMessage;
        }
    }
} 