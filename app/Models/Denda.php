<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';

    protected $fillable = [
        'peminjaman_id',
        'pengembalian_id',
        'anggota_id',
        'jumlah_hari_terlambat',
        'jumlah_denda',
        'jumlah_denda_asal',
        'stok_restored',
        'status_pembayaran',
        'tanggal_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'jumlah_denda' => 'decimal:2',
        'jumlah_denda_asal' => 'decimal:2',
        'stok_restored' => 'integer',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function pengembalian()
    {
        return $this->belongsTo(Pengembalian::class);
    }
} 