<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'it_manager', 'guard_name' => 'web']
        );

        $this->command?->info('✅ Rôle it_manager créé ou déjà présent (guard: web).');
    }
}
