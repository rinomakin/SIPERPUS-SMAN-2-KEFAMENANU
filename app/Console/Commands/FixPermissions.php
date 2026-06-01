<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class FixPermissions extends Command
{
    protected $signature = 'fix:permissions';
    protected $description = 'Fix admin permissions';

    public function handle()
    {
        $this->info('=== FIXING ADMIN PERMISSIONS ===');
        
        // Get admin role
        $adminRole = Role::where('kode_peran', 'ADMIN')->first();
        if (!$adminRole) {
            $this->error('Admin role not found!');
            return 1;
        }
        $this->info('Found admin role: ' . $adminRole->nama_peran);
        
        // Get all permissions
        $allPermissions = Permission::all();
        if ($allPermissions->count() == 0) {
            $this->error('No permissions found in database!');
            return 1;
        }
        $this->info('Found ' . $allPermissions->count() . ' permissions');
        
        // Assign all permissions to admin role
        $adminRole->syncPermissions($allPermissions->pluck('id')->toArray());
        $this->info('All permissions assigned to admin role');
        
        // Verify
        $adminRole->refresh();
        $this->info('Admin role now has ' . $adminRole->permissions->count() . ' permissions');
        
        // Test admin user
        $adminUser = User::with('role.permissions')->where('email', 'admin@perpustakaan.com')->first();
        if ($adminUser) {
            $this->info('Admin user found: ' . $adminUser->nama_lengkap);
            $this->info('Admin user has ' . ($adminUser->role ? $adminUser->role->permissions->count() : 0) . ' permissions');
            
            // Test specific permissions
            $this->info('');
            $this->info('=== TESTING PERMISSIONS ===');
            $this->info('dashboard.view: ' . ($adminUser->hasPermission('dashboard.view') ? 'YES' : 'NO'));
            $this->info('buku.view: ' . ($adminUser->hasPermission('buku.view') ? 'YES' : 'NO'));
            $this->info('anggota.view: ' . ($adminUser->hasPermission('anggota.view') ? 'YES' : 'NO'));
            $this->info('user.view: ' . ($adminUser->hasPermission('user.view') ? 'YES' : 'NO'));
        }
        
        $this->info('');
        $this->info('🎉 Permissions fixed successfully!');
        
        return 0;
    }
}
