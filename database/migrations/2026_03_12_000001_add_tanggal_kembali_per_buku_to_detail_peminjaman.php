<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->date('tanggal_harus_kembali')->nullable()->after('jumlah');
            $table->time('jam_kembali')->nullable()->after('tanggal_harus_kembali');
        });
    }

    public function down(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->dropColumn(['tanggal_harus_kembali', 'jam_kembali']);
        });
    }
};
