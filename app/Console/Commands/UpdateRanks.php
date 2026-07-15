<?php
// app/Console/Commands/UpdateRanks.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRanks extends Command
{
    protected $signature = 'ranks:update 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--all : Mettre à jour tous les utilisateurs}
                            {--period= : Période au format YYYY-MM}';
    
    protected $description = 'Mettre à jour les grades des utilisateurs';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Mise à jour des grades...');

        $period = $this->option('period') ?? date('Y-m');

        // Cas 1: Utilisateur spécifique
        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            $this->updateUserRank($user, $period);
            return 0;
        }

        // Cas 2: Tous les utilisateurs
        if ($this->option('all')) {
            $this->updateAllRanks($period);
            return 0;
        }

        // Cas 3: Par défaut, utilisateurs actifs
        $this->updateActiveRanks($period);
        return 0;
    }

    /**
     * Mettre à jour le grade d'un utilisateur spécifique
     */
    private function updateUserRank(User $user, string $period): void
    {
        try {
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank_name ?? 'Distributor';

            $newRank = $this->rankCalculator->calculateAdvancedRank($user);

            if (!$newRank) {
                $this->warn("⚠️ Aucun grade trouvé pour {$user->name}");
                return;
            }

            if ($newRank->id != $oldRankId) {
                DB::beginTransaction();

                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->rank_name = $newRank->name;
                $user->rank_level = $newRank->level;
                $user->last_rank_update = now();
                $user->saveQuietly(); // ✅ Évite les événements

                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'monthly_pv_at_time' => $user->monthly_pv,
                    'notes' => "Mise à jour mensuelle {$period}",
                ]);

                DB::commit();

                $this->info("✅ {$user->name}: {$oldRankName} → {$newRank->name}");
            } else {
                $this->line("⏸️ {$user->name}: Grade inchangé ({$newRank->name})");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour grade', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->error("❌ Erreur pour {$user->name}: {$e->getMessage()}");
        }
    }

    /**
     * Mettre à jour tous les utilisateurs
     */
    private function updateAllRanks(string $period): void
    {
        $users = User::where('is_active', true)->get();
        
        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur actif');
            return;
        }

        $this->info("📊 Mise à jour de {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $unchanged = 0;

        foreach ($users as $user) {
            $oldRankId = $user->rank_id;
            $newRank = $this->rankCalculator->calculateAdvancedRank($user);
            
            if ($newRank && $newRank->id != $oldRankId) {
                $this->updateUserRank($user, $period);
                $updated++;
            } else {
                $unchanged++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("📊 RÉSULTATS:");
        $this->line("   ✅ Promus: {$updated}");
        $this->line("   ⏸️ Inchangés: {$unchanged}");
        $this->line("   📊 Total: " . ($updated + $unchanged));
    }

    /**
     * Mettre à jour les utilisateurs actifs uniquement
     */
    private function updateActiveRanks(string $period): void
    {
        // Utilisateurs avec des commandes récentes (30 jours)
        $users = User::whereHas('orders', function($query) {
            $query->where('payment_status', 'completed')
                  ->where('created_at', '>=', now()->subDays(30));
        })->where('is_active', true)
          ->limit(100)
          ->get();

        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur actif avec commandes récentes');
            return;
        }

        $this->info("📊 {$users->count()} utilisateurs actifs trouvés");
        $this->updateAllRanks($period);
    }
}