<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecalculateTeamPV extends Command
{
    protected $signature = 'team:recalculate {--user= : ID de l utilisateur specifique}';
    protected $description = 'Recalcule les PV d equipe et les grades';

    public function handle(RankUpdateService $rankService)
    {
        $query = User::where('is_active', true);
        
        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }
        
        $users = $query->get();
        $this->info("Recalcul des PV d'equipe pour {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            
            $user->updateTeamPVOptimized();
            $rankService->triggerRankUpdate($user, 'team_recalculate');
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Recalcul termine');
    }
}