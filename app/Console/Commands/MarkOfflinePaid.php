<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Services\MLM\MarkOfflinePaidService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOfflinePaid extends Command
{
    protected $signature = 'commissions:mark-offline-paid
                            {--from=2023-11 : Période minimale inclusive (bande A)}
                            {--to=2024-08 : Période maximale inclusive (bande A)}
                            {--dry-run : Simuler sans écrire}
                            {--force : Exécution réelle sans confirmation interactive}';

    protected $description = 'Marque bande A payée offline (historique) sans wallet ni commission_payments';

    public function handle(MarkOfflinePaidService $service): int
    {
        $from = (string) $this->option('from');
        $to = (string) $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        $rangeCheck = $service->assertBandARange($from, $to);
        if (! $rangeCheck['ok']) {
            $this->error($rangeCheck['message']);

            return self::FAILURE;
        }

        $periods = $service->resolvePeriods($from, $to);
        $this->info('Périodes ciblées : '.$periods->pluck('period')->implode(', '));

        $safety = $service->assertSafeToMark($periods);
        if (! $safety['ok']) {
            $this->error($safety['message']);

            return self::FAILURE;
        }

        $preview = $service->preview($periods);

        $this->table(
            ['Période', 'Statut période', 'Déjà hist.', 'Comm. pending', 'History pending', 'Brut USD'],
            collect($preview['periods'])->map(fn ($row) => [
                $row['period'],
                $row['status'],
                $row['is_historical'] ? 'oui' : 'non',
                $row['commissions_pending'],
                $row['history_pending'],
                number_format($row['gross_usd'], 2),
            ])
        );

        $totals = $preview['totals'];
        $this->newLine();
        $this->info(sprintf(
            'Total — périodes: %d, commissions pending: %d, history pending: %d, brut: %s USD',
            $totals['periods'],
            $totals['commissions_pending'],
            $totals['history_pending'],
            number_format((float) $totals['gross_usd'], 2)
        ));

        $walletCount = Wallet::query()->count();
        $this->line("Wallets (inchangés attendus) : {$walletCount}");

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture. Relancer avec --force sans --dry-run après validation Samuel.');

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force')) {
            if (! $this->confirm('Appliquer le marquage offline (bande A) ?', false)) {
                $this->warn('Annulé.');

                return self::SUCCESS;
            }
        }

        $paidAt = Carbon::now();
        $result = $service->apply($periods, $paidAt);

        $this->table(
            ['Période', 'Comm. → paid', 'History → paid', 'Brut USD'],
            collect($result['periods'])->map(fn ($row) => [
                $row['period'],
                $row['commissions_updated'],
                $row['history_updated'],
                number_format($row['gross_usd'], 2),
            ])
        );

        $applied = $result['totals'];
        $this->info(sprintf(
            'Appliqué — périodes: %d, commissions: %d, history: %d, brut marqué: %s USD',
            $applied['periods_updated'],
            $applied['commissions_updated'],
            $applied['history_updated'],
            number_format((float) $applied['gross_usd'], 2)
        ));

        $this->line('Wallets après : '.Wallet::query()->count().' (doit être identique).');

        return self::SUCCESS;
    }
}
