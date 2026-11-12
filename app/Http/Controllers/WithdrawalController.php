<?php
// app/Http/Controllers/WithdrawalController.php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GuichetWithdrawalRequestedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user && ! $user->isWalletEligible()) {
                abort(403, 'Les caissiers et responsables IT n’ont pas de portefeuille.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $balance = $wallet ? $wallet->balance : 0;

        $query = Withdrawal::where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $stats = [
            'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_pending' => $pendingWithdrawals,
            'total_requests' => Withdrawal::where('user_id', $user->id)->count(),
            'completed_count' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'pending_count' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Guichet = méthode active aujourd'hui (pas d'API pour les autres encore)
        $methods = ['guichet', 'crypto', 'mobile_money', 'bank'];

        return view('withdrawal.index', compact(
            'withdrawals',
            'balance',
            'stats',
            'methods',
            'pendingWithdrawals'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'method' => 'required|in:guichet,crypto,mobile_money,bank',
            'address' => 'required_if:method,crypto|nullable|string|max:255',
            'phone' => 'required_if:method,mobile_money|nullable|string|max:20',
            'bank_details' => 'required_if:method,bank|nullable|string|max:255',
        ]);

        // Autres méthodes sans API : bloquer pour l'instant (guichet seul opérationnel)
        if ($request->method !== 'guichet') {
            return back()->with('error',
                'Cette méthode n’est pas encore disponible. Utilisez le retrait guichet (espèces) au comptoir.'
            )->withInput();
        }

        if (!$wallet) {
            return back()->with('error', 'Portefeuille introuvable.');
        }

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Solde insuffisant. Disponible : $' . number_format($wallet->balance, 2));
        }

        $threshold = config('commission.kyc.withdrawal_threshold', 5000);
        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        if (($totalWithdrawn + $request->amount) >= $threshold && $user->kyc_status !== 'verified') {
            return back()->with('error',
                'Vous devez compléter le KYC pour retirer plus de $' .
                number_format($threshold, 0) .
                '. <a href="' . route('kyc.index') . '" style="color: #6366f1; text-decoration: underline;">Vérifier mon identité</a>'
            );
        }

        $todayWithdrawals = Withdrawal::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'pending')
            ->sum('amount');

        if ($todayWithdrawals + $request->amount > 5000) {
            return back()->with('error', 'Plafond journalier de retraits atteint (5 000 USD).');
        }

        // Guichet : pas de frais (espèces au comptoir)
        $fee = $request->method === 'guichet' ? 0 : ($request->amount * 0.025);
        $netAmount = $request->amount - $fee;

        DB::beginTransaction();

        try {
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'method' => $request->method,
                'payment_address' => $request->address,
                'phone_number' => $request->phone,
                'bank_details' => $request->bank_details,
                'status' => 'pending',
                'notes' => $request->method === 'guichet'
                    ? 'Retrait guichet — en attente remise espèces'
                    : 'Withdrawal request',
            ]);

            $wallet->balance -= $request->amount;
            $wallet->pending_balance += $request->amount;
            $wallet->save();

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
                'description' => $request->method === 'guichet'
                    ? 'Demande retrait guichet (espèces)'
                    : 'Withdrawal request via ' . $request->method,
                'completed_at' => null,
            ]);

            DB::commit();

            if ($request->method === 'guichet') {
                $cashiers = User::role('cashier')->get();
                if ($cashiers->isNotEmpty()) {
                    Notification::send($cashiers, new GuichetWithdrawalRequestedNotification($withdrawal->fresh('user')));
                }
            }

            Log::info('Withdrawal request', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
                'amount' => $request->amount,
                'method' => $request->method,
            ]);

            $msg = $request->method === 'guichet'
                ? 'Retrait guichet de $' . number_format($request->amount, 2) .
                  ' confirmé. Présentez-vous au comptoir : le caissier a été notifié et vous remettra l’argent + le reçu.'
                : 'Demande de retrait de $' . number_format($request->amount, 2) . ' enregistrée.';

            return redirect()->route('withdrawal.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing withdrawal', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $withdrawal = Withdrawal::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('withdrawal.show', compact('withdrawal'));
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $withdrawal = Withdrawal::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);
            if ($wallet) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $withdrawal->amount;
                $wallet->pending_balance -= $withdrawal->amount;
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $withdrawal->amount,
                    'fee' => 0,
                    'net_amount' => $withdrawal->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'description' => 'Cancellation of withdrawal #' . $withdrawal->id,
                    'completed_at' => now(),
                ]);
            }

            $withdrawal->status = 'cancelled';
            $withdrawal->notes = 'Cancelled by user';
            $withdrawal->save();

            DB::commit();

            Log::info('Withdrawal cancelled', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
            ]);

            return redirect()->route('withdrawal.index')
                ->with('success', 'Withdrawal cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling withdrawal', [
                'user_id' => $user->id,
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'method' => 'required|in:crypto,mobile_money,bank',
            'address' => 'required_if:method,crypto|string|max:255',
            'phone' => 'required_if:method,mobile_money|string|max:20',
            'bank_details' => 'required_if:method,bank|string|max:255',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.'
            ], 400);
        }

        $amount = $request->amount;
        $fee = $amount * 0.025;
        $netAmount = $amount - $fee;

        DB::beginTransaction();

        try {
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'method' => $request->method,
                'payment_address' => $request->address,
                'phone_number' => $request->phone,
                'bank_details' => $request->bank_details,
                'status' => 'pending',
                'notes' => 'API withdrawal request',
            ]);

            $wallet->balance -= $amount;
            $wallet->pending_balance += $amount;
            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted',
                'data' => $withdrawal,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error API withdrawal', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}