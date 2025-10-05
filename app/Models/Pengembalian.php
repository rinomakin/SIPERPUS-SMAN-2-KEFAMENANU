<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';
    
    protected $fillable = [
        'nomor_pengembalian',
        'peminjaman_id',
        'anggota_id',
        'user_id',
        'tanggal_pengembalian',
        'jam_pengembalian',
        'jumlah_hari_terlambat',
        'total_denda',
        'status_denda',
        'tanggal_pembayaran_denda',
        'catatan',
        'status'
    ];

    protected $casts = [
        'tanggal_pengembalian' => 'date',
        'jam_pengembalian' => 'datetime',
        'tanggal_pembayaran_denda' => 'date',
        'total_denda' => 'decimal:2',
        'jumlah_hari_terlambat' => 'integer',
    ];

    // Relasi ke Peminjaman
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    // Relasi ke Anggota
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    // Relasi ke User (Petugas)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Detail Pengembalian
    public function detailPengembalian(): HasMany
    {
        return $this->hasMany(DetailPengembalian::class);
    }

    // Relasi ke Denda
    public function denda(): HasMany
    {
        return $this->hasMany(Denda::class);
    }

    // Method untuk generate nomor pengembalian
    public static function generateNomorPengembalian(): string
    {
        $today = now()->format('Ymd');
        $lastPengembalian = self::where('nomor_pengembalian', 'like', "KMB-{$today}%")
            ->orderBy('nomor_pengembalian', 'desc')
            ->first();

        if ($lastPengembalian) {
            $lastNumber = (int) substr($lastPengembalian->nomor_pengembalian, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "KMB-{$today}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Method untuk cek apakah ada denda
    public function hasDenda(): bool
    {
        return $this->total_denda > 0;
    }

    // Method untuk cek status denda
    public function isDendaPaid(): bool
    {
        return $this->status_denda === 'sudah_dibayar';
    }

    // Method untuk cek status pengembalian
    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
    }

    // Method untuk cek status pengembalian
    public function isCancelled(): bool
    {
        return $this->status === 'dibatalkan';
    }

    
}
