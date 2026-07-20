<?php

namespace App\Observers;

use App\Models\Order;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->payment_status === 'completed' || $order->status === 'completed') {
            $this->handleCompletedOrder($order);
        }
    }

    public function updated(Order $order): void
    {
        $changedFields = [];

        if ($order->wasChanged('status')) {
            $changedFields[] = 'status: ' . $order->getOriginal('status') . ' -> ' . $order->status;
        }

        if ($order->wasChanged('payment_status')) {
            $changedFields[] = 'payment_status: ' . $order->getOriginal('payment_status') . ' -> ' . $order->payment_status;
        }

        if (!empty($changedFields)) {
            Log::info('Order updated', [
                'order_id' => $order->id,
                'changes' => $changedFields,
            ]);

            if ($order->status === 'completed' || $order->payment_status === 'completed') {
                $this->handleCompletedOrder($order);
            }
        }
    }

    private function handleCompletedOrder(Order $order): void
    {
        if (!$order->user_id) {
            Log::warning('Order without user', ['order_id' => $order->id]);
            return;
        }

        Log::info('Processing completed order', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);

        try {
            $user = $order->user;
            $user->updateMonthlyPV();
            $user->updateTeamPVWithoutEvents();
            $user->updateAllAncestorsWithoutEvents();
            $user->calculateAndUpdateRank();

            $this->updateAncestorsRanks($user);
            $this->updateQualifiedBranches($user);

            Log::info('Order processing complete', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'new_rank' => $user->rank_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing completed order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function updateAncestorsRanks($user): void
    {
        $current = $user->parrain;
        $depth = 0;
        $processed = [];

        while ($current && $depth < 5 && !in_array($current->id, $processed)) {
            $processed[] = $current->id;

            try {
                $current->calculateAndUpdateRank();
                Log::info('Ancestor rank updated', [
                    'ancestor_id' => $current->id,
                    'ancestor_name' => $current->name,
                    'rank' => $current->rank_name,
                ]);
            } catch (\Exception $e) {
                Log::error('Error updating ancestor rank', [
                    'ancestor_id' => $current->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $current = $current->parrain;
            $depth++;
        }
    }

    private function updateQualifiedBranches($user): void
    {
        try {
            $calculator = app(\App\Services\MLM\AdvancedRankCalculator::class);
            $period = date('Y-m');
            $calculator->calculateQualifiedBranches($user, $period);
        } catch (\Exception $e) {
            Log::error('Error updating qualified branches', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}