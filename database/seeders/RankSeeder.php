<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('ranks')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $ranks = [
            [
                'level' => 1,
                'name' => 'Distributeur',
                'slug' => 'distributor',
                'min_pv' => 0,
                'min_bv' => 0,
                'monthly_pv_required' => 0,
                'team_pv_required' => 0,
                'bonus_percentage' => 6,
                'is_active' => 1,
                'conditions' => json_encode([['label' => 'Inscription', 'value' => 'Validée']]),
                'commission_types' => json_encode(['Bonus Consommateur (6%)']),
            ],
            [
                'level' => 2,
                'name' => 'Qualification',
                'slug' => 'supervisor',
                'min_pv' => 100,
                'min_bv' => 100,
                'monthly_pv_required' => 20,
                'team_pv_required' => 0,
                'bonus_percentage' => 6,
                'is_active' => 1,
                'conditions' => json_encode([
                    ['label' => 'PV Personnel', 'value' => '≥ 100 PV'],
                    ['label' => 'PV Mensuel', 'value' => '≥ 20 PV']
                ]),
                'commission_types' => json_encode(['Bonus Direct (6%)', 'Bonus Consommateur (6%)']),
            ],
            [
                'level' => 3,
                'name' => 'Cumul Directeur',
                'slug' => 'assistant-manager',
                'min_pv' => 200,
                'min_bv' => 200,
                'monthly_pv_required' => 20,
                'team_pv_required' => 0,
                'bonus_percentage' => 22,
                'is_active' => 1,
                'conditions' => json_encode([
                    ['label' => 'PV Personnel', 'value' => '≥ 200 PV'],
                    ['label' => 'PV Mensuel', 'value' => '≥ 20 PV']
                ]),
                'commission_types' => json_encode(['Bonus Direct (22%)', 'Bonus Indirect', 'Bonus Consommateur (6%)']),
            ],
            [
                'level' => 4,
                'name' => 'Directeur',
                'slug' => 'manager',
                'min_pv' => 1000,
                'min_bv' => 1000,
                'monthly_pv_required' => 25,
                'team_pv_required' => 0,
                'bonus_percentage' => 26,
                'is_active' => 1,
            ],
            [
                'level' => 5,
                'name' => 'Manager Senior',
                'slug' => 'senior-manager',
                'min_pv' => 3800,
                'min_bv' => 3800,
                'monthly_pv_required' => 30,
                'team_pv_required' => 500,
                'bonus_percentage' => 30,
                'is_active' => 1,
            ],
        ];

        foreach ($ranks as $rank) {
            DB::table('ranks')->insert($rank);
        }
    }
}
