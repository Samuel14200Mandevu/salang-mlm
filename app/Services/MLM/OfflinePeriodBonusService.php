<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Calcule les bonus d'un mois « hors système » à partir des PV importés.
 * Écrit dans `commissions` (payables) et `commission_history` (affichage admin).
 */
class OfflinePeriodBonusService
{
    public function __construct(
        protected PeriodCommissionCalculator $calculator
    ) {}

    /**
     * @return array{
     *   period:string,
     *   created:int,
     *   total_amount:float,
     *   members:int,
     *   history_rows:int
     * }
     */
    public function calculate(string $periodValue): array
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $periodValue)) {
            throw new \InvalidArgumentException('Période invalide (attendu YYYY-MM).');
        }

        $pvCount = PVHistory::query()->where('period', $periodValue)->count();
        if ($pvCount === 0) {
            throw new \RuntimeException(
                "Aucun PV importé pour {$periodValue}. Importez d'abord le fichier Excel."
            );
        }

        $period = CommissionPeriod::findOrCreateForValue($periodValue);
        $period->status = 'calculating';
        $period->save();

        DB::beginTransaction();
        try {
            $result = $this->calculator->calculateForPeriod($period);

            $historyRows = $this->mirrorToCommissionHistory($period);

            $period->status = 'calculated';
            $period->calculation_date = now();
            $period->total_commissions = (float) $result['total_amount'];
            $period->notes = trim(($period->notes ? $period->notes."\n" : '').
                'Calcul caissier hors-système le '.now()->format('Y-m-d H:i'));
            $period->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $period->status = 'failed';
            $period->save();
            Log::error('OfflinePeriodBonusService failed', [
                'period' => $periodValue,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $members = Commission::query()
            ->where('commission_period_id', $period->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->pluck('user_id')
            ->unique()
            ->count();

        return [
            'period' => $periodValue,
            'created' => (int) $result['created'],
            'total_amount' => (float) $result['total_amount'],
            'members' => $members,
            'history_rows' => $historyRows,
        ];
    }

    /**
     * Recopie les commissions D/I/L de la période dans commission_history
     * (comme si le système les avait générées).
     */
    protected function mirrorToCommissionHistory(CommissionPeriod $period): int
    {
        CommissionHistory::query()
            ->where('period', $period->period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->delete();

        $rows = Commission::query()
            ->where('commission_period_id', $period->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->get();

        $created = 0;
        foreach ($rows as $commission) {
            $history = CommissionHistory::create([
                'user_id' => $commission->user_id,
                'from_user_id' => $commission->from_user_id,
                'period' => $period->period,
                'type' => $commission->type,
                'amount' => $commission->amount,
                'percentage' => $commission->percentage,
                'pv_used' => $commission->pv_used ?? null,
                'generation' => $commission->generation,
                'status' => 'calculated',
                'description' => $commission->description,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('commissions', 'source_commission_history_id')) {
                $commission->source_commission_history_id = $history->id;
                $commission->saveQuietly();
            }

            $created++;
        }

        return $created;
    }
}
