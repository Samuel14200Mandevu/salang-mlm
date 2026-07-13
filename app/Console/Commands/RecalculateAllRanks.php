<?php
// app/Console/Commands/RecalculateAllRanks.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecalculateAllRanks extends Command
{
    protected $signature = 'ranks:recalculate {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Recalculer les grades de tous les utilisateurs';

    public function handle(): void
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $updated = $user->calculateAndUpdateRank();
                $this->info(($updated ? "✅" : "⚠️") . " Grade de {$user->name}: {$user->rank}");
            } else {
                $this->error("❌ Utilisateur ID {$userId} non trouvé");
            }
            return;
        }

        $this->info("Recalcul des grades pour tous les utilisateurs...");
        $users = User::where('is_active', true)->get();
        $bar = $this->output->createProgressBar($users->count());
        $updated = 0;

        foreach ($users as $user) {
            if ($user->calculateAndUpdateRank()) {
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$updated} utilisateurs mis à jour sur {$users->count()}");
    }
}