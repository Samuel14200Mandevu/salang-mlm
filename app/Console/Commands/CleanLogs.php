<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanLogs extends Command
{
    protected $signature = 'logs:clean {--days=7 : Nombre de jours à conserver}';
    protected $description = 'Nettoyer les logs anciens';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $this->info("Nettoyage des logs de plus de {$days} jours...");

        $logFiles = File::files(storage_path('logs'));
        $deleted = 0;

        foreach ($logFiles as $file) {
            if ($file->getMTime() < now()->subDays($days)->timestamp) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->info("✅ {$deleted} fichiers supprimés");
    }
}
