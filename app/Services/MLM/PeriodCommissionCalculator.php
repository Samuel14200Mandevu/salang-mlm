<?php
// app/Services/MLM/PeriodCommissionCalculator.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\PVHistory;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PeriodCommissionCalculator — FIN DE PÉRIODE
 *
 * Calcule et persiste pour chaque user ayant du PV sur la période :
 *   1. Bonus Direct   (monthly_pv × taux_direct)
 *   2. Bonus Indirect (gén. 1-8   : PV_descendant × (buyerRate − descRate))
 *   3. Leadership     (gén. 1-12  : PV_descendant × leadershipRate)
 *
 * Basé sur pv_history (source de vérité du PV).
 *
 * ALIGNÉ 1:1 avec AdminCommissionHistoryController.
 */
class PeriodCommissionCalculator
{
    const MAX_GENERATIONS_INDIRECT = 8;
    const MAX_GENERATIONS_LEADERSHIP = 12;
    const MAX_TREE_GENERATIONS = 12;

    /**
     * Calcule et persiste Direct + Indirect + Leadership pour une période.
     *
     * @param  CommissionPeriod  $period
     * @param  int|null          $userId  Limiter le calcul à un seul user
     * @return array{created:int, total_amount:float}
     */
    public function calculateForPeriod(CommissionPeriod $period, ?int $userId = null): array
    {
        set_time_limit(1800);
        ini_set('memory_limit', '2048M');

        $allUsers = User::where('is_active', true)->get()->keyBy('id');

        // Agrège le PV par user sur la période
        $pvQuery = PVHistory::where('period', $period->period);
        if ($userId) {
            $pvQuery->where('user_id', $userId);
        }

        $pvByUser = $pvQuery
            ->select('user_id', DB::raw('SUM(amount) as total_pv'))
            ->groupBy('user_id')
            ->pluck('total_pv', 'user_id')
            ->toArray();

        if (empty($pvByUser)) {
            return ['created' => 0, 'total_amount' => 0];
        }

        $totalCreated = 0;
        $totalAmount = 0;

        foreach ($pvByUser as $uid => $userMonthlyPV) {
            $user = $allUsers->get($uid);
            if (!$user || $user->user_type !== 'member') {
                continue;
            }

            // ─── 0. Nettoyage : supprime les anciennes commissions calculées ───
            Commission::where('user_id', $uid)
                ->where('commission_period_id', $period->id)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->delete();

            // ─── 1. BONUS DIRECT (sur monthly_pv total) ───
            $directBonus = $this->calculateDirectBonus($user, (int)$userMonthlyPV, $period);
            if ($directBonus) {
                Commission::create(array_merge($directBonus, [
                    'commission_period_id' => $period->id,
                ]));
                $totalCreated++;
                $totalAmount += $directBonus['amount'];
            }

            // ─── 2. INDIRECT + LEADERSHIP ───
            $commissions = $this->calculateIndirectAndLeadership(
                $user, $period, (int)$userMonthlyPV, $allUsers
            );

            foreach ($commissions as $data) {
                Commission::create(array_merge($data, [
                    'commission_period_id' => $period->id,
                ]));
                $totalCreated++;
                $totalAmount += $data['amount'];
            }
        }

        return [
            'created' => $totalCreated,
            'total_amount' => $totalAmount,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 1. BONUS DIRECT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Bonus direct = monthly_pv (PV total de la période) × taux_direct(rank)
     *
     * Règles :
     *   - rank < 3 → pas de bonus direct
     *   - Vérifie conditions PV (personnel + groupe)
     *   - Un seul bonus par user par période
     */
    private function calculateDirectBonus(User $user, int $userMonthlyPV, CommissionPeriod $period): ?array
    {
        $rankLevel = $user->rank_level ?? 1;

        // Rank < 3 → pas de bonus direct
        if ($rankLevel < 3) {
            return null;
        }

        // Vérifie conditions PV
        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

        if ($userMonthlyPV < $req['personal']) return null;
        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) return null;

        // Taux selon le rank
        $rate = $this->getDirectRate($rankLevel);
        if ($rate <= 0) return null;

        $amount = $userMonthlyPV * ($rate / 100);
        if ($amount <= 0) return null;

        return [
            'user_id'          => $user->id,
            'from_user_id'     => $user->id,
            'period'           => $period->period,
            'type'             => 'direct',
            'source'           => 'period',
            'amount'           => $amount,
            'percentage'       => $rate,
            'pv_used'          => $userMonthlyPV,
            'generation'       => 0,
            'calculation_type' => 'period',
            'status'           => 'pending',
            'description'      => "Bonus Direct Mensuel - {$rate}% sur {$userMonthlyPV} PV pour {$period->period}",
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. INDIRECT + LEADERSHIP
    // ═══════════════════════════════════════════════════════════════

    private function calculateIndirectAndLeadership(
        User $user,
        CommissionPeriod $period,
        int $userMonthlyPV,
        $allUsers
    ): array {
        $commissions = [];
        $requirements = $this->getMonthlyPVRequirements();
        $userRank = $user->rank_level ?? 1;
        $userRate = $this->getDirectRate($userRank);
        $req = $requirements[$userRank] ?? ['personal' => 0, 'group' => 0];

        // Vérif PV (personnel + groupe)
        if ($userMonthlyPV < $req['personal']) return $commissions;
        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) return $commissions;

        // Parcours de l'arbre descendant
        $descendantsData = $this->getAllDescendantsWithAncestors($user, self::MAX_TREE_GENERATIONS);
        if (empty($descendantsData)) return $commissions;

        $descendantIds = array_column($descendantsData, 'descendant_id');

        // PV des descendants sur la période
        $descendantPVs = PVHistory::whereIn('user_id', $descendantIds)
            ->where('period', $period->period)
            ->select('user_id', DB::raw('SUM(amount) as total_pv'))
            ->groupBy('user_id')
            ->pluck('total_pv', 'user_id')
            ->toArray();

        foreach ($descendantsData as $data) {
            $generation = $data['generation'];
            $descendantId = $data['descendant_id'];
            $descendantRank = $data['descendant_rank'];
            $isExcluded = $data['is_excluded'];

            // Branche exclue
            if ($isExcluded) continue;

            $descendantPV = $descendantPVs[$descendantId] ?? 0;
            if ($descendantPV <= 0) continue;

            $descendantRate = $this->getDirectRate($descendantRank);
            $descendant = $allUsers->get($descendantId);

            // ─────────────── INDIRECT (gén. 1-8) ───────────────
            if (
                $generation <= self::MAX_GENERATIONS_INDIRECT
                && $userRank >= 3
                && $descendantRank >= 3
                && $userRank > $descendantRank
            ) {
                $rateDifference = max(0, $userRate - $descendantRate);

                if ($rateDifference > 0) {
                    $amount = $descendantPV * ($rateDifference / 100);

                    if ($amount > 0) {
                        $commissions[] = [
                            'user_id'          => $user->id,
                            'from_user_id'     => $descendantId,
                            'period'           => $period->period,
                            'type'             => 'indirect',
                            'source'           => 'period',
                            'amount'           => $amount,
                            'percentage'       => $rateDifference,
                            'pv_used'          => $descendantPV,
                            'generation'       => $generation,
                            'calculation_type' => 'period',
                            'status'           => 'pending',
                            'description'      => "Bonus Indirect Génération {$generation} ({$rateDifference}% sur {$descendantPV} PV pour {$period->period}) - " . ($descendant?->name ?? 'N/A'),
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }
                }
            }

            // ─────────────── LEADERSHIP (gén. 1-12) ───────────────
            if ($generation <= self::MAX_GENERATIONS_LEADERSHIP && $userRank >= 5) {
                $leadershipRate = $this->getLeadershipRate($userRank);

                if ($leadershipRate > 0) {
                    $amount = $descendantPV * ($leadershipRate / 100);

                    if ($amount > 0) {
                        $commissions[] = [
                            'user_id'          => $user->id,
                            'from_user_id'     => $descendantId,
                            'period'           => $period->period,
                            'type'             => 'leadership',
                            'source'           => 'period',
                            'amount'           => $amount,
                            'percentage'       => $leadershipRate,
                            'pv_used'          => $descendantPV,
                            'generation'       => $generation,
                            'calculation_type' => 'period',
                            'status'           => 'pending',
                            'description'      => "Leadership Bonus Génération {$generation} ({$leadershipRate}% sur {$descendantPV} PV pour {$period->period}) - " . ($descendant?->name ?? 'N/A'),
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }
                }
            }
        }

        return $commissions;
    }

    // ═══════════════════════════════════════════════════════════════
    // PARCOURS DE L'ARBRE (avec exclusion)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Récupère tous les descendants avec leurs ancêtres pour l'exclusion.
     *
     * Règle d'exclusion : si un descendant a un rank >= buyer → sa branche est exclue.
     *
     * @return array<int, array{generation:int, descendant_id:int, descendant_rank:int, is_excluded:bool}>
     */
    private function getAllDescendantsWithAncestors(User $user, int $maxGenerations = 12): array
    {
        $descendants = [];
        $currentGeneration = 1;
        $userRank = $user->rank_level ?? 1;

        $currentLevel = [[
            'id' => $user->id,
            'rank' => $userRank,
            'is_excluded' => false,
        ]];
        $processedIds = [$user->id];

        while ($currentGeneration <= $maxGenerations && !empty($currentLevel)) {
            $nextLevel = [];
            $currentIds = array_column($currentLevel, 'id');

            $children = User::whereIn('parrain_id', $currentIds)
                ->where('is_active', true)
                ->get();

            foreach ($children as $child) {
                if (in_array($child->id, $processedIds)) continue;

                // Retrouve le parent dans le niveau courant
                $parent = null;
                foreach ($currentLevel as $p) {
                    if ($p['id'] == $child->parrain_id) {
                        $parent = $p;
                        break;
                    }
                }

                if (!$parent) continue;

                $childRank = $child->rank_level ?? 1;
                $isExcluded = $parent['is_excluded'];

                // Exclusion : si le descendant a un rank >= buyer
                if (!$isExcluded && $childRank >= $userRank) {
                    $isExcluded = true;
                }

                $descendants[] = [
                    'generation'      => $currentGeneration,
                    'descendant_id'   => $child->id,
                    'descendant_rank' => $childRank,
                    'is_excluded'     => $isExcluded,
                ];

                $nextLevel[] = [
                    'id' => $child->id,
                    'rank' => $childRank,
                    'is_excluded' => $isExcluded,
                ];
                $processedIds[] = $child->id;
            }

            $currentLevel = $nextLevel;
            $currentGeneration++;
        }

        return $descendants;
    }

    // ═══════════════════════════════════════════════════════════════
    // BARÈMES
    // ═══════════════════════════════════════════════════════════════

    private function getDirectRate($rankLevel): float
    {
        $rates = [1=>0, 2=>0, 3=>22, 4=>26, 5=>30, 6=>34, 7=>40, 8=>43, 9=>45];
        return $rates[$rankLevel] ?? 0;
    }

    private function getLeadershipRate($rankLevel): float
    {
        $rates = [5=>0.5, 6=>1.1, 7=>1.8, 8=>2.6, 9=>3.5];
        return $rates[$rankLevel] ?? 0;
    }

    private function getMonthlyPVRequirements(): array
    {
        return [
            1 => ['personal' => 0,   'group' => 0],
            2 => ['personal' => 10,  'group' => 0],
            3 => ['personal' => 20,  'group' => 0],
            4 => ['personal' => 25,  'group' => 0],
            5 => ['personal' => 30,  'group' => 500],
            6 => ['personal' => 50,  'group' => 1000],
            7 => ['personal' => 100, 'group' => 2000],
            8 => ['personal' => 180, 'group' => 3000],
            9 => ['personal' => 300, 'group' => 5000],
        ];
    }
}