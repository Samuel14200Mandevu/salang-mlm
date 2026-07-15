<?php
// app/Services/MLM/CommissionDistributor.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Package;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionDistributor
{
    /**
     * Distribuer les commissions pour un achat
     */
    public function distributeCommissions(User $buyer, Package $package, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        // ✅ Vérifier si l'acheteur est actif
        if (!$buyer->is_active) {
            Log::info('Commissions non distribuées - compte inactif', [
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
            ]);
            return $commissions; // ⛔ Aucune commission
        }

        // 1. Commission Directe (pour le parrain)
        $direct = $this->calculateDirectBonus($buyer, $package, $orderId, $period);
        if ($direct) {
            $commissions[] = $direct;
        }

        // 2. Commissions Indirectes (pour les parrains supérieurs)
        $indirects = $this->calculateIndirectBonuses($buyer, $package, $orderId, $period);
        $commissions = array_merge($commissions, $indirects);

        // 3. Bonus de Leadership
        $leaderships = $this->calculateLeadershipBonuses($buyer, $package, $orderId, $period);
        $commissions = array_merge($commissions, $leaderships);

        return $commissions;
    }

    /**
     * Calculer la commission directe
     */
    private function calculateDirectBonus(User $buyer, Package $package, $orderId, CommissionPeriod $period): ?Commission
    {
        $sponsor = $buyer->parrain;
        if (!$sponsor) return null;

        // ✅ Vérifier que le sponsor est actif
        if (!$sponsor->is_active) {
            Log::info('Commission directe non distribuée - sponsor inactif', [
                'sponsor_id' => $sponsor->id,
                'sponsor_name' => $sponsor->name,
            ]);
            return null;
        }

        $rank = $sponsor->rankObject;
        $sponsorPercentage = $rank ? $rank->bonus_percentage : $this->getDefaultCommissionRate($rank?->level ?? 1);

        $amount = $package->price * ($sponsorPercentage / 100);

        return Commission::create([
            'user_id' => $sponsor->id,
            'from_user_id' => $buyer->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'direct',
            'amount' => $amount,
            'percentage' => $sponsorPercentage,
            'description' => "Commission directe ({$sponsorPercentage}%) pour achat de {$package->name} par {$buyer->name}",
            'order_id' => $orderId,
            'package_id' => $package->id,
            'generation' => 1,
            'calculation_type' => 'automatic',
            'status' => 'pending',
        ]);
    }

    /**
     * Calculer les commissions indirectes (différence)
     */
    private function calculateIndirectBonuses(User $buyer, Package $package, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        // ✅ Le pourcentage de l'acheteur
        $buyerRank = $buyer->rankObject;
        $buyerPercentage = $buyerRank ? $buyerRank->bonus_percentage : 0;

        $current = $buyer->parrain;
        $generation = 1;
        $previousPercentage = $buyerPercentage;
        $processed = [];

        while ($current && $generation <= 9) {
            // ✅ Éviter les boucles infinies
            if (in_array($current->id, $processed)) {
                break;
            }
            $processed[] = $current->id;

            // ✅ Vérifier que le parrain est actif
            if (!$current->is_active) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $currentRank = $current->rankObject;
            $currentPercentage = $currentRank ? $currentRank->bonus_percentage : 0;

            // ✅ Différence entre le pourcentage actuel et le précédent
            $difference = max(0, $currentPercentage - $previousPercentage);

            if ($difference > 0) {
                $amount = $package->price * ($difference / 100);

                $commissions[] = Commission::create([
                    'user_id' => $current->id,
                    'from_user_id' => $buyer->id,
                    'commission_period_id' => $period->id,
                    'period' => $period->period,
                    'type' => 'indirect',
                    'amount' => $amount,
                    'percentage' => $difference,
                    'description' => "Commission indirecte génération {$generation} ({$difference}%) pour achat de {$package->name}",
                    'order_id' => $orderId,
                    'package_id' => $package->id,
                    'generation' => $generation,
                    'calculation_type' => 'automatic',
                    'status' => 'pending',
                ]);
            }

            $previousPercentage = $currentPercentage;
            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
    }

    /**
     * Calculer les bonus de leadership
     */
    private function calculateLeadershipBonuses(User $buyer, Package $package, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $leadershipRates = $this->getLeadershipRates();
        $leadershipConditions = $this->getLeadershipConditions();

        $current = $buyer->parrain;
        $generation = 1;
        $processed = [];

        while ($current && $generation <= 9) {
            if (in_array($current->id, $processed)) {
                break;
            }
            $processed[] = $current->id;

            // ✅ Vérifier que le parrain est actif
            if (!$current->is_active) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $currentRank = $current->rankObject;
            $rankLevel = $currentRank ? $currentRank->level : 0;

            if ($rankLevel >= 5 && isset($leadershipRates[$rankLevel])) {
                $conditions = $leadershipConditions[$rankLevel];

                if ($current->monthly_pv >= $conditions['personal_pv'] &&
                    $this->getGroupMonthlyPV($current) >= $conditions['group_pv']) {

                    $rate = $leadershipRates[$rankLevel];
                    $amount = $package->price * ($rate / 100);

                    $commissions[] = Commission::create([
                        'user_id' => $current->id,
                        'from_user_id' => $buyer->id,
                        'commission_period_id' => $period->id,
                        'period' => $period->period,
                        'type' => 'leadership',
                        'amount' => $amount,
                        'percentage' => $rate,
                        'description' => "Leadership niveau {$rankLevel} ({$rate}%) génération {$generation}",
                        'order_id' => $orderId,
                        'package_id' => $package->id,
                        'generation' => $generation,
                        'calculation_type' => 'automatic',
                        'status' => 'pending',
                    ]);
                }
            }

            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
    }

    /**
     * Obtenir les taux de commission par défaut
     */
    private function getDefaultCommissionRate(int $level): float
    {
        $rates = [
            1 => 6,
            2 => 10,
            3 => 14,
            4 => 18,
            5 => 22,
            6 => 26,
            7 => 30,
            8 => 34,
            9 => 38,
        ];
        return $rates[$level] ?? 6;
    }

    /**
     * Obtenir les taux de leadership
     */
    private function getLeadershipRates(): array
    {
        return [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ];
    }

    /**
     * Obtenir les conditions de leadership
     */
    private function getLeadershipConditions(): array
    {
        return [
            5 => ['personal_pv' => 30, 'group_pv' => 500],
            6 => ['personal_pv' => 50, 'group_pv' => 1000],
            7 => ['personal_pv' => 100, 'group_pv' => 2000],
            8 => ['personal_pv' => 180, 'group_pv' => 3000],
            9 => ['personal_pv' => 300, 'group_pv' => 5000],
        ];
    }

    /**
     * Calculer le PV mensuel du groupe
     */
    private function getGroupMonthlyPV(User $user): int
    {
        $total = 0;
        $descendants = $this->getAllDescendants($user);

        foreach ($descendants as $descendant) {
            $total += $descendant->monthly_pv;
        }

        return $total;
    }

    /**
     * Récupérer tous les descendants (avec protection)
     */
    private function getAllDescendants(User $user, int $maxDepth = 20, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        $descendants = [];
        $processed = [$user->id];

        foreach ($user->filleuls as $filleul) {
            if (in_array($filleul->id, $processed)) {
                continue;
            }
            $processed[] = $filleul->id;
            $descendants[] = $filleul;
            $descendants = array_merge($descendants, $this->getAllDescendants($filleul, $maxDepth, $currentDepth + 1));
        }

        return $descendants;
    }

    /**
     * Recalculer les commissions pour une période
     */
    public function recalculateCommissionsForPeriod(string $period): array
    {
        $periodObj = CommissionPeriod::where('period', $period)->first();
        if (!$periodObj) {
            return ['error' => 'Period not found'];
        }

        DB::beginTransaction();

        try {
            Commission::where('commission_period_id', $periodObj->id)->delete();

            $orders = \App\Models\Order::whereBetween('paid_at', [
                $periodObj->start_date,
                $periodObj->end_date
            ])->where('payment_status', 'completed')->get();

            $totalCommissions = 0;
            $commissionCount = 0;

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $commissions = $this->distributeCommissions(
                                $order->user,
                                $package,
                                $order->id,
                                $periodObj
                            );

                            foreach ($commissions as $commission) {
                                $totalCommissions += $commission->amount;
                                $commissionCount++;
                            }
                        }
                    }
                }
            }

            $periodObj->total_commissions = $totalCommissions;
            $periodObj->save();

            DB::commit();

            return [
                'period' => $period,
                'commissions_generated' => $commissionCount,
                'total_amount' => $totalCommissions,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recalculating commissions', [
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}