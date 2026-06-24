<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'Starter', 'slug' => 'starter', 'price' => 30, 'pv_value' => 0, 'bv_value' => 0, 'commission_rate' => 30],
            ['name' => 'Silver', 'slug' => 'silver', 'price' => 85, 'pv_value' => 0, 'bv_value' => 0, 'commission_rate' => 30],
            ['name' => 'Bronze', 'slug' => 'bronze', 'price' => 350, 'pv_value' => 200, 'bv_value' => 200, 'commission_rate' => 30],
            ['name' => 'Gold', 'slug' => 'gold', 'price' => 1450, 'pv_value' => 1000, 'bv_value' => 1000, 'commission_rate' => 30],
            ['name' => 'Emerald', 'slug' => 'emerald', 'price' => 4850, 'pv_value' => 3800, 'bv_value' => 3800, 'commission_rate' => 30],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
        
        $this->command->info('✅ Packages créés/mis à jour');
    }
}
