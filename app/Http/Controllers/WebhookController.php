<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function crypto(Request $request)
    {
        Log::info('Crypto webhook received', $request->all());

        $data = $this->validateCryptoRequest($request);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'confirmed' || $data['status'] === 'completed') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Crypto deposit {$data['currency']} on {$data['network']}",
                    'metadata' => json_encode([
                        'tx_hash' => $data['tx_hash'] ?? null,
                        'network' => $data['network'],
                        'currency' => $data['currency'],
                    ]),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Crypto webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing crypto webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function mobileMoney(Request $request)
    {
        Log::info('Mobile Money webhook received', $request->all());

        $data = $this->validateMobileMoneyRequest($request);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'success') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Mobile Money deposit {$data['provider']}",
                    'metadata' => json_encode([
                        'provider' => $data['provider'],
                        'phone_number' => $data['phone_number'],
                    ]),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mobile Money webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing mobile money webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function payment(Request $request)
    {
        Log::info('Payment webhook received', $request->all());

        $data = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'status' => 'required|in:completed,pending,failed',
            'payment_method' => 'required|string',
            'order_id' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'completed') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Deposit via {$data['payment_method']}",
                    'metadata' => json_encode($data['metadata'] ?? []),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateCryptoRequest($request)
    {
        return $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'network' => 'required|string',
            'status' => 'required|string|in:confirmed,pending,failed',
            'tx_hash' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);
    }

    private function validateMobileMoneyRequest($request)
    {
        return $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'provider' => 'required|string|in:Airtel,Orange,M-Pesa',
            'phone_number' => 'required|string',
            'status' => 'required|string|in:success,pending,failed',
            'order_id' => 'nullable|string',
        ]);
    }

    private function processOrderPayment($user, $orderId)
    {
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            Log::warning('Order not found', ['order_id' => $orderId]);
            return;
        }

        $order->payment_status = 'completed';
        $order->paid_at = now();
        $order->save();

        Log::info('Order paid', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $user->id,
        ]);
    }
}