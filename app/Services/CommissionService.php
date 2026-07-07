<?php
// app/Services/CommissionService.php

namespace App\Services;

use App\Models\User;
use App\Models\Commission;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\RankHistory;
use App\Models\Rank;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Calculer les commissions pour un achat de package
     */
    public function calculatePackageCommission($userId, $packageId, $orderId = null)
    {
        $user = User::find($userId);
        $package = Package::find($packageId);
        
        if (!$user || !$package) {
            return false;
        }

        DB::beginTransaction();
        
        try {
            // 1. Commission Directe (30%) - Pour le parrain
            if ($user->sponsor_id) {
                // ✅ CORRECTION : Chercher par sponsor_id (code texte)
                $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();
                if ($sponsor) {
                    $directAmount = $package->price * 0.30;
                    $this->createCommission(
                        $sponsor->id,
                        $user->id,
                        'direct',
                        $directAmount,
                        30,
                        'Commission directe pour achat de ' . $package->name,
                        $orderId,
                        $packageId
                    );
                }
            }

            // 2. Commission Indirecte (15%) - Pour le parrain du parrain
            if ($user->sponsor_id) {
                // ✅ CORRECTION : Chercher par sponsor_id (code texte)
                $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();
                if ($sponsor && $sponsor->sponsor_id) {
                    // ✅ CORRECTION : Chercher par sponsor_id (code texte)
                    $grandSponsor = User::where('sponsor_id', $sponsor->sponsor_id)->first();
                    if ($grandSponsor) {
                        $indirectAmount = $package->price * 0.15;
                        $this->createCommission(
                            $grandSponsor->id,
                            $user->id,
                            'indirect',
                            $indirectAmount,
                            15,
                            'Commission indirecte pour achat de ' . $package->name,
                            $orderId,
                            $packageId
                        );
                    }
                }
            }

            // 3. Commission Leadership (10%) - Pour les leaders 3+ niveaux
            $this->calculateLeadershipCommission($user, $package, $orderId);

            // 4. Mettre à jour les PV/BV de l'utilisateur
            $user->pv_balance += $package->pv_value;
            $user->bv_balance += $package->bv_value;
            $user->save();

            // 5. Vérifier et mettre à jour le grade
            $rankService = new RankService();
            $rankService->updateRank($user->id);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission calculation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculer la commission de leadership
     */
    private function calculateLeadershipCommission($user, $package, $orderId)
    {
        $level = 1;
        // ✅ CORRECTION : Chercher par sponsor_id (code texte)
        $currentSponsor = User::where('sponsor_id', $user->sponsor_id)->first();
        
        while ($currentSponsor && $level <= 5) {
            // Vérifier si le sponsor a assez de PV pour être leader
            if ($currentSponsor->pv_balance >= 1000) {
                $leadershipAmount = $package->price * 0.10;
                $this->createCommission(
                    $currentSponsor->id,
                    $user->id,
                    'leadership',
                    $leadershipAmount,
                    10,
                    'Commission leadership niveau ' . $level . ' pour achat de ' . $package->name,
                    $orderId,
                    $package->id
                );
            }
            
            // ✅ CORRECTION : Chercher par sponsor_id (code texte)
            $currentSponsor = User::where('sponsor_id', $currentSponsor->sponsor_id)->first();
            $level++;
        }
    }

    /**
     * Créer une commission
     */
    private function createCommission($userId, $fromUserId, $type, $amount, $percentage, $description, $orderId = null, $packageId = null)
    {
        $commission = Commission::create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId,
            'type' => $type,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => $description,
            'order_id' => $orderId,
            'package_id' => $packageId,
            'status' => 'pending',
        ]);

        // Créditer le portefeuille
        $wallet = Wallet::where('user_id', $userId)->first();
        if ($wallet) {
            $wallet->balance += $amount;
            $wallet->save();
            
            // Créer une transaction
            Transaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'type' => 'commission',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $wallet->balance - $amount,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => $description,
                'completed_at' => now(),
            ]);

            // Marquer la commission comme payée
            $commission->status = 'paid';
            $commission->paid_at = now();
            $commission->save();

            // Envoyer la notification par email
            $user = User::find($userId);
            if ($user && class_exists(CommissionPaidNotification::class)) {
                try {
                    $user->notify(new CommissionPaidNotification(
                        $amount,
                        $type,
                        $commission->id
                    ));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi notification commission: ' . $e->getMessage());
                }
            }
        }

        return $commission;
    }

    /**
     * Mettre à jour le grade de l'utilisateur (redirige vers RankService)
     */
    public function updateUserRank($user)
    {
        $rankService = new RankService();
        return $rankService->updateRank($user->id);
    }

    /**
     * Calculer le profit retail (25%)
     */
    public function calculateRetailProfit($userId, $amount, $productId = null, $orderId = null)
    {
        $user = User::find($userId);
        if (!$user) return false;

        $profitAmount = $amount * 0.25;
        
        $this->createCommission(
            $user->id,
            null,
            'retail',
            $profitAmount,
            25,
            'Profit retail sur vente de produit',
            $orderId,
            null
        );

        return true;
    }

    /**
     * Récupérer les statistiques de commissions d'un utilisateur
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
}