<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'detail_peminjaman';

    protected $fillable = [
        'peminjaman_id',
        'buku_id',
        'jumlah',
        'tanggal_harus_kembali',
        'jam_kembali',
        'kondisi_kembali',
        'catatan',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    public function detailPengembalian()
    {
        return $this->hasMany(DetailPengembalian::class, 'detail_peminjaman_id');
    }
} 