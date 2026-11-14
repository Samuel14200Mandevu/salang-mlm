<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\User;
use App\Services\MLM\TeamPVCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Calcule les bonus d'un mois « hors système » à partir des PV importés.
 * Écrit dans `commissions` (payables) et `commission_history` (affichage admin).
 */
class OfflinePeriodBonusService
{
    public function __construct(
        protected PeriodCommissionCalculator $calculator,
        protected TeamPVCalculator $teamPVCalculator,
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
     * Recalcule D/I/L pour un membre + (optionnel) ascendants + descendants
     * sur une période, sans recalculer tout le mois.
     *
     * @return array{
     *   period:string,
     *   member:string,
     *   created:int,
     *   total_amount:float,
     *   processed:int,
     *   skipped_no_pv:int,
     *   branch_size:int,
     *   history_rows:int
     * }
     */
    public function calculateBranch(
        string $periodValue,
        string $memberRef,
        bool $withAncestors = true,
        bool $withDescendants = true,
    ): array {
        if (! preg_match('/^\d{4}-\d{2}$/', $periodValue)) {
            throw new \InvalidArgumentException('Période invalide (attendu YYYY-MM).');
        }

        $memberRef = trim($memberRef);
        if ($memberRef === '') {
            throw new \InvalidArgumentException('Indiquez le code membre (ou l’ID) concerné.');
        }

        $pvCount = PVHistory::query()->where('period', $periodValue)->count();
        if ($pvCount === 0) {
            throw new \RuntimeException(
                "Aucun PV importé pour {$periodValue}. Importez d'abord le fichier Excel."
            );
        }

        $member = $this->resolveMember($memberRef);
        if (! $member) {
            throw new \RuntimeException("Membre introuvable : {$memberRef}");
        }

        $branchIds = collect([(int) $member->id]);

        if ($withAncestors) {
            $branchIds = $branchIds->merge($this->teamPVCalculator->getAncestorIds($member));
        }

        if ($withDescendants) {
            $branchIds = $branchIds->merge(
                $member->getDescendants()->pluck('id')->map(fn ($id) => (int) $id)
            );
        }

        $branchIds = $branchIds->unique()->filter()->values();
        $period = CommissionPeriod::findOrCreateForValue($periodValue);

        $created = 0;
        $totalAmount = 0.0;
        $processedIds = [];
        $skippedNoPv = 0;

        DB::beginTransaction();
        try {
            foreach ($branchIds as $userId) {
                $uid = (int) $userId;
                $hasPv = PVHistory::query()
                    ->where('period', $periodValue)
                    ->where('user_id', $uid)
                    ->exists();

                if (! $hasPv) {
                    $skippedNoPv++;
                    continue;
                }

                $result = $this->calculator->calculateForPeriod($period, $uid);
                $created += (int) $result['created'];
                $totalAmount += (float) $result['total_amount'];
                $processedIds[] = $uid;
            }

            $historyRows = $this->mirrorToCommissionHistory($period, $processedIds);

            $period->total_commissions = (float) Commission::query()
                ->where('commission_period_id', $period->id)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->sum('amount');
            $period->notes = trim(($period->notes ? $period->notes."\n" : '').
                'Recalcul branche '.$member->sponsor_id.' ('.count($processedIds).' membre(s)) le '.now()->format('Y-m-d H:i'));
            if ($period->status === 'pending' || $period->status === 'failed') {
                $period->status = 'calculated';
                $period->calculation_date = now();
            }
            $period->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OfflinePeriodBonusService::calculateBranch failed', [
                'period' => $periodValue,
                'member' => $memberRef,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return [
            'period' => $periodValue,
            'member' => (string) ($member->sponsor_id ?: $member->id),
            'created' => $created,
            'total_amount' => $totalAmount,
            'processed' => count($processedIds),
            'skipped_no_pv' => $skippedNoPv,
            'branch_size' => $branchIds->count(),
            'history_rows' => $historyRows,
        ];
    }

    protected function resolveMember(string $memberRef): ?User
    {
        $query = User::query()->where('user_type', 'member');

        $byCode = (clone $query)->where('sponsor_id', $memberRef)->first();
        if ($byCode) {
            return $byCode;
        }

        if (ctype_digit($memberRef)) {
            return (clone $query)->whereKey((int) $memberRef)->first();
        }

        return null;
    }

    /**
     * Recopie les commissions D/I/L de la période dans commission_history
     * (comme si le système les avait générées).
     *
     * @param  list<int>|null  $userIds  Si fourni, ne resynchronise que ces bénéficiaires.
     */
    protected function mirrorToCommissionHistory(CommissionPeriod $period, ?array $userIds = null): int
    {
        $historyQuery = CommissionHistory::query()
            ->where('period', $period->period)
            ->whereIn('type', ['direct', 'indirect', 'leadership']);

        $commissionQuery = Commission::query()
            ->where('commission_period_id', $period->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership']);

        if ($userIds !== null) {
            if ($userIds === []) {
                return 0;
            }
            $historyQuery->whereIn('user_id', $userIds);
            $commissionQuery->whereIn('user_id', $userIds);
        }

        $historyQuery->delete();

        $rows = $commissionQuery->get();

        $created = 0;
        foreach ($rows as $commission) {
            $history = CommissionHistory::create([
                'user_id' => $commission->user_id,
                'from_user_id' => $commission->from_user_id,
                'period' => $period->period,
                'type' => $commission->type,
                'amount' => $commission->amount,
                'percentage' => $commission->percentage,
                'pv_used' => (float) ($commission->pv_used ?? 0),
                'generation' => $commission->generation,
                'status' => 'pending',
                'description' => $commission->description,
            ]);

            if (Schema::hasColumn('commissions', 'source_commission_history_id')) {
                $commission->source_commission_history_id = $history->id;
                $commission->saveQuietly();
            }

            $created++;
        }

        return $created;
    }
}
