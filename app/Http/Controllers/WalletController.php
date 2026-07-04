<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    /**
     * Afficher la page du portefeuille
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        $balance = $wallet ? $wallet->balance : 0;
        $pendingBalance = $wallet ? $wallet->pending_balance : 0;
        $totalWithdrawn = $wallet ? $wallet->total_withdrawn : 0;
        $totalDeposited = $wallet ? $wallet->total_deposited : 0;
        
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('wallet.index', compact(
            'balance', 
            'pendingBalance', 
            'totalWithdrawn', 
            'totalDeposited',
            'transactions'
        ));
    }

    /**
     * Afficher le formulaire de dépôt
     */
    public function deposit()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->balance : 0;
        
        // Récupérer l'historique des dépôts
        $deposits = Transaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('wallet.deposit', compact('balance', 'deposits'));
    }

    /**
     * Traiter la demande de dépôt
     */
    public function storeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:crypto,mobile_money,bank,card',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;
        $amount = $request->amount;
        $paymentMethod = $request->payment_method;

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'total_withdrawn' => 0,
                'total_deposited' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);
        }

        // Créer la transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $amount,
            'fee' => 0,
            'net_amount' => $amount,
            'balance_before' => $wallet->balance,
            'balance_after' => $wallet->balance + $amount,
            'status' => 'pending',
            'reference' => 'DEP-' . strtoupper(uniqid()),
            'description' => 'Dépôt via ' . ucfirst(str_replace('_', ' ', $paymentMethod)),
            'metadata' => json_encode(['payment_method' => $paymentMethod]),
            'completed_at' => null,
        ]);

        // Mettre à jour le solde
        $wallet->balance += $amount;
        $wallet->total_deposited += $amount;
        $wallet->save();

        // Mettre à jour la transaction
        $transaction->status = 'completed';
        $transaction->balance_after = $wallet->balance;
        $transaction->completed_at = now();
        $transaction->save();

        return redirect()->route('wallet.index')
            ->with('success', 'Votre dépôt de $' . number_format($amount, 2) . ' a été effectué avec succès.');
    }

    /**
     * Afficher les transactions
     */
    public function transactions()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('wallet.transactions', compact('transactions'));
    }

    /**
     * Exporter les transactions
     */
    public function export()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return back()->with('info', 'Fonctionnalité d\'export en cours de développement.');
    }

    /**
     * API - Récupérer le solde
     */
    public function apiBalance()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        return response()->json([
            'balance' => $wallet ? $wallet->balance : 0,
            'pending_balance' => $wallet ? $wallet->pending_balance : 0,
        ]);
    }
}