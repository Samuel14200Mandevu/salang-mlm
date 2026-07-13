<?php
// app/Observers/OrderObserver.php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Après la création d'une commande
     */
    public function created(Order $order): void
    {
        // Mettre à jour le grade de l'utilisateur
        if ($order->user_id) {
            $order->user->calculateAndUpdateRank();
        }
    }

    /**
     * Après la mise à jour d'une commande
     */
    public function updated(Order $order): void
    {
        // Si la commande est terminée ou payée
        if ($order->wasChanged('status') || $order->wasChanged('payment_status')) {
            if ($order->status === 'completed' || $order->payment_status === 'completed') {
                if ($order->user_id) {
                    $order->user->calculateAndUpdateRank();
                    
                    // Mettre à jour les PV mensuels de l'utilisateur
                    $order->user->updateMonthlyPV();
                }
            }
        }
    }
}