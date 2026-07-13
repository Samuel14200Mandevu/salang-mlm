<?php
// app/Jobs/ProcessWithdrawals.php

namespace App\Jobs;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\WithdrawalApprovedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWithdrawals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function handle(): void
    {
        Log::info('Starting withdrawal processing');

        try {
            $pendingWithdrawals = Withdrawal::where('status', 'pending')
                ->where('created_at', '<=', now()->subHours(24))
                ->limit(100)
                ->get();

            $processed = 0;
            $errors = [];

            foreach ($pendingWithdrawals as $withdrawal) {
                try {
                    DB::beginTransaction();

                    $wallet = Wallet::find($withdrawal->wallet_id);

                    if (!$wallet) {
                        $errors[] = "Wallet not found for withdrawal #{$withdrawal->id}";
                        DB::rollBack();
                        continue;
                    }

                    // Check if still sufficient balance
                    if ($wallet->balance < $withdrawal->amount) {
                        $withdrawal->status = 'failed';
                        $withdrawal->notes = 'Insufficient balance';
                        $withdrawal->save();

                        $errors[] = "Insufficient balance for withdrawal #{$withdrawal->id}";
                        DB::rollBack();
                        continue;
                    }

                    // Process withdrawal
                    $balanceBefore = $wallet->balance;
                    $wallet->balance -= $withdrawal->amount;
                    $wallet->total_withdrawn += $withdrawal->amount;
                    $wallet->save();

                    Transaction::create([
                        'user_id' => $withdrawal->user_id,
                        'wallet_id' => $wallet->id,
                        'type' => 'withdrawal',
                        'amount' => -$withdrawal->amount,
                        'fee' => $withdrawal->fee,
                        'net_amount' => -$withdrawal->net_amount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'status' => 'completed',
                        'description' => "Withdrawal via {$withdrawal->method}",
                        'metadata' => json_encode([
                            'withdrawal_id' => $withdrawal->id,
                            'method' => $withdrawal->method,
                        ]),
                        'completed_at' => now(),
                    ]);

                    $withdrawal->status = 'completed';
                    $withdrawal->processed_at = now();
                    $withdrawal->completed_at = now();
                    $withdrawal->save();

                    DB::commit();
                    $processed++;

                    // Send notification
                    try {
                        $withdrawal->user->notify(new WithdrawalApprovedNotification(
                            $withdrawal->amount,
                            $withdrawal->method,
                            $withdrawal->net_amount,
                            $withdrawal->id
                        ));
                    } catch (\Exception $e) {
                        Log::error('Error sending withdrawal notification', [
                            'withdrawal_id' => $withdrawal->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Error processing withdrawal #{$withdrawal->id}: " . $e->getMessage();
                }
            }

            Log::info('Withdrawal processing completed', [
                'processed' => $processed,
                'errors' => count($errors),
                'errors_list' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing withdrawals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}