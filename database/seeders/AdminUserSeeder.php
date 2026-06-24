<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@salang.com')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@salang.com',
                'phone' => '+225 0700000000',
                'password' => Hash::make('password123'),
                'sponsor_id' => 'SALADMIN',
                'rank_id' => Rank::where('slug', 'pearl')->first()?->id ?? 10,
                'package_id' => Package::where('slug', 'emerald')->first()?->id ?? 5,
                'pv_balance' => 50000,
                'bv_balance' => 50000,
                'total_earnings' => 15000,
                'is_active' => true,
                'country' => "Côte d'Ivoire",
                'city' => 'Abidjan',
            ]);

            $admin->assignRole('admin');

            Wallet::create([
                'user_id' => $admin->id,
                'balance' => 2500,
                'pending_balance' => 500,
                'total_withdrawn' => 1000,
                'total_deposited' => 4000,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            Genealogy::create([
                'user_id' => $admin->id,
                'sponsor_id' => null,
                'parent_id' => null,
                'level' => 0,
                'total_children' => 0,
            ]);
            
            $this->command->info('✅ Admin créé');
        } else {
            $this->command->info('ℹ️ Admin existe déjà');
        }
    }
}
