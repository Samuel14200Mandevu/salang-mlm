<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        $balance = $wallet ? $wallet->balance : 0;
        
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('withdrawal.index', compact('balance', 'withdrawals'));
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'method' => 'required|in:crypto,mobile_money,bank',
        ]);
        
        if (!$wallet) {
            return back()->with('error', 'Portefeuille introuvable.');
        }
        
        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Solde insuffisant. Vous avez $' . number_format($wallet->balance, 2));
        }

        // Vérifier KYC pour les gros retraits
        $threshold = config('commission.kyc.withdrawal_threshold', 5000);
        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        if (($totalWithdrawn + $request->amount) >= $threshold && $user->kyc_status !== 'verified') {
            return back()->with('error', 
                'Vous devez compléter la vérification KYC pour effectuer un retrait de plus de $' . 
                number_format($threshold, 0) . 
                '. <a href="' . route('kyc.index') . '" style="color: #6366f1; text-decoration: underline;">Vérifier mon identité</a>'
            );
        }
        
        // Calculer les frais (2.5%)
        $fee = $request->amount * 0.025;
        $netAmount = $request->amount - $fee;
        
        // Créer la demande de retrait
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'method' => $request->method,
            'payment_address' => $request->payment_address,
            'phone_number' => $request->phone_number,
            'bank_details' => $request->bank_details,
            'status' => 'pending',
            'notes' => 'Demande de retrait',
        ]);
        
        // Débiter le portefeuille
        $wallet->balance -= $request->amount;
        $wallet->save();
        
        // Créer une transaction
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'withdrawal',
            'amount' => -$request->amount,
            'fee' => $fee,
            'net_amount' => -$netAmount,
            'balance_before' => $wallet->balance + $request->amount,
            'balance_after' => $wallet->balance,
            'status' => 'pending',
            'description' => 'Demande de retrait via ' . $request->method,
            'completed_at' => null,
        ]);
        
        return redirect()->route('withdrawal.index')
            ->with('success', '✅ Demande de retrait envoyée avec succès !');
    }
}