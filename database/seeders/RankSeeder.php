<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Grades MLM Salang — alignés sur la BD locale (salang_mlm.ranks).
 * Idempotent : updateOrInsert par level (pas de truncate).
 */
class RankSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $ranks = [
            [
                'level' => 1,
                'name' => 'Distributeur',
                'slug' => 'distributor',
                'min_pv' => 0,
                'min_bv' => 0,
                'monthly_pv_required' => 0,
                'team_pv_required' => 0,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 0,
                'pv_payment_required' => 0,
                'description' => 'Grade de base, point de départ dans le système Salang',
                'conditions' => json_encode([['label' => 'Inscription', 'value' => 'Validée']]),
                'commission_types' => json_encode(['Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 2,
                'name' => 'Qualification',
                'slug' => 'supervisor',
                'min_pv' => 100,
                'min_bv' => 100,
                'monthly_pv_required' => 20,
                'team_pv_required' => 0,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 0,
                'pv_payment_required' => 20,
                'description' => 'Premier grade actif, débutez vos commissions',
                'conditions' => json_encode([
                    ['label' => 'PV Personnel', 'value' => '≥ 100 PV'],
                    ['label' => 'PV Mensuel', 'value' => '≥ 20 PV'],
                ]),
                'commission_types' => json_encode(['Bonus Direct (6%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 3,
                'name' => 'Cumul Directeur',
                'slug' => 'assistant-manager',
                'min_pv' => 200,
                'min_bv' => 200,
                'monthly_pv_required' => 20,
                'team_pv_required' => 0,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 22,
                'pv_payment_required' => 20,
                'description' => 'Grade intermédiaire, augmentez vos commissions',
                'conditions' => json_encode([
                    ['label' => 'PV Personnel', 'value' => '≥ 200 PV'],
                    ['label' => 'PV Mensuel', 'value' => '≥ 20 PV'],
                ]),
                'commission_types' => json_encode(['Bonus Direct (22%)', 'Bonus Indirect', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 4,
                'name' => 'Directeur',
                'slug' => 'manager',
                'min_pv' => 1000,
                'min_bv' => 1000,
                'monthly_pv_required' => 25,
                'team_pv_required' => 0,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 26,
                'pv_payment_required' => 25,
                'description' => 'Grade de leader, commencez à développer votre réseau',
                'conditions' => json_encode([
                    ['type' => 'personal_pv', 'label' => 'PV ≥ 1000', 'value' => 1000],
                    ['type' => 'branches', 'label' => '3 branches Niveau 3 avec CUMUL ≥ 1000 PV', 'branches' => 3, 'group_pv' => 1000, 'rank_level' => 3],
                    ['type' => 'branches', 'label' => '2 branches Niveau 3 avec CUMUL ≥ 2200 PV', 'branches' => 2, 'group_pv' => 2200, 'rank_level' => 3],
                ]),
                'commission_types' => json_encode(['Bonus Direct (26%)', 'Bonus Indirect', 'Bonus Consommateur (6%)']),
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
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 30,
                'pv_payment_required' => 30,
                'description' => 'Grade de manager, optimisez les commissions de votre réseau',
                'conditions' => json_encode([
                    ['type' => 'branches', 'label' => '3 branches Niveau 4 avec CUMUL ≥ 3800 PV', 'branches' => 3, 'group_pv' => 3800, 'rank_level' => 4],
                    ['type' => 'branches', 'label' => '2 branches Niveau 4 avec CUMUL ≥ 7800 PV', 'branches' => 2, 'group_pv' => 7800, 'rank_level' => 4],
                    ['type' => 'branches_mixed', 'label' => '2 branches Niveau 4 + 4 branches Niveau 3 avec CUMUL ≥ 3800 PV', 'branches' => ['2' => 4, '4' => 3], 'group_pv' => 3800],
                    ['type' => 'branches_mixed', 'label' => '1 branche Niveau 4 + 6 branches Niveau 3 avec CUMUL ≥ 3800 PV', 'branches' => ['1' => 4, '6' => 3], 'group_pv' => 3800],
                ]),
                'commission_types' => json_encode(['Bonus Direct (30%)', 'Bonus Indirect', 'Bonus Leadership (0.5%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 6,
                'name' => 'Directeur Envolée',
                'slug' => 'soaring-manager',
                'min_pv' => 16000,
                'min_bv' => 16000,
                'monthly_pv_required' => 50,
                'team_pv_required' => 1000,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 34,
                'pv_payment_required' => 50,
                'description' => 'Grade de directeur, développez un réseau profond',
                'conditions' => json_encode([
                    ['type' => 'branches', 'label' => '3 branches Niveau 5 avec CUMUL ≥ 16000 PV', 'branches' => 3, 'group_pv' => 16000, 'rank_level' => 5],
                    ['type' => 'branches', 'label' => '2 branches Niveau 5 avec CUMUL ≥ 35000 PV', 'branches' => 2, 'group_pv' => 35000, 'rank_level' => 5],
                    ['type' => 'branches_mixed', 'label' => '2 branches Niveau 5 + 4 branches Niveau 4 avec CUMUL ≥ 16000 PV', 'branches' => ['2' => 5, '4' => 4], 'group_pv' => 16000],
                    ['type' => 'branches_mixed', 'label' => '1 branche Niveau 5 + 6 branches Niveau 4 avec CUMUL ≥ 16000 PV', 'branches' => ['1' => 5, '6' => 4], 'group_pv' => 16000],
                ]),
                'commission_types' => json_encode(['Bonus Direct (34%)', 'Bonus Indirect', 'Bonus Leadership (1.1%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 7,
                'name' => 'Saphire Manager',
                'slug' => 'sapphire-manager',
                'min_pv' => 73000,
                'min_bv' => 73000,
                'monthly_pv_required' => 100,
                'team_pv_required' => 2000,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 40,
                'pv_payment_required' => 100,
                'description' => 'Grade saphir, accédez aux primes mondiales',
                'conditions' => json_encode([
                    ['type' => 'branches', 'label' => '3 branches Niveau 6 avec CUMUL ≥ 73000 PV', 'branches' => 3, 'group_pv' => 73000, 'rank_level' => 6],
                    ['type' => 'branches', 'label' => '2 branches Niveau 6 avec CUMUL ≥ 145000 PV', 'branches' => 2, 'group_pv' => 145000, 'rank_level' => 6],
                    ['type' => 'branches_mixed', 'label' => '2 branches Niveau 6 + 4 branches Niveau 5 avec CUMUL ≥ 73000 PV', 'branches' => ['2' => 6, '4' => 5], 'group_pv' => 73000],
                    ['type' => 'branches_mixed', 'label' => '1 branche Niveau 6 + 6 branches Niveau 5 avec CUMUL ≥ 73000 PV', 'branches' => ['1' => 6, '6' => 5], 'group_pv' => 73000],
                ]),
                'commission_types' => json_encode(['Bonus Direct (40%)', 'Bonus Indirect', 'Bonus Leadership (1.8%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 8,
                'name' => 'Diamant Bleu',
                'slug' => 'blue-diamond',
                'min_pv' => 280000,
                'min_bv' => 280000,
                'monthly_pv_required' => 180,
                'team_pv_required' => 3000,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 43,
                'pv_payment_required' => 200,
                'description' => 'Grade diamant, primes mondiales significatives',
                'conditions' => json_encode([
                    ['type' => 'branches', 'label' => '3 branches Niveau 7 avec CUMUL ≥ 280000 PV', 'branches' => 3, 'group_pv' => 280000, 'rank_level' => 7],
                    ['type' => 'branches', 'label' => '2 branches Niveau 7 avec CUMUL ≥ 580000 PV', 'branches' => 2, 'group_pv' => 580000, 'rank_level' => 7],
                    ['type' => 'branches_mixed', 'label' => '2 branches Niveau 7 + 4 branches Niveau 6 avec CUMUL ≥ 280000 PV', 'branches' => ['2' => 7, '4' => 6], 'group_pv' => 280000],
                    ['type' => 'branches_mixed', 'label' => '1 branche Niveau 7 + 6 branches Niveau 6 avec CUMUL ≥ 280000 PV', 'branches' => ['1' => 7, '6' => 6], 'group_pv' => 280000],
                ]),
                'commission_types' => json_encode(['Bonus Direct (43%)', 'Bonus Indirect', 'Bonus Leadership (2.6%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 9,
                'name' => 'Perle Diamant',
                'slug' => 'diamond',
                'min_pv' => 400000,
                'min_bv' => 400000,
                'monthly_pv_required' => 300,
                'team_pv_required' => 5000,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 45,
                'pv_payment_required' => 300,
                'description' => 'Grade ultime, bonuses mondiaux maximum',
                'conditions' => json_encode([
                    ['type' => 'branches', 'label' => '3 branches Niveau 8 avec CUMUL ≥ 400000 PV', 'branches' => 3, 'group_pv' => 400000, 'rank_level' => 8],
                    ['type' => 'branches', 'label' => '2 branches Niveau 8 avec CUMUL ≥ 780000 PV', 'branches' => 2, 'group_pv' => 780000, 'rank_level' => 8],
                    ['type' => 'branches_mixed', 'label' => '2 branches Niveau 8 + 4 branches Niveau 7 avec CUMUL ≥ 400000 PV', 'branches' => ['2' => 8, '4' => 7], 'group_pv' => 400000],
                    ['type' => 'branches_mixed', 'label' => '1 branche Niveau 8 + 6 branches Niveau 7 avec CUMUL ≥ 400000 PV', 'branches' => ['1' => 8, '6' => 7], 'group_pv' => 400000],
                ]),
                'commission_types' => json_encode(['Bonus Direct (45%)', 'Bonus Indirect', 'Bonus Leadership (3.5%)', 'Bonus Consommateur (6%)']),
                'is_active' => 1,
            ],
            [
                'level' => 10,
                'name' => 'Pearl',
                'slug' => 'pearl',
                'min_pv' => 500000,
                'min_bv' => 500000,
                'monthly_pv_required' => 0,
                'team_pv_required' => 0,
                'min_sponsors' => 0,
                'min_team' => 0,
                'bonus_percentage' => 50,
                'pv_payment_required' => 0,
                'description' => 'Grade Pearl, niveau avancé du système',
                'conditions' => json_encode([['label' => 'Être niveau 10', 'value' => 'Avoir les conditions requises pour Pearl']]),
                'commission_types' => json_encode(['Bonus Direct (50%)', 'Bonus Indirect', 'Bonus Leadership (4.0%)', 'Bonus Consommateur (6%)']),
                'is_active' => 0,
            ],
        ];

        foreach ($ranks as $rank) {
            $payload = array_merge($rank, [
                'updated_at' => $now,
            ]);

            $exists = DB::table('ranks')->where('level', $rank['level'])->exists();
            if (! $exists) {
                $payload['created_at'] = $now;
            }

            DB::table('ranks')->updateOrInsert(
                ['level' => $rank['level']],
                $payload
            );
        }
    }
}
