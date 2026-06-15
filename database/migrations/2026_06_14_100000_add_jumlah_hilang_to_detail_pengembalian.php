<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pengembalian', function (Blueprint $table) {
            $table->integer('jumlah_hilang')->default(0)->after('jumlah_dikembalikan');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pengembalian', function (Blueprint $table) {
            $table->dropColumn('jumlah_hilang');
        });
    }
};
