<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AssignPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('kode_peran', 'ADMIN')->first();
        $kepalaSekolahRole = Role::where('kode_peran', 'KEPALA_SEKOLAH')->first();
        $petugasRole = Role::where('kode_peran', 'PETUGAS')->first();

        if (!$adminRole || !$kepalaSekolahRole || !$petugasRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Admin gets all permissions
        $allPermissions = Permission::where('status', 'aktif')->pluck('slug')->toArray();
        $adminRole->syncPermissions($allPermissions);

        // Kepala Sekolah gets view/read-only + laporan + export permissions only
        // They are supervisors/observers, not operators
        $kepalaSekolahPermissions = [
            // Dashboard
            'dashboard.view',

            // User Management - view only
            'user.view',

            // Role Management - view only
            'role.view',

            // Anggota - view & export only
            'anggota.view',
            'anggota.export',

            // Buku - view & export only
            'buku.view',
            'buku.export',
            'buku.print-barcode',

            // Kelas & Jurusan - view only
            'kelas.view',
            'jurusan.view',

            // Katalog Buku - view only
            'kategori-buku.view',
            'jenis-buku.view',
            'sumber-buku.view',
            'rak-buku.view',

            // Peminjaman - view & export only
            'peminjaman.view',
            'peminjaman.show',
            'peminjaman.export',
            'peminjaman.scan',

            // Pengembalian - view & export only
            'pengembalian.view',
            'pengembalian.show',
            'pengembalian.export',
            'pengembalian.scan',

            // Riwayat Transaksi
            'riwayat-transaksi.view',
            'riwayat-transaksi.export',

            // Denda - view & export only
            'denda.view',
            'denda.export',

            // Buku Tamu - view & export only (no edit/delete)
            'buku-tamu.view',
            'buku-tamu.export',

            // Laporan - full access (core kepsek need)
            'laporan.view',
            'laporan.anggota',
            'laporan.buku',
            'laporan.peminjaman',
            'laporan.pengembalian',
            'laporan.denda',
            'laporan.buku-tamu',
            'laporan.kas',

            // Pengaturan - view only
            'pengaturan.view',
        ];

        $kepalaSekolahRole->syncPermissions($kepalaSekolahPermissions);

        // Petugas gets operational permissions
        $petugasPermissions = [
            // Dashboard
            'dashboard.view',

            // Pengaturan - view only (edit can be granted by Admin via permission UI)
            'pengaturan.view',

            // Anggota - create & edit (no delete)
            'anggota.view',
            'anggota.create',
            'anggota.edit',
            'anggota.cetak-kartu',

            // Buku - view only
            'buku.view',

            // Peminjaman - full operational access
            'peminjaman.view',
            'peminjaman.create',
            'peminjaman.edit',
            'peminjaman.show',
            'peminjaman.scan',

            // Pengembalian - full operational access
            'pengembalian.view',
            'pengembalian.create',
            'pengembalian.show',
            'pengembalian.scan',

            // Riwayat transaksi
            'riwayat-transaksi.view',
            'riwayat-transaksi.export',

            // Denda - manage fines (view, create, edit)
            'denda.view',
            'denda.create',
            'denda.edit',

            // Buku Tamu - full access (front desk task)
            'buku-tamu.view',
            'buku-tamu.create',
            'buku-tamu.edit',
            'buku-tamu.delete',
            'buku-tamu.export',
        ];
        
        $petugasRole->syncPermissions($petugasPermissions);

        $this->command->info('Permissions assigned successfully!');
        $this->command->info('Admin: ' . count($allPermissions) . ' permissions');
        $this->command->info('Kepala Sekolah: ' . count($kepalaSekolahPermissions) . ' permissions');
        $this->command->info('Petugas: ' . count($petugasPermissions) . ' permissions');
    }
}
