<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nama_peran' => 'Administrator',
                'kode_peran' => 'ADMIN',
                'deskripsi' => 'Role untuk administrator sistem dengan akses penuh',
                'status' => 'aktif'
            ],
            [
                'nama_peran' => 'Kepala Sekolah',
                'kode_peran' => 'KEPALA_SEKOLAH',
                'deskripsi' => 'Role untuk kepala sekolah dengan akses laporan',
                'status' => 'aktif'
            ],
            [
                'nama_peran' => 'Petugas',
                'kode_peran' => 'PETUGAS',
                'deskripsi' => 'Role untuk petugas perpustakaan dengan akses terbatas',
                'status' => 'aktif'
            ],
            [
                'nama_peran' => 'Anggota',
                'kode_peran' => 'ANGGOTA',
                'deskripsi' => 'Role untuk anggota perpustakaan dengan akses terbatas',
                'status' => 'aktif'
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['kode_peran' => $role['kode_peran']],
                $role
            );
        }
    }
}
