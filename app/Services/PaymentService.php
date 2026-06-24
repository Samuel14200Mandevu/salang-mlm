<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Traiter un paiement crypto
     */
    public function processCryptoPayment($userId, $amount, $cryptoType, $address)
    {
        // Simulation de traitement crypto
        Log::info('Crypto payment initiated', [
            'user_id' => $userId,
            'amount' => $amount,
            'crypto' => $cryptoType,
            'address' => $address
        ]);

        // Ici, vous intégrerez l'API de paiement crypto
        // Exemple avec Stripe, Coinbase, etc.

        return $this->completePayment($userId, $amount, 'crypto_' . $cryptoType);
    }

    /**
     * Traiter un paiement Mobile Money
     */
    public function processMobileMoneyPayment($userId, $amount, $provider, $phoneNumber)
    {
        Log::info('Mobile Money payment initiated', [
            'user_id' => $userId,
            'amount' => $amount,
            'provider' => $provider,
            'phone' => $phoneNumber
        ]);

        // Ici, vous intégrerez l'API Mobile Money
        // Exemple avec Airtel, Orange, M-Pesa

        return $this->completePayment($userId, $amount, 'mobile_money_' . $provider);
    }

    /**
     * Finaliser un paiement
     */
    private function completePayment($userId, $amount, $method)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            Log::error('Wallet not found for user: ' . $userId);
            return false;
        }

        DB::beginTransaction();
        
        try {
            $balanceBefore = $wallet->balance;
            
            // Créditer le portefeuille
            $wallet->balance += $amount;
            $wallet->total_deposited += $amount;
            $wallet->save();

            // Créer la transaction
            Transaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => 'Dépôt via ' . $method,
                'metadata' => json_encode(['method' => $method]),
                'completed_at' => now(),
            ]);

            DB::commit();
            
            Log::info('Payment completed for user: ' . $userId, [
                'amount' => $amount,
                'method' => $method
            ]);
            
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment completion error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Traiter un retrait
     */
    public function processWithdrawal($withdrawalId)
    {
        $withdrawal = Withdrawal::find($withdrawalId);
        
        if (!$withdrawal) {
            Log::error('Withdrawal not found: ' . $withdrawalId);
            return false;
        }
        
        if ($withdrawal->status !== 'pending') {
            Log::warning('Withdrawal already processed: ' . $withdrawalId);
            return false;
        }

        $wallet = Wallet::find($withdrawal->wallet_id);
        
        if (!$wallet) {
            Log::error('Wallet not found for withdrawal: ' . $withdrawalId);
            return false;
        }
        
        if ($wallet->balance < $withdrawal->amount) {
            Log::warning('Insufficient balance for withdrawal: ' . $withdrawalId);
            return false;
        }

        DB::beginTransaction();
        
        try {
            $balanceBefore = $wallet->balance;
            
            // Débiter le portefeuille
            $wallet->balance -= $withdrawal->amount;
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();

            // Créer la transaction
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
                'description' => 'Retrait via ' . $withdrawal->method,
                'metadata' => json_encode(['withdrawal_id' => $withdrawal->id]),
                'completed_at' => now(),
            ]);

            // Mettre à jour le retrait
            $withdrawal->status = 'completed';
            $withdrawal->completed_at = now();
            $withdrawal->save();

            DB::commit();
            
            Log::info('Withdrawal processed: ' . $withdrawalId);
            
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal processing error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer une demande de retrait
     */
    public function createWithdrawal($userId, $amount, $method, $details)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet not found'];
        }
        
        if ($wallet->balance < $amount) {
            return ['success' => false, 'message' => 'Insufficient balance'];
        }
        
        if ($amount < 10) {
            return ['success' => false, 'message' => 'Minimum withdrawal amount is $10'];
        }

        $fee = $amount * 0.025; // 2.5% de frais
        $netAmount = $amount - $fee;

        $withdrawal = Withdrawal::create([
            'user_id' => $userId,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'method' => $method,
            'payment_address' => $details['address'] ?? null,
            'phone_number' => $details['phone'] ?? null,
            'bank_details' => $details['bank'] ?? null,
            'status' => 'pending',
            'notes' => 'Demande de retrait',
        ]);

        return [
            'success' => true,
            'message' => 'Withdrawal request created successfully',
            'withdrawal' => $withdrawal
        ];
    }
}
