<?php
// app/Services/CommissionService.php

namespace App\Services;

use App\Models\User;
use App\Models\Commission;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\RankHistory;
use App\Models\CommissionPeriod;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\RankConditionChecker;
use App\Services\MLM\CommissionDistributor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    protected $rankCalculator;
    protected $rankChecker;
    protected $commissionDistributor;

    public function __construct(
        AdvancedRankCalculator $rankCalculator,
        RankConditionChecker $rankChecker,
        CommissionDistributor $commissionDistributor
    ) {
        $this->rankCalculator = $rankCalculator;
        $this->rankChecker = $rankChecker;
        $this->commissionDistributor = $commissionDistributor;
    }

    /**
     * Calculer les commissions pour un achat de package
     */
    public function calculatePackageCommission($userId, $packageId, $orderId = null)
    {
        $user = User::find($userId);
        $package = Package::find($packageId);
        
        if (!$user || !$package) {
            Log::error('User ou Package non trouvé', [
                'user_id' => $userId,
                'package_id' => $packageId
            ]);
            return false;
        }

        // Récupérer la période en cours
        $period = CommissionPeriod::where('period', date('Y-m'))->first();
        if (!$period) {
            $period = $this->createCurrentPeriod();
        }

        DB::beginTransaction();
        
        try {
            // 1. Mettre à jour les PV/BV
            $this->updateUserPVBV($user, $package);

            // 2. Calculer les commissions
            $commissions = $this->commissionDistributor->distributeCommissions(
                $user,
                $package,
                $orderId,
                $period
            );

            // 3. Créditer les wallets immédiatement
            foreach ($commissions as $commission) {
                $wallet = Wallet::where('user_id', $commission->user_id)->first();
                if ($wallet) {
                    $wallet->balance += $commission->amount;
                    $wallet->save();
                    
                    $commission->status = 'paid';
                    $commission->paid_at = now();
                    $commission->save();
                }
            }

            // 4. Mettre à jour les grades
            $this->updateRanks($user);

            DB::commit();
            
            Log::info('Commissions calculées avec succès', [
                'user_id' => $userId,
                'package_id' => $packageId,
                'total_commissions' => collect($commissions)->sum('amount')
            ]);
            
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul commissions: ' . $e->getMessage(), [
                'user_id' => $userId,
                'package_id' => $packageId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Mettre à jour les PV/BV d'un utilisateur
     */
    private function updateUserPVBV(User $user, Package $package)
    {
        $user->pv_balance += $package->pv_value;
        $user->bv_balance += $package->bv_value;
        $user->monthly_pv += $package->pv_value;
        $user->monthly_bv += $package->bv_value;
        $user->save();

        $this->updateNetworkPVBV($user, $package);
    }

    /**
     * Mettre à jour les PV/BV du réseau (parrains)
     */
    private function updateNetworkPVBV(User $user, Package $package)
    {
        $current = $user->parrain;
        $level = 1;

        while ($current && $level <= 9) {
            $current->team_pv += $package->pv_value;
            $current->team_bv += $package->bv_value;
            $current->save();
            
            $current = $current->parrain;
            $level++;
        }
    }

    /**
     * Mettre à jour les grades
     */
    private function updateRanks(User $user)
    {
        $newRank = $this->rankCalculator->calculateAdvancedRank($user);
        if ($newRank && $newRank->id != $user->rank_id) {
            $this->updateUserRankInternal($user, $newRank);
        }

        $current = $user->parrain;
        while ($current) {
            $newRank = $this->rankCalculator->calculateAdvancedRank($current);
            if ($newRank && $newRank->id != $current->rank_id) {
                $this->updateUserRankInternal($current, $newRank);
            }
            $current = $current->parrain;
        }
    }

    /**
     * Mettre à jour le grade d'un utilisateur (interne)
     */
    private function updateUserRankInternal(User $user, $newRank)
    {
        $oldRankId = $user->rank_id;
        $oldRankName = $user->rank_name;

        $user->rank_id = $newRank->id;
        $user->rank = $newRank->name;
        $user->last_rank_update = now();
        $user->save();

        RankHistory::create([
            'user_id' => $user->id,
            'old_rank_id' => $oldRankId,
            'new_rank_id' => $newRank->id,
            'old_rank_name' => $oldRankName,
            'new_rank_name' => $newRank->name,
            'pv_at_time' => $user->pv_balance,
            'bv_at_time' => $user->bv_balance,
            'notes' => 'Mise à jour automatique',
        ]);

        Log::info('Grade mis à jour', [
            'user_id' => $user->id,
            'old_rank' => $oldRankName,
            'new_rank' => $newRank->name,
        ]);
    }

    /**
     * Créer la période en cours
     */
    private function createCurrentPeriod()
    {
        $now = now();
        $period = $now->format('Y-m');
        
        return CommissionPeriod::create([
            'period' => $period,
            'start_date' => $now->copy()->startOfMonth(),
            'end_date' => $now->copy()->endOfMonth(),
            'status' => 'pending',
        ]);
    }

    /**
     * Mettre à jour le grade d'un utilisateur (méthode publique)
     */
    public function updateUserRank($user)
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }
        
        if (!$user) {
            return false;
        }
        
        $newRank = $this->rankCalculator->calculateAdvancedRank($user);
        if ($newRank && $newRank->id != $user->rank_id) {
            $this->updateUserRankInternal($user, $newRank);
            return true;
        }
        
        return false;
    }

    /**
     * Récupérer les statistiques de commissions
     */
    public function getUserCommissionStats($userId)
    {
        $totalDirect = Commission::where('user_id', $userId)
            ->where('type', 'direct')
            ->where('status', 'paid')
            ->sum('amount');
            
        $totalIndirect = Commission::where('user_id', $userId)
            ->where('type', 'indirect')
            ->where('status', 'paid')
            ->sum('amount');
            
        $totalLeadership = Commission::where('user_id', $userId)
            ->where('type', 'leadership')
            ->where('status', 'paid')
            ->sum('amount');
            
        $totalRetail = Commission::where('user_id', $userId)
            ->where('type', 'retail')
            ->where('status', 'paid')
            ->sum('amount');
            
        return [
            'direct' => $totalDirect,
            'indirect' => $totalIndirect,
            'leadership' => $totalLeadership,
            'retail' => $totalRetail,
            'total' => $totalDirect + $totalIndirect + $totalLeadership + $totalRetail,
        ];
    }

    /**
     * Récupérer les commissions d'un utilisateur
     */
    public function getUserCommissions($userId, $status = null)
    {
        $query = Commission::where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Récupérer le total des commissions par type
     */
    public function getCommissionsByType($userId)
    {
        return Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();
    }

    /**
     * Calculer le profit retail
     */
    public function calculateRetailProfit($userId, $amount, $productId = null, $orderId = null)
    {
        $user = User::find($userId);
        if (!$user) return false;

        $profitAmount = $amount * 0.25;
        
        Commission::create([
            'user_id' => $user->id,
            'from_user_id' => null,
            'type' => 'retail',
            'amount' => $profitAmount,
            'percentage' => 25,
            'description' => 'Profit retail sur vente de produit',
            'order_id' => $orderId,
            'package_id' => null,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($wallet) {
            $wallet->balance += $profitAmount;
            $wallet->save();
        }

        return true;
    }
}