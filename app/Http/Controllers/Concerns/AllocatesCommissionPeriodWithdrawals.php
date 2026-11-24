<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Commission;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

trait AllocatesCommissionPeriodWithdrawals
{
    /**
     * Attribue les retraits completed au bonus d’une période (FIFO par période).
     * Ainsi un paiement remis en novembre pour des bonus octobre réduit bien le Reste d’octobre.
     *
     * @param  list<int>  $userIds
     * @param  list<string>  $coreTypes
     * @return array<int, float> user_id => montant payé alloué à $selectedPeriod
     */
    protected function allocateCompletedWithdrawalsToPeriod(array $userIds, string $selectedPeriod, array $coreTypes): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === [] || $selectedPeriod === '') {
            return [];
        }

        $withdrawnByUser = Withdrawal::query()
            ->where('status', 'completed')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('SUM(amount) as total_paid'))
            ->groupBy('user_id')
            ->pluck('total_paid', 'user_id');

        $commissionRows = Commission::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('type', $coreTypes)
            ->whereNotNull('period')
            ->where('period', '!=', '')
            ->select('user_id', 'period', DB::raw('SUM(amount) as total'))
            ->groupBy('user_id', 'period')
            ->get()
            ->groupBy('user_id');

        $paidByUser = [];
        foreach ($userIds as $userId) {
            $remaining = round((float) ($withdrawnByUser[$userId] ?? 0), 2);
            $allocated = 0.0;

            $periods = ($commissionRows->get($userId) ?? collect())
                ->sortBy('period')
                ->values();

            foreach ($periods as $row) {
                if ($remaining <= 0) {
                    break;
                }

                $periodTotal = round((float) ($row->total ?? 0), 2);
                $take = min($remaining, $periodTotal);
                $remaining = round($remaining - $take, 2);

                if ((string) $row->period === $selectedPeriod) {
                    $allocated = $take;
                    break;
                }
            }

            $paidByUser[$userId] = $allocated;
        }

        return $paidByUser;
    }

    /**
     * Remises guichet par type (Direct / I&L) pour le détail membre.
     *
     * @param  list<string>  $coreTypes
     * @param  \Illuminate\Support\Collection<int, \App\Models\Commission>  $commissions
     * @return list<array{
     *     key:string,
     *     label:string,
     *     total:float,
     *     remitted:float,
     *     reste:float,
     *     wallet:float,
     *     pending:float,
     *     status:string,
     *     status_label:string,
     *     coverage:string
     * }>
     */
    protected function memberPeriodPhasePayouts(
        int $userId,
        ?string $selectedPeriod,
        array $coreTypes,
        $commissions
    ): array {
        $directTotal = round((float) $commissions->where('type', 'direct')->sum('amount'), 2);
        $ilTotal = round(
            (float) $commissions->where('type', 'indirect')->sum('amount')
            + (float) $commissions->where('type', 'leadership')->sum('amount'),
            2
        );
        $directWallet = round((float) $commissions->where('type', 'direct')->where('status', 'paid')->sum('amount'), 2);
        $ilWallet = round(
            (float) $commissions->where('type', 'indirect')->where('status', 'paid')->sum('amount')
            + (float) $commissions->where('type', 'leadership')->where('status', 'paid')->sum('amount'),
            2
        );
        $directPending = round(
            (float) $commissions->where('type', 'direct')->whereIn('status', ['pending', 'approved'])->sum('amount'),
            2
        );
        $ilPending = round(
            (float) $commissions->whereIn('type', ['indirect', 'leadership'])->whereIn('status', ['pending', 'approved'])->sum('amount'),
            2
        );

        $remittedPeriod = 0.0;
        if ($selectedPeriod) {
            $remittedPeriod = (float) ($this->allocateCompletedWithdrawalsToPeriod(
                [$userId],
                $selectedPeriod,
                $coreTypes
            )[$userId] ?? 0);
        } else {
            $remittedPeriod = round((float) Withdrawal::query()
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->sum('amount'), 2);
        }

        // Même ordre que le guichet : Direct d’abord, puis I&L.
        $retireDirect = round(min($remittedPeriod, $directTotal), 2);
        $retireIl = round(min(max(0, $remittedPeriod - $directTotal), $ilTotal), 2);

        $build = function (
            string $key,
            string $label,
            float $total,
            float $remitted,
            float $wallet,
            float $pending
        ): array {
            $reste = round(max(0, $total - $remitted), 2);
            $stillInWallet = round(max(0, $wallet - $remitted), 2);
            if ($total <= 0) {
                $status = 'empty';
                $statusLabel = '—';
                $coverage = 'Aucun bonus';
            } elseif ($reste <= 0.004) {
                $status = 'paye';
                $statusLabel = 'Payé';
                $coverage = 'Tout payé au guichet';
            } elseif ($remitted > 0.004) {
                $status = 'partiel';
                $statusLabel = 'Partiel';
                $pct = $total > 0 ? round(($remitted / $total) * 100) : 0;
                $coverage = ($pct >= 45 && $pct <= 55)
                    ? 'Moitié payée ('.$pct.' %)'
                    : 'Partiellement payé ('.$pct.' %)';
            } elseif ($wallet > 0.004) {
                $status = 'portefeuille';
                $statusLabel = 'En portefeuille';
                $coverage = 'Créditée au portefeuille, pas encore remis';
            } else {
                $status = 'attente';
                $statusLabel = 'En attente';
                $coverage = 'Pas encore envoyée au portefeuille';
            }

            return [
                'key' => $key,
                'label' => $label,
                'total' => $total,
                'remitted' => $remitted,
                'reste' => $reste,
                'wallet' => $stillInWallet,
                'pending' => $pending,
                'status' => $status,
                'status_label' => $statusLabel,
                'coverage' => $coverage,
            ];
        };

        return array_values(array_filter([
            $directTotal > 0 || $retireDirect > 0
                ? $build('direct', 'Direct', $directTotal, $retireDirect, $directWallet, $directPending)
                : null,
            $ilTotal > 0 || $retireIl > 0
                ? $build('il', 'Indirect & Leadership', $ilTotal, $retireIl, $ilWallet, $ilPending)
                : null,
        ]));
    }
}
