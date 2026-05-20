<?php

namespace App\Imports;

use App\Models\Buku;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;

class BukuImport implements ToModel, WithHeadingRow, SkipsOnError
{
    private $errors      = [];
    private $imported    = 0;
    private $rowNumber   = 1; // baris data ke-n (tidak termasuk header)
    private $errorTracker = [];

    public function model(array $row)
    {
        $this->rowNumber++;

        try {
            // Lewati baris kosong / baris referensi di bawah data contoh
            if (empty(trim((string)($row['judul_buku'] ?? '')))) {
                return null;
            }

            // ── Bersihkan & konversi nilai ──
            $judulBuku     = trim((string)($row['judul_buku'] ?? ''));
            $isbn          = trim((string)($row['isbn'] ?? ''));
            $penulis       = trim((string)($row['penulis'] ?? ''));
            $penerbit      = trim((string)($row['penerbit'] ?? ''));
            $kategoriId    = !empty($row['kategori_id'])    ? (int)$row['kategori_id']    : null;
            $jenisId       = !empty($row['jenis_id'])       ? (int)$row['jenis_id']       : null;
            $sumberId      = !empty($row['sumber_id'])      ? (int)$row['sumber_id']      : null;
            $tahunTerbit   = !empty($row['tahun_terbit'])   ? (int)$row['tahun_terbit']   : null;
            $jumlahHalaman = !empty($row['jumlah_halaman']) ? (int)$row['jumlah_halaman'] : null;
            $bahasa        = !empty(trim((string)($row['bahasa'] ?? ''))) ? trim((string)$row['bahasa']) : 'Indonesia';
            $jumlahStok    = !empty($row['jumlah_stok'])    ? (int)$row['jumlah_stok']    : 1;
            $lokasiRak     = trim((string)($row['lokasi_rak'] ?? ''));
            $status        = !empty(trim((string)($row['status'] ?? ''))) ? trim((string)$row['status']) : 'tersedia';
            $deskripsi     = trim((string)($row['deskripsi'] ?? ''));

            // ── Validasi wajib ──
            if (empty($judulBuku)) {
                $this->addError("Baris {$this->rowNumber}: Judul buku wajib diisi");
                return null;
            }

            if (empty($penulis)) {
                $this->addError("Baris {$this->rowNumber}: Nama penulis wajib diisi");
                return null;
            }

            if (empty($penerbit)) {
                $this->addError("Baris {$this->rowNumber}: Nama penerbit wajib diisi");
                return null;
            }

            if (empty($kategoriId) || !\App\Models\KategoriBuku::find($kategoriId)) {
                $this->addError("Baris {$this->rowNumber}: ID Kategori tidak valid (nilai: {$row['kategori_id']})");
                return null;
            }

            if (empty($jenisId) || !\App\Models\JenisBuku::find($jenisId)) {
                $this->addError("Baris {$this->rowNumber}: ID Jenis tidak valid (nilai: {$row['jenis_id']})");
                return null;
            }

            if (!empty($sumberId) && !\App\Models\SumberBuku::find($sumberId)) {
                $this->addError("Baris {$this->rowNumber}: ID Sumber tidak valid (nilai: {$sumberId})");
                return null;
            }

            if (!empty($tahunTerbit) && ($tahunTerbit < 1900 || $tahunTerbit > (int)date('Y') + 1)) {
                $this->addError("Baris {$this->rowNumber}: Tahun terbit tidak valid (1900 – " . ((int)date('Y') + 1) . ")");
                return null;
            }

            if (!empty($jumlahHalaman) && $jumlahHalaman < 1) {
                $this->addError("Baris {$this->rowNumber}: Jumlah halaman harus lebih dari 0");
                return null;
            }

            if ($jumlahStok < 1) {
                $this->addError("Baris {$this->rowNumber}: Jumlah stok harus lebih dari 0");
                return null;
            }

            if (!in_array($status, ['tersedia', 'tidak_tersedia'])) {
                $this->addError("Baris {$this->rowNumber}: Status harus 'tersedia' atau 'tidak_tersedia'");
                return null;
            }

            // ── Auto-generate barcode (setiap baris langsung disimpan, tidak batch) ──
            try {
                $barcode = Buku::generateBarcode();
            } catch (\Exception $e) {
                $this->addError("Baris {$this->rowNumber}: Gagal generate barcode — " . $e->getMessage());
                return null;
            }

            $this->imported++;

            return new Buku([
                'judul_buku'     => $judulBuku,
                'isbn'           => !empty($isbn)       ? $isbn       : null,
                'barcode'        => $barcode,
                'pengarang'      => $penulis,   // field di model adalah pengarang
                'penerbit'       => $penerbit,
                'kategori_id'    => $kategoriId,
                'jenis_id'       => $jenisId,
                'sumber_id'      => !empty($sumberId)   ? $sumberId   : null,
                'tahun_terbit'   => $tahunTerbit,
                'jumlah_halaman' => $jumlahHalaman,
                'bahasa'         => $bahasa,
                'jumlah_stok'    => $jumlahStok,
                'stok_tersedia'  => $jumlahStok,
                'lokasi_rak'     => !empty($lokasiRak)  ? $lokasiRak  : null,
                'status'         => $status,
                'deskripsi'      => !empty($deskripsi)  ? $deskripsi  : null,
            ]);

        } catch (\Exception $e) {
            $this->addError("Baris {$this->rowNumber}: " . $e->getMessage());
            return null;
        }
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    private function addError(string $message): void
    {
        if (!in_array($message, $this->errorTracker)) {
            $this->errors[]       = $message;
            $this->errorTracker[] = $message;
        }
    }
}
