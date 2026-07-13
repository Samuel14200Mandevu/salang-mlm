<?php
// app/Services/MLM/MonthlyCommissionService.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\CommissionPayment;
use App\Models\UserMonthlyRank;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonthlyCommissionService
{
    protected $rankCalculator;
    protected $commissionDistributor;

    public function __construct(
        AdvancedRankCalculator $rankCalculator,
        CommissionDistributor $commissionDistributor
    ) {
        $this->rankCalculator = $rankCalculator;
        $this->commissionDistributor = $commissionDistributor;
    }

    /**
     * Créer la période du mois
     */
    public function createMonthlyPeriod($year, $month): CommissionPeriod
    {
        $period = sprintf('%04d-%02d', $year, $month);
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return CommissionPeriod::updateOrCreate(
            ['period' => $period],
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Calculer les PV/BV mensuels
     */
    public function calculateMonthlyPVBV($periodId): bool
    {
        $period = CommissionPeriod::findOrFail($periodId);
        $period->update(['status' => 'calculating']);

        DB::beginTransaction();

        try {
            // Réinitialiser les PV mensuels
            User::query()->update([
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
            ]);

            // Calculer les PV/BV du mois pour chaque utilisateur
            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            foreach ($orders as $order) {
                $user = $order->user;
                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $pv = $package->pv_value * $item->quantity;
                            $bv = $package->bv_value * $item->quantity;

                            $user->monthly_pv += $pv;
                            $user->monthly_bv += $bv;
                            $user->pv_balance += $pv;
                            $user->bv_balance += $bv;
                            $user->save();

                            // Ajouter aux PV/BV de l'équipe (parrains)
                            $this->addTeamPVBV($user, $pv, $bv);
                        }
                    }
                }
            }

            DB::commit();
            $period->update(['status' => 'calculated']);
            
            Log::info("PV/BV mensuels calculés pour la période {$period->period}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul PV/BV mensuels: ' . $e->getMessage());
            $period->update(['status' => 'pending', 'notes' => 'Erreur: ' . $e->getMessage()]);
            return false;
        }
    }

    /**
     * Calculer les rangs mensuels
     */
    public function calculateMonthlyRanks($periodId): bool
    {
        $period = CommissionPeriod::findOrFail($periodId);
        $period->update(['status' => 'calculating']);

        DB::beginTransaction();

        try {
            $users = User::all();

            foreach ($users as $user) {
                // Compter les filleuls directs
                $directSponsors = User::where('parrain_id', $user->id)->count();
                $user->direct_sponsors_count = $directSponsors;
                $user->save();

                // Compter les branches qualifiées
                $qualifiedBranches = $this->countQualifiedBranches($user);
                $user->qualified_branches = $qualifiedBranches;
                $user->save();

                // Calculer le rang du mois
                $rank = $this->rankCalculator->calculateAdvancedRank($user);
                if ($rank) {
                    $user->rank_id = $rank->id;
                    $user->rank = $rank->name;
                    $user->last_rank_update = now();
                    $user->save();

                    // Enregistrer le rang mensuel
                    UserMonthlyRank::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'period' => $period->period,
                        ],
                        [
                            'rank_id' => $rank->id,
                            'pv_monthly' => $user->monthly_pv,
                            'bv_monthly' => $user->monthly_bv,
                            'team_pv' => $user->team_pv,
                            'team_bv' => $user->team_bv,
                            'direct_sponsors' => $directSponsors,
                            'qualified_branches' => $qualifiedBranches,
                        ]
                    );
                }
            }

            DB::commit();
            $period->update(['status' => 'calculated']);
            
            Log::info("Rangs mensuels calculés pour la période {$period->period}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul rangs mensuels: ' . $e->getMessage());
            $period->update(['status' => 'pending', 'notes' => 'Erreur: ' . $e->getMessage()]);
            return false;
        }
    }

    /**
     * Calculer les commissions mensuelles
     */
    public function calculateMonthlyCommissions($periodId): bool
    {
        $period = CommissionPeriod::findOrFail($periodId);
        $period->update(['status' => 'calculating']);

        DB::beginTransaction();

        try {
            // Récupérer toutes les commandes du mois
            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $totalCommissions = 0;

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            // Calculer les commissions pour cette commande
                            $commissions = $this->commissionDistributor->distributeCommissions(
                                $order->user,
                                $package,
                                $order->id,
                                $period
                            );

                            foreach ($commissions as $commission) {
                                $totalCommissions += $commission->amount;
                            }
                        }
                    }
                }
            }

            // Mettre à jour le total
            $period->total_commissions = $totalCommissions;
            $period->save();

            DB::commit();
            $period->update(['status' => 'calculated']);
            
            Log::info("Commissions mensuelles calculées pour la période {$period->period}", [
                'total' => $totalCommissions
            ]);
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul commissions mensuelles: ' . $e->getMessage());
            $period->update(['status' => 'pending', 'notes' => 'Erreur: ' . $e->getMessage()]);
            return false;
        }
    }

    /**
     * Générer les paiements des commissions
     */
    public function generatePayments($periodId): bool
    {
        $period = CommissionPeriod::findOrFail($periodId);
        $period->update(['status' => 'paying']);

        DB::beginTransaction();

        try {
            // Regrouper les commissions par utilisateur
            $commissionsByUser = Commission::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->select('user_id', DB::raw('SUM(amount) as total'))
                ->groupBy('user_id')
                ->get();

            $totalPaid = 0;

            foreach ($commissionsByUser as $item) {
                $user = User::find($item->user_id);
                if (!$user) continue;

                // Montant après taxes (5%)
                $taxAmount = $item->total * 0.05;
                $netAmount = $item->total - $taxAmount;

                // Créer le paiement
                $payment = CommissionPayment::create([
                    'user_id' => $user->id,
                    'commission_period_id' => $period->id,
                    'total_amount' => $item->total,
                    'tax_amount' => $taxAmount,
                    'net_amount' => $netAmount,
                    'status' => 'approved',
                ]);

                // Créditer le wallet
                $wallet = Wallet::where('user_id', $user->id)->first();
                if ($wallet) {
                    $balanceBefore = $wallet->balance;
                    $wallet->balance += $netAmount;
                    $wallet->save();

                    // Créer la transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $wallet->id,
                        'type' => 'commission',
                        'amount' => $netAmount,
                        'fee' => $taxAmount,
                        'net_amount' => $netAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'status' => 'completed',
                        'description' => "Commission mensuelle {$period->period}",
                        'metadata' => json_encode(['period' => $period->period]),
                        'completed_at' => now(),
                    ]);

                    // Marquer les commissions comme payées
                    Commission::where('commission_period_id', $period->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'paid',
                            'paid_at' => now()
                        ]);

                    $payment->status = 'paid';
                    $payment->paid_at = now();
                    $payment->save();

                    $totalPaid += $netAmount;
                }
            }

            // Mettre à jour la période
            $period->status = 'paid';
            $period->payment_date = now();
            $period->total_paid = $totalPaid;
            $period->save();

            DB::commit();
            
            Log::info("Paiements générés pour la période {$period->period}", [
                'total' => $totalPaid
            ]);
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération paiements: ' . $e->getMessage());
            $period->update(['status' => 'calculated', 'notes' => 'Erreur paiement: ' . $e->getMessage()]);
            return false;
        }
    }

    /**
     * Ajouter aux PV/BV de l'équipe
     */
    private function addTeamPVBV($user, $pv, $bv)
    {
        $current = $user->parrain;
        while ($current) {
            $current->team_pv += $pv;
            $current->team_bv += $bv;
            $current->save();
            $current = $current->parrain;
        }
    }

    /**
     * Compter les branches qualifiées
     */
    private function countQualifiedBranches($user): int
    {
        $count = 0;
        $filleuls = User::where('parrain_id', $user->id)->get();

        foreach ($filleuls as $filleul) {
            if ($this->isQualifiedBranch($filleul)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Vérifier si une branche est qualifiée
     */
    private function isQualifiedBranch($user): bool
    {
        $rankLevel = $user->rank ? $user->rank->level : 1;
        return $rankLevel >= 3;
    }
}