<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Services\MLM\HidePeriodsService;
use Illuminate\Console\Command;

class HidePeriods extends Command
{
    protected $signature = 'commissions:hide-periods
                            {--from=2024-10 : Période minimale inclusive (bande C)}
                            {--to=2026-08 : Période maximale inclusive (bande C)}
                            {--dry-run : Simuler sans écrire}
                            {--force : Exécution réelle sans confirmation interactive}';

    protected $description = 'Masque les périodes bande C (is_hidden) sans modifier commissions ni wallets';

    public function handle(HidePeriodsService $service): int
    {
        $from = (string) $this->option('from');
        $to = (string) $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        $rangeCheck = $service->assertBandCRange($from, $to);
        if (! $rangeCheck['ok']) {
            $this->error($rangeCheck['message']);

            return self::FAILURE;
        }

        $periods = $service->resolvePeriods($from, $to);
        $this->info('Périodes ciblées : '.$periods->pluck('period')->implode(', '));

        $safety = $service->assertSafeToHide($periods);
        if (! $safety['ok']) {
            $this->error($safety['message']);

            return self::FAILURE;
        }

        $preview = $service->preview($periods);

        $this->table(
            ['Période', 'Statut', 'Masquée', 'Historique', 'Brut USD'],
            collect($preview['periods'])->map(fn ($row) => [
                $row['period'],
                $row['status'],
                $row['is_hidden'] ? 'oui' : 'non',
                $row['is_historical'] ? 'oui' : 'non',
                number_format($row['total_commissions'], 2),
            ])
        );

        $totals = $preview['totals'];
        $this->newLine();
        $this->info(sprintf(
            'Total — périodes: %d, déjà masquées: %d, à masquer: %d, brut concerné (nouveau): %s USD',
            $totals['periods'],
            $totals['already_hidden'],
            $totals['to_hide'],
            number_format((float) $totals['gross_usd'], 2)
        ));

        $this->line('Wallets (inchangés) : '.Wallet::query()->count());

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture. Relancer avec --force sans --dry-run après validation Samuel.');

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force')) {
            if (! $this->confirm('Masquer ces périodes (bande C) ?', false)) {
                $this->warn('Annulé.');

                return self::SUCCESS;
            }
        }

        $result = $service->apply($periods);

        $this->info(sprintf(
            'Appliqué — %d période(s) mises à jour is_hidden=1 (sur %d).',
            $result['totals']['periods_updated'],
            $result['totals']['periods_total']
        ));

        return self::SUCCESS;
    }
}
