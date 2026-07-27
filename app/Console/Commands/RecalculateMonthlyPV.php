<?php
// app/Console/Commands/RecalculateMonthlyPV.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateMonthlyPV extends Command
{
    protected $signature = 'monthly:recalculate 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--force : Forcer le recalcul même si déjà fait ce mois-ci}';
    
    protected $description = 'Recalculer les PV mensuels pour tous les utilisateurs';

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'error' => '<fg=red>✗</>',
            'process' => '<fg=cyan>⟳</>',
            'database' => '<fg=magenta>◉</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $this->info($this->icon('database') . ' Recalcul des PV mensuels...');

        $isForce = $this->option('force');
        $userId = $this->option('user');

        if (!$isForce && !$userId) {
            $lastRecalc = cache('last_monthly_recalc');
            if ($lastRecalc && $lastRecalc->isCurrentMonth()) {
                $this->warn($this->icon('warning') . ' Recalcul déjà effectué ce mois-ci. Utilisez --force pour forcer.');
                return 0;
            }
        }

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->info($this->icon('info') . " Utilisateur spécifique: ID {$userId}");
        }

        $users = $query->get();
        $this->info("{$users->count()} utilisateurs à traiter");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $updated = 0;
        $totalPV = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                try {
                    $oldPV = $user->monthly_pv;
                    
                    $user->updateMonthlyPV();
                    $newPV = $user->monthly_pv;
                    
                    if ($oldPV != $newPV) {
                        $updated++;
                        $totalPV += $newPV;
                        
                        Log::info('PV mensuel recalculé', [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'old_pv' => $oldPV,
                            'new_pv' => $newPV,
                            'month' => now()->format('Y-m'),
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erreur pour l'utilisateur {$user->id}: " . $e->getMessage();
                    Log::error('Erreur recalcul PV mensuel', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                $bar->advance();
            }

            DB::commit();

            cache(['last_monthly_recalc' => now()], now()->endOfMonth());

            $bar->finish();
            $this->newLine(2);

            $this->info($this->icon('success') . ' Recalcul terminé !');
            $this->line("   Total: {$users->count()}");
            $this->line("   Mis à jour: {$updated}");
            $this->line("   Total PV: {$totalPV}");
            $this->line("   Grades inchangés (basés sur PV cumulé)");
            
            if (!empty($errors)) {
                $this->warn($this->icon('warning') . ' Erreurs:');
                foreach ($errors as $error) {
                    $this->line("   - {$error}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($this->icon('error') . ' Erreur: ' . $e->getMessage());
            Log::error('Erreur critique recalcul PV mensuel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}