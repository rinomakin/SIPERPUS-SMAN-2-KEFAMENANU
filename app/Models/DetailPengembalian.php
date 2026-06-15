<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengembalian extends Model
{
    use HasFactory;

    protected $table = 'detail_pengembalian';
    
    protected $fillable = [
        'pengembalian_id',
        'buku_id',
        'detail_peminjaman_id',
        'kondisi_kembali',
        'jumlah_dikembalikan',
        'jumlah_hilang',
        'denda_buku',
        'catatan_buku'
    ];

    protected $casts = [
        'jumlah_dikembalikan' => 'integer',
        'denda_buku' => 'decimal:2',
    ];

    // Relasi ke Pengembalian
    public function pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class);
    }

    // Relasi ke Buku
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }

    // Relasi ke Detail Peminjaman
    public function detailPeminjaman(): BelongsTo
    {
        return $this->belongsTo(DetailPeminjaman::class);
    }

    // Method untuk cek kondisi buku
    public function isBookDamaged(): bool
    {
        return in_array($this->kondisi_kembali, ['sedikit_rusak', 'rusak']);
    }

    // Method untuk cek apakah buku hilang
    public function isBookLost(): bool
    {
        return $this->kondisi_kembali === 'hilang';
    }

    // Method untuk cek apakah buku dalam kondisi baik
    public function isBookGood(): bool
    {
        return $this->kondisi_kembali === 'baik';
    }

    // Method untuk cek apakah ada denda untuk buku ini
    public function hasDenda(): bool
    {
        return $this->denda_buku > 0;
    }

    // Method untuk mendapatkan label kondisi buku
    public function getKondisiLabel(): string
    {
        return match($this->kondisi_kembali) {
            'baik' => 'Baik',
            'sedikit_rusak' => 'Sedikit Rusak',
            'rusak' => 'Rusak',
            'hilang' => 'Hilang',
            default => 'Tidak Diketahui'
        };
    }

    // Method untuk mendapatkan class CSS berdasarkan kondisi
    public function getKondisiClass(): string
    {
        return match($this->kondisi_kembali) {
            'baik' => 'text-green-600',
            'sedikit_rusak' => 'text-yellow-600',
            'rusak' => 'text-red-600',
            'hilang' => 'text-red-800',
            default => 'text-gray-600'
        };
    }
}
