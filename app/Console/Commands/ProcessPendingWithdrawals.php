<?php
// app/Console/Commands/ProcessPendingWithdrawals.php

namespace App\Console\Commands;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingWithdrawals extends Command
{
    protected $signature = 'withdrawals:process 
                            {--id= : ID du retrait spécifique}
                            {--dry-run : Simulation sans modifications}
                            {--force : Forcer le traitement même si pas 48h}';
    
    protected $description = 'Traiter les demandes de retrait en attente (48h)';

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'error' => '<fg=red>✗</>',
            'process' => '<fg=cyan>⟳</>',
            'clock' => '<fg=yellow>⌛</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $this->info($this->icon('process') . ' Traitement des retraits...');
        
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        if ($this->option('id')) {
            $withdrawal = Withdrawal::find($this->option('id'));
            if (!$withdrawal) {
                $this->error($this->icon('error') . ' Retrait non trouvé');
                return 1;
            }
            
            if ($isDryRun) {
                $this->warn($this->icon('warning') . ' Mode SIMULATION - Aucune modification');
                $this->line("   Retrait #{$withdrawal->id}: {$withdrawal->amount} USD - Statut: {$withdrawal->status}");
                $this->line("   Créé: {$withdrawal->created_at->diffForHumans()}");
                $this->line("   48h écoulées: " . ($withdrawal->created_at->diffInHours(now()) >= 48 ? '✓ Oui' : '✗ Non'));
                return 0;
            }
            
            $this->processWithdrawal($withdrawal, $isForce);
            return 0;
        }

        $query = Withdrawal::where('status', 'pending');
        
        if (!$isForce) {
            $query->where('created_at', '<=', now()->subHours(48));
        }
        
        $withdrawals = $query->get();

        if ($withdrawals->isEmpty()) {
            $this->info($this->icon('success') . ' Aucun retrait en attente de traitement');
            if (!$isForce) {
                $this->line('   ' . $this->icon('clock') . ' Les retraits sont traités après 48h');
            }
            return 0;
        }

        $this->info("{$withdrawals->count()} retrait(s) à traiter");

        if ($isDryRun) {
            $this->warn($this->icon('warning') . ' Mode SIMULATION - Aucune modification');
            
            $headers = ['ID', 'Montant', 'Méthode', 'Heures', 'Utilisateur', 'Statut 48h'];
            $rows = [];
            
            foreach ($withdrawals as $withdrawal) {
                $hours = $withdrawal->created_at->diffInHours(now());
                $isReady = $hours >= 48 ? '✓ Oui' : '✗ Non (' . (48 - $hours) . 'h restantes)';
                $rows[] = [
                    $withdrawal->id,
                    '$' . number_format($withdrawal->amount, 2),
                    $withdrawal->method,
                    $hours . 'h',
                    $withdrawal->user->name ?? 'N/A',
                    $isReady,
                ];
            }
            
            $this->table($headers, $rows);
            $this->info($this->icon('success') . ' Simulation terminée');
            return 0;
        }

        $bar = $this->output->createProgressBar($withdrawals->count());
        $bar->start();

        $processed = 0;
        $errors = 0;

        foreach ($withdrawals as $withdrawal) {
            $result = $this->processWithdrawal($withdrawal, $isForce);
            if ($result) {
                $processed++;
            } else {
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info($this->icon('success') . ' Traitement terminé');
        $this->line("   Traités: {$processed}");
        $this->line("   Erreurs: {$errors}");

        return 0;
    }

    private function processWithdrawal($withdrawal, $isForce = false)
    {
        if (!$isForce && $withdrawal->created_at->diffInHours(now()) < 48) {
            $hoursLeft = 48 - $withdrawal->created_at->diffInHours(now());
            $this->line($this->icon('clock') . " Retrait #{$withdrawal->id}: encore {$hoursLeft}h avant traitement");
            return false;
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if (!$wallet) {
                $withdrawal->status = 'failed';
                $withdrawal->notes = 'Wallet non trouvé';
                $withdrawal->save();
                DB::commit();
                $this->error($this->icon('error') . " Retrait #{$withdrawal->id}: wallet non trouvé");
                return false;
            }

            // Passer en "processing"
            $withdrawal->status = 'processing';
            $withdrawal->processing_started_at = now();
            $withdrawal->save();

            $this->line($this->icon('process') . " Retrait #{$withdrawal->id}: en cours de traitement...");

            // Vérifier le solde
            if ($wallet->balance < $withdrawal->amount) {
                $withdrawal->status = 'failed';
                $withdrawal->notes = 'Solde insuffisant après vérification';
                $withdrawal->save();
                DB::commit();
                $this->error($this->icon('error') . " Retrait #{$withdrawal->id}: solde insuffisant");
                return false;
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
                'fee' => $withdrawal->fee ?? 0,
                'net_amount' => -($withdrawal->amount - ($withdrawal->fee ?? 0)),
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => "Retrait via {$withdrawal->method} - #{$withdrawal->id}",
                'metadata' => json_encode([
                    'withdrawal_id' => $withdrawal->id,
                    'method' => $withdrawal->method,
                    'processing_time' => $withdrawal->created_at->diffInHours(now()) . 'h',
                ]),
                'completed_at' => now(),
            ]);

            // Marquer comme terminé
            $withdrawal->status = 'completed';
            $withdrawal->completed_at = now();
            $withdrawal->save();

            DB::commit();
            
            $this->line($this->icon('success') . " Retrait #{$withdrawal->id}: {$withdrawal->amount} USD vers {$withdrawal->method} (48h)");
            
            Log::info('Retrait traité avec succès', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'processing_hours' => $withdrawal->created_at->diffInHours(now()),
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur traitement retrait', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);
            $this->error($this->icon('error') . " Erreur retrait #{$withdrawal->id}: {$e->getMessage()}");
            return false;
        }
    }
}