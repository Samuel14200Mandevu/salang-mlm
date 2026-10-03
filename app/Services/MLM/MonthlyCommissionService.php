<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\CommissionPayment;
use App\Models\UserMonthlyRank;
use App\Models\PVHistory;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonthlyCommissionService
{
    protected $rankCalculator;
    protected $commissionDistributor;
    protected $periodCalculator;
    protected TeamPVCalculator $teamPVCalculator;

    protected PaymentEligibilityChecker $paymentEligibilityChecker;

    protected CommissionHistoryPaymentSync $commissionHistoryPaymentSync;

    public function __construct(
        AdvancedRankCalculator $rankCalculator,
        CommissionDistributor $commissionDistributor,
        PeriodCommissionCalculator $periodCalculator,
        TeamPVCalculator $teamPVCalculator,
        PaymentEligibilityChecker $paymentEligibilityChecker,
        CommissionHistoryPaymentSync $commissionHistoryPaymentSync
    ) {
        $this->rankCalculator = $rankCalculator;
        $this->commissionDistributor = $commissionDistributor;
        $this->periodCalculator = $periodCalculator;
        $this->teamPVCalculator = $teamPVCalculator;
        $this->paymentEligibilityChecker = $paymentEligibilityChecker;
        $this->commissionHistoryPaymentSync = $commissionHistoryPaymentSync;
    }

    // ═══════════════════════════════════════════════════════════════
    // CRÉATION DE PÉRIODE (CALENDRIER MLM 8 → 7)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Créer la période MLM pour un mois donné.
     *
     * @param  int  $year   Année du DÉBUT de la période MLM
     * @param  int  $month  Mois du DÉBUT de la période MLM
     *
     * Ex : createMonthlyPeriod(2026, 10) → période "2026-10"
     *                                       du 08/10/2026 au 07/11/2026
     *                                       paiement le 15/11/2026
     */
    public function createMonthlyPeriod($year, $month): CommissionPeriod
    {
        $periodValue = sprintf('%04d-%02d', $year, $month);

        // ✅ Utilise la logique centralisée du modèle
        return CommissionPeriod::findOrCreateForValue($periodValue);
    }

    /**
     * Crée la période MLM courante (basée sur la date d'aujourd'hui).
     */
    public function createCurrentPeriod(): CommissionPeriod
    {
        $periodValue = CommissionPeriod::getPeriodValueForDate(now());
        return CommissionPeriod::findOrCreateForValue($periodValue);
    }

    // ═══════════════════════════════════════════════════════════════
    // CALCUL PV / BV
    // ═══════════════════════════════════════════════════════════════

    /**
     * Calculer les PV/BV mensuels depuis pv_history (source de vérité).
     *
     * ⚠️ NE TOUCHE PLUS pv_balance / bv_balance (gérés temps réel)
     * Recalcule uniquement : monthly_pv, monthly_bv, team_pv, team_bv
     */
    public function calculateMonthlyPVBV($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);
            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            // Réinitialiser SEULEMENT les compteurs mensuels (pas team_pv : source TeamPVCalculator)
            User::withoutEvents(function () {
                User::query()->update([
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                ]);
            });

            // Récupérer les PV depuis pv_history (source unique de vérité)
            $pvByUser = PVHistory::where('period', $period->period)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();

            // Récupérer les BV depuis les commandes
            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $userBV = [];
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->package_id) {
                        $package = \App\Models\Package::find($item->package_id);
                        if ($package) {
                            $bv = ($package->bv_value ?? 0) * $item->quantity;
                            $userBV[$order->user_id] = ($userBV[$order->user_id] ?? 0) + $bv;
                        }
                    }
                }
            }

            // Mettre à jour monthly_pv / monthly_bv
            foreach ($pvByUser as $userId => $pv) {
                $user = User::find($userId);
                if ($user) {
                    $user->monthly_pv = $pv;
                    $user->monthly_bv = $userBV[$userId] ?? 0;
                    $user->saveQuietly();
                }
            }

            // Recalcul team_pv / team_bv via la source de vérité
            User::withoutEvents(function () {
                User::where('is_active', true)->chunkById(100, function ($users) {
                    foreach ($users as $user) {
                        $this->teamPVCalculator->updateUser($user);
                    }
                });
            });

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("PV/BV mensuels calculés pour {$period->period}", [
                'users_updated' => count($pvByUser),
                'total_pv'      => array_sum($pvByUser),
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
     * Propager team_pv / team_bv vers les uplines (12 niveaux)
     */
    private function addTeamPVBVWithoutEvents($user, $pv, $bv)
    {
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 12;

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

    // ═══════════════════════════════════════════════════════════════
    // CALCUL DES RANGS
    // ═══════════════════════════════════════════════════════════════

    public function calculateMonthlyRanks($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);
            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            User::withoutEvents(function () use ($period) {
                User::chunk(100, function ($users) use ($period) {
                    foreach ($users as $user) {
                        if (method_exists($user, 'calculateAndUpdateRank')) {
                            $user->calculateAndUpdateRank();
                        }

                        UserMonthlyRank::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'period'  => $period->period,
                            ],
                            [
                                'rank_id'            => $user->rank_id,
                                'rank_name'          => $user->rank_name ?? 'Distributeur',
                                'rank_level'         => $user->rank_level ?? 1,
                                'pv_monthly'         => $user->monthly_pv ?? 0,
                                'bv_monthly'         => $user->monthly_bv ?? 0,
                                'team_pv'            => $user->team_pv ?? 0,
                                'team_bv'            => $user->team_bv ?? 0,
                                'direct_sponsors'    => User::where('parrain_id', $user->id)->count(),
                                'qualified_branches' => $this->countQualifiedBranches($user),
                            ]
                        );
                    }
                });
            });

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("Rangs mensuels calculés pour {$period->period}");
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

    // ═══════════════════════════════════════════════════════════════
    // CALCUL DES COMMISSIONS (2 PHASES)
    // ═══════════════════════════════════════════════════════════════

    public function calculateMonthlyCommissions($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            if (!in_array($period->status, ['calculated', 'pending'])) {
                Log::warning('Statut invalide pour calcul commissions', [
                    'period' => $period->period,
                    'status' => $period->status,
                ]);
                return false;
            }

            $period->update(['status' => 'calculating']);

            DB::beginTransaction();

            // Nettoyer les anciennes commissions de la période
            Commission::where('commission_period_id', $period->id)->delete();

            $orders = \App\Models\Order::whereBetween('paid_at', [
                $period->start_date,
                $period->end_date
            ])->where('payment_status', 'completed')->get();

            $totalCommissions = 0;
            $commissionCount = 0;

            // ══════════════════════════════════════════════════════
            // PHASE 1 : TEMPS RÉEL (cash_pos + sponsor + direct)
            // ══════════════════════════════════════════════════════
            foreach ($orders as $order) {
                $user = $order->user;
                if (!$user) continue;

                foreach ($order->items as $item) {
                    $itemData = null;
                    if ($item->package_id) {
                        $itemData = \App\Models\Package::find($item->package_id);
                    } elseif ($item->product_id) {
                        $itemData = \App\Models\Product::find($item->product_id);
                    }

                    if ($itemData) {
                        $commissions = $this->commissionDistributor->distributeCommissions(
                            $user,
                            $itemData,
                            $order->id,
                            $period,
                            true
                        );

                        foreach ($commissions as $commission) {
                            $totalCommissions += $commission->amount;
                            $commissionCount++;
                        }
                    }
                }
            }

            // ══════════════════════════════════════════════════════
            // PHASE 2 : DIFFÉRÉE (indirect + leadership)
            // ══════════════════════════════════════════════════════
            $periodResult = $this->periodCalculator->calculateForPeriod($period);
            $totalCommissions += $periodResult['total_amount'];
            $commissionCount += $periodResult['created'];

            $period->total_commissions = $totalCommissions;
            $period->notes = "{$commissionCount} commissions générées pour {$orders->count()} commandes";
            $period->save();

            DB::commit();
            $period->update(['status' => 'calculated']);

            Log::info("Commissions mensuelles calculées pour {$period->period}", [
                'total'            => $totalCommissions,
                'commission_count' => $commissionCount,
                'order_count'      => $orders->count(),
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur calcul commissions mensuelles: ' . $e->getMessage());
            if (isset($period)) {
                $period->update([
                    'status' => 'pending',
                    'notes'  => 'Erreur: ' . $e->getMessage()
                ]);
            }
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // NETTOYAGE
    // ═══════════════════════════════════════════════════════════════

    public function cleanPeriod($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            DB::beginTransaction();

            Commission::where('commission_period_id', $period->id)->delete();
            CommissionPayment::where('commission_period_id', $period->id)->delete();
            UserMonthlyRank::where('period', $period->period)->delete();
            PVHistory::where('period', $period->period)->delete();

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

    // ═══════════════════════════════════════════════════════════════
    // PROGRESSION
    // ═══════════════════════════════════════════════════════════════

    public function getProcessingProgress($periodId): array
    {
        $period = CommissionPeriod::findOrFail($periodId);

        $totalUsers = User::count();
        $processedUsers = UserMonthlyRank::where('period', $period->period)->count();

        $steps = [
            'pv_calculation'         => in_array($period->status, ['calculated', 'paid']) ? 100 : 0,
            'rank_calculation'       => $processedUsers > 0
                ? min(100, ($processedUsers / max($totalUsers, 1)) * 100)
                : 0,
            'commission_calculation' => Commission::where('commission_period_id', $period->id)->count() > 0 ? 100 : 0,
            'payment_generation'     => $period->status === 'paid' ? 100 : 0,
        ];

        return [
            'period'           => $period->period,
            'status'           => $period->status_label,
            'steps'            => $steps,
            'overall_progress' => array_sum($steps) / count($steps),
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // PAIEMENTS (le 15 du mois M+1)
    // ═══════════════════════════════════════════════════════════════

    public function generatePayments($periodId): bool
    {
        try {
            $period = CommissionPeriod::findOrFail($periodId);

            $blockReason = $period->paymentBlockReason();
            if ($blockReason !== null) {
                Log::warning('generatePayments refused', [
                    'period' => $period->period,
                    'reason' => $blockReason,
                    'status' => $period->status,
                    'is_historical' => $period->is_historical,
                    'is_hidden' => $period->is_hidden,
                ]);

                return false;
            }

            $commissionCount = Commission::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->count();

            if ($commissionCount === 0) {
                $period->update([
                    'status'       => 'paid',
                    'payment_date' => now(),
                    'total_paid'   => 0,
                    'notes'        => 'Aucune commission à payer'
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

            foreach ($commissionsByUser as $item) {
                $user = User::find($item->user_id);
                if (! $user) {
                    continue;
                }

                $gross = (float) $item->total;
                $eligibility = $this->paymentEligibilityChecker->evaluate(
                    $user,
                    $period->period,
                    $gross
                );

                if ($eligibility->shouldCreateDeferredPayment()) {
                    CommissionPayment::create([
                        'user_id'              => $user->id,
                        'commission_period_id' => $period->id,
                        'total_amount'         => $gross,
                        'tax_amount'           => 0,
                        'net_amount'           => 0,
                        'status'               => 'pending',
                        'notes'                => $eligibility->message,
                    ]);
                    if ($eligibility->reasonCode === 'kyc_pending') {
                        $pendingKycCount++;
                    }
                    continue;
                }

                $tax = $this->paymentEligibilityChecker->calculateNetAmount($gross);
                $taxRate = $tax['tax_rate'];
                $taxAmount = $tax['tax_amount'];
                $netAmount = $tax['net_amount'];

                $payment = CommissionPayment::create([
                    'user_id'              => $user->id,
                    'commission_period_id' => $period->id,
                    'total_amount'         => $item->total,
                    'tax_amount'           => $taxAmount,
                    'net_amount'           => $netAmount,
                    'status'               => 'approved',
                    'notes'                => "Paiement automatique {$period->period}",
                ]);

                // Wallet
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'balance'          => 0,
                        'pending_balance'  => 0,
                        'total_withdrawn'  => 0,
                        'total_deposited'  => 0,
                        'currency'         => 'USD',
                        'is_active'        => true,
                    ]
                );

                $balanceBefore = $wallet->balance;
                $wallet->balance += $netAmount;
                $wallet->total_deposited += $netAmount;
                $wallet->save();

                Transaction::create([
                    'user_id'        => $user->id,
                    'wallet_id'      => $wallet->id,
                    'type'           => 'commission',
                    'amount'         => $netAmount,
                    'fee'            => $taxAmount,
                    'net_amount'     => $netAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $wallet->balance,
                    'status'         => 'completed',
                    'reference'      => 'COMM-' . $period->period . '-' . $user->id,
                    'description'    => "Commission mensuelle {$period->period}",
                    'metadata'       => json_encode([
                        'period'               => $period->period,
                        'commission_period_id' => $period->id,
                        'tax_rate'             => $taxRate,
                        'total_commission'     => $item->total,
                    ]),
                    'completed_at'   => now(),
                ]);

                Commission::where('commission_period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->update(['status' => 'paid', 'paid_at' => now()]);

                $this->commissionHistoryPaymentSync->markPaidForUserPeriod(
                    $user->id,
                    $period->period
                );

                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->save();

                $totalPaid += $netAmount;
                $paymentCount++;
            }

            $period->status = 'paid';
            $period->payment_date = now();
            $period->total_paid = $totalPaid;
            $period->notes = "{$paymentCount} paiements générés. {$pendingKycCount} en attente KYC.";
            $period->save();

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération paiements: ' . $e->getMessage());
            if (isset($period)) {
                $period->update([
                    'status' => 'calculated',
                    'notes'  => 'Erreur paiement: ' . $e->getMessage()
                ]);
            }
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // RESET PV MENSUELS (le 8 à 00h00)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Reset monthly_pv / monthly_bv.
     * Appelée le 8 à 00h00 → début du nouveau mois MLM.
     */
    public function resetMonthlyPV(): bool
    {
        try {
            Log::info('Reset PV mensuels — début');

            DB::beginTransaction();

            $updated = User::withoutEvents(function () {
                return User::query()->update([
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                ]);
            });

            DB::commit();

            Log::info('Reset PV mensuels — terminé', [
                'users_updated' => $updated,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur reset PV mensuels: ' . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // RECALCUL PV
    // ═══════════════════════════════════════════════════════════════

    /**
     * ✅ CORRIGÉ : utilise getPeriodValueForDate() (calendrier MLM)
     */
    public function recalculateMonthlyPV(): bool
    {
        try {
            // ✅ Utilise la logique MLM
            $currentPeriodValue = CommissionPeriod::getPeriodValueForDate(now());

            $period = CommissionPeriod::where('period', $currentPeriodValue)
                ->whereIn('status', ['active', 'pending'])
                ->first();

            if (!$period) return false;

            DB::beginTransaction();

            $pvByUser = PVHistory::where('period', $period->period)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();

            foreach ($pvByUser as $userId => $pv) {
                User::where('id', $userId)->update(['monthly_pv' => $pv]);
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur recalcul PV mensuels: ' . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function countQualifiedBranches($user): int
    {
        $count = 0;
        foreach (User::where('parrain_id', $user->id)->get() as $filleul) {
            if (($filleul->rank_level ?? 1) >= 3) $count++;
        }
        return $count;
    }
}