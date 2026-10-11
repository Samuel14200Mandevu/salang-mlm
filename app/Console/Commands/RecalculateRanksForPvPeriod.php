<?php

namespace App\Console\Commands;

use App\Jobs\RecalculateAfterPVImport;
use App\Models\PVHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\TeamPVCalculator;
use Illuminate\Console\Command;

class RecalculateRanksForPvPeriod extends Command
{
    protected $signature = 'mlm:recalculate-ranks-for-pv-period
                            {period=2026-09 : Période YYYY-MM (ex. 2026-09)}
                            {--sync : Recalcul immédiat (recommandé)}
                            {--queue : Envoyer vers rank-recalculation au lieu du sync}';

    protected $description = 'Recalcule team_pv et grades pour les membres ayant des PV personnels sur une période';

    public function handle(): int
    {
        $period = $this->argument('period');
        $useSync = $this->option('sync') || ! $this->option('queue');

        $userIds = PVHistory::query()
            ->where('period', $period)
            ->where('type', 'personal')
            ->where('amount', '>', 0)
            ->distinct()
            ->pluck('user_id')
            ->filter()
            ->values()
            ->all();

        if ($userIds === []) {
            $this->warn("Aucun membre avec PV personnels pour la période {$period}.");

            return self::SUCCESS;
        }

        $this->info('Période : '.$period);
        $this->info('Membres concernés : '.count($userIds));

        if ($useSync) {
            $this->info('Recalcul synchrone (team_pv + grades + ancêtres)...');
            (new RecalculateAfterPVImport($userIds, $period))->handle(
                app(AdvancedRankCalculator::class),
                app(TeamPVCalculator::class)
            );
            $this->info('Terminé.');
        } else {
            RecalculateAfterPVImport::dispatch($userIds, $period)
                ->onQueue('rank-recalculation');
            $this->warn('Job envoyé sur rank-recalculation — lancez queue:work si besoin.');
        }

        return self::SUCCESS;
    }
}
