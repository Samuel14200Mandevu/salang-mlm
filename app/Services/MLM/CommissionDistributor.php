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
     */
    private function getCommissionRate(int $level): float
    {
        $rates = [
            1 => 0,   // Distributeur
            2 => 0,   // Qualification
            3 => 22,  // Cumul Directeur
            4 => 26,  // Directeur
            5 => 30,  // Manager Senior
            6 => 34,  // Directeur Envolée
            7 => 40,  // Saphire Manager
            8 => 43,  // Diamant Bleu
            9 => 45,  // Perle Diamant
        ];
        return $rates[$level] ?? 0;
    }

    /**
     * Taux de leadership en fonction du grade
     */
    private function getLeadershipRate(int $rankLevel): float
    {
        $rates = [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ];
        return $rates[$rankLevel] ?? 0;
    }

    /**
     * Conditions de PV mensuel pour toucher les commissions
     */
    private function getMonthlyPVRequirements(): array
    {
        return [
            1 => ['personal' => 0, 'group' => 0],
            2 => ['personal' => 10, 'group' => 0],
            3 => ['personal' => 30, 'group' => 0],
            4 => ['personal' => 40, 'group' => 0],
            5 => ['personal' => 50, 'group' => 500],
            6 => ['personal' => 75, 'group' => 1000],
            7 => ['personal' => 100, 'group' => 2000],
            8 => ['personal' => 200, 'group' => 3000],
            9 => ['personal' => 300, 'group' => 5000],
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
     * Vérifier les conditions de PV pour un utilisateur
     */
    private function checkPVConditions(User $user, int $rankLevel): bool
    {
        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

        if (($user->monthly_pv ?? 0) < $req['personal']) {
            return false;
        }

        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) {
            return false;
        }

        return true;
    }

    /**
     * Récupérer les données d'un item
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
     * Récupérer la source de la commande
     */
    private function getSourceFromOrder(int $orderId): string
    {
        $order = \App\Models\Order::find($orderId);
        return $order ? $order->source : 'unknown';
    }

    /**
     * Récupérer le montant de la commission POS depuis la commande
     */
    private function getCommissionAmountFromOrder(int $orderId): float
    {
        $order = \App\Models\Order::find($orderId);
        if (!$order) {
            return 0;
        }
        
        if (isset($order->metadata['commission_amount'])) {
            return (float) $order->metadata['commission_amount'];
        }
        
        return 0;
    }

    /**
     * Distribuer les commissions pour un achat
     */
    public function distributeCommissions(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $itemData = $this->getItemData($item);
        if (!$itemData) {
            return $commissions;
        }

        $itemType = $this->getItemType($item);
        $isPackage = ($itemType === 'package');
        $itemPV = $this->getItemPV($item);
        $sponsor = $buyer->parrain;

        $source = $this->getSourceFromOrder($orderId);
        $isPosSource = ($source === 'pos');
        $isMlmSource = ($source === 'mlm' || $source === 'web' || $source === 'online' || $source === 'membership');

        // ============================================================
        // 0. AJOUTER LE PV AU SPONSOR
        // ============================================================
        if ($sponsor && $sponsor->is_active && $itemPV > 0) {
            
            if ($isPosSource) {
                $sponsor->increment('team_pv', $itemPV);
                
                if ($buyer->user_type === 'member') {
                    $buyer->increment('pv_balance', $itemPV);
                    $buyer->increment('monthly_pv', $itemPV);
                }
            }
            
            if ($isMlmSource && $buyer->user_type === 'member') {
                $buyer->increment('pv_balance', $itemPV);
                $buyer->increment('monthly_pv', $itemPV);
                $sponsor->increment('team_pv', $itemPV);
            }
        }

        // ============================================================
        // 1. COMMISSION CASH POS
        // ============================================================
        if ($isPosSource && $sponsor && $sponsor->is_active) {
            $commissionAmount = $this->getCommissionAmountFromOrder($orderId);
            
            if ($commissionAmount > 0) {
                $commission = Commission::create([
                    'user_id' => $sponsor->id,
                    'from_user_id' => $buyer->id,
                    'commission_period_id' => $period->id,
                    'period' => $period->period,
                    'type' => 'cash_pos',
                    'amount' => $commissionAmount,
                    'percentage' => 0,
                    'description' => "Commission CASH POS (${commissionAmount}$) - Achat POS par {$buyer->name}",
                    'order_id' => $orderId,
                    'package_id' => $isPackage ? $this->getItemId($item) : null,
                    'product_id' => !$isPackage ? $this->getItemId($item) : null,
                    'generation' => 1,
                    'calculation_type' => 'automatic',
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                $commissions[] = $commission;
            }
        }

        // ============================================================
        // 2. COMMISSIONS MLM - UNIQUEMENT POUR LES MEMBRES
        // ============================================================
        if ($buyer->user_type === 'member' && $buyer->is_active && $sponsor && $isMlmSource) {
            
            // Sponsor Bonus
            if ($isPackage && $sponsor->is_active) {
                $hasSponsorBonus = $this->hasReceivedSponsorBonus($sponsor, $buyer);
                
                if (!$hasSponsorBonus) {
                    $sponsorBonus = $this->calculateSponsorBonus($buyer, $itemData, $orderId, $period);
                    if ($sponsorBonus) {
                        $commissions[] = $sponsorBonus;
                    }
                }
            }
            
            // Bonus Direct, Indirect, Leadership (CORRIGÉS)
            $directs = $this->calculateDirectBonuses($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $directs);
            
            $indirects = $this->calculateIndirectBonusesCorrected($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $indirects);
            
            $leaderships = $this->calculateLeadershipBonusesCorrected($buyer, $itemData, $orderId, $period);
            $commissions = array_merge($commissions, $leaderships);
            
            $this->triggerRankUpdates($buyer);
        }

        return $commissions;
    }

    /**
     * 1. SPONSOR BONUS (inchangé)
     */
    private function calculateSponsorBonus(User $buyer, $item, $orderId, CommissionPeriod $period): ?Commission
    {
        $sponsor = $buyer->parrain;
        if (!$sponsor) return null;

        if (!$sponsor->is_active) {
            return null;
        }

        $itemType = $this->getItemType($item);
        if ($itemType !== 'package') {
            return null;
        }

        if ($this->hasReceivedSponsorBonus($sponsor, $buyer)) {
            return null;
        }

        $rank = $sponsor->rankObject;
        $rankLevel = $rank ? $rank->level : 1;

        if (!$this->checkPVConditions($sponsor, $rankLevel)) {
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
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * 2. BONUS DIRECT (inchangé)
     */
    private function calculateDirectBonuses(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;

        if ($buyerLevel < 3) {
            return $commissions;
        }

        if (!$this->checkPVConditions($buyer, $buyerLevel)) {
            return $commissions;
        }

        $buyerRate = $this->getCommissionRate($buyerLevel);
        
        if ($buyerRate <= 0) {
            return $commissions;
        }

        $buyerMonthlyPV = $buyer->monthly_pv ?? 0;

        if ($buyerMonthlyPV > 0 && $buyerRate > 0) {
            $amount = $buyerMonthlyPV * ($buyerRate / 100);

            if ($amount > 0) {
                $itemName = $this->getItemName($item);
                $itemType = $this->getItemType($item);
                $itemId = $this->getItemId($item);

                $commission = Commission::create([
                    'user_id' => $buyer->id,
                    'from_user_id' => $buyer->id,
                    'commission_period_id' => $period->id,
                    'period' => $period->period,
                    'type' => 'direct',
                    'amount' => $amount,
                    'percentage' => $buyerRate,
                    'description' => "Bonus Direct - {$buyerRate}% sur PV mensuel de {$buyerMonthlyPV} PV pour {$buyer->name}",
                    'order_id' => $orderId,
                    'package_id' => $itemType === 'package' ? $itemId : null,
                    'product_id' => $itemType === 'product' ? $itemId : null,
                    'generation' => 0,
                    'calculation_type' => 'automatic',
                    'status' => 'pending',
                ]);

                $commissions[] = $commission;
            }
        }

        return $commissions;
    }

    /**
     * 3. BONUS INDIRECT - CORRIGÉ
     * 
     * RÈGLES :
     * - Générations 1 à 7
     * - Le bénéficiaire doit avoir un grade >= 3
     * - Le descendant doit avoir un grade >= 3 (sinon pas de commission)
     * - Le bénéficiaire doit avoir un grade supérieur au descendant
     * - Si un descendant a un grade >= bénéficiaire, toute sa branche est exclue
     */
    private function calculateIndirectBonusesCorrected(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;
        $buyerRate = $this->getCommissionRate($buyerLevel);

        $pvAmount = $this->getItemPV($item);

        // Le bénéficiaire doit avoir un grade >= 3
        if ($pvAmount <= 0 || $buyerLevel < 3) {
            return $commissions;
        }

        // Vérifier les conditions de PV du bénéficiaire
        if (!$this->checkPVConditions($buyer, $buyerLevel)) {
            return $commissions;
        }

        // Récupérer les descendants avec exclusion des branches
        $descendantsData = $this->getDescendantsWithExclusion($buyer, 7);

        foreach ($descendantsData as $data) {
            $generation = $data['generation'];
            $descendant = $data['descendant'];
            $isExcluded = $data['is_excluded'];

            // Si la branche est exclue, passer
            if ($isExcluded) {
                Log::info('Branche exclue pour indirect', [
                    'descendant_id' => $descendant->id,
                    'descendant_name' => $descendant->name,
                    'generation' => $generation,
                ]);
                continue;
            }

            $descendantRank = $descendant->rankObject;
            $descendantLevel = $descendantRank ? $descendantRank->level : 1;
            $descendantRate = $this->getCommissionRate($descendantLevel);

            // ============================================================
            // NOUVELLE RÈGLE : Le descendant doit avoir un grade >= 3
            // Sinon, il ne génère pas de commission indirecte
            // ============================================================
            if ($descendantLevel < 3) {
                Log::info('Descendant ignoré pour indirect - grade < 3', [
                    'descendant_id' => $descendant->id,
                    'descendant_name' => $descendant->name,
                    'descendant_level' => $descendantLevel,
                ]);
                continue;
            }

            // Le bénéficiaire doit avoir un grade supérieur au descendant
            if ($buyerLevel > $descendantLevel) {
                $rateDifference = max(0, $buyerRate - $descendantRate);

                if ($rateDifference > 0) {
                    $amount = $pvAmount * ($rateDifference / 100);

                    if ($amount > 0) {
                        $itemName = $this->getItemName($item);
                        $itemType = $this->getItemType($item);
                        $itemId = $this->getItemId($item);

                        $commission = Commission::create([
                            'user_id' => $buyer->id,
                            'from_user_id' => $descendant->id,
                            'commission_period_id' => $period->id,
                            'period' => $period->period,
                            'type' => 'indirect',
                            'amount' => $amount,
                            'percentage' => $rateDifference,
                            'description' => "Bonus Indirect Génération {$generation} ({$rateDifference}%) sur {$pvAmount} PV pour {$descendant->name}",
                            'order_id' => $orderId,
                            'package_id' => $itemType === 'package' ? $itemId : null,
                            'product_id' => $itemType === 'product' ? $itemId : null,
                            'generation' => $generation,
                            'calculation_type' => 'automatic',
                            'status' => 'pending',
                        ]);

                        $commissions[] = $commission;

                        Log::info('Bonus indirect créé', [
                            'buyer_id' => $buyer->id,
                            'buyer_name' => $buyer->name,
                            'buyer_level' => $buyerLevel,
                            'descendant_id' => $descendant->id,
                            'descendant_name' => $descendant->name,
                            'descendant_level' => $descendantLevel,
                            'generation' => $generation,
                            'rate_difference' => $rateDifference,
                            'amount' => $amount,
                        ]);
                    }
                }
            }
        }

        return $commissions;
    }

    /**
     * 4. LEADERSHIP BONUS - CORRIGÉ
     * 
     * RÈGLES :
     * - Générations 1 à 7
     * - Le bénéficiaire doit avoir un grade >= 5
     * - S'applique à tous les descendants (pas de condition de grade)
     * - Pas d'exclusion de branche
     */
    private function calculateLeadershipBonusesCorrected(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;

        $pvAmount = $this->getItemPV($item);

        if ($pvAmount <= 0 || $buyerLevel < 5) {
            return $commissions;
        }

        // Vérifier les conditions de PV du bénéficiaire
        if (!$this->checkPVConditions($buyer, $buyerLevel)) {
            return $commissions;
        }

        $leadershipRate = $this->getLeadershipRate($buyerLevel);
        if ($leadershipRate <= 0) {
            return $commissions;
        }

        // Récupérer tous les descendants (sans exclusion pour le leadership)
        $descendantsData = $this->getAllDescendants($buyer, 7);

        foreach ($descendantsData as $data) {
            $generation = $data['generation'];
            $descendant = $data['descendant'];

            $amount = $pvAmount * ($leadershipRate / 100);

            if ($amount > 0) {
                $itemName = $this->getItemName($item);
                $itemType = $this->getItemType($item);
                $itemId = $this->getItemId($item);

                $commission = Commission::create([
                    'user_id' => $buyer->id,
                    'from_user_id' => $descendant->id,
                    'commission_period_id' => $period->id,
                    'period' => $period->period,
                    'type' => 'leadership',
                    'amount' => $amount,
                    'percentage' => $leadershipRate,
                    'description' => "Leadership Bonus Génération {$generation} ({$leadershipRate}%) sur {$pvAmount} PV pour {$descendant->name}",
                    'order_id' => $orderId,
                    'package_id' => $itemType === 'package' ? $itemId : null,
                    'product_id' => $itemType === 'product' ? $itemId : null,
                    'generation' => $generation,
                    'calculation_type' => 'automatic',
                    'status' => 'pending',
                ]);

                $commissions[] = $commission;

                Log::info('Leadership bonus créé', [
                    'buyer_id' => $buyer->id,
                    'buyer_name' => $buyer->name,
                    'buyer_level' => $buyerLevel,
                    'descendant_id' => $descendant->id,
                    'descendant_name' => $descendant->name,
                    'generation' => $generation,
                    'rate' => $leadershipRate,
                    'amount' => $amount,
                ]);
            }
        }

        return $commissions;
    }

    /**
     * Récupérer les descendants avec exclusion des branches
     * 
     * RÈGLE : Si un descendant a un grade >= bénéficiaire, toute sa branche est exclue
     */
    private function getDescendantsWithExclusion(User $user, int $maxGenerations = 7): array
    {
        $descendants = [];
        $currentGeneration = 1;
        $userLevel = $user->rankObject ? $user->rankObject->level : 1;

        $currentLevel = [
            [
                'id' => $user->id,
                'is_excluded' => false
            ]
        ];
        $processedIds = [$user->id];

        while ($currentGeneration <= $maxGenerations && !empty($currentLevel)) {
            $nextLevel = [];

            $currentIds = array_column($currentLevel, 'id');

            $children = User::whereIn('parrain_id', $currentIds)
                ->where('is_active', true)
                ->get();

            foreach ($children as $child) {
                if (in_array($child->id, $processedIds)) {
                    continue;
                }

                // Trouver le parent
                $parent = null;
                foreach ($currentLevel as $p) {
                    if ($p['id'] == $child->parrain_id) {
                        $parent = $p;
                        break;
                    }
                }

                if (!$parent) {
                    continue;
                }

                $childRank = $child->rankObject;
                $childLevel = $childRank ? $childRank->level : 1;

                // RÈGLE D'EXCLUSION : Si le descendant a un grade >= bénéficiaire
                $isExcluded = $parent['is_excluded'];

                if (!$isExcluded && $childLevel >= $userLevel) {
                    $isExcluded = true;
                }

                $descendants[] = [
                    'generation' => $currentGeneration,
                    'descendant' => $child,
                    'is_excluded' => $isExcluded,
                ];

                $nextLevel[] = [
                    'id' => $child->id,
                    'is_excluded' => $isExcluded,
                ];

                $processedIds[] = $child->id;
            }

            $currentLevel = $nextLevel;
            $currentGeneration++;
        }

        return $descendants;
    }

    /**
     * Récupérer tous les descendants (sans exclusion)
     */
    private function getAllDescendants(User $user, int $maxGenerations = 7): array
    {
        $descendants = [];
        $currentGeneration = 1;
        $currentLevel = [$user->id];
        $processedIds = [$user->id];

        while ($currentGeneration <= $maxGenerations && !empty($currentLevel)) {
            $nextLevel = [];

            $children = User::whereIn('parrain_id', $currentLevel)
                ->where('is_active', true)
                ->get();

            foreach ($children as $child) {
                if (!in_array($child->id, $processedIds)) {
                    $descendants[] = [
                        'generation' => $currentGeneration,
                        'descendant' => $child,
                    ];
                    $nextLevel[] = $child->id;
                    $processedIds[] = $child->id;
                }
            }

            $currentLevel = $nextLevel;
            $currentGeneration++;
        }

        return $descendants;
    }

    /**
     * Déclencher la mise à jour des grades
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

        } catch (\Exception $e) {
            Log::error('Error triggering rank updates', [
                'buyer_id' => $buyer->id,
                'error' => $e->getMessage(),
            ]);
        }
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