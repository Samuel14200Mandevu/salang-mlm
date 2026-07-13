<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupRun extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Sauvegarder la base de données';

    public function handle(): void
    {
        $this->info('Sauvegarde de la base de données...');
        
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/');
        
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        
        $fullPath = $path . $filename;
        $command = "mysqldump --host={$host} --user={$username} --password={$password} {$database} > {$fullPath}";
        exec($command);
        
        $this->info("✅ Backup créé: {$filename}");
    }
}
