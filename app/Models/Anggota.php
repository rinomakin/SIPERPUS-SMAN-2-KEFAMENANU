<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'nomor_anggota',
        'barcode_anggota',
        'nama_lengkap',
        'jenis_kelamin',
        'alamat',
        'nomor_telepon',
        'email',
        'kelas_id',
        'jabatan',
        'jenis_anggota',
        'foto',
        'status',
        'tanggal_bergabung',
        'tanggal_lahir',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'tanggal_lahir' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan()
    {
        return $this->hasOneThrough(Jurusan::class, Kelas::class, 'id', 'id', 'kelas_id', 'jurusan_id');
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function denda()
    {
        return $this->hasMany(Denda::class);
    }

    public function bukuTamu()
    {
        return $this->hasMany(BukuTamu::class);
    }

    // Method untuk generate nomor anggota otomatis dengan locking untuk mencegah duplikasi
    public static function generateNomorAnggota()
    {
        return DB::transaction(function () {
            // Lock the table to prevent race conditions
            $lastMember = self::lockForUpdate()->orderBy('id', 'desc')->first();
            $lastNumber = $lastMember ? intval(substr($lastMember->nomor_anggota, 3)) : 0;
            $newNumber = $lastNumber + 1;
            $nomorAnggota = 'AGT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            
            // Double check untuk memastikan tidak ada duplikasi
            while (self::where('nomor_anggota', $nomorAnggota)->exists()) {
                $newNumber++;
                $nomorAnggota = 'AGT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            }
            
            return $nomorAnggota;
        });
    }

    // Method untuk generate barcode anggota otomatis dengan locking untuk mencegah duplikasi
    public static function generateBarcodeAnggota()
    {
        return DB::transaction(function () {
            // Lock the table to prevent race conditions
            $lastMember = self::lockForUpdate()->orderBy('id', 'desc')->first();
            $lastNumber = $lastMember ? intval(substr($lastMember->barcode_anggota, 2)) : 0;
            $newNumber = $lastNumber + 1;
            $barcodeAnggota = 'BC' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
            
            // Double check untuk memastikan tidak ada duplikasi
            while (self::where('barcode_anggota', $barcodeAnggota)->exists()) {
                $newNumber++;
                $barcodeAnggota = 'BC' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
            }
            
            return $barcodeAnggota;
        });
    }

    // Method untuk generate nomor dan barcode sekaligus dengan locking
    public static function generateNomorDanBarcode()
    {
        return DB::transaction(function () {
            // Lock the table to prevent race conditions
            $lastMember = self::lockForUpdate()->orderBy('id', 'desc')->first();
            
            // Get the highest nomor_anggota number
            $maxNomor = self::selectRaw('CAST(SUBSTRING(nomor_anggota, 4) AS UNSIGNED) as nomor_number')
                ->where('nomor_anggota', 'LIKE', 'AGT%')
                ->orderByRaw('CAST(SUBSTRING(nomor_anggota, 4) AS UNSIGNED) DESC')
                ->first();
            
            $lastNomorNumber = $maxNomor ? (int)$maxNomor->nomor_number : 0;
            $newNomorNumber = $lastNomorNumber + 1;
            $nomorAnggota = 'AGT' . str_pad($newNomorNumber, 6, '0', STR_PAD_LEFT);
            
            // Get the highest barcode_anggota number
            $maxBarcode = self::selectRaw('CAST(SUBSTRING(barcode_anggota, 3) AS UNSIGNED) as barcode_number')
                ->where('barcode_anggota', 'LIKE', 'BC%')
                ->orderByRaw('CAST(SUBSTRING(barcode_anggota, 3) AS UNSIGNED) DESC')
                ->first();
            
            $lastBarcodeNumber = $maxBarcode ? (int)$maxBarcode->barcode_number : 0;
            $newBarcodeNumber = $lastBarcodeNumber + 1;
            $barcodeAnggota = 'BC' . str_pad($newBarcodeNumber, 8, '0', STR_PAD_LEFT);
            
            // Double check dan regenerate jika ada duplikasi
            $maxAttempts = 20;
            $attempts = 0;
            
            while ($attempts < $maxAttempts) {
                $exists = self::where('nomor_anggota', $nomorAnggota)
                    ->orWhere('barcode_anggota', $barcodeAnggota)
                    ->exists();
                
                if (!$exists) {
                    break;
                }
                
                // Regenerate dengan increment yang berbeda
                $newNomorNumber++;
                $newBarcodeNumber++;
                $nomorAnggota = 'AGT' . str_pad($newNomorNumber, 6, '0', STR_PAD_LEFT);
                $barcodeAnggota = 'BC' . str_pad($newBarcodeNumber, 8, '0', STR_PAD_LEFT);
                $attempts++;
            }
            
            // Jika masih ada duplikasi setelah max attempts, gunakan timestamp dengan random
            if ($attempts >= $maxAttempts) {
                $timestamp = time();
                $random = mt_rand(1000, 9999);
                $nomorAnggota = 'AGT' . str_pad($timestamp . $random, 6, '0', STR_PAD_LEFT);
                $barcodeAnggota = 'BC' . str_pad($timestamp . $random, 8, '0', STR_PAD_LEFT);
            }
            
            return [
                'nomor_anggota' => $nomorAnggota,
                'barcode_anggota' => $barcodeAnggota
            ];
        });
    }

    // Method alternatif yang lebih sederhana dan robust
    public static function generateUniqueCodes()
    {
        return DB::transaction(function () {
            // Generate timestamp-based codes with random component
            $timestamp = time();
            $random = mt_rand(1000, 9999);
            $uniqueId = $timestamp . $random;
            
            $nomorAnggota = 'AGT' . str_pad($uniqueId, 6, '0', STR_PAD_LEFT);
            $barcodeAnggota = 'BC' . str_pad($uniqueId, 8, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness by checking and regenerating if needed
            $maxAttempts = 10;
            $attempts = 0;
            
            while ($attempts < $maxAttempts) {
                $exists = self::where('nomor_anggota', $nomorAnggota)
                    ->orWhere('barcode_anggota', $barcodeAnggota)
                    ->exists();
                
                if (!$exists) {
                    break;
                }
                
                // Regenerate with new random component
                $timestamp = time();
                $random = mt_rand(1000, 9999);
                $uniqueId = $timestamp . $random;
                
                $nomorAnggota = 'AGT' . str_pad($uniqueId, 6, '0', STR_PAD_LEFT);
                $barcodeAnggota = 'BC' . str_pad($uniqueId, 8, '0', STR_PAD_LEFT);
                $attempts++;
            }
            
            return [
                'nomor_anggota' => $nomorAnggota,
                'barcode_anggota' => $barcodeAnggota
            ];
        });
    }

    // Method untuk membersihkan data duplikasi
    public static function cleanDuplicateData()
    {
        return DB::transaction(function () {
            // Find duplicates by nomor_anggota
            $duplicateNomor = self::select('nomor_anggota')
                ->groupBy('nomor_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('nomor_anggota');

            // Find duplicates by barcode_anggota
            $duplicateBarcode = self::select('barcode_anggota')
                ->groupBy('barcode_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('barcode_anggota');

            $cleaned = 0;

            // Clean nomor_anggota duplicates (keep the oldest record)
            foreach ($duplicateNomor as $nomor) {
                $duplicates = self::where('nomor_anggota', $nomor)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, delete the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $duplicates[$i]->delete();
                    $cleaned++;
                }
            }

            // Clean barcode_anggota duplicates (keep the oldest record)
            foreach ($duplicateBarcode as $barcode) {
                $duplicates = self::where('barcode_anggota', $barcode)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, delete the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $duplicates[$i]->delete();
                    $cleaned++;
                }
            }

            return $cleaned;
        });
    }

    // Method untuk regenerate nomor dan barcode yang duplikasi
    public static function regenerateDuplicateCodes()
    {
        return DB::transaction(function () {
            $regenerated = 0;

            // Find and fix nomor_anggota duplicates
            $duplicateNomor = self::select('nomor_anggota')
                ->groupBy('nomor_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('nomor_anggota');

            foreach ($duplicateNomor as $nomor) {
                $duplicates = self::where('nomor_anggota', $nomor)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, regenerate the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $newData = self::generateUniqueCodes();
                    $duplicates[$i]->update([
                        'nomor_anggota' => $newData['nomor_anggota'],
                        'barcode_anggota' => $newData['barcode_anggota']
                    ]);
                    $regenerated++;
                }
            }

            // Find and fix barcode_anggota duplicates
            $duplicateBarcode = self::select('barcode_anggota')
                ->groupBy('barcode_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('barcode_anggota');

            foreach ($duplicateBarcode as $barcode) {
                $duplicates = self::where('barcode_anggota', $barcode)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, regenerate the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $newData = self::generateUniqueCodes();
                    $duplicates[$i]->update([
                        'nomor_anggota' => $newData['nomor_anggota'],
                        'barcode_anggota' => $newData['barcode_anggota']
                    ]);
                    $regenerated++;
                }
            }

            return $regenerated;
        });
    }

    // Method untuk membersihkan data duplikasi dengan regenerate
    public static function cleanAndRegenerateDuplicates()
    {
        return DB::transaction(function () {
            $processed = 0;

            // Find all duplicates
            $duplicateNomor = self::select('nomor_anggota')
                ->groupBy('nomor_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('nomor_anggota');

            $duplicateBarcode = self::select('barcode_anggota')
                ->groupBy('barcode_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('barcode_anggota');

            // Process nomor_anggota duplicates
            foreach ($duplicateNomor as $nomor) {
                $duplicates = self::where('nomor_anggota', $nomor)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, regenerate the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $newData = self::generateUniqueCodes();
                    $duplicates[$i]->update([
                        'nomor_anggota' => $newData['nomor_anggota'],
                        'barcode_anggota' => $newData['barcode_anggota']
                    ]);
                    $processed++;
                }
            }

            // Process barcode_anggota duplicates
            foreach ($duplicateBarcode as $barcode) {
                $duplicates = self::where('barcode_anggota', $barcode)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Keep the first one, regenerate the rest
                for ($i = 1; $i < count($duplicates); $i++) {
                    $newData = self::generateUniqueCodes();
                    $duplicates[$i]->update([
                        'nomor_anggota' => $newData['nomor_anggota'],
                        'barcode_anggota' => $newData['barcode_anggota']
                    ]);
                    $processed++;
                }
            }

            return $processed;
        });
    }
} 