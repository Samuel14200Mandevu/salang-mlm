<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\User;
use App\Services\MLM\PeriodCommissionCalculator;
use Illuminate\Console\Command;

class PurgeSourceCommissions extends Command
{
    protected $signature = 'commissions:purge-source
                            {member : Code membre (sponsor_id) ou ID user}
                            {--period= : Période YYYY-MM (défaut : toutes les périodes avec D/I/L non payés liés)}
                            {--dry-run : Afficher sans modifier}';

    protected $description = 'Retire le bonus non payé d’un membre et les I&L non payés des ascendants issus de lui, puis recalcule';

    public function handle(PeriodCommissionCalculator $calculator): int
    {
        $memberArg = trim((string) $this->argument('member'));
        $dryRun = (bool) $this->option('dry-run');

        $user = User::query()
            ->when(
                ctype_digit($memberArg),
                fn ($q) => $q->where('sponsor_id', $memberArg)->orWhere('id', (int) $memberArg),
                fn ($q) => $q->where('sponsor_id', $memberArg)
            )
            ->first(['id', 'name', 'sponsor_id', 'parrain_id', 'pv_balance', 'monthly_pv']);

        if (! $user) {
            $this->error("Membre introuvable : {$memberArg}");

            return self::FAILURE;
        }

        $this->info("Membre : {$user->name} (#{$user->id} · code {$user->sponsor_id})");
        $this->line('PV balance : '.(float) $user->pv_balance.' · monthly_pv : '.(float) $user->monthly_pv);

        $periodOpt = $this->option('period');
        $periodValues = [];

        if (is_string($periodOpt) && $periodOpt !== '') {
            $periodValues = [$periodOpt];
        } else {
            $fromHistory = PVHistory::query()
                ->where('user_id', $user->id)
                ->whereNotNull('period')
                ->distinct()
                ->pluck('period')
                ->all();

            $fromCommissions = Commission::query()
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('from_user_id', $user->id);
                })
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->whereIn('status', ['pending', 'approved', 'cancelled'])
                ->whereNotNull('period')
                ->distinct()
                ->pluck('period')
                ->all();

            $periodValues = array_values(array_unique(array_filter([...$fromHistory, ...$fromCommissions])));
        }

        if ($periodValues === []) {
            $periodValues = [CommissionPeriod::getPeriodValueForDate(now())];
        }

        sort($periodValues);

        $ownUnpaid = Commission::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'cancelled'])
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->get(['id', 'type', 'amount', 'period', 'status']);

        $ilUnpaid = Commission::query()
            ->where('from_user_id', $user->id)
            ->whereIn('type', ['indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'cancelled'])
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->get(['id', 'user_id', 'type', 'amount', 'period', 'status']);

        $ownPaid = Commission::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->count();

        $ilPaid = Commission::query()
            ->where('from_user_id', $user->id)
            ->whereIn('type', ['indirect', 'leadership'])
            ->where('status', 'paid')
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->count();

        $this->table(
            ['Cible', 'Non payés', 'Montant', 'Payés (conservés)'],
            [
                [
                    'Bonus du membre (D/I/L)',
                    $ownUnpaid->count(),
                    '$'.number_format((float) $ownUnpaid->sum('amount'), 2),
                    $ownPaid,
                ],
                [
                    'I&L ascendants (from lui)',
                    $ilUnpaid->count(),
                    '$'.number_format((float) $ilUnpaid->sum('amount'), 2),
                    $ilPaid,
                ],
            ]
        );

        $this->line('Périodes : '.implode(', ', $periodValues));
        $ancestorIds = $calculator->ancestorIds((int) $user->id);
        $this->line('Ascendants à recalculer : '.count($ancestorIds));

        if ($dryRun) {
            $this->warn('Dry-run : aucune modification.');

            return self::SUCCESS;
        }

        foreach ($periodValues as $periodValue) {
            $period = CommissionPeriod::findOrCreateForValue((string) $periodValue);
            $this->info("Recalcul {$period->period}…");
            $result = $calculator->recalculateSourceAndAncestors($period, (int) $user->id);
            $this->line(sprintf(
                '  → créées=%d · total=$%s · ascendants=%d · I&L source purgés=%d',
                $result['created'],
                number_format($result['total_amount'], 2),
                $result['ancestors'],
                $result['purged_source_il']
            ));
        }

        $remainOwn = Commission::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'cancelled'])
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->count();

        $remainIl = Commission::query()
            ->where('from_user_id', $user->id)
            ->whereIn('type', ['indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'cancelled'])
            ->when($periodOpt, fn ($q) => $q->where('period', $periodOpt))
            ->count();

        $this->newLine();
        $this->info("Terminé. Restant non payé — membre : {$remainOwn} · I&L ascendants from lui : {$remainIl}");
        if ($remainIl > 0) {
            $this->comment('Des I&L non payés restent s’il a encore du PV sur la période (normal).');
        }

        return self::SUCCESS;
    }
}
