<?php
// app/Jobs/CalculatePVBV.php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
use App\Models\CommissionPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculatePVBV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $period;
    public int $timeout = 3600;

    public function __construct(string $period = null)
    {
        $this->period = $period ?? date('Y-m', strtotime('last month'));
    }

    public function handle(): void
    {
        Log::info('Starting PV/BV calculation', ['period' => $this->period]);

        try {
            $periodObj = CommissionPeriod::where('period', $this->period)->first();

            if (!$periodObj) {
                throw new \Exception("Period {$this->period} not found");
            }

            $startDate = $periodObj->start_date;
            $endDate = $periodObj->end_date;

            $periodObj->status = 'calculating';
            $periodObj->save();

            $users = User::where('is_active', true)->get();
            $processed = 0;

            foreach ($users as $user) {
                $monthlyPV = OrderItem::whereHas('order', function ($query) use ($user, $startDate, $endDate) {
                    $query->where('user_id', $user->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->where('payment_status', 'completed');
                })->sum('pv_value');

                $monthlyBV = OrderItem::whereHas('order', function ($query) use ($user, $startDate, $endDate) {
                    $query->where('user_id', $user->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->where('payment_status', 'completed');
                })->sum('bv_value');

                $user->monthly_pv = (int) $monthlyPV;
                $user->monthly_bv = (int) $monthlyBV;
                $user->save();

                $processed++;
            }

            $periodObj->status = 'calculated';
            $periodObj->save();

            Log::info('PV/BV calculation completed', [
                'period' => $this->period,
                'users_processed' => $processed,
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating PV/BV', [
                'period' => $this->period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}