<?php
// app/Console/Commands/ProcessCommissions.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Console\Command;

class ProcessCommissions extends Command
{
    protected $signature = 'commissions:process {--period=}';
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

        $query = Commission::where('status', 'pending');
        
        if ($this->option('period')) {
            $query->where('period', $this->option('period'));
        }

        $pendingCommissions = $query->get();
        
        if ($pendingCommissions->count() === 0) {
            $this->info('Aucune commission en attente.');
            return;
        }

        $processed = 0;
        foreach ($pendingCommissions as $commission) {
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