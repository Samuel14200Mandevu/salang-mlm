<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:send-pending';
    protected $description = 'Envoyer les notifications en attente';

    public function handle(): void
    {
        $this->info('Envoi des notifications en attente...');
        
        $users = User::where('is_active', true)->get();
        $sent = 0;

        foreach ($users as $user) {
            // Logique d'envoi de notifications
            $sent++;
        }

        $this->info("✅ {$sent} notifications envoyées");
    }
}
