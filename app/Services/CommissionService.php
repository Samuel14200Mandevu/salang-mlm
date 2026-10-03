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
use App\Support\MlmPeriod;
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

        $period = CommissionPeriod::getCurrentPeriod()
            ?? CommissionPeriod::findOrCreateForValue(MlmPeriod::current());

        if (!$orderId) {
            Log::warning('calculatePackageCommission sans order_id', [
                'user_id' => $userId,
                'package_id' => $packageId,
            ]);
        }

        DB::beginTransaction();

        try {
            // PV + commissions temps réel : uniquement via CommissionDistributor
            $commissions = $this->commissionDistributor->distributeCommissions(
                $user,
                $package,
                $orderId ?? 0,
                $period
            );

            foreach ($commissions as $commission) {
                if ($commission->status === 'paid') {
                    continue;
                }

                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $commission->user_id],
                    [
                        'balance' => 0,
                        'pending_balance' => 0,
                        'total_withdrawn' => 0,
                        'total_deposited' => 0,
                        'currency' => 'USD',
                        'is_active' => true,
                    ]
                );
                $wallet->balance += $commission->amount;
                $wallet->total_deposited += $commission->amount;
                $wallet->save();

                $commission->status = 'paid';
                $commission->paid_at = now();
                $commission->save();
            }

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