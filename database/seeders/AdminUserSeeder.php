<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@salang.com',
            'password' => Hash::make('password123'),
            'sponsor_id' => 'SALADMIN',
            'rank_id' => 10,
            'package_id' => 5,
            'pv_balance' => 50000,
            'is_active' => true,
        ]);

        $admin->assignRole('admin');
    }
}
