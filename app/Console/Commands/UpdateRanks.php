<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;

class UpdateRanks extends Command
{
    protected $signature = 'ranks:update 
                            {--user= : ID de l utilisateur specifique}
                            {--all : Mettre a jour tous les utilisateurs}';
    
    protected $description = 'Mettre a jour les grades des utilisateurs';

    public function handle(RankUpdateService $rankService)
    {
        $this->info('Mise a jour des grades...');

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('Utilisateur non trouve');
                return 1;
            }
            $rankService->triggerRankUpdate($user, 'command_update');
            $this->info("Grade mis a jour pour {$user->name}: " . ($user->rank ?? 'Distributeur'));
            return 0;
        }

        $users = User::where('is_active', true)->get();
        
        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur actif');
            return 0;
        }

        $this->info("Mise a jour de {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $rankService->triggerRankUpdate($user, 'command_update_all');
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Mise a jour terminee');
    }
}