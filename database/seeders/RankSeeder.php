<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'Distributor', 'slug' => 'distributor', 'min_pv' => 0, 'bonus_percentage' => 0],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'min_pv' => 100, 'bonus_percentage' => 5],
            ['name' => 'Assistant Manager', 'slug' => 'assistant-manager', 'min_pv' => 200, 'bonus_percentage' => 10],
            ['name' => 'Manager', 'slug' => 'manager', 'min_pv' => 500, 'bonus_percentage' => 15],
            ['name' => 'Senior Manager', 'slug' => 'senior-manager', 'min_pv' => 1000, 'bonus_percentage' => 20],
            ['name' => 'Soaring Manager', 'slug' => 'soaring-manager', 'min_pv' => 2000, 'bonus_percentage' => 25],
            ['name' => 'Sapphire Manager', 'slug' => 'sapphire-manager', 'min_pv' => 5000, 'bonus_percentage' => 30],
            ['name' => 'Blue Diamond', 'slug' => 'blue-diamond', 'min_pv' => 10000, 'bonus_percentage' => 35],
            ['name' => 'Diamond', 'slug' => 'diamond', 'min_pv' => 20000, 'bonus_percentage' => 40],
            ['name' => 'Pearl', 'slug' => 'pearl', 'min_pv' => 50000, 'bonus_percentage' => 50],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['slug' => $rank['slug']],
                $rank
            );
        }
        
        $this->command->info('✅ Rangs créés/mis à jour');
    }
}
