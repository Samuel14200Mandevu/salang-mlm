<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateMonthlyPV extends Command
{
    protected $signature = 'monthly:recalculate 
                            {--user= : ID de l utilisateur specifique}
                            {--force : Forcer le recalcul meme si deja fait ce mois-ci}';
    
    protected $description = 'Recalculer les PV mensuels pour tous les utilisateurs';

    public function handle(RankUpdateService $rankService)
    {
        $this->info('Recalcul des PV mensuels...');

        $isForce = $this->option('force');
        $userId = $this->option('user');

        if (!$isForce && !$userId) {
            $lastRecalc = cache('last_monthly_recalc');
            if ($lastRecalc && $lastRecalc->isCurrentMonth()) {
                $this->warn('Recalcul deja effectue ce mois-ci. Utilisez --force pour forcer.');
                return 0;
            }
        }

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->info('Utilisateur specifique: ID ' . $userId);
        }

        $users = $query->get();
        $this->info($users->count() . ' utilisateurs a traiter');
        
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
                        
                        Log::info('PV mensuel recalcule', [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'user_type' => $user->user_type,
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

            $this->info('Recalcul termine');
            $this->line("   Total: {$users->count()}");
            $this->line("   Mis a jour: {$updated}");
            $this->line("   Total PV: {$totalPV}");

            if ($updated > 0) {
                $this->info('Recalcul des grades en cours...');
                foreach ($users as $user) {
                    try {
                        $rankService->triggerRankUpdate($user, 'monthly_recalculate');
                    } catch (\Exception $e) {
                        Log::error('Erreur recalcul grade apres monthly', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                $this->info('Recalcul des grades termine');
            }
            
            if (!empty($errors)) {
                $this->warn('Erreurs:');
                foreach ($errors as $error) {
                    $this->line("   - {$error}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erreur: ' . $e->getMessage());
            Log::error('Erreur critique recalcul PV mensuel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}