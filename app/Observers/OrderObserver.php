<?php

namespace App\Observers;

use App\Jobs\RecalculateAfterPVImport;
use App\Models\Order;
use App\Services\ActivityRegisterService;
use App\Support\MlmPeriod;
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
        $statusChanged = $order->wasChanged('status') || $order->wasChanged('payment_status');

        if (! $statusChanged) {
            return;
        }

        if ($order->status === 'completed' || $order->payment_status === 'completed') {
            $this->handleCompletedOrder($order);
        }
    }

    private function handleCompletedOrder(Order $order): void
    {
        if (! $order->user_id) {
            Log::warning('Order without user', ['order_id' => $order->id]);

            return;
        }

        try {
            RecalculateAfterPVImport::dispatch([$order->user_id], MlmPeriod::current())
                ->onQueue('rank-recalculation');

            Log::info('Order completed, recalculation dispatched', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing completed order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            app(ActivityRegisterService::class)->markPurchase($order);
        } catch (\Throwable $e) {
            Log::warning('Activity register: markPurchase failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}