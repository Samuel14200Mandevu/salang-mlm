<?php
// app/Http/Controllers/Admin/WalletController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $query = Wallet::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('min_balance')) {
            $query->where('balance', '>=', $request->min_balance);
        }

        if ($request->filled('max_balance')) {
            $query->where('balance', '<=', $request->max_balance);
        }

        $wallets = $query->orderBy('balance', 'desc')->paginate(20);

        $stats = [
            'total_balance' => Wallet::sum('balance'),
            'total_wallets' => Wallet::count(),
            'active_wallets' => Wallet::where('is_active', true)->count(),
            'total_withdrawn' => Wallet::sum('total_withdrawn'),
            'total_deposited' => Wallet::sum('total_deposited'),
            'avg_balance' => Wallet::avg('balance'),
            'max_balance' => Wallet::max('balance'),
            'min_balance' => Wallet::min('balance'),
            'zero_balance' => Wallet::where('balance', 0)->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    public function show($id)
    {
        $wallet = Wallet::with(['user', 'user.rank', 'user.package'])
            ->findOrFail($id);

        $transactions = Transaction::where('wallet_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_deposited' => Transaction::where('wallet_id', $id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_withdrawn' => Transaction::where('wallet_id', $id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_commission' => Transaction::where('wallet_id', $id)
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_adjustment' => Transaction::where('wallet_id', $id)
                ->where('type', 'adjustment')
                ->where('status', 'completed')
                ->sum('amount'),
            'transaction_count' => Transaction::where('wallet_id', $id)->count(),
        ];

        return view('admin.wallets.show', compact('wallet', 'transactions', 'stats'));
    }

    public function adjust(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|min:5|max:500',
            'type' => 'required|in:credit,debit',
        ]);

        $wallet = Wallet::findOrFail($id);

        DB::beginTransaction();

        try {
            $amount = $request->amount;
            $balanceBefore = $wallet->balance;

            if ($request->type === 'debit') {
                $amount = -$amount;
                if ($balanceBefore + $amount < 0) {
                    return back()->with('error', 'Insufficient balance for this debit.');
                }
            }

            $wallet->balance += $amount;
            $wallet->save();

            Transaction::create([
                'user_id' => $wallet->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => $request->reason,
                'metadata' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name,
                ]),
                'completed_at' => now(),
            ]);

            Log::info('Balance adjustment', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'amount' => $amount,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ]);

            DB::commit();

            $action = $request->type === 'credit' ? 'credited' : 'debited';
            return redirect()->route('admin.wallets')
                ->with('success', "Balance {$action} of $" . number_format(abs($amount), 2) . " successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adjusting balance', [
                'wallet_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $wallet = Wallet::findOrFail($id);
        $wallet->is_active = !$wallet->is_active;
        $wallet->save();

        $status = $wallet->is_active ? 'unfrozen' : 'frozen';

        Log::info('Wallet ' . $status, [
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.wallets')
            ->with('success', "Wallet {$status} successfully.");
    }

    public function export(Request $request)
    {
        $query = Wallet::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $wallets = $query->orderBy('balance', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wallets_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($wallets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'User', 'Email', 'Balance', 'Pending Balance',
                'Total Withdrawn', 'Total Deposited', 'Currency', 'Status', 'Created At'
            ]);

            foreach ($wallets as $w) {
                fputcsv($file, [
                    $w->id,
                    $w->user->name ?? 'N/A',
                    $w->user->email ?? 'N/A',
                    number_format($w->balance, 2),
                    number_format($w->pending_balance, 2),
                    number_format($w->total_withdrawn, 2),
                    number_format($w->total_deposited, 2),
                    $w->currency,
                    $w->is_active ? 'Active' : 'Inactive',
                    $w->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stats()
    {
        $stats = [
            'total_balance' => Wallet::sum('balance'),
            'total_wallets' => Wallet::count(),
            'active_wallets' => Wallet::where('is_active', true)->count(),
            'total_withdrawn' => Wallet::sum('total_withdrawn'),
            'total_deposited' => Wallet::sum('total_deposited'),
            'avg_balance' => Wallet::avg('balance'),
            'zero_balance' => Wallet::where('balance', 0)->count(),
        ];

        $topWallets = Wallet::with('user')
            ->orderBy('balance', 'desc')
            ->limit(10)
            ->get()
            ->map(function($wallet) {
                return [
                    'user_name' => $wallet->user->name ?? 'N/A',
                    'balance' => $wallet->balance,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'top_wallets' => $topWallets,
        ]);
    }
}