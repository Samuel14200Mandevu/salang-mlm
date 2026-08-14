<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\OrderItem;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ResetMonthlyPV extends Command
{
    protected $signature = 'pv:reset-monthly 
                            {--user= : ID de l utilisateur specifique}
                            {--dry-run : Simuler sans effectuer les modifications}
                            {--force : Forcer l execution meme si ce n est pas le 7}';
    
    protected $description = 'Reinitialiser les PV mensuels et recalculer les grades';

    public function handle(RankUpdateService $rankService)
    {
        $this->info('Reinitialisation des PV mensuels...');

        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $isForce = $this->option('force');

        if (now()->day != 7 && !$userId && !$isForce) {
            $this->warn('Cette commande est concue pour etre executee le 7 de chaque mois.');
            $this->line('   Utilisez --user=ID pour forcer sur un utilisateur specifique.');
            $this->line('   OU utilisez --force pour forcer l execution.');
            
            if (!$this->confirm('Voulez-vous continuer quand meme ?')) {
                return 0;
            }
        }

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->info('Utilisateur specifique: ID ' . $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Aucun utilisateur trouve');
            return 1;
        }

        $this->info($users->count() . ' utilisateurs a traiter');

        if ($isDryRun) {
            $this->warn('Mode SIMULATION - Aucune modification');
            
            $headers = ['ID', 'Nom', 'Type', 'PV actuel', 'Nouveau PV', 'Grade'];
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
                
                $rows[] = [
                    $user->id,
                    $user->name,
                    $user->user_type ?? 'member',
                    $user->monthly_pv ?? 0,
                    (int) $totalPV,
                    $user->rank_name ?? 'Distributeur',
                ];
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->table($headers, $rows);
            $this->info('Simulation terminee');
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
                    
                    $user->monthly_pv = (int) $totalPV;
                    $user->monthly_bv = (int) $totalBV;
                    $user->saveQuietly();
                    
                    Cache::forget("user_{$user->id}");
                    Cache::forget("user_monthly_rank_{$user->id}");
                    
                    $updated++;
                    $totalPVSum += $totalPV;

                } catch (\Exception $e) {
                    $errors[] = "Erreur pour l'utilisateur {$user->id}: " . $e->getMessage();
                    Log::error('Erreur reinitialisation PV mensuel', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $bar->advance();
            }

            DB::commit();

            $bar->finish();
            $this->newLine(2);

            $this->info('Reinitialisation terminee');
            $this->line("   Total traites: {$users->count()}");
            $this->line("   Mis a jour: {$updated}");
            $this->line("   Total PV cumule: {$totalPVSum}");

            $this->info('Recalcul des grades en cours...');
            foreach ($users as $user) {
                try {
                    $rankService->triggerRankUpdate($user, 'monthly_reset');
                } catch (\Exception $e) {
                    Log::error('Erreur recalcul grade apres reset', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info('Recalcul des grades termine');

            if (!empty($errors)) {
                $this->warn('Details des erreurs:');
                foreach ($errors as $error) {
                    $this->line("   - {$error}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erreur: ' . $e->getMessage());
            Log::error('Erreur critique reinitialisation PV mensuel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}