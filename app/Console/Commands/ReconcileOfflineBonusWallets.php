<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrige les soldes wallet gonflés après un recalcul qui avait effacé
 * des commissions déjà payées, puis un second « Tout envoyer ».
 *
 * Cible : balance = commissions D/I/L payées − retraits complétés.
 */
class ReconcileOfflineBonusWallets extends Command
{
    protected $signature = 'wallets:reconcile-offline-bonus
                            {--dry-run : Simuler sans écrire}
                            {--force : Appliquer les ajustements}';

    protected $description = 'Aligne les soldes wallet sur les commissions D/I/L payées (corrige le double crédit)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run') || ! $this->option('force');

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture. Relancer avec --force pour appliquer.');
        }

        $paidByUser = Commission::query()
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->map(fn ($v) => round((float) $v, 2));

        $withdrawnByUser = Withdrawal::query()
            ->where('status', 'completed')
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->map(fn ($v) => round((float) $v, 2));

        $wallets = Wallet::query()->orderBy('id')->get();
        $adjusted = 0;
        $unchanged = 0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($wallets as $wallet) {
            $paid = (float) ($paidByUser[$wallet->user_id] ?? 0);
            $withdrawn = (float) ($withdrawnByUser[$wallet->user_id] ?? 0);
            $expected = round(max(0, $paid - $withdrawn), 2);
            $current = round((float) $wallet->balance, 2);
            $delta = round($expected - $current, 2);

            if (abs($delta) < 0.005) {
                $unchanged++;
                continue;
            }

            $this->line(sprintf(
                'user #%d wallet #%d : %s → %s (Δ %s)',
                $wallet->user_id,
                $wallet->id,
                number_format($current, 2, '.', ''),
                number_format($expected, 2, '.', ''),
                number_format($delta, 2, '.', '')
            ));

            if ($delta < 0) {
                $totalDebit += abs($delta);
            } else {
                $totalCredit += $delta;
            }

            if ($dryRun) {
                $adjusted++;
                continue;
            }

            DB::transaction(function () use ($wallet, $expected, $current, $delta) {
                $locked = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();
                if (! $locked) {
                    return;
                }

                $before = round((float) $locked->balance, 2);
                $locked->balance = $expected;
                // total_deposited suit le crédit net réellement dû
                $locked->total_deposited = max(
                    (float) $locked->total_withdrawn,
                    round((float) $locked->total_deposited + $delta, 2)
                );
                $locked->save();

                Transaction::create([
                    'user_id' => $locked->user_id,
                    'wallet_id' => $locked->id,
                    'type' => 'adjustment',
                    'amount' => abs($delta),
                    'fee' => 0,
                    'net_amount' => abs($delta),
                    'balance_before' => $before,
                    'balance_after' => $expected,
                    'status' => 'completed',
                    'description' => $delta < 0
                        ? 'Correction double crédit bonus hors-système (recalcul branche)'
                        : 'Réconciliation solde vs commissions payées',
                    'metadata' => [
                        'source' => 'wallets_reconcile_offline_bonus',
                        'delta' => $delta,
                        'previous_balance' => $current,
                        'expected_balance' => $expected,
                    ],
                    'completed_at' => now(),
                ]);
            });

            $adjusted++;
        }

        // Annule les transactions offline liées à des commissions disparues / non payées.
        $cancelledOrphans = $this->cancelOrphanOfflineCredits($dryRun);

        $this->newLine();
        $this->info("Wallets traités : {$adjusted} ajusté(s), {$unchanged} déjà OK.");
        $this->info('Total à retirer : $'.number_format($totalDebit, 2));
        $this->info('Total à ajouter : $'.number_format($totalCredit, 2));
        $this->info("Transactions orphelines annulées : {$cancelledOrphans}");

        if (! $dryRun) {
            $sum = round((float) Wallet::sum('balance'), 2);
            $paid = round((float) Commission::query()
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->where('status', 'paid')
                ->sum('amount'), 2);
            $this->info("Après correction : solde wallets = \${$sum} · commissions payées = \${$paid}");
        }

        return self::SUCCESS;
    }

    protected function cancelOrphanOfflineCredits(bool $dryRun): int
    {
        $cancelled = 0;
        $paidIds = Commission::query()
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $paidLookup = array_fill_keys($paidIds, true);

        Transaction::query()
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->where('metadata', 'like', '%offline_bonus_pay%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($dryRun, $paidLookup, &$cancelled) {
                foreach ($rows as $tx) {
                    $meta = $tx->metadata;
                    if (is_string($meta)) {
                        $decoded = json_decode($meta, true);
                        $meta = is_string($decoded) ? json_decode($decoded, true) : $decoded;
                    }
                    if (! is_array($meta)) {
                        $meta = [];
                    }

                    $cid = (int) ($meta['commission_id'] ?? 0);
                    if ($cid > 0 && isset($paidLookup[$cid])) {
                        continue;
                    }

                    $cancelled++;
                    if ($dryRun) {
                        continue;
                    }

                    $meta['cancelled_reason'] = 'orphan_after_branch_recalc';
                    $meta['cancelled_at'] = now()->toIso8601String();
                    $tx->status = 'cancelled';
                    $tx->metadata = $meta;
                    $tx->description = trim(($tx->description ? $tx->description.' · ' : '').
                        'Annulé : commission recalculée / remplacée');
                    $tx->save();
                }
            });

        return $cancelled;
    }
}
