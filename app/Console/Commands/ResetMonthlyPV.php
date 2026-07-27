<?php
// app/Console/Commands/ResetMonthlyPV.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use App\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ResetMonthlyPV extends Command
{
    protected $signature = 'pv:reset-monthly 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--dry-run : Simuler sans effectuer les modifications}
                            {--force : Forcer l\'exécution même si ce n\'est pas le 7}';
    
    protected $description = 'Réinitialiser les PV mensuels à 0 et recalculer correctement';

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'error' => '<fg=red>✗</>',
            'process' => '<fg=cyan>⟳</>',
            'clock' => '<fg=yellow>⌛</>',
            'database' => '<fg=magenta>◉</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $this->info($this->icon('process') . ' Réinitialisation des PV mensuels...');

        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $isForce = $this->option('force');

        if (now()->day != 7 && !$userId && !$isForce) {
            $this->warn($this->icon('warning') . ' Cette commande est conçue pour être exécutée le 7 de chaque mois.');
            $this->line('   Utilisez --user=ID pour forcer sur un utilisateur spécifique.');
            $this->line('   OU utilisez --force pour forcer l\'exécution.');
            
            if (!$this->confirm('Voulez-vous continuer quand même ?')) {
                return 0;
            }
        }

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->info($this->icon('info') . " Utilisateur spécifique: ID {$userId}");
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn($this->icon('warning') . ' Aucun utilisateur trouvé');
            return 1;
        }

        $this->info("{$users->count()} utilisateurs à traiter");

        if ($isDryRun) {
            $this->warn($this->icon('warning') . ' Mode SIMULATION - Aucune modification');
            
            $headers = ['ID', 'Nom', 'PV actuel', 'Nouveau PV', 'Grade'];
            $rows = [];
            
            $bar = $this->output->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                $monthStart = now()->startOfMonth();
                $monthEnd = now()->endOfMonth();
                
                $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.user_id', $user->id)
                    ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                    ->where('orders.payment_status', 'completed')
                    ->sum('order_items.pv_value');
                
                $rankName = $user->rank_name ?? 'Distributeur';
                
                $rows[] = [
                    $user->id,
                    $user->name,
                    $user->monthly_pv,
                    (int) $totalPV,
                    $rankName,
                ];
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->table($headers, $rows);
            $this->info($this->icon('success') . ' Simulation terminée');
            return 0;
        }

        $updated = 0;
        $errors = [];
        $totalPVSum = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                try {
                    $monthStart = now()->startOfMonth();
                    $monthEnd = now()->endOfMonth();
                    
                    $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->where('orders.user_id', $user->id)
                        ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                        ->where('orders.payment_status', 'completed')
                        ->sum('order_items.pv_value');
                    
                    $totalBV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->where('orders.user_id', $user->id)
                        ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                        ->where('orders.payment_status', 'completed')
                        ->sum('order_items.bv_value');
                    
                    $oldPV = $user->monthly_pv;
                    
                    $user->monthly_pv = (int) $totalPV;
                    $user->monthly_bv = (int) $totalBV;
                    $user->saveQuietly();
                    
                    Cache::forget("user_{$user->id}");
                    Cache::forget("user_monthly_rank_{$user->id}");
                    
                    $updated++;
                    $totalPVSum += $totalPV;
                    
                    if ($oldPV != $totalPV) {
                        Log::info('PV mensuel réinitialisé', [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'old_monthly_pv' => $oldPV,
                            'new_monthly_pv' => $totalPV,
                            'month' => now()->format('Y-m'),
                        ]);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Erreur pour l'utilisateur {$user->id} ({$user->name}): " . $e->getMessage();
                    Log::error('Erreur réinitialisation PV mensuel', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $bar->advance();
            }

            DB::commit();

            $bar->finish();
            $this->newLine(2);

            $this->info($this->icon('success') . ' Réinitialisation terminée');
            $this->line("   Total traités: {$users->count()}");
            $this->line("   Mis à jour: {$updated}");
            $this->line("   Total PV cumulé: {$totalPVSum}");
            $this->line("   Grades inchangés (basés sur PV cumulé)");

            if (!empty($errors)) {
                $this->warn($this->icon('warning') . ' Détails des erreurs:');
                foreach ($errors as $error) {
                    $this->line("   - {$error}");
                }
            }

            if ($totalPVSum > 0) {
                $this->newLine();
                $this->info($this->icon('info') . ' Des utilisateurs ont déjà des PV ce mois-ci !');
                $this->line("   Total PV: {$totalPVSum}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($this->icon('error') . ' Erreur lors de la réinitialisation: ' . $e->getMessage());
            Log::error('Erreur critique réinitialisation PV mensuel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}