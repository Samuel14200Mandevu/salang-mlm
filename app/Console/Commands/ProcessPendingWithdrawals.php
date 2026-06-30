<?php

namespace App\Console\Commands;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingWithdrawals extends Command
{
    protected $signature = 'withdrawals:process {--id=}';
    protected $description = 'Traiter les demandes de retrait en attente';

    public function handle()
    {
        $this->info('🔄 Traitement des retraits...');

        if ($this->option('id')) {
            $withdrawal = Withdrawal::find($this->option('id'));
            if (!$withdrawal) {
                $this->error('❌ Retrait non trouvé');
                return 1;
            }
            $this->processWithdrawal($withdrawal);
            return 0;
        }

        $withdrawals = Withdrawal::where('status', 'pending')->get();

        if ($withdrawals->isEmpty()) {
            $this->info('✅ Aucun retrait en attente');
            return 0;
        }

        $bar = $this->output->createProgressBar($withdrawals->count());
        $bar->start();

        foreach ($withdrawals as $withdrawal) {
            $this->processWithdrawal($withdrawal);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Traitement terminé');
    }

    private function processWithdrawal($withdrawal)
    {
        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if (!$wallet || $wallet->balance < $withdrawal->amount) {
                $withdrawal->status = 'failed';
                $withdrawal->notes = 'Solde insuffisant';
                $withdrawal->save();
                DB::commit();
                $this->error("❌ Retrait #{$withdrawal->id}: solde insuffisant");
                return;
            }

            // Débiter le portefeuille
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $withdrawal->amount;
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();

            // Créer la transaction
            Transaction::create([
                'user_id' => $withdrawal->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => -$withdrawal->amount,
                'fee' => $withdrawal->fee,
                'net_amount' => -$withdrawal->net_amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => "Retrait via {$withdrawal->method}",
                'metadata' => json_encode(['withdrawal_id' => $withdrawal->id]),
                'completed_at' => now(),
            ]);

            // Mettre à jour le retrait
            $withdrawal->status = 'completed';
            $withdrawal->completed_at = now();
            $withdrawal->save();

            DB::commit();
            $this->line("✅ Retrait #{$withdrawal->id}: {$withdrawal->amount} USD vers {$withdrawal->method}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur traitement retrait', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);
            $this->error("❌ Erreur retrait #{$withdrawal->id}: {$e->getMessage()}");
        }
    }
}