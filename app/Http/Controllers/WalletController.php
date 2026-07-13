<?php
// app/Http/Controllers/WalletController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
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

        $transactionStats = [
            'total_commission' => Transaction::where('user_id', $user->id)
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_deposit' => Transaction::where('user_id', $user->id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_withdrawal' => Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount'),
            'count_commission' => Transaction::where('user_id', $user->id)
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->count(),
            'count_deposit' => Transaction::where('user_id', $user->id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->count(),
            'count_withdrawal' => Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->count(),
        ];

        $monthlyCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        return view('wallet.index', compact(
            'balance',
            'pendingBalance',
            'totalWithdrawn',
            'totalDeposited',
            'transactions',
            'transactionStats',
            'monthlyCommissions'
        ));
    }

    public function deposit()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->balance : 0;

        $deposits = Transaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('wallet.deposit', compact('balance', 'deposits'));
    }

    public function storeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
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

        DB::beginTransaction();

        try {
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
                'description' => 'Deposit via ' . ucfirst(str_replace('_', ' ', $paymentMethod)),
                'metadata' => json_encode(['payment_method' => $paymentMethod]),
            ]);

            $wallet->balance += $amount;
            $wallet->total_deposited += $amount;
            $wallet->save();

            $transaction->status = 'completed';
            $transaction->balance_after = $wallet->balance;
            $transaction->completed_at = now();
            $transaction->save();

            DB::commit();

            Log::info('Deposit made', [
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => $paymentMethod,
            ]);

            return redirect()->route('wallet.index')
                ->with('success', 'Your deposit of $' . number_format($amount, 2) . ' was successful.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error depositing', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error depositing: ' . $e->getMessage());
        }
    }

    public function transactions(Request $request)
    {
        $user = Auth::user();

        $query = Transaction::where('user_id', $user->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        $types = Transaction::where('user_id', $user->id)->distinct()->pluck('type');
        $statuses = ['pending', 'completed', 'failed', 'cancelled'];

        return view('wallet.transactions', compact('transactions', 'types', 'statuses'));
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->when($request->filled('type'), function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('date_from'), function($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'transactions_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Type', 'Amount', 'Fee', 'Net',
                'Balance Before', 'Balance After', 'Status', 'Description', 'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    ucfirst($t->type),
                    number_format($t->amount, 2),
                    number_format($t->fee, 2),
                    number_format($t->net_amount, 2),
                    number_format($t->balance_before, 2),
                    number_format($t->balance_after, 2),
                    ucfirst($t->status),
                    $t->description ?? '',
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function apiBalance()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return response()->json([
            'success' => true,
            'balance' => $wallet ? $wallet->balance : 0,
            'pending_balance' => $wallet ? $wallet->pending_balance : 0,
            'total_withdrawn' => $wallet ? $wallet->total_withdrawn : 0,
            'total_deposited' => $wallet ? $wallet->total_deposited : 0,
        ]);
    }

    public function apiTransactions(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->when($request->filled('type'), function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}