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
                'calculation_date' => $endDate,
                'payment_date' => $startDate->copy()->addMonth()->day(15),
                'status' => 'pending',
                'total_commissions' => 0,
                'total_paid' => 0,
            ]
        );
    }

    /**
     * Calculer les PV/BV mensuels
     */
    public function calculateMonthlyPVBV($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);
            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            // Réinitialiser les PV/BV mensuels
            User::withoutEvents(function () {
                User::query()->update([
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                    'team_pv' => 0,
                    'team_bv' => 0,
                ]);
            });

            // Récupérer les commandes de la période
            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $userPV = [];
            $userBV = [];

            foreach ($orders as $order) {
                $user = $order->user;
                if (!$user) continue;

                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $pv = $package->pv_value * $item->quantity;
                            $bv = $package->bv_value * $item->quantity;

                            if (!isset($userPV[$user->id])) {
                                $userPV[$user->id] = 0;
                                $userBV[$user->id] = 0;
                            }
                            $userPV[$user->id] += $pv;
                            $userBV[$user->id] += $bv;
                        }
                    }
                }
            }

            // Mettre à jour les utilisateurs
            foreach ($userPV as $userId => $pv) {
                $user = User::find($userId);
                if ($user) {
                    $user->monthly_pv = $pv;
                    $user->monthly_bv = $userBV[$userId] ?? 0;
                    $user->pv_balance += $pv;
                    $user->bv_balance += $userBV[$userId] ?? 0;
                    $user->saveQuietly();

                    // Mettre à jour les PV d'équipe
                    $this->addTeamPVBVWithoutEvents($user, $pv, $userBV[$userId] ?? 0);
                }
            }

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("PV/BV mensuels calculés pour la période {$period->period}", [
                'users_updated' => count($userPV),
                'total_pv' => array_sum($userPV),
            ]);
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul PV/BV mensuels: ' . $e->getMessage());
            if (isset($period)) {
                $period->update(['status' => 'pending', 'notes' => 'Erreur: ' . $e->getMessage()]);
            }
            return false;
        }
    }

    /**
     * Ajouter aux PV/BV de l'équipe sans événements
     */
    private function addTeamPVBVWithoutEvents($user, $pv, $bv)
    {
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 9;

        while ($current && $level <= $maxLevel) {
            User::withoutEvents(function () use ($current, $pv, $bv) {
                $current->team_pv += $pv;
                $current->team_bv += $bv;
                $current->saveQuietly();
            });
            $current = $current->parrain;
            $level++;
        }
    }

    /**
     * Calculer les rangs mensuels
     */
    public function calculateMonthlyRanks($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);
            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            User::withoutEvents(function () use ($period) {
                User::chunk(100, function ($users) use ($period) {
                    foreach ($users as $user) {
                        // Calculer et mettre à jour le grade
                        if (method_exists($user, 'calculateAndUpdateRank')) {
                            $user->calculateAndUpdateRank();
                        }
                        
                        // Enregistrer le grade mensuel
                        UserMonthlyRank::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'period' => $period->period,
                            ],
                            [
                                'rank_id' => $user->rank_id,
                                'rank_name' => $user->rank_name ?? 'Distributeur',
                                'rank_level' => $user->rank_level ?? 1,
                                'pv_monthly' => $user->monthly_pv ?? 0,
                                'bv_monthly' => $user->monthly_bv ?? 0,
                                'team_pv' => $user->team_pv ?? 0,
                                'team_bv' => $user->team_bv ?? 0,
                                'direct_sponsors' => User::where('parrain_id', $user->id)->count(),
                                'qualified_branches' => $this->countQualifiedBranches($user),
                            ]
                        );
                    }
                });
            });

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("Rangs mensuels calculés pour la période {$period->period}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul rangs mensuels: ' . $e->getMessage());
            if (isset($period)) {
                $period->update(['status' => 'pending', 'notes' => 'Erreur: ' . $e->getMessage()]);
            }
            return false;
        }
    }

    /**
     * Obtenir la progression du traitement
     */
    public function getProcessingProgress($periodId): array
    {
        $period = CommissionPeriod::findOrFail($periodId);

        $totalUsers = User::count();
        $processedUsers = UserMonthlyRank::where('period', $period->period)->count();

        $steps = [
            'pv_calculation' => $period->status === 'calculated' ? 100 : 0,
            'rank_calculation' => $processedUsers > 0 ? min(100, ($processedUsers / max($totalUsers, 1)) * 100) : 0,
            'commission_calculation' => Commission::where('commission_period_id', $period->id)->count() > 0 ? 100 : 0,
            'payment_generation' => $period->status === 'paid' ? 100 : 0,
        ];

        return [
            'period' => $period->period,
            'status' => $period->status_label,
            'steps' => $steps,
            'overall_progress' => array_sum($steps) / count($steps),
        ];
    }

    /**
     * Nettoyer une période
     */
    public function cleanPeriod($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            DB::beginTransaction();

            Commission::where('commission_period_id', $period->id)->delete();
            CommissionPayment::where('commission_period_id', $period->id)->delete();
            UserMonthlyRank::where('period', $period->period)->delete();

            $period->status = 'pending';
            $period->total_commissions = 0;
            $period->total_paid = 0;
            $period->notes = 'Nettoyé manuellement';
            $period->save();

            DB::commit();

            Log::info("Période {$period->period} nettoyée");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur nettoyage période: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculer les commissions mensuelles
     */
    public function calculateMonthlyCommissions($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            if ($period->status !== 'calculated' && $period->status !== 'pending') {
                Log::warning('Tentative de calcul des commissions avec statut invalide', [
                    'period' => $period->period,
                    'status' => $period->status,
                ]);
                return false;
            }

            $orderCount = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->count();

            if ($orderCount === 0) {
                Log::info('Aucune commande trouvée pour la période', [
                    'period' => $period->period,
                ]);
                $period->update([
                    'status' => 'calculated',
                    'total_commissions' => 0,
                    'notes' => 'Aucune commande dans cette période'
                ]);
                return true;
            }

            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            // Supprimer les anciennes commissions
            Commission::where('commission_period_id', $period->id)->delete();

            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $totalCommissions = 0;
            $commissionCount = 0;

            foreach ($orders as $order) {
                $user = $order->user;
                if (!$user) continue;

                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $commissions = $this->commissionDistributor->distributeCommissions(
                                $user,
                                $package,
                                $order->id,
                                $period
                            );

                            foreach ($commissions as $commission) {
                                $totalCommissions += $commission->amount;
                                $commissionCount++;
                            }
                        }
                    }
                }
            }

            $period->total_commissions = $totalCommissions;
            $period->notes = "{$commissionCount} commissions générées pour {$orderCount} commandes";
            $period->save();

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("Commissions mensuelles calculées pour la période {$period->period}", [
                'total' => $totalCommissions,
                'commission_count' => $commissionCount,
                'order_count' => $orderCount
            ]);
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul commissions mensuelles: ' . $e->getMessage());
            if (isset($period)) {
                $period->update([
                    'status' => 'pending',
                    'notes' => 'Erreur: ' . $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Générer les paiements des commissions
     */
    public function generatePayments($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            if ($period->status !== 'calculated') {
                Log::warning('Tentative de génération de paiements sans commissions', [
                    'period' => $period->period,
                    'status' => $period->status,
                ]);
                return false;
            }

            $commissionCount = Commission::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->count();

            if ($commissionCount === 0) {
                Log::info('Aucune commission en attente pour la période', [
                    'period' => $period->period,
                ]);
                $period->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                    'total_paid' => 0,
                    'notes' => 'Aucune commission à payer'
                ]);
                return true;
            }

            $period->update(['status' => 'paying']);

            DB::beginTransaction();

            CommissionPayment::where('commission_period_id', $period->id)->delete();

            $commissionsByUser = Commission::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->select('user_id', DB::raw('SUM(amount) as total'))
                ->groupBy('user_id')
                ->get();

            $totalPaid = 0;
            $paymentCount = 0;
            $pendingKycCount = 0;
            $usersProcessed = 0;

            foreach ($commissionsByUser as $item) {
                $user = User::find($item->user_id);
                if (!$user) {
                    Log::warning('Utilisateur non trouvé pour le paiement', [
                        'user_id' => $item->user_id,
                        'period' => $period->period
                    ]);
                    continue;
                }

                // VÉRIFICATION KYC
                if ($user->kyc_status !== 'verified') {
                    Log::info('KYC non vérifié, paiement en attente', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'kyc_status' => $user->kyc_status,
                        'period' => $period->period,
                        'amount' => $item->total,
                    ]);

                    CommissionPayment::create([
                        'user_id' => $user->id,
                        'commission_period_id' => $period->id,
                        'total_amount' => $item->total,
                        'tax_amount' => 0,
                        'net_amount' => 0,
                        'status' => 'pending',
                        'notes' => 'KYC non vérifié - paiement en attente de vérification',
                    ]);

                    $pendingKycCount++;
                    continue;
                }

                // Vérification compte actif
                if (!$user->is_active) {
                    Log::info('Utilisateur inactif, paiement ignoré', [
                        'user_id' => $user->id,
                        'period' => $period->period
                    ]);
                    continue;
                }

                // Vérification grade
                $userRank = $user->rankObject;
                if (!$userRank) {
                    Log::warning('Aucun grade trouvé pour l\'utilisateur', [
                        'user_id' => $user->id,
                        'period' => $period->period
                    ]);
                    continue;
                }

                // Vérification PV mensuel requis
                $monthlyPvRequired = $userRank->monthly_pv_required ?? 0;
                if ($user->monthly_pv < $monthlyPvRequired) {
                    Log::info('PV mensuel insuffisant pour le paiement', [
                        'user_id' => $user->id,
                        'monthly_pv' => $user->monthly_pv,
                        'required_pv' => $monthlyPvRequired,
                        'period' => $period->period
                    ]);

                    CommissionPayment::create([
                        'user_id' => $user->id,
                        'commission_period_id' => $period->id,
                        'total_amount' => $item->total,
                        'tax_amount' => 0,
                        'net_amount' => 0,
                        'status' => 'pending',
                        'notes' => "PV mensuel insuffisant ({$user->monthly_pv} PV requis: {$monthlyPvRequired} PV)",
                    ]);

                    continue;
                }

                // Vérification montant minimum
                $minPayment = config('commission.min_payment', 1);
                if ($item->total < $minPayment) {
                    Log::info('Montant inférieur au minimum de paiement', [
                        'user_id' => $user->id,
                        'amount' => $item->total,
                        'min_payment' => $minPayment,
                        'period' => $period->period
                    ]);

                    CommissionPayment::create([
                        'user_id' => $user->id,
                        'commission_period_id' => $period->id,
                        'total_amount' => $item->total,
                        'tax_amount' => 0,
                        'net_amount' => 0,
                        'status' => 'pending',
                        'notes' => "Montant inférieur au minimum de paiement ({$item->total} < {$minPayment})",
                    ]);

                    continue;
                }

                // Calcul des taxes
                $taxRate = config('commission.tax_rate', 5);
                $taxAmount = $item->total * ($taxRate / 100);
                $netAmount = $item->total - $taxAmount;

                // Créer le paiement
                $payment = CommissionPayment::create([
                    'user_id' => $user->id,
                    'commission_period_id' => $period->id,
                    'total_amount' => $item->total,
                    'tax_amount' => $taxAmount,
                    'net_amount' => $netAmount,
                    'status' => 'approved',
                    'notes' => "Paiement automatique pour la période {$period->period}",
                ]);

                // Créditer le wallet
                $wallet = Wallet::where('user_id', $user->id)->first();

                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'pending_balance' => 0,
                        'total_withdrawn' => 0,
                        'total_deposited' => 0,
                        'currency' => 'USD',
                        'is_active' => true,
                    ]);
                    Log::info('Wallet créé automatiquement', [
                        'user_id' => $user->id,
                        'period' => $period->period
                    ]);
                }

                if ($wallet) {
                    $balanceBefore = $wallet->balance;
                    $wallet->balance += $netAmount;
                    $wallet->total_deposited += $netAmount;
                    $wallet->save();

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
                        'reference' => 'COMM-' . $period->period . '-' . $user->id,
                        'description' => "Commission mensuelle {$period->period}",
                        'metadata' => json_encode([
                            'period' => $period->period,
                            'commission_period_id' => $period->id,
                            'tax_rate' => $taxRate,
                            'total_commission' => $item->total,
                        ]),
                        'completed_at' => now(),
                    ]);

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
                    $paymentCount++;
                    $usersProcessed++;

                    Log::debug('Paiement effectué', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'amount' => $netAmount,
                        'period' => $period->period
                    ]);
                }
            }

            $period->status = 'paid';
            $period->payment_date = now();
            $period->total_paid = $totalPaid;
            $period->notes = "{$paymentCount} paiements générés pour {$usersProcessed} utilisateurs. "
                . "{$pendingKycCount} paiements en attente de KYC.";
            $period->save();

            DB::commit();

            Log::info("Paiements générés pour la période {$period->period}", [
                'total' => $totalPaid,
                'payment_count' => $paymentCount,
                'users_processed' => $usersProcessed,
                'pending_kyc' => $pendingKycCount,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération paiements: ' . $e->getMessage(), [
                'period' => $period->period,
                'trace' => $e->getTraceAsString()
            ]);
            if (isset($period)) {
                $period->update([
                    'status' => 'calculated',
                    'notes' => 'Erreur paiement: ' . $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Réinitialiser les PV mensuels (le 7)
     */
    public function resetMonthlyPV(): bool
    {
        try {
            Log::info('Début de la réinitialisation des PV mensuels');

            DB::beginTransaction();

            $updated = User::withoutEvents(function () {
                return User::query()->update([
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                ]);
            });

            DB::commit();

            Log::info('Réinitialisation des PV mensuels terminée', [
                'users_updated' => $updated,
                'date' => now()->toDateString(),
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réinitialisation des PV mensuels', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Recalculer les PV mensuels
     */
    public function recalculateMonthlyPV(): bool
    {
        try {
            Log::info('Début du recalcul des PV mensuels');

            $currentPeriod = date('Y-m');
            $period = CommissionPeriod::where('period', $currentPeriod)
                ->whereIn('status', ['active', 'pending'])
                ->first();

            if (!$period) {
                Log::warning('Aucune période active trouvée pour le recalcul des PV');
                return false;
            }

            DB::beginTransaction();

            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $userPV = [];

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if (!isset($userPV[$order->user_id])) {
                        $userPV[$order->user_id] = 0;
                    }
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $userPV[$order->user_id] += $package->pv_value * $item->quantity;
                        }
                    }
                }
            }

            foreach ($userPV as $userId => $pv) {
                $user = User::find($userId);
                if ($user) {
                    $user->monthly_pv = $pv;
                    $user->saveQuietly();
                }
            }

            DB::commit();

            Log::info('Recalcul des PV mensuels terminé', [
                'users_updated' => count($userPV),
                'total_pv' => array_sum($userPV),
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du recalcul des PV mensuels', [
                'error' => $e->getMessage(),
            ]);
            return false;
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
        $rankLevel = $user->rank_level ?? 1;
        return $rankLevel >= 3;
    }
}