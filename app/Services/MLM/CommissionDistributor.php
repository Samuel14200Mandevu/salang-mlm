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

class CommissionDistributor
{
    private function getCommissionRate(int $level): float
    {
        $rates = [
            1 => 0,
            2 => 0,
            3 => 22,
            4 => 26,
            5 => 30,
            6 => 34,
            7 => 40,
            8 => 43,
            9 => 45,
        ];
        return $rates[$level] ?? 0;
    }

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

    private function hasReceivedSponsorBonus(User $sponsor, User $buyer): bool
    {
        return Commission::where('user_id', $sponsor->id)
            ->where('from_user_id', $buyer->id)
            ->where('type', 'sponsor')
            ->exists();
    }

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

    public function distributeCommissions(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        if (!$buyer->is_active) {
            Log::info('Commissions non distribuees - compte inactif', [
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'item_type' => $this->getItemType($item),
                'item_name' => $this->getItemName($item),
            ]);
            return $commissions;
        }

        $itemData = $this->getItemData($item);
        if (!$itemData) {
            Log::warning('Item non trouve pour la distribution des commissions', [
                'item' => $item,
            ]);
            return $commissions;
        }

        $sponsor = $buyer->parrain;
        if ($sponsor && $sponsor->is_active) {
            $hasSponsorBonus = $this->hasReceivedSponsorBonus($sponsor, $buyer);

            if (!$hasSponsorBonus) {
                $sponsorBonus = $this->calculateSponsorBonus($buyer, $itemData, $orderId, $period);
                if ($sponsorBonus) {
                    $commissions[] = $sponsorBonus;
                    Log::info('Sponsor bonus distribue pour la premiere fois', [
                        'sponsor_id' => $sponsor->id,
                        'buyer_id' => $buyer->id,
                        'amount' => $sponsorBonus->amount,
                    ]);
                }
            } else {
                Log::info('Sponsor bonus deja distribue pour ce filleul', [
                    'sponsor_id' => $sponsor->id,
                    'buyer_id' => $buyer->id,
                ]);
            }
        }

        $directs = $this->calculateDirectBonuses($buyer, $itemData, $orderId, $period);
        $commissions = array_merge($commissions, $directs);

        $indirects = $this->calculateIndirectBonuses($buyer, $itemData, $orderId, $period);
        $commissions = array_merge($commissions, $indirects);

        $leaderships = $this->calculateLeadershipBonuses($buyer, $itemData, $orderId, $period);
        $commissions = array_merge($commissions, $leaderships);

        $this->triggerRankUpdates($buyer);

        return $commissions;
    }

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

    private function calculateSponsorBonus(User $buyer, $item, $orderId, CommissionPeriod $period): ?Commission
    {
        $sponsor = $buyer->parrain;
        if (!$sponsor) return null;

        if (!$sponsor->is_active) {
            Log::info('Sponsor bonus non distribue - sponsor inactif', [
                'sponsor_id' => $sponsor->id,
                'sponsor_name' => $sponsor->name,
            ]);
            return null;
        }

        if ($this->hasReceivedSponsorBonus($sponsor, $buyer)) {
            Log::info('Sponsor bonus deja distribue - ignore', [
                'sponsor_id' => $sponsor->id,
                'buyer_id' => $buyer->id,
            ]);
            return null;
        }

        $rank = $sponsor->rankObject;
        $rankLevel = $rank ? $rank->level : 1;

        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

        if ($sponsor->monthly_pv < $req['personal']) {
            Log::info('Sponsor bonus non distribue - PV personnel insuffisant', [
                'sponsor_id' => $sponsor->id,
                'monthly_pv' => $sponsor->monthly_pv,
                'required' => $req['personal'],
            ]);
            return null;
        }

        if ($req['group'] > 0 && $sponsor->team_pv < $req['group']) {
            Log::info('Sponsor bonus non distribue - PV groupe insuffisant', [
                'sponsor_id' => $sponsor->id,
                'team_pv' => $sponsor->team_pv,
                'required' => $req['group'],
            ]);
            return null;
        }

        $itemName = $this->getItemName($item);
        $itemPrice = $this->getItemPrice($item);
        $itemType = $this->getItemType($item);
        $itemId = $this->getItemId($item);

        if ($rankLevel == 1) {
            $amount = 10;
            $percentage = null;
            $description = "Sponsor bonus (10$ fixe) pour activation de {$buyer->name}";
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
            'package_id' => $itemType === 'package' ? $itemId : null,
            'product_id' => $itemType === 'product' ? $itemId : null,
            'generation' => 1,
            'calculation_type' => 'automatic',
            'status' => 'pending',
        ]);
    }

    private function calculateDirectBonuses(User $buyer, $item, $orderId, CommissionPeriod $period): array
    {
        $commissions = [];

        $buyerRank = $buyer->rankObject;
        $buyerLevel = $buyerRank ? $buyerRank->level : 1;

        if ($buyerLevel < 3) {
            return $commissions;
        }

        $sponsor = $buyer->parrain;
        if (!$sponsor) return $commissions;

        if (!$sponsor->is_active) {
            return $commissions;
        }

        $sponsorRank = $sponsor->rankObject;
        $sponsorLevel = $sponsorRank ? $sponsorRank->level : 1;

        if ($sponsorLevel < 3) {
            return $commissions;
        }

        $requirements = $this->getMonthlyPVRequirements();
        $req = $requirements[$sponsorLevel] ?? ['personal' => 0, 'group' => 0];

        if ($sponsor->monthly_pv < $req['personal']) {
            return $commissions;
        }

        if ($req['group'] > 0 && $sponsor->team_pv < $req['group']) {
            return $commissions;
        }

        $sponsorRate = $this->getCommissionRate($sponsorLevel);
        $pvAmount = $this->getItemPV($item);

        if ($pvAmount > 0 && $sponsorRate > 0) {
            $amount = $pvAmount * ($sponsorRate / 100);

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
                'description' => "Commission directe ({$sponsorRate}%) sur PV de {$pvAmount} pour {$buyer->name} ({$itemName})",
                'order_id' => $orderId,
                'package_id' => $itemType === 'package' ? $itemId : null,
                'product_id' => $itemType === 'product' ? $itemId : null,
                'generation' => 1,
                'calculation_type' => 'automatic',
                'status' => 'pending',
            ]);
        }

        return $commissions;
    }

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

            if ($current->monthly_pv < $req['personal']) {
                $current = $current->parrain;
                $generation++;
                continue;
            }

            if ($req['group'] > 0 && $current->team_pv < $req['group']) {
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
                        'description' => "Commission indirecte generation {$generation} ({$difference}%) sur PV de {$pvAmount} ({$itemName})",
                        'order_id' => $orderId,
                        'package_id' => $itemType === 'package' ? $itemId : null,
                        'product_id' => $itemType === 'product' ? $itemId : null,
                        'generation' => $generation,
                        'calculation_type' => 'automatic',
                        'status' => 'pending',
                    ]);
                }
            }

            $previousRate = $currentRate;
            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
    }

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

                if ($current->monthly_pv >= $req['personal'] &&
                    ($req['group'] == 0 || $current->team_pv >= $req['group'])) {

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
                            'description' => "Leadership niveau {$rankLevel} ({$rate}%) generation {$generation} sur PV de {$pvAmount} ({$itemName})",
                            'order_id' => $orderId,
                            'package_id' => $itemType === 'package' ? $itemId : null,
                            'product_id' => $itemType === 'product' ? $itemId : null,
                            'generation' => $generation,
                            'calculation_type' => 'automatic',
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            $current = $current->parrain;
            $generation++;
        }

        return $commissions;
    }

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

    public function distributeCommissionsForOrder($order): array
    {
        $period = CommissionPeriod::getCurrentPeriod();
        if (!$period) {
            Log::error('Aucune periode de commission trouvee');
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