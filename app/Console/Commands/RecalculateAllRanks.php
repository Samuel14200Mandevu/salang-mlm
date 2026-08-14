<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;

class RecalculateAllRanks extends Command
{
    protected $signature = 'ranks:recalculate {--user= : ID de l utilisateur specifique}';
    protected $description = 'Recalculer les grades de tous les utilisateurs';

    public function handle(RankUpdateService $rankService)
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $rankService->triggerRankUpdate($user, 'recalculate_command');
                $this->info("Grade recalculé pour {$user->name}: {$user->rank_name}");
            } else {
                $this->error("Utilisateur ID {$userId} non trouve");
            }
            return;
        }

        $this->info("Recalcul des grades pour tous les utilisateurs...");
        $users = User::where('is_active', true)->get();
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $rankService->triggerRankUpdate($user, 'recalculate_all');
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recalcul termine pour {$users->count()} utilisateurs");
    }
}