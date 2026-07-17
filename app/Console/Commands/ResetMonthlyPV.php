<?php
// app/Console/Commands/ResetMonthlyPV.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Console\Command;

class ResetMonthlyPV extends Command
{
    protected $signature = 'monthly:reset {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Réinitialiser et recalculer les PV mensuels correctement';

    public function handle()
    {
        $query = User::where('is_active', true);
        
        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }
        
        $users = $query->get();
        $this->info("Réinitialisation des PV mensuels pour {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();
            
            //  Calculer UNIQUEMENT à partir des commandes
            $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $user->id)
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->where('orders.payment_status', 'completed')
                ->sum('order_items.pv_value');
            
            $totalBV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $user->id)
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->where('orders.payment_status', 'completed')
                ->sum('order_items.bv_value');
            
            //  Mise à jour directe
            $user->monthly_pv = (int) $totalPV;
            $user->monthly_bv = (int) $totalBV;
            $user->saveQuietly();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info(' Réinitialisation terminée !');
    }
}