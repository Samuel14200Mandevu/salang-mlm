<?php
// app/Console/Commands/StatusCommand.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Models\Withdrawal;
use App\Models\Order;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    protected $signature = 'mlm:status';
    protected $description = 'Afficher le statut du système MLM';

    public function handle()
    {
        $this->info('📊 STATUT MLM');
        $this->line('');

        $headers = ['Indicateur', 'Valeur'];
        $data = [
            ['Total utilisateurs', User::count()],
            ['Utilisateurs actifs', User::where('is_active', true)->count()],
            ['Commissions en attente', Commission::where('status', 'pending')->sum('amount') . ' USD'],
            ['Commissions payées', Commission::where('status', 'paid')->sum('amount') . ' USD'],
            ['Retraits en attente', Withdrawal::where('status', 'pending')->count()],
            ['Commandes en cours', Order::where('status', 'pending')->count()],
            ['Commandes complétées', Order::where('payment_status', 'completed')->count()],
        ];

        $this->table($headers, $data);
        
        $this->line('');
        $this->info('✅ Système opérationnel');
    }
}