<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users', 'manage packages', 'manage products',
            'manage commissions', 'manage wallets', 'view reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);
        
        Role::firstOrCreate(['name' => 'user']);
        
        $this->command->info('✅ Permissions créées/mises à jour');
    }
}
