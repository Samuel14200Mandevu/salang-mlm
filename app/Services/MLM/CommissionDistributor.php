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

        // Récupérer le pourcentage du parrain selon son grade
        $sponsorPercentage = $sponsor->rank ? $sponsor->rank->bonus_percentage : 6;

        // Calculer le montant
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
        $buyerPercentage = $buyer->rank ? $buyer->rank->bonus_percentage : 0;

        $current = $buyer->parrain;
        $generation = 1;

        while ($current && $generation <= 9) {
            $currentPercentage = $current->rank ? $current->rank->bonus_percentage : 0;

            // La différence entre le % du parrain et celui de l'acheteur
            $difference = max(0, $currentPercentage - $buyerPercentage);

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
        
        // Taux de leadership par niveau
        $leadershipRates = [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ];

        // Conditions de PV pour le leadership
        $leadershipConditions = [
            5 => ['personal_pv' => 30, 'group_pv' => 500],
            6 => ['personal_pv' => 50, 'group_pv' => 1000],
            7 => ['personal_pv' => 100, 'group_pv' => 2000],
            8 => ['personal_pv' => 180, 'group_pv' => 3000],
            9 => ['personal_pv' => 300, 'group_pv' => 5000],
        ];

        $current = $buyer->parrain;
        $generation = 1;

        while ($current && $generation <= 9) {
            $rankLevel = $current->rank ? $current->rank->level : 0;

            // Vérifier si le parrain est éligible pour le leadership
            if ($rankLevel >= 5 && isset($leadershipRates[$rankLevel])) {
                // Vérifier les conditions de PV
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
     * Récupérer tous les descendants
     */
    private function getAllDescendants(User $user): array
    {
        $descendants = [];
        
        foreach ($user->filleuls as $filleul) {
            $descendants[] = $filleul;
            $descendants = array_merge($descendants, $this->getAllDescendants($filleul));
        }
        
        return $descendants;
    }
}