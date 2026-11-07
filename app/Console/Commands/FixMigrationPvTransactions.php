<?php

namespace App\Console\Commands;

use App\Models\PvTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrige les lignes d’historique « Migration initiale » qui ont additionné
 * à tort pv_balance + monthly_pv (double comptage).
 *
 * Ne touche PAS à users.pv_balance / monthly_pv : seuls les montants
 * d’audit dans pv_transactions sont rectifiés.
 */
class FixMigrationPvTransactions extends Command
{
    protected $signature = 'pv:fix-migration-transactions
                            {--dry-run : Simuler sans écrire}
                            {--force : Exécuter sans confirmation}';

    protected $description = 'Corrige le double comptage des transactions PV de migration initiale';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info('Correction des transactions Migration (pv_balance + monthly_pv).');
        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture.');
        }

        $candidates = PvTransaction::query()
            ->where(function ($q) {
                $q->where('source', 'migration')
                    ->orWhere('description', 'like', '%Migration initiale%')
                    ->orWhere('description', 'like', '%pv_balance:%');
            })
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Aucune transaction de migration trouvée.');

            return self::SUCCESS;
        }

        $toFix = [];
        foreach ($candidates as $tx) {
            $parsed = $this->parseBalances((string) $tx->description);
            if ($parsed === null) {
                continue;
            }

            [$pvBalance, $monthlyPv] = $parsed;
            $current = (float) $tx->pv_amount;
            $sum = $pvBalance + $monthlyPv;

            // Cas cible : montant = somme des deux champs (souvent égaux → ×2).
            if (abs($current - $sum) > 0.01) {
                continue;
            }

            // Si monthly_pv == 0, rien à corriger même si somme == pv_balance.
            if ($monthlyPv <= 0) {
                continue;
            }

            $correctPv = $pvBalance;
            $correctBv = (float) ($tx->bv_amount ?: $pvBalance);

            // BV souvent = pv_balance ; si BV == somme aussi, le ramener.
            if (abs((float) $tx->bv_amount - $sum) < 0.01) {
                $correctBv = $pvBalance;
            } elseif ((float) $tx->bv_amount <= 0) {
                $correctBv = $pvBalance;
            }

            $toFix[] = [
                'tx' => $tx,
                'from_pv' => $current,
                'to_pv' => $correctPv,
                'from_bv' => (float) $tx->bv_amount,
                'to_bv' => $correctBv,
                'pv_balance' => $pvBalance,
                'monthly_pv' => $monthlyPv,
            ];
        }

        if ($toFix === []) {
            $this->info('Aucune transaction à corriger (déjà OK ou pattern non reconnu).');
            $this->line('Candidats examinés : '.$candidates->count());

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'User', 'PV avant', 'PV après', 'BV avant', 'BV après'],
            array_map(static function (array $row) {
                return [
                    $row['tx']->id,
                    $row['tx']->user_id,
                    $row['from_pv'],
                    $row['to_pv'],
                    $row['from_bv'],
                    $row['to_bv'],
                ];
            }, $toFix)
        );

        $this->info(count($toFix).' transaction(s) à corriger.');

        if ($dryRun) {
            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm('Appliquer ces corrections ?', true)) {
            $this->warn('Annulé.');

            return self::FAILURE;
        }

        $updated = 0;
        DB::transaction(function () use ($toFix, &$updated) {
            foreach ($toFix as $row) {
                /** @var PvTransaction $tx */
                $tx = $row['tx'];
                $meta = is_array($tx->metadata) ? $tx->metadata : [];
                $meta['migration_fix'] = [
                    'fixed_at' => now()->toIso8601String(),
                    'old_pv_amount' => $row['from_pv'],
                    'old_bv_amount' => $row['from_bv'],
                    'parsed_pv_balance' => $row['pv_balance'],
                    'parsed_monthly_pv' => $row['monthly_pv'],
                    'reason' => 'Double comptage pv_balance + monthly_pv à l’import',
                ];

                $tx->pv_amount = $row['to_pv'];
                $tx->bv_amount = $row['to_bv'];
                $tx->description = sprintf(
                    'Migration initiale des PV personnels (%s PV). Note : monthly_pv=%s était un compteur de période, pas un crédit supplémentaire.',
                    rtrim(rtrim(number_format($row['to_pv'], 1, '.', ''), '0'), '.'),
                    rtrim(rtrim(number_format($row['monthly_pv'], 1, '.', ''), '0'), '.')
                );
                $tx->metadata = $meta;
                $tx->save();
                $updated++;
            }
        });

        $this->info("Corrigé : {$updated} transaction(s). Les soldes users.pv_balance n’ont pas été modifiés.");

        return self::SUCCESS;
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function parseBalances(string $description): ?array
    {
        if (! preg_match('/pv_balance:\s*([0-9]+(?:\.[0-9]+)?)/i', $description, $mPv)) {
            return null;
        }
        if (! preg_match('/monthly_pv:\s*([0-9]+(?:\.[0-9]+)?)/i', $description, $mMo)) {
            return null;
        }

        return [(float) $mPv[1], (float) $mMo[1]];
    }
}
