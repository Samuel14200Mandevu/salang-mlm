<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\MLM\CommissionDistributor;
use App\Services\MobileMoneyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected MobileMoneyService $mobileMoneyService;

    public function __construct(MobileMoneyService $mobileMoneyService)
    {
        $this->mobileMoneyService = $mobileMoneyService;
    }

    /**
     * Traiter un paiement crypto
     */
    public function processCryptoPayment($userId, $amount, $cryptoType, $address)
    {
        Log::info('Crypto payment initiated', [
            'user_id' => $userId,
            'amount' => $amount,
            'crypto' => $cryptoType,
            'address' => $address
        ]);

        return $this->completePayment($userId, $amount, 'crypto_' . $cryptoType);
    }

    /**
     * Traiter un paiement Mobile Money (Orange, Airtel, M-Pesa)
     */
    public function processMobileMoneyPayment($userId, $amount, $provider, $phoneNumber)
    {
        $provider = strtolower($provider);
        
        Log::info('Mobile Money payment initiated', [
            'user_id' => $userId,
            'amount' => $amount,
            'provider' => $provider,
            'phone' => $phoneNumber
        ]);

        // Vérifier le provider
        $validProviders = ['orange', 'airtel', 'mpesa'];
        if (!in_array($provider, $validProviders)) {
            Log::error('Invalid mobile money provider', ['provider' => $provider]);
            return [
                'success' => false,
                'message' => 'Provider non supporté. Utilisez Orange, Airtel ou M-Pesa.'
            ];
        }

        // Valider le numéro pour l'opérateur choisi
        if (!$this->mobileMoneyService->validateNumberForOperator($phoneNumber, $provider)) {
            return [
                'success' => false,
                'message' => 'Le numéro de téléphone ne correspond pas à l\'opérateur sélectionné.'
            ];
        }

        // Appeler le service MobileMoneyService avec FlexPay
        $result = $this->mobileMoneyService->initiatePayment(
            $amount,
            $phoneNumber,
            $provider,
            $userId
        );

        if ($result['success']) {
            // Créer une transaction en attente
            $wallet = Wallet::where('user_id', $userId)->first();
            if ($wallet) {
                Transaction::create([
                    'user_id' => $userId,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'fee' => 0,
                    'net_amount' => $amount,
                    'balance_before' => $wallet->balance,
                    'balance_after' => $wallet->balance,
                    'status' => 'pending',
                    'reference' => $result['reference'] ?? null,
                    'description' => 'Dépôt via ' . ucfirst($provider) . ' Money',
                    'metadata' => json_encode([
                        'provider' => $provider,
                        'phone' => $phoneNumber,
                        'transaction_id' => $result['transaction_id'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]),
                ]);
            }
        }

        return $result;
    }

    /**
     * Finaliser un paiement
     */
    public function completePayment($userId, $amount, $method)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            Log::error('Wallet not found for user: ' . $userId);
            return [
                'success' => false,
                'message' => 'Portefeuille non trouvé'
            ];
        }

        DB::beginTransaction();
        
        try {
            $balanceBefore = $wallet->balance;
            
            $wallet->balance += $amount;
            $wallet->total_deposited += $amount;
            $wallet->save();

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
                'reference' => 'DEP-' . strtoupper(uniqid()),
                'description' => 'Dépôt via ' . $method,
                'metadata' => json_encode(['method' => $method]),
                'completed_at' => now(),
            ]);

            DB::commit();
            
            Log::info('Payment completed for user: ' . $userId, [
                'amount' => $amount,
                'method' => $method
            ]);
            
            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'balance' => $wallet->balance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment completion error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors du paiement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter un retrait
     */
    public function processWithdrawal($withdrawalId)
    {
        $withdrawal = Withdrawal::find($withdrawalId);
        
        if (!$withdrawal) {
            return ['success' => false, 'message' => 'Retrait non trouvé'];
        }
        
        if ($withdrawal->status !== 'pending') {
            return ['success' => false, 'message' => 'Retrait déjà traité'];
        }

        $wallet = Wallet::find($withdrawal->wallet_id);
        
        if (!$wallet) {
            return ['success' => false, 'message' => 'Portefeuille non trouvé'];
        }
        
        if ($wallet->balance < $withdrawal->amount) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }

        DB::beginTransaction();
        
        try {
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
                'reference' => 'WTH-' . strtoupper(uniqid()),
                'description' => 'Retrait via ' . $withdrawal->method,
                'metadata' => json_encode(['withdrawal_id' => $withdrawal->id]),
                'completed_at' => now(),
            ]);

            $withdrawal->status = 'completed';
            $withdrawal->completed_at = now();
            $withdrawal->save();

            DB::commit();
            
            Log::info('Withdrawal processed: ' . $withdrawalId);
            
            return ['success' => true, 'message' => 'Retrait effectué avec succès'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal processing error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors du retrait: ' . $e->getMessage()];
        }
    }

    /**
     * Créer une demande de retrait
     */
    public function createWithdrawal($userId, $amount, $method, $details)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            return ['success' => false, 'message' => 'Portefeuille non trouvé'];
        }
        
        if ($wallet->balance < $amount) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        
        if ($amount < 10) {
            return ['success' => false, 'message' => 'Le montant minimum de retrait est de $10'];
        }

        // Vérifier les limites quotidiennes
        $todayWithdrawals = Withdrawal::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->where('status', 'pending')
            ->sum('amount');

        if ($todayWithdrawals + $amount > 5000) {
            return ['success' => false, 'message' => 'Limite quotidienne de retrait atteinte (5000 USD)'];
        }

        $fee = $amount * 0.025; // 2.5% de frais
        $netAmount = $amount - $fee;

        DB::beginTransaction();
        
        try {
            // Débiter le wallet
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $amount;
            $wallet->pending_balance += $amount;
            $wallet->save();

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

            Transaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'fee' => $fee,
                'net_amount' => -$netAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'pending',
                'reference' => 'WTH-' . strtoupper(uniqid()),
                'description' => 'Demande de retrait via ' . $method,
                'metadata' => json_encode(['withdrawal_id' => $withdrawal->id]),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Demande de retrait créée avec succès',
                'withdrawal' => $withdrawal
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal creation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la création du retrait'];
        }
    }

    /**
     * Confirmer un paiement mobile via webhook
     */
    public function confirmMobilePayment($reference, $provider)
    {
        $transaction = Transaction::where('reference', $reference)->first();
        
        if (!$transaction) {
            Log::error('Transaction not found', ['reference' => $reference]);
            return ['success' => false, 'message' => 'Transaction non trouvée'];
        }

        if ($transaction->status === 'completed') {
            return ['success' => true, 'message' => 'Transaction déjà complétée'];
        }

        $wallet = Wallet::find($transaction->wallet_id);
        
        if (!$wallet) {
            return ['success' => false, 'message' => 'Portefeuille non trouvé'];
        }

        DB::beginTransaction();
        
        try {
            $balanceBefore = $wallet->balance;
            
            $wallet->balance += $transaction->amount;
            $wallet->total_deposited += $transaction->amount;
            $wallet->save();

            $transaction->status = 'completed';
            $transaction->balance_before = $balanceBefore;
            $transaction->balance_after = $wallet->balance;
            $transaction->completed_at = now();
            $transaction->save();

            DB::commit();

            Log::info('Mobile payment confirmed', [
                'reference' => $reference,
                'provider' => $provider,
                'user_id' => $transaction->user_id
            ]);

            return ['success' => true, 'message' => 'Paiement confirmé'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobile payment confirmation error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Erreur lors de la confirmation'];
        }
    }
}