<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Console\Command;

class ProcessCommissions extends Command
{
    protected $signature = 'commissions:process';
    protected $description = 'Traiter toutes les commissions en attente';

    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    public function handle()
    {
        $this->info('Traitement des commissions en attente...');

        // Récupérer les commissions en attente
        $pendingCommissions = Commission::where('status', 'pending')->get();
        
        if ($pendingCommissions->count() === 0) {
            $this->info('Aucune commission en attente.');
            return;
        }

        $processed = 0;
        foreach ($pendingCommissions as $commission) {
            // Vérifier si l'utilisateur existe toujours
            if ($commission->user) {
                $wallet = $commission->user->wallet;
                if ($wallet) {
                    $wallet->balance += $commission->amount;
                    $wallet->save();
                    
                    $commission->status = 'paid';
                    $commission->paid_at = now();
                    $commission->save();
                    
                    $processed++;
                }
            }
        }

        $this->info($processed . ' commissions traitées avec succès.');
    }
}
