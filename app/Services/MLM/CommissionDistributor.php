<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Package;
use App\Models\Product;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Jobs\UpdateRanks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CommissionDistributor
{
    /**
     * Taux de commission par niveau (Bonus Direct)
     * Bonus Direct : Calculé sur le PV mensuel du sponsor
     */
    private function getCommissionRate(int $level): float
    {
        $rates = [
            1 => 0,   // Distributeur - Pas de commission directe
            2 => 0,   // Qualification - Pas de commission directe
            3 => 22,  // Cumul Directeur - Commission directe 22%
            4 => 26,  // Directeur - Commission directe 26%
            5 => 30,  // Manager Senior - Commission directe 30%
            6 => 34,  // Directeur Envolée - Commission directe 34%
            7 => 40,  // Saphire Manager - Commission directe 40%
            8 => 43,  // Diamant Bleu - Commission directe 43%
            9 => 45,  // Perle Diamant - Commission directe 45%
        ];
        return $rates[$level] ?? 0;
    }

    /**
     * Obtenir le taux de commission en fonction du PV mensuel du sponsor
     */
    private function getCommissionRateBasedOnMonthlyPV(User $sponsor): float
    {
        $rank = $sponsor->rankObject;
        $rankLevel = $rank ? $rank->level : 1;
        $monthlyPV = $sponsor->monthly_pv ?? 0;
        
        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];
        
        if ($monthlyPV < $req['personal']) {
            Log::info('Sponsor ne remplit pas les conditions de PV mensuel', [
                'sponsor_id' => $sponsor->id,
                'sponsor_level' => $rankLevel,
                'monthly_pv' => $monthlyPV,
                'required' => $req['personal'],
            ]);
            return 0;
        }
        
        if ($req['group'] > 0 && ($sponsor->team_pv ?? 0) < $req['group']) {
            Log::info('Sponsor ne remplit pas les conditions de PV groupe', [
                'sponsor_id' => $sponsor->id,
                'team_pv' => $sponsor->team_pv,
                'required' => $req['group'],
            ]);
            return 0;
        }
        
        return $this->getCommissionRate($rankLevel);
    }

    /**
     * Conditions de PV mensuel pour toucher les commissions
     */
    private function getMonthlyPVRequirements(): array
    {
        return [
            1 => ['personal' => 0, 'group' => 0],
            2 => ['personal' => 10, 'group' => 0],
            3 => ['personal' => 20, 'group' => 0],
            4 => ['personal' => 25, 'group' => 0],
            5 => ['personal' => 30, 'group' => 300],
            6 => ['personal' => 50, 'group' => 500],
            7 => ['personal' => 100, 'group' => 1000],
            8 => ['personal' => 200, 'group' => 2000],
            9 => ['personal' => 300, 'group' => 3000],
        ];
    }

    /**
     * Vérifier si un sponsor a déjà reçu le bonus pour ce filleul
     */
    private function hasReceivedSponsorBonus(User $sponsor, User $buyer): bool
    {
        return Commission::where('user_id', $sponsor->id)
            ->where('from_user_id', $buyer->id)
            ->where('type', 'sponsor')
            ->exists();
    }

    /**
     * Récupérer les données d'un item (package ou produit)
     */
    private function getItemData($item)
    {
        if ($item instanceof Package || $item instanceof Product) {
            return $item;
        }

        if (is_array($item)) {
            $type = $item['type'] ?? null;
            $id = $item['id'] ?? null;

            if ($type === 'package' && $id) {
                return Package::find($id);
            }

            if ($type === 'product' && $id) {
                return Product::find($id);
            }
        }

        return null;
    }

    /**
     * Récupérer le PV d'un item
     */
    private function getItemPV($item): int
    {
        if ($item instanceof Package || $item instanceof Product) {
            return $item->pv_value ?? 0;
        }

        if (is_array($item)) {
            return $item['pv_value'] ?? 0;
        }

        return 0;
    }

    /**
     * Récupérer le prix d'un item
     */
    private function getItemPrice($item): float
    {
        if ($item instanceof Package || $item instanceof Product) {
            return $item->price ?? 0;
        }

        if (is_array($item)) {
            return $item['price'] ?? 0;
        }

        return 0;
    }

    /**
     * Récupérer le nom d'un item
     */
    private function getItemName($item): string
    {
        if ($item instanceof Package || $item instanceof Product) {
            return $item->name ?? 'Item';
        }

        if (is_array($item)) {
            return $item['name'] ?? 'Item';
        }

        return 'Item';
    }

    /**
     * Récupérer le type d'un item
     */
    private function getItemType($item): string
    {
        if ($item instanceof Package) {
            return 'package';
        }

        if ($item instanceof Product) {
            return 'product';
        }

        if (is_array($item)) {
            return $item['type'] ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * Récupérer l'ID d'un item
     */
    private function getItemId($item): ?int
    {
        if ($item instanceof Package || $item instanceof Product) {
            return $item->id;
        }

        if (is_array($item)) {
            return $item['id'] ?? null;
        }

        return null;
    }

    /**
     * Distribuer les commissions pour un achat
     * 
     * 1. Sponsor Bonus : Commission de parrainage - UNIQUEMENT lors de l'activation (package)
     * 2. Bonus Direct : Calculé sur le PV mensuel du sponsor
     * 3. Bonus Indirect : Commission sur l'effort des descendants (générations 2+)
     * 4. Leadership Bonus : Commission supplémentaire pour les leaders (niveaux 5+)
     */
    public function distributeCommissions(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        // ✅ SUPPRIMÉ : La vérification de is_active pour permettre l'activation manuelle
        // Les commissions sponsor sont distribuées même si le compte n'est pas encore actif

        $itemData = $this->getItemData($item);
        if (!$itemData) {
            Log::warning('Item non trouvé pour la distribution des commissions', [
                'item' => $item,
            ]);
            return $commissions;
        }

        $itemType = $this->getItemType($item);
        $isPackage = ($itemType === 'package');

        // ============================================================
        // 1. SPONSOR BONUS - UNIQUEMENT POUR L'ACTIVATION AVEC PACKAGE
        // ============================================================
        $sponsor = $buyer->parrain;
        
        if ($sponsor && $sponsor->is_active && $isPackage) {
            // Vérifier si le sponsor a déjà reçu le bonus pour ce filleul
            $hasSponsorBonus = $this->hasReceivedSponsorBonus($sponsor, $buyer);

            if (!$hasSponsorBonus) {
                $sponsorBonus = $this->calculateSponsorBonus($buyer, $itemData, $orderId, $period);
                if ($sponsorBonus) {
                    $commissions[] = $sponsorBonus;
                    Log::info('Sponsor bonus distribué pour activation avec package', [
                        'sponsor_id' => $sponsor->id,
                        'buyer_id' => $buyer->id,
                        'package_name' => $this->getItemName($item),
                        'amount' => $sponsorBonus->amount,
                        'activation_method' => $buyer->activation_method ?? 'code',
                    ]);
                }
            } else {
                Log::info('Sponsor bonus non distribué - déjà existant', [
                    'sponsor_id' => $sponsor->id,
                    'buyer_id' => $buyer->id,
                    'has_bonus' => $hasSponsorBonus,
                ]);
            }
        } else {
            if ($sponsor && $sponsor->is_active && !$isPackage) {
                Log::info('Sponsor bonus non distribué - achat produit, pas un package', [
                    'sponsor_id' => $sponsor->id,
                    'buyer_id' => $buyer->id,
                    'item_type' => $itemType,
                    'item_name' => $this->getItemName($item),
                ]);
            }
        }

        // ============================================================
        // 2. BONUS DIRECT - Uniquement si le compte est actif
        // ============================================================
        if ($buyer->is_active) {
            $directs = $this->calculateDirectBonuses($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $directs);

            // ============================================================
            // 3. BONUS INDIRECT
            // ============================================================
            $indirects = $this->calculateIndirectBonuses($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $indirects);

            // ============================================================
            // 4. LEADERSHIP BONUS
            // ============================================================
            $leaderships = $this->calculateLeadershipBonuses($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $leaderships);

            // Déclencher la mise à jour des grades
            $this->triggerRankUpdates($buyer);
        }

        return $commissions;
    }

    /**
     * Déclencher la mise à jour des grades pour l'acheteur et ses parrains
     */
    private function triggerRankUpdates(User $buyer): void
    {
        try {
            dispatch(new UpdateRanks($buyer->id));

            if ($buyer->parrain) {
                dispatch(new UpdateRanks($buyer->parrain->id));
            }

            $current = $buyer->parrain;
            $depth = 0;
            $processed = [];

            while ($current && $depth < 9 && !in_array($current->id, $processed)) {
                $processed[] = $current->id;
                dispatch(new UpdateRanks($current->id));
                $current = $current->parrain;
                $depth++;
            }

            Log::info('Rank updates triggered', [
                'buyer_id' => $buyer->id,
                'ancestors' => count($processed),
            ]);

        } catch (\Exception $e) {
            Log::error('Error triggering rank updates', [
                'buyer_id' => $buyer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 1. SPONSOR BONUS - CORRIGÉ
     * Commission de parrainage - UNE SEULE FOIS lors de l'activation avec package
     * ✅ Ne vérifie plus is_active ni activated_at pour permettre l'activation manuelle
     * ✅ Status 'paid' directement
     */
    private function calculateSponsorBonus(User $buyer, $item, $orderId, CommissionPeriod $period): ?Commission
    {
        $sponsor = $buyer->parrain;
        if (!$sponsor) return null;

        if (!$sponsor->is_active) {
            Log::info('Sponsor bonus non distribué - sponsor inactif', [
                'sponsor_id' => $sponsor->id,
                'sponsor_name' => $sponsor->name,
            ]);
            return null;
        }

        // Vérifier que c'est bien un package (activation)
        $itemType = $this->getItemType($item);
        if ($itemType !== 'package') {
            Log::info('Sponsor bonus non distribué - pas un package', [
                'buyer_id' => $buyer->id,
                'item_type' => $itemType,
            ]);
            return null;
        }

        // ✅ Vérifier que le sponsor n'a pas déjà reçu le bonus
        if ($this->hasReceivedSponsorBonus($sponsor, $buyer)) {
            Log::info('Sponsor bonus déjà distribué - ignoré', [
                'sponsor_id' => $sponsor->id,
                'buyer_id' => $buyer->id,
            ]);
            return null;
        }

        // ✅ SUPPRESSION DE LA VÉRIFICATION DE activated_at
        // ✅ SUPPRESSION DE LA VÉRIFICATION DE is_active

        $rank = $sponsor->rankObject;
        $rankLevel = $rank ? $rank->level : 1;

        // Vérifier les conditions de PV mensuel du sponsor
        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

        if (($sponsor->monthly_pv ?? 0) < $req['personal']) {
            Log::info('Sponsor bonus non distribué - PV personnel insuffisant', [
                'sponsor_id' => $sponsor->id,
                'monthly_pv' => $sponsor->monthly_pv,
                'required' => $req['personal'],
            ]);
            return null;
        }

        if ($req['group'] > 0 && ($sponsor->team_pv ?? 0) < $req['group']) {
            Log::info('Sponsor bonus non distribué - PV groupe insuffisant', [
                'sponsor_id' => $sponsor->id,
                'team_pv' => $sponsor->team_pv,
                'required' => $req['group'],
            ]);
            return null;
        }

        $itemName = $this->getItemName($item);
        $itemPrice = $this->getItemPrice($item);
        $itemId = $this->getItemId($item);

        if ($rankLevel == 1) {
            $amount = 10;
            $percentage = null;
            $description = "Sponsor bonus (10$ fixe) pour activation de {$buyer->name} avec {$itemName}";
        } else {
            $amount = $itemPrice * 0.30;
            $percentage = 30;
            $description = "Sponsor bonus (30%) pour activation de {$buyer->name} avec {$itemName}";
        }

        Log::info('Création Sponsor Bonus', [
            'sponsor_id' => $sponsor->id,
            'buyer_id' => $buyer->id,
            'rank_level' => $rankLevel,
            'amount' => $amount,
            'package' => $itemName,
            'activation_method' => $buyer->activation_method ?? 'code',
        ]);

        return Commission::create([
            'user_id' => $sponsor->id,
            'from_user_id' => $buyer->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'sponsor',
            'amount' => $amount,
            'percentage' => $percentage ?? 0,
            'description' => $description,
            'order_id' => $orderId,
            'package_id' => $itemId,
            'product_id' => null,
            'generation' => 1,
            'calculation_type' => 'automatic',
            'status' => 'paid', // ✅ Payé immédiatement
            'paid_at' => now(),  // ✅ Date de paiement
        ]);
    }

    /**
     * 2. BONUS DIRECT - CALCULÉ SUR LE PV MENSUEL DU SPONSOR
     */
    private function calculateDirectBonuses(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;

        if ($buyerLevel < 3) {
            Log::info('Pas de bonus direct - acheteur niveau < 3', [
                'buyer_id' => $buyer->id,
                'buyer_level' => $buyerLevel,
            ]);
            return $commissions;
        }

        $sponsor = $buyer->parrain;
        if (!$sponsor) {
            Log::info('Pas de bonus direct - pas de sponsor', ['buyer_id' => $buyer->id]);
            return $commissions;
        }

        if (!$sponsor->is_active) {
            Log::info('Pas de bonus direct - sponsor inactif', ['sponsor_id' => $sponsor->id]);
            return $commissions;
        }

        $sponsorRank = $sponsor->rankObject;
        $sponsorLevel = $sponsorRank ? $sponsorRank->level : 1;

        if ($sponsorLevel < 3) {
            Log::info('Pas de bonus direct - sponsor niveau < 3', [
                'sponsor_id' => $sponsor->id,
                'sponsor_level' => $sponsorLevel,
            ]);
            return $commissions;
        }

        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$sponsorLevel] ?? ['personal' => 0, 'group' => 0];

        if (($sponsor->monthly_pv ?? 0) < $req['personal']) {
            Log::info('Pas de bonus direct - PV personnel insuffisant', [
                'sponsor_id' => $sponsor->id,
                'monthly_pv' => $sponsor->monthly_pv,
                'required' => $req['personal'],
            ]);
            return $commissions;
        }

        if ($req['group'] > 0 && ($sponsor->team_pv ?? 0) < $req['group']) {
            Log::info('Pas de bonus direct - PV groupe insuffisant', [
                'sponsor_id' => $sponsor->id,
                'team_pv' => $sponsor->team_pv,
                'required' => $req['group'],
            ]);
            return $commissions;
        }

        $sponsorRate = $this->getCommissionRate($sponsorLevel);
        
        if ($sponsorRate <= 0) {
            Log::info('Pas de bonus direct - taux à 0', [
                'sponsor_id' => $sponsor->id,
                'sponsor_level' => $sponsorLevel,
            ]);
            return $commissions;
        }

        $sponsorMonthlyPV = $sponsor->monthly_pv ?? 0;

        if ($sponsorMonthlyPV > 0 && $sponsorRate > 0) {
            $amount = $sponsorMonthlyPV * ($sponsorRate / 100);

            $itemName = $this->getItemName($item);
            $itemType = $this->getItemType($item);
            $itemId = $this->getItemId($item);

            $commissions[] = Commission::create([
                'user_id' => $sponsor->id,
                'from_user_id' => $buyer->id,
                'commission_period_id' => $period->id,
                'period' => $period->period,
                'type' => 'direct',
                'amount' => $amount,
                'percentage' => $sponsorRate,
                'description' => "Bonus Direct ({$sponsorRate}%) sur PV Mensuel de {$sponsorMonthlyPV} PV pour parrainage de {$buyer->name} ({$itemName})",
                'order_id' => $orderId,
                'package_id' => $itemType === 'package' ? $itemId : null,
                'product_id' => $itemType === 'product' ? $itemId : null,
                'generation' => 1,
                'calculation_type' => 'automatic',
                'status' => 'pending',
            ]);

            Log::info('Bonus direct créé sur PV mensuel du sponsor', [
                'sponsor_id' => $sponsor->id,
                'sponsor_monthly_pv' => $sponsorMonthlyPV,
                'amount' => $amount,
                'rate' => $sponsorRate,
            ]);
        }

        return $commissions;
    }

    /**
     * 3. BONUS INDIRECT
     */
    private function calculateIndirectBonuses(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;
        $buyerRate = $this->getCommissionRate($buyerLevel);

        $pvAmount = $this->getItemPV($item);

        if ($pvAmount <= 0 || $buyerLevel < 3) {
            return $commissions;
        }

        $current = $buyer->parrain;
        $generation = 2;
        $previousRate = $buyerRate;
        $processed = [];

        while ($current && $generation <= 9) {
            if (in_array($current->id, $processed)) {
                break;
            }
            $processed[] = $current->id;

            if (!$current->is_active) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $currentRank = $current->rankObject;
            $currentLevel = $currentRank ? $currentRank->level : 1;

            if ($currentLevel < 3) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $requirements = $this->getMonthlyPVRequirements();
            $req = $requirements[$currentLevel] ?? ['personal' => 0, 'group' => 0];

            if (($current->monthly_pv ?? 0) < $req['personal']) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            if ($req['group'] > 0 && ($current->team_pv ?? 0) < $req['group']) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $currentRate = $this->getCommissionRate($currentLevel);
            $difference = max(0, $currentRate - $previousRate);

            if ($difference > 0) {
                $amount = $pvAmount * ($difference / 100);

                if ($amount > 0) {
                    $itemName = $this->getItemName($item);
                    $itemType = $this->getItemType($item);
                    $itemId = $this->getItemId($item);

                    $commissions[] = Commission::create([
                        'user_id' => $current->id,
                        'from_user_id' => $buyer->id,
                        'commission_period_id' => $period->id,
                        'period' => $period->period,
                        'type' => 'indirect',
                        'amount' => $amount,
                        'percentage' => $difference,
                        'description' => "Bonus Indirect Génération {$generation} ({$difference}%) - PV Mensuel {$current->monthly_pv} sur PV de {$pvAmount} pour {$buyer->name} ({$itemName})",
                        'order_id' => $orderId,
                        'package_id' => $itemType === 'package' ? $itemId : null,
                        'product_id' => $itemType === 'product' ? $itemId : null,
                        'generation' => $generation,
                        'calculation_type' => 'automatic',
                        'status' => 'pending',
                    ]);

                    Log::info('Bonus indirect créé', [
                        'user_id' => $current->id,
                        'generation' => $generation,
                        'amount' => $amount,
                        'difference' => $difference,
                    ]);
                }
            }

            $previousRate = $currentRate;
            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
    }

    /**
     * 4. LEADERSHIP BONUS
     */
    private function calculateLeadershipBonuses(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $leadershipRates = [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ];

        $pvAmount = $this->getItemPV($item);

        if ($pvAmount <= 0) {
            return $commissions;
        }

        $current = $buyer->parrain;
        $generation = 1;
        $processed = [];

        while ($current && $generation <= 9) {
            if (in_array($current->id, $processed)) {
                break;
            }
            $processed[] = $current->id;

            if (!$current->is_active) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            $currentRank = $current->rankObject;
            $rankLevel = $currentRank ? $currentRank->level : 0;

            if ($rankLevel >= 5 && isset($leadershipRates[$rankLevel])) {
                $requirements = $this->getMonthlyPVRequirements();
                $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

                if (($current->monthly_pv ?? 0) >= $req['personal'] &&
                    ($req['group'] == 0 || ($current->team_pv ?? 0) >= $req['group'])) {

                    $rate = $leadershipRates[$rankLevel];
                    $amount = $pvAmount * ($rate / 100);

                    if ($amount > 0) {
                        $itemName = $this->getItemName($item);
                        $itemType = $this->getItemType($item);
                        $itemId = $this->getItemId($item);

                        $commissions[] = Commission::create([
                            'user_id' => $current->id,
                            'from_user_id' => $buyer->id,
                            'commission_period_id' => $period->id,
                            'period' => $period->period,
                            'type' => 'leadership',
                            'amount' => $amount,
                            'percentage' => $rate,
                            'description' => "Leadership Niveau {$rankLevel} ({$rate}%) - PV Mensuel {$current->monthly_pv} Génération {$generation} sur PV de {$pvAmount} pour {$buyer->name} ({$itemName})",
                            'order_id' => $orderId,
                            'package_id' => $itemType === 'package' ? $itemId : null,
                            'product_id' => $itemType === 'product' ? $itemId : null,
                            'generation' => $generation,
                            'calculation_type' => 'automatic',
                            'status' => 'pending',
                        ]);

                        Log::info('Leadership bonus créé', [
                            'user_id' => $current->id,
                            'rank_level' => $rankLevel,
                            'generation' => $generation,
                            'amount' => $amount,
                            'rate' => $rate,
                        ]);
                    }
                }
            }

            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
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
                    $itemData = null;

                    if ($item->package_id) {
                        $itemData = Package::find($item->package_id);
                    } elseif ($item->product_id) {
                        $itemData = Product::find($item->product_id);
                    }

                    if ($itemData) {
                        $commissions = $this->distributeCommissions(
                            $order->user,
                            $itemData,
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

    /**
     * Distribuer les commissions pour une commande entière
     */
    public function distributeCommissionsForOrder($order): array
    {
        $period = CommissionPeriod::getCurrentPeriod();
        if (!$period) {
            Log::error('Aucune période de commission trouvée');
            return [];
        }

        $commissions = [];

        foreach ($order->items as $item) {
            $itemData = null;

            if ($item->package_id) {
                $itemData = Package::find($item->package_id);
            } elseif ($item->product_id) {
                $itemData = Product::find($item->product_id);
            }

            if ($itemData) {
                $itemCommissions = $this->distributeCommissions(
                    $order->user,
                    $itemData,
                    $order->id,
                    $period
                );
                $commissions = array_merge($commissions, $itemCommissions);
            }
        }

        return $commissions;
    }
}