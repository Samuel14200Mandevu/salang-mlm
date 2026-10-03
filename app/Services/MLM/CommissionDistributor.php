<?php
// app/Services/MLM/CommissionDistributor.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Package;
use App\Models\Product;
use App\Models\PVHistory;
use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPeriod;
use App\Jobs\RecalculateAfterPVImport;
use App\Support\MlmPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CommissionDistributor — TEMPS RÉEL UNIQUEMENT
 *
 * Ce service ne calcule QUE :
 *   1. PV history + incréments (pv_balance, monthly_pv, team_pv)
 *   2. Cash POS (achat au comptoir)
 *   3. Sponsor bonus (1ère fois qu'un filleul active un package)
 *
 * ⚠️ NE CALCULE PLUS en temps réel :
 *   - Bonus Direct   → calculé en fin de période (PeriodCommissionCalculator)
 *   - Bonus Indirect → calculé en fin de période
 *   - Leadership     → calculé en fin de période
 *
 * Ces 3 types dépendent du PV cumulé du mois MLM (du 8 au 7),
 * qui n'est connu qu'à la fin de la période.
 */
class CommissionDistributor
{
    /**
     * Nombre maximum de générations pour Indirect.
     */
    const MAX_GENERATIONS_INDIRECT = 8;

    /**
     * Nombre maximum de générations pour Leadership.
     */
    const MAX_GENERATIONS_LEADERSHIP = 12;

    /**
     * Nombre maximum absolu parcouru dans l'arbre.
     */
    const MAX_TREE_GENERATIONS = 12;

    // ═══════════════════════════════════════════════════════════════
    // HELPERS ITEM
    // ═══════════════════════════════════════════════════════════════

    private function getItemData($item)
    {
        if ($item instanceof Package || $item instanceof Product) return $item;

        if (is_array($item)) {
            $type = $item['type'] ?? null;
            $id = $item['id'] ?? null;
            if ($type === 'package' && $id) return Package::find($id);
            if ($type === 'product' && $id) return Product::find($id);
        }
        return null;
    }

    private function getItemPV($item): int
    {
        if ($item instanceof Package || $item instanceof Product) return $item->pv_value ?? 0;
        if (is_array($item)) return $item['pv_value'] ?? 0;
        return 0;
    }

    private function getItemPrice($item): float
    {
        if ($item instanceof Package || $item instanceof Product) return $item->price ?? 0;
        if (is_array($item)) return $item['price'] ?? 0;
        return 0;
    }

    private function getItemName($item): string
    {
        if ($item instanceof Package || $item instanceof Product) return $item->name ?? 'Item';
        if (is_array($item)) return $item['name'] ?? 'Item';
        return 'Item';
    }

    private function getItemType($item): string
    {
        if ($item instanceof Package) return 'package';
        if ($item instanceof Product) return 'product';
        if (is_array($item)) return $item['type'] ?? 'unknown';
        return 'unknown';
    }

    private function getItemId($item): ?int
    {
        if ($item instanceof Package || $item instanceof Product) return $item->id;
        if (is_array($item)) return $item['id'] ?? null;
        return null;
    }

    private function getSourceFromOrder(int $orderId): string
    {
        $order = \App\Models\Order::find($orderId);
        return $order ? $order->source : 'unknown';
    }

    private function getCommissionAmountFromOrder(int $orderId): float
    {
        $order = \App\Models\Order::find($orderId);
        if (!$order) return 0;
        if (isset($order->metadata['commission_amount'])) {
            return (float) $order->metadata['commission_amount'];
        }
        return 0;
    }

    // ═══════════════════════════════════════════════════════════════
    // VÉRIFICATIONS
    // ═══════════════════════════════════════════════════════════════

    private function hasReceivedSponsorBonus(User $sponsor, User $buyer): bool
    {
        $inCommissions = Commission::where('user_id', $sponsor->id)
            ->where('from_user_id', $buyer->id)
            ->where('type', 'sponsor')
            ->exists();

        if ($inCommissions) return true;

        return CommissionHistory::where('user_id', $sponsor->id)
            ->where('from_user_id', $buyer->id)
            ->where('type', 'sponsor')
            ->exists();
    }

    private function checkPVConditions(User $user, int $rankLevel): bool
    {
        $requirements = [
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
        $req = $requirements[$rankLevel] ?? ['personal' => 0, 'group' => 0];

        if (($user->monthly_pv ?? 0) < $req['personal']) return false;
        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) return false;

        return true;
    }

    // ═══════════════════════════════════════════════════════════════
    // POINT D'ENTRÉE PRINCIPAL
    // ═══════════════════════════════════════════════════════════════

    /**
     * Distribuer les commissions TEMPS RÉEL uniquement.
     *
     * TRAITEMENT IMMÉDIAT :
     *   1. PV history + incréments (pv_balance, monthly_pv, team_pv)
     *   2. Cash POS
     *   3. Sponsor bonus (une seule fois par filleul)
     *
     * TRAITEMENT DIFFÉRÉ (le 8 à 00h05 via PeriodCommissionCalculator) :
     *   - Bonus Direct (sur monthly_pv total)
     *   - Bonus Indirect (gén. 1-8, sur PV descendant)
     *   - Leadership (gén. 1-12, sur PV descendant)
     */
    public function distributeCommissions(
        User $buyer,
        $item,
        $orderId,
        CommissionPeriod $period,
        bool $commissionsOnly = false
    ): array {
        $commissions = [];

        $itemData = $this->getItemData($item);
        if (!$itemData) return $commissions;

        $itemType   = $this->getItemType($item);
        $isPackage  = ($itemType === 'package');
        $itemPV     = $this->getItemPV($item);
        $sponsor    = $buyer->parrain;

        $source       = $this->getSourceFromOrder($orderId);
        $isPosSource  = ($source === 'pos');
        $isMlmSource  = in_array($source, ['mlm', 'web', 'online', 'membership']);

        // ══════════════════════════════════════════════════════════
        // 0. PV HISTORY + INCRÉMENTS (sauf rejeu fin de période)
        // ══════════════════════════════════════════════════════════
        if (!$commissionsOnly && $itemPV > 0) {

            if ($isPosSource && $sponsor && $sponsor->is_active) {
                $sponsor->increment('team_pv', $itemPV);
            }

            if ($isMlmSource && $sponsor && $sponsor->is_active) {
                $sponsor->increment('team_pv', $itemPV);
            }

            if ($buyer->user_type === 'member' && ($isPosSource || $isMlmSource)) {
                $buyer->increment('pv_balance', $itemPV);
                $buyer->increment('monthly_pv', $itemPV);
                $this->recordPVHistory($buyer, $period, $itemPV, $orderId, $item, $source);
            }

            $buyer->refresh();
            if ($sponsor) $sponsor->refresh();
        }

        // ══════════════════════════════════════════════════════════
        // 1. CASH POS
        // ══════════════════════════════════════════════════════════
        if ($isPosSource && $sponsor && $sponsor->is_active) {
            $commissionAmount = $this->getCommissionAmountFromOrder($orderId);

            if ($commissionAmount > 0) {
                $commissions[] = Commission::create([
                    'user_id'              => $sponsor->id,
                    'from_user_id'         => $buyer->id,
                    'commission_period_id' => $period->id,
                    'period'               => $period->period,
                    'type'                 => 'cash_pos',
                    'source'               => 'pos',
                    'amount'               => $commissionAmount,
                    'percentage'           => 0,
                    'description'          => "Commission CASH POS (\${$commissionAmount}) - Achat POS par {$buyer->name}",
                    'order_id'             => $orderId,
                    'package_id'           => $isPackage ? $this->getItemId($item) : null,
                    'product_id'           => !$isPackage ? $this->getItemId($item) : null,
                    'generation'           => 0,
                    'calculation_type'     => 'automatic',
                    'status'               => 'paid',
                    'paid_at'              => now(),
                ]);
            }
        }

        // ══════════════════════════════════════════════════════════
        // 2. SPONSOR BONUS UNIQUEMENT
        // ══════════════════════════════════════════════════════════
        if ($buyer->user_type === 'member' && $buyer->is_active && $sponsor && $isMlmSource) {

            // --- Sponsor Bonus (1 seule fois par filleul) ---
            if ($isPackage && $sponsor->is_active
                && !$this->hasReceivedSponsorBonus($sponsor, $buyer)) {
                $sponsorBonus = $this->calculateSponsorBonus($buyer, $itemData, $orderId, $period);
                if ($sponsorBonus) $commissions[] = $sponsorBonus;
            }

            // ❌ Direct / Indirect / Leadership : RETIRÉS
            //    → Calculés en fin de période par PeriodCommissionCalculator

            if (!$commissionsOnly) {
                $this->triggerRankUpdates($buyer);
            }
        }

        return $commissions;
    }

    // ═══════════════════════════════════════════════════════════════
    // ENREGISTREMENT PV HISTORY
    // ═══════════════════════════════════════════════════════════════

    private function recordPVHistory(
        User $buyer,
        CommissionPeriod $period,
        int $itemPV,
        int $orderId,
        $item,
        string $source
    ): void {
        try {
            PVHistory::create([
                'user_id'    => $buyer->id,
                'amount'     => $itemPV,
                'date'       => now()->toDateString(),
                'period'     => $period->period,
                'type'       => 'personal',
                'notes'      => "Commande #{$orderId} - " . $this->getItemName($item) . " (source: {$source})",
                'created_by' => auth()->id() ?? $buyer->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur enregistrement PVHistory', [
                'user_id'  => $buyer->id,
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SPONSOR BONUS
    // ═══════════════════════════════════════════════════════════════

    private function calculateSponsorBonus(User $buyer, $item, $orderId, CommissionPeriod $period): ?Commission
    {
        $sponsor = $buyer->parrain;
        if (!$sponsor || !$sponsor->is_active) return null;

        if ($this->getItemType($item) !== 'package') return null;
        if ($this->hasReceivedSponsorBonus($sponsor, $buyer)) return null;

        $rank = $sponsor->rankObject;
        $rankLevel = $rank ? $rank->level : 1;

        if (!$this->checkPVConditions($sponsor, $rankLevel)) return null;

        $itemName  = $this->getItemName($item);
        $itemPrice = $this->getItemPrice($item);
        $itemId    = $this->getItemId($item);

        if ($rankLevel == 1) {
            $amount = 10;
            $percentage = 0;
            $description = "Sponsor bonus (10\$ fixe) pour activation de {$buyer->name} avec {$itemName}";
        } else {
            $amount = $itemPrice * 0.30;
            $percentage = 30;
            $description = "Sponsor bonus (30%) pour activation de {$buyer->name} avec {$itemName}";
        }

        return Commission::create([
            'user_id'              => $sponsor->id,
            'from_user_id'         => $buyer->id,
            'commission_period_id' => $period->id,
            'period'               => $period->period,
            'type'                 => 'sponsor',
            'source'               => 'mlm',
            'amount'               => $amount,
            'percentage'           => $percentage,
            'description'          => $description,
            'order_id'             => $orderId,
            'package_id'           => $itemId,
            'product_id'           => null,
            'generation'           => 1,
            'calculation_type'     => 'automatic',
            'status'               => 'paid',
            'paid_at'              => now(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // MISE À JOUR DES GRADES
    // ═══════════════════════════════════════════════════════════════

    private function triggerRankUpdates(User $buyer): void
    {
        try {
            $userIds = [$buyer->id];
            $current = $buyer->parrain;
            $depth = 0;
            $processed = [];

            while ($current && $depth < 13 && !in_array($current->id, $processed)) {
                $processed[] = $current->id;
                $userIds[] = $current->id;
                $current = $current->parrain;
                $depth++;
            }

            RecalculateAfterPVImport::dispatch(
                array_values(array_unique($userIds)),
                MlmPeriod::current()
            )->onQueue('rank-recalculation');
        } catch (\Exception $e) {
            Log::error('Error triggering rank updates', [
                'buyer_id' => $buyer->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // UTILITAIRE PUBLIC
    // ═══════════════════════════════════════════════════════════════

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
                    $order->user, $itemData, $order->id, $period
                );
                $commissions = array_merge($commissions, $itemCommissions);
            }
        }

        return $commissions;
    }
}