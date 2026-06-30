<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use Illuminate\Console\Command;

class UpdateRanks extends Command
{
    protected $signature = 'ranks:update {--user=}';
    protected $description = 'Mettre à jour les grades des utilisateurs';

    public function handle()
    {
        $this->info('🔄 Mise à jour des grades...');

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            $this->updateUserRank($user);
            return 0;
        }

        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        foreach ($users as $user) {
            if ($this->updateUserRank($user)) {
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$updated} utilisateurs mis à jour");
    }

    private function updateUserRank($user)
    {
        $ranks = Rank::orderBy('min_pv', 'asc')->get();
        $oldRankId = $user->rank_id;
        $oldRankName = $user->rank;

        foreach ($ranks as $rank) {
            if ($user->pv_balance >= $rank->min_pv) {
                $user->rank = $rank->name;
                $user->rank_id = $rank->id;
            }
        }

        if ($user->rank_id != $oldRankId) {
            RankHistory::create([
                'user_id' => $user->id,
                'old_rank_id' => $oldRankId,
                'new_rank_id' => $user->rank_id,
                'old_rank_name' => $oldRankName,
                'new_rank_name' => $user->rank,
                'pv_at_time' => $user->pv_balance,
                'bv_at_time' => $user->bv_balance,
                'notes' => 'Mise à jour automatique',
            ]);

            $user->save();
            $this->line("📈 {$user->name}: {$oldRankName} → {$user->rank}");
            return true;
        }

        return false;
    }
}