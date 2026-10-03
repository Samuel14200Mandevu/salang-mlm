<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillWallets extends Command
{
    protected $signature = 'wallets:backfill
                            {--dry-run : Simuler sans écrire}
                            {--status=paid : Statut commission_history à sommer (paid|pending|all)}
                            {--include-zero : Créer aussi les wallets à solde 0}';

    protected $description = 'Crée des lignes wallets depuis commission_history (idempotent via updateOrCreate)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $status = (string) $this->option('status');
        $includeZero = (bool) $this->option('include-zero');

        if (! in_array($status, ['paid', 'pending', 'all'], true)) {
            $this->error('Option --status invalide (paid|pending|all).');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture.');
        }

        $query = DB::table('commission_history')
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->groupBy('user_id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $balances = $query->pluck('total', 'user_id');
        $this->info('Soldes calculés pour '.$balances->count().' users (status='.$status.').');

        if ($balances->isEmpty()) {
            $this->warn('Aucune ligne commission_history pour ce filtre status.');
            $this->line('En prod actuelle, tout est souvent en « pending » — tester --status=pending ou --status=all.');
        }

        $created = 0;
        $skipped = 0;
        $totalBalance = 0.0;

        User::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->chunk(100, function ($users) use ($balances, $dryRun, $includeZero, &$created, &$skipped, &$totalBalance) {
                foreach ($users as $user) {
                    $balance = (float) $balances->get($user->id, 0);

                    if ($balance <= 0 && ! $includeZero) {
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line(sprintf('  [DRY] User %d (%s) → %.2f USD', $user->id, $user->email, $balance));
                        $created++;
                        $totalBalance += $balance;

                        continue;
                    }

                    Wallet::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'balance' => $balance,
                            'pending_balance' => 0,
                            'total_withdrawn' => 0,
                            'total_deposited' => 0,
                            'currency' => 'USD',
                            'is_active' => true,
                        ]
                    );

                    $created++;
                    $totalBalance += $balance;
                }
            });

        $this->newLine();
        $this->info("Wallets traités : {$created}");
        $this->line("Users ignorés (solde ≤ 0 sans --include-zero) : {$skipped}");
        $this->line('Total balances : '.number_format($totalBalance, 2).' USD');

        if ($dryRun) {
            $this->newLine();
            $this->warn('Relancer sans --dry-run après backup et validation métier du statut.');
        }

        return self::SUCCESS;
    }
}
