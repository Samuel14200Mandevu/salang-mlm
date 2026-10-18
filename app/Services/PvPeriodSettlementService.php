<?php

namespace App\Services;

use App\Jobs\RecalculateAfterPVImport;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\PvTransaction;
use App\Models\UserPvBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clôture MLM (après le 7) : crédite le reliquat available_pv → pv_balance + pv_history.
 * - Parrain sans filleuls membres : totalité des PV ventes POS de la période.
 * - Parrain avec filleuls : reliquat non distribué durant 8→7.
 */
class PvPeriodSettlementService
{
    public function settleClosedPeriod(?string $periodValue = null, bool $dryRun = false): array
    {
        $periodValue = $periodValue ?? CommissionPeriod::getClosedPeriodValue();
        CommissionPeriod::findOrCreateForValue($periodValue);
        $dates = CommissionPeriod::getPeriodDates($periodValue);
        $closureDate = $dates['end']->toDateString();

        $stats = [
            'period' => $periodValue,
            'dry_run' => $dryRun,
            'users_settled' => 0,
            'pv_settled' => 0,
            'bv_settled' => 0,
            'user_ids' => [],
        ];

        $query = UserPvBalance::query()
            ->where(function ($q) {
                $q->where('available_pv', '>', 0)
                    ->orWhere('available_bv', '>', 0);
            })
            ->with('user');

        $query->chunkById(100, function ($balances) use (&$stats, $periodValue, $closureDate, $dryRun) {
            foreach ($balances as $balance) {
                $user = $balance->user;
                if (! $user || ! $user->is_active) {
                    continue;
                }

                $pvAmount = (int) $balance->available_pv;
                $bvAmount = (int) $balance->available_bv;

                if ($pvAmount <= 0 && $bvAmount <= 0) {
                    continue;
                }

                $hasMemberDownlines = $user->hasDirectMemberDownlines();
                $note = $hasMemberDownlines
                    ? 'Clôture période MLM — reliquat non distribué (ventes POS 8→7)'
                    : 'Clôture période MLM — PV ventes clients (sans filleul membre)';

                if ($dryRun) {
                    $stats['users_settled']++;
                    $stats['pv_settled'] += $pvAmount;
                    $stats['bv_settled'] += $bvAmount;
                    $stats['user_ids'][] = $user->id;

                    continue;
                }

                DB::transaction(function () use (
                    $balance,
                    $user,
                    $pvAmount,
                    $bvAmount,
                    $periodValue,
                    $closureDate,
                    $note,
                    &$stats
                ) {
                    $locked = UserPvBalance::where('id', $balance->id)->lockForUpdate()->first();
                    if (! $locked) {
                        return;
                    }

                    $pvAmount = (int) $locked->available_pv;
                    $bvAmount = (int) $locked->available_bv;

                    if ($pvAmount <= 0 && $bvAmount <= 0) {
                        return;
                    }

                    if ($pvAmount > 0) {
                        $user->increment('pv_balance', $pvAmount);
                    }
                    if ($bvAmount > 0) {
                        $user->increment('bv_balance', $bvAmount);
                    }

                    PVHistory::create([
                        'user_id' => $user->id,
                        'amount' => $pvAmount,
                        'date' => $closureDate,
                        'period' => $periodValue,
                        'type' => 'personal',
                        'notes' => $note,
                        'created_by' => null,
                    ]);

                    PvTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'source' => 'period_settlement',
                        'pv_amount' => $pvAmount,
                        'bv_amount' => $bvAmount,
                        'description' => "Clôture {$periodValue} — validation PV portefeuille",
                        'metadata' => [
                            'mlm_period' => $periodValue,
                            'has_member_downlines' => $user->hasDirectMemberDownlines(),
                            'settlement_type' => $user->hasDirectMemberDownlines() ? 'remainder' : 'leaf_sponsor',
                        ],
                    ]);

                    $locked->available_pv = 0;
                    $locked->available_bv = 0;
                    $locked->last_updated_at = now();
                    $locked->save();

                    $stats['users_settled']++;
                    $stats['pv_settled'] += $pvAmount;
                    $stats['bv_settled'] += $bvAmount;
                    $stats['user_ids'][] = $user->id;
                });
            }
        });

        $stats['user_ids'] = array_values(array_unique($stats['user_ids']));

        if (! $dryRun && $stats['user_ids'] !== []) {
            RecalculateAfterPVImport::dispatch($stats['user_ids'], $periodValue)
                ->onQueue('rank-recalculation');

            Log::info('Clôture PV période MLM terminée', $stats);
        }

        return $stats;
    }
}
