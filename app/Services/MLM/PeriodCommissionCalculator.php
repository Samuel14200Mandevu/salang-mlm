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

        $alreadyCommissionedOrderIds = Commission::whereNotNull('order_id')
            ->whereIn('type', ['cash_pos', 'sponsor'])
            ->pluck('order_id')
            ->unique()
            ->values()
            ->all();

        // Agrège le PV par user sur la période
        $pvQuery = PVHistory::where('period', $period->period);
        if ($userId) {
            $pvQuery->where('user_id', $userId);
        }

        if ($alreadyCommissionedOrderIds !== []) {
            $pvQuery->where(function ($q) use ($alreadyCommissionedOrderIds) {
                $q->whereNull('order_id')
                    ->orWhereNotIn('order_id', $alreadyCommissionedOrderIds);
            });
        }

        $pvByUser = $pvQuery
            ->select('user_id', DB::raw('SUM(amount) as total_pv'))
            ->groupBy('user_id')
            ->pluck('total_pv', 'user_id')
            ->toArray();

        // Sans PV : supprimer les D/I/L orphelins non payés (ex. PV effacés puis recalcul).
        if (empty($pvByUser)) {
            $orphanQuery = Commission::query()
                ->where('commission_period_id', $period->id)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->whereIn('status', ['pending', 'approved', 'cancelled']);

            if ($userId) {
                // Bonus du membre + I&L des ascendants générés depuis ce membre.
                $orphanQuery->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhere(function ($q2) use ($userId) {
                            $q2->where('from_user_id', $userId)
                                ->whereIn('type', ['indirect', 'leadership']);
                        });
                });
            }

            $orphanQuery->delete();

            return ['created' => 0, 'total_amount' => 0];
        }

        // Calcul mois entier : retirer aussi les D/I/L non payés des membres qui n’ont plus de PV.
        if ($userId === null) {
            $userIdsWithPv = array_map('intval', array_keys($pvByUser));
            Commission::query()
                ->where('commission_period_id', $period->id)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->whereIn('status', ['pending', 'approved', 'cancelled'])
                ->whereNotIn('user_id', $userIdsWithPv)
                ->delete();
        }

        $totalCreated = 0;
        $totalAmount = 0;

        foreach ($pvByUser as $uid => $userMonthlyPV) {
            $user = $allUsers->get($uid);
            if (!$user || $user->user_type !== 'member') {
                continue;
            }

            // ─── 0. Nettoyage : ne touche PAS aux commissions déjà payées (évite double crédit) ───
            Commission::where('user_id', $uid)
                ->where('commission_period_id', $period->id)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->whereIn('status', ['pending', 'approved', 'cancelled'])
                ->delete();

            // Compensation anti-double-paiement : par (type + from_user_id),
            // jamais par type seul — sinon un résidu est crédité sur un autre downline.
            $paidRemaining = $this->paidRemainingByTypeAndSource((int) $uid, (int) $period->id);
            $paidMeta = $this->paidMetaByTypeAndSource((int) $uid, (int) $period->id);

            // ─── 1. BONUS DIRECT (sur monthly_pv total) ───
            $directBonus = $this->calculateDirectBonus($user, (int) $userMonthlyPV, $period);
            if ($directBonus) {
                $fromUserId = (int) ($directBonus['from_user_id'] ?? $uid);
                $directBonus['amount'] = $this->amountAfterPaidSettlement(
                    (float) $directBonus['amount'],
                    (float) ($directBonus['percentage'] ?? 0),
                    (float) ($directBonus['pv_used'] ?? $userMonthlyPV),
                    'direct',
                    $fromUserId,
                    $paidMeta
                );
                $netDirect = round($this->netPendingAfterPaid(
                    $directBonus['amount'],
                    'direct',
                    $fromUserId,
                    $paidRemaining
                ), 2);
                if ($netDirect > 0) {
                    $directBonus['amount'] = $netDirect;
                    Commission::create(array_merge($directBonus, [
                        'commission_period_id' => $period->id,
                    ]));
                    $totalCreated++;
                    $totalAmount += $netDirect;
                }
            }

            // ─── 2. INDIRECT + LEADERSHIP ───
            $commissions = $this->calculateIndirectAndLeadership(
                $user, $period, (int)$userMonthlyPV, $allUsers
            );

            foreach ($commissions as $data) {
                $type = (string) ($data['type'] ?? '');
                $fromUserId = (int) ($data['from_user_id'] ?? 0);
                $net = round($this->netPendingAfterPaid($data['amount'], $type, $fromUserId, $paidRemaining), 2);
                // Évite les pending $0.00 (ex. 4.662 − 4.66 déjà payé → 0.002 stocké en 0.00).
                if ($net <= 0) {
                    continue;
                }
                $data['amount'] = $net;
                Commission::create(array_merge($data, [
                    'commission_period_id' => $period->id,
                ]));
                $totalCreated++;
                $totalAmount += $net;
            }
        }

        return [
            'created' => $totalCreated,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Après un changement de PV sur un membre : recalcule son D/I/L,
     * purge les I&L non payés des ascendants issus de lui, puis recalcule
     * chaque ascendant (I&L restants sur le PV encore présent).
     *
     * @return array{created:int, total_amount:float, ancestors:int, purged_source_il:int}
     */
    public function recalculateSourceAndAncestors(CommissionPeriod $period, int $sourceUserId): array
    {
        $result = $this->calculateForPeriod($period, $sourceUserId);

        // Couvre le cas « encore du PV » : le calcul source ne touche pas
        // les lignes I&L créditées aux ascendants (user_id = ascendant).
        $purgedSourceIl = Commission::query()
            ->where('commission_period_id', $period->id)
            ->where('from_user_id', $sourceUserId)
            ->whereIn('type', ['indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'cancelled'])
            ->delete();

        $created = (int) ($result['created'] ?? 0);
        $totalAmount = (float) ($result['total_amount'] ?? 0);
        $ancestorIds = $this->ancestorIds($sourceUserId);

        foreach ($ancestorIds as $ancestorId) {
            $ancestorResult = $this->calculateForPeriod($period, $ancestorId);
            $created += (int) ($ancestorResult['created'] ?? 0);
            $totalAmount += (float) ($ancestorResult['total_amount'] ?? 0);
        }

        return [
            'created' => $created,
            'total_amount' => $totalAmount,
            'ancestors' => count($ancestorIds),
            'purged_source_il' => (int) $purgedSourceIl,
        ];
    }

    /**
     * @return list<int>
     */
    public function ancestorIds(int $userId, int $maxGenerations = self::MAX_TREE_GENERATIONS): array
    {
        $ids = [];
        $currentId = $userId;

        for ($i = 0; $i < $maxGenerations; $i++) {
            $parrainId = User::query()->where('id', $currentId)->value('parrain_id');
            if (! $parrainId) {
                break;
            }
            $parrainId = (int) $parrainId;
            if (in_array($parrainId, $ids, true)) {
                break;
            }
            $ids[] = $parrainId;
            $currentId = $parrainId;
        }

        return $ids;
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
            'source'           => 'mlm',
            'amount'           => $amount,
            'percentage'       => $rate,
            'pv_used'          => $userMonthlyPV,
            'generation'       => 0,
            'calculation_type' => 'automatic',
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

            $descendantPV = $descendantPVs[$descendantId] ?? 0;
            if ($descendantPV <= 0) {
                continue;
            }

            $descendantRate = $this->getDirectRate($descendantRank);
            $descendant = $allUsers->get($descendantId);
            $branchBlocked = ! empty($data['is_branch_blocked']);

            // ─────────────── INDIRECT (gén. 1-8) ───────────────
            // Jambe entière exclue si un filleul (direct ou indirect) a un
            // grade ≥ upline (is_branch_blocked). Ne s'applique PAS au Leadership.
            if (
                ! $branchBlocked
                && $generation <= self::MAX_GENERATIONS_INDIRECT
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
                            'source'           => 'mlm',
                            'amount'           => $amount,
                            'percentage'       => $rateDifference,
                            'pv_used'          => $descendantPV,
                            'generation'       => $generation,
                            'calculation_type' => 'automatic',
                            'status'           => 'pending',
                            'description'      => "Bonus Indirect Génération {$generation} ({$rateDifference}% sur {$descendantPV} PV pour {$period->period}) - " . ($descendant?->name ?? 'N/A'),
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }
                }
            }

            // ─────────────── LEADERSHIP (gén. 1-12) ───────────────
            // Pas de coupure par grade sur le chemin ni par branch block :
            // uniquement max 12 gén. Le barème L (taux) reste indexé par le
            // grade du bénéficiaire (0 % en dessous du grade 5).
            if ($generation <= self::MAX_GENERATIONS_LEADERSHIP) {
                $leadershipRate = $this->getLeadershipRate($userRank);

                if ($leadershipRate > 0) {
                    $amount = $descendantPV * ($leadershipRate / 100);

                    if ($amount > 0) {
                        $commissions[] = [
                            'user_id'          => $user->id,
                            'from_user_id'     => $descendantId,
                            'period'           => $period->period,
                            'type'             => 'leadership',
                            'source'           => 'mlm',
                            'amount'           => $amount,
                            'percentage'       => $leadershipRate,
                            'pv_used'          => $descendantPV,
                            'generation'       => $generation,
                            'calculation_type' => 'automatic',
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
     * Récupère tous les descendants avec flags d'exclusion.
     *
     * - is_excluded : le nœud (ou un ancêtre sur le chemin) a un rank ≥ buyer
     *   → sous-arbre (historique / compression chemin).
     * - is_branch_blocked : un filleul direct OU indirect de la jambe a un rank ≥ buyer
     *   → toute la jambe hors bonus Indirect ; les autres jambes restent éligibles.
     *
     * Leadership ignore les deux flags : seule la profondeur (max 12 gén.) compte.
     * Les règles différentiel de taux / grade min bénéficiaire restent dans
     * calculateIndirectAndLeadership.
     *
     * @return array<int, array{generation:int, descendant_id:int, descendant_rank:int, is_excluded:bool, is_branch_blocked:bool}>
     */
    private function getAllDescendantsWithAncestors(User $user, int $maxGenerations = 12): array
    {
        $raw = [];
        $currentGeneration = 1;
        $userRank = (int) ($user->rank_level ?? 1);

        $currentLevel = [[
            'id' => $user->id,
            'rank' => $userRank,
            'is_excluded' => false,
            'branch_id' => null,
        ]];
        $processedIds = [$user->id];
        $blockedBranches = [];

        while ($currentGeneration <= $maxGenerations && ! empty($currentLevel)) {
            $nextLevel = [];
            $currentIds = array_column($currentLevel, 'id');

            $children = User::whereIn('parrain_id', $currentIds)
                ->where('is_active', true)
                ->get();

            foreach ($children as $child) {
                if (in_array($child->id, $processedIds, true)) {
                    continue;
                }

                $parent = null;
                foreach ($currentLevel as $p) {
                    if ($p['id'] == $child->parrain_id) {
                        $parent = $p;
                        break;
                    }
                }

                if (! $parent) {
                    continue;
                }

                $childRank = (int) ($child->rank_level ?? 1);
                $isExcluded = (bool) $parent['is_excluded'];
                if (! $isExcluded && $childRank >= $userRank) {
                    $isExcluded = true;
                }

                // Jambe = filleul direct (gén. 1) + toute sa descendance
                $branchId = $currentGeneration === 1
                    ? (int) $child->id
                    : (int) $parent['branch_id'];

                if ($childRank >= $userRank) {
                    $blockedBranches[$branchId] = true;
                }

                $raw[] = [
                    'generation' => $currentGeneration,
                    'descendant_id' => (int) $child->id,
                    'descendant_rank' => $childRank,
                    'is_excluded' => $isExcluded,
                    'branch_id' => $branchId,
                ];

                $nextLevel[] = [
                    'id' => $child->id,
                    'rank' => $childRank,
                    'is_excluded' => $isExcluded,
                    'branch_id' => $branchId,
                ];
                $processedIds[] = $child->id;
            }

            $currentLevel = $nextLevel;
            $currentGeneration++;
        }

        $descendants = [];
        foreach ($raw as $row) {
            $descendants[] = [
                'generation' => $row['generation'],
                'descendant_id' => $row['descendant_id'],
                'descendant_rank' => $row['descendant_rank'],
                'is_excluded' => $row['is_excluded'],
                'is_branch_blocked' => isset($blockedBranches[$row['branch_id']]),
            ];
        }

        return $descendants;
    }

    /**
     * Montants déjà payés indexés par "type|from_user_id".
     *
     * @return array<string, float>
     */
    private function paidRemainingByTypeAndSource(int $userId, int $periodId): array
    {
        $meta = $this->paidMetaByTypeAndSource($userId, $periodId);
        $paid = [];
        foreach ($meta as $key => $row) {
            $paid[$key] = (float) ($row['amount'] ?? 0);
        }

        return $paid;
    }

    /**
     * Métadonnées des lignes déjà payées : montant, PV max, taux max.
     *
     * @return array<string, array{amount: float, pv_used: float, percentage: float}>
     */
    private function paidMetaByTypeAndSource(int $userId, int $periodId): array
    {
        $rows = Commission::query()
            ->where('user_id', $userId)
            ->where('commission_period_id', $periodId)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->selectRaw('type, COALESCE(from_user_id, 0) as from_user_id, SUM(amount) as total_paid, MAX(pv_used) as max_pv, MAX(percentage) as max_pct')
            ->groupBy('type', 'from_user_id')
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $key = $this->paidRemainingKey((string) $row->type, (int) $row->from_user_id);
            $meta[$key] = [
                'amount' => (float) $row->total_paid,
                'pv_used' => (float) $row->max_pv,
                'percentage' => (float) $row->max_pct,
            ];
        }

        return $meta;
    }

    private function paidRemainingKey(string $type, int $fromUserId): string
    {
        return $type.'|'.$fromUserId;
    }

    /**
     * Si une ligne est déjà payée pour le même volume de PV, on ne crée pas de
     * « rattrapage » uniquement dû à une hausse de taux (ex. passage 26% → 30%).
     * Les PV supplémentaires restent dus au taux courant.
     *
     * @param  array<string, array{amount: float, pv_used: float, percentage: float}>  $paidMeta
     */
    private function amountAfterPaidSettlement(
        float $calculatedAmount,
        float $currentPercentage,
        float $currentPvUsed,
        string $type,
        int $fromUserId,
        array $paidMeta
    ): float {
        if ($calculatedAmount <= 0) {
            return 0.0;
        }

        $key = $this->paidRemainingKey($type, $fromUserId);
        $paid = $paidMeta[$key] ?? null;
        if ($paid === null || ($paid['amount'] ?? 0) <= 0) {
            return $calculatedAmount;
        }

        $paidPv = (float) ($paid['pv_used'] ?? 0);
        $paidPct = (float) ($paid['percentage'] ?? 0);

        // Même volume (ou moins) déjà soldé → geler le taux au max déjà payé
        // pour éviter un pending de rattrapage après promotion de grade.
        if ($paidPv > 0 && $currentPvUsed <= ($paidPv + 0.00001) && $paidPct > 0) {
            $lockedPct = min($currentPercentage, $paidPct);
            if ($lockedPct < $currentPercentage) {
                return max(0.0, $currentPvUsed * ($lockedPct / 100));
            }
        }

        return $calculatedAmount;
    }

    /**
     * Déduit du montant calculé ce qui est déjà payé pour le même type ET la même source
     * (from_user_id). Évite de recréer un pending déjà crédité, sans reporter le résidu
     * sur un autre downline.
     *
     * @param  array<string, float>  $paidRemaining
     */
    private function netPendingAfterPaid(
        float|int|string $amount,
        string $type,
        int $fromUserId,
        array &$paidRemaining
    ): float {
        $amount = (float) $amount;
        if ($amount <= 0 || $type === '') {
            return 0.0;
        }

        $key = $this->paidRemainingKey($type, $fromUserId);
        $alreadyPaid = (float) ($paidRemaining[$key] ?? 0);
        if ($alreadyPaid <= 0) {
            return $amount;
        }

        $deduct = min($amount, $alreadyPaid);
        $paidRemaining[$key] = $alreadyPaid - $deduct;

        return max(0.0, $amount - $deduct);
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