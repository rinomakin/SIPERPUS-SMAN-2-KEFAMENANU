<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Role;

class SyncAnggotaToUsers extends Command
{
    protected $signature = 'sync:anggota-user';
    protected $description = 'Buat User account untuk semua anggota yang punya email tapi belum punya User';

    public function handle()
    {
        $roleAnggota = Role::where('kode_peran', 'ANGGOTA')->first();

        if (!$roleAnggota) {
            $this->error('Role ANGGOTA belum ada. Jalankan db:seed --class=RoleSeeder dulu.');
            return 1;
        }

        $anggotaList = Anggota::whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($anggotaList as $anggota) {
            if (User::where('email', $anggota->email)->exists()) {
                $skipped++;
                continue;
            }

            $password = $anggota->tanggal_lahir
                ? bcrypt($anggota->tanggal_lahir)
                : bcrypt('password123');

            User::create([
                'nama_lengkap' => $anggota->nama_lengkap,
                'email' => $anggota->email,
                'password' => $password,
                'peran_id' => $roleAnggota->id,
                'nomor_telepon' => $anggota->nomor_telepon,
                'alamat' => $anggota->alamat,
                'status' => 'aktif',
            ]);

            $created++;
        }

        $this->info("Sinkronisasi selesai.");
        $this->info("- User berhasil dibuat: {$created}");
        $this->info("- User sudah ada (skip): {$skipped}");

        return 0;
    }
}
