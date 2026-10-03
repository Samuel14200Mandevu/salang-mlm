<?php

namespace App\Console\Commands;

use App\Services\MLM\CommissionHistoryToCommissionsSync;
use Illuminate\Console\Command;

class SyncCommissionsFromHistory extends Command
{
    protected $signature = 'commissions:sync-from-history
                            {--dry-run : Simuler sans écrire}
                            {--period=* : Limiter à une ou plusieurs périodes YYYY-MM}
                            {--from= : Période minimale inclusive}
                            {--to= : Période maximale inclusive}
                            {--ensure-periods : Crée/met à jour commission_periods (calculated)}
                            {--chunk=500 : Taille des chunks history}';

    protected $description = 'Sync idempotent commission_history → commissions (sans generatePayments)';

    public function handle(CommissionHistoryToCommissionsSync $sync): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ensurePeriods = (bool) $this->option('ensure-periods');

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture.');
        }

        if (! $ensurePeriods && ! $dryRun) {
            $this->warn('Recommandé : --ensure-periods pour créer les commission_periods en calculated.');
        }

        $periods = $sync->resolvePeriods(
            $this->option('from'),
            $this->option('to'),
            $this->option('period') ?? []
        );

        if ($periods->isEmpty()) {
            $this->error('Aucune période trouvée dans commission_history pour ces filtres.');

            return self::FAILURE;
        }

        $this->info('Périodes (ordre chronologique) : '.$periods->implode(', '));

        $report = $sync->syncPeriods(
            $periods,
            $dryRun,
            $ensurePeriods,
            (int) $this->option('chunk')
        );

        $this->table(
            ['Période', 'Créées', 'Ignorées', 'Erreurs', 'Brut history USD'],
            collect($report['periods'])->map(fn ($row) => [
                $row['period'],
                $row['created'],
                $row['skipped'],
                $row['errors'],
                number_format($row['gross'], 2),
            ])
        );

        $totals = $report['totals'];
        $this->newLine();
        $this->info(sprintf(
            'Total — créées: %d, ignorées: %d, erreurs: %d, brut: %s USD',
            $totals['created'],
            $totals['skipped'],
            $totals['errors'],
            number_format((float) $totals['gross'], 2)
        ));

        if ($dryRun) {
            $this->warn('Relancer sans --dry-run après backup pour appliquer.');
        } else {
            $this->line('Prochaine étape (manuelle) : commissions:generate-payments {period} période par période.');
        }

        return $totals['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
