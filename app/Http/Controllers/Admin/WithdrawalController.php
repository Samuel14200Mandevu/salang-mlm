<?php
// app/Http/Controllers/Admin/WithdrawalController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\WithdrawalApprovedNotification;
use App\Notifications\WithdrawalRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with(['user', 'wallet']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Withdrawal::count(),
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'processing' => Withdrawal::where('status', 'processing')->count(),
            'completed' => Withdrawal::where('status', 'completed')->count(),
            'failed' => Withdrawal::where('status', 'failed')->count(),
            'total_amount' => Withdrawal::where('status', 'completed')->sum('amount'),
            'total_fees' => Withdrawal::where('status', 'completed')->sum('fee'),
            'avg_amount' => Withdrawal::where('status', 'completed')->avg('amount'),
            'today_pending' => Withdrawal::where('status', 'pending')
                ->whereDate('created_at', today())
                ->count(),
        ];

        $methods = Withdrawal::distinct()->pluck('method');
        $statuses = ['pending', 'processing', 'completed', 'failed'];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'methods', 'statuses'));
    }

    public function show($id)
    {
        $withdrawal = Withdrawal::with(['user', 'user.wallet', 'wallet'])
            ->findOrFail($id);

        $userWithdrawals = Withdrawal::where('user_id', $withdrawal->user_id)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.withdrawals.show', compact('withdrawal', 'userWithdrawals'));
    }

    public function process($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal cannot be processed.');
        }

        $withdrawal->status = 'processing';
        $withdrawal->processed_at = now();
        $withdrawal->save();

        Log::info('Withdrawal processing', [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.withdrawals')
            ->with('success', "Withdrawal #{$withdrawal->id} is now processing.");
    }

    public function approve(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->with('error', 'This withdrawal cannot be approved.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if (!$wallet) {
                return back()->with('error', 'Wallet not found.');
            }

            if ($wallet->balance < $withdrawal->amount) {
                return back()->with('error', 'Insufficient balance for this withdrawal.');
            }

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
                'description' => "Withdrawal approved via {$withdrawal->method}",
                'metadata' => json_encode([
                    'withdrawal_id' => $withdrawal->id,
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name,
                ]),
                'completed_at' => now(),
            ]);

            $withdrawal->status = 'completed';
            $withdrawal->processed_at = now();
            $withdrawal->completed_at = now();
            $withdrawal->notes = $request->notes ?? 'Withdrawal approved by admin';
            $withdrawal->save();

            DB::commit();

            try {
                $withdrawal->user->notify(new WithdrawalApprovedNotification(
                    $withdrawal->amount,
                    $withdrawal->method,
                    $withdrawal->net_amount,
                    $withdrawal->id
                ));
            } catch (\Exception $e) {
                Log::error('Error sending withdrawal approved notification', [
                    'withdrawal_id' => $withdrawal->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Withdrawal approved', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.withdrawals')
                ->with('success', "Withdrawal #{$withdrawal->id} approved successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving withdrawal', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->with('error', 'This withdrawal cannot be rejected.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if ($wallet) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $withdrawal->amount;
                $wallet->save();

                Transaction::create([
                    'user_id' => $withdrawal->user_id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $withdrawal->amount,
                    'fee' => 0,
                    'net_amount' => $withdrawal->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'description' => 'Refund for rejected withdrawal #' . $withdrawal->id,
                    'metadata' => json_encode([
                        'withdrawal_id' => $withdrawal->id,
                        'admin_id' => auth()->id(),
                        'reason' => $request->reason,
                    ]),
                    'completed_at' => now(),
                ]);
            }

            $withdrawal->status = 'failed';
            $withdrawal->processed_at = now();
            $withdrawal->notes = 'Rejected: ' . $request->reason;
            $withdrawal->save();

            DB::commit();

            try {
                $withdrawal->user->notify(new WithdrawalRejectedNotification(
                    $withdrawal->amount,
                    $request->reason,
                    $withdrawal->id
                ));
            } catch (\Exception $e) {
                Log::error('Error sending withdrawal rejected notification', [
                    'withdrawal_id' => $withdrawal->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Withdrawal rejected', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.withdrawals')
                ->with('success', "Withdrawal #{$withdrawal->id} rejected.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting withdrawal', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = Withdrawal::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="withdrawals_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'User', 'Email', 'Amount', 'Fee (2.5%)', 'Net',
                'Method', 'Status', 'Created At', 'Processed At', 'Completed At'
            ]);

            foreach ($withdrawals as $w) {
                fputcsv($file, [
                    $w->id,
                    $w->user->name ?? 'N/A',
                    $w->user->email ?? 'N/A',
                    number_format($w->amount, 2),
                    number_format($w->fee, 2),
                    number_format($w->net_amount, 2),
                    $w->method,
                    $w->status,
                    $w->created_at->format('Y-m-d H:i'),
                    $w->processed_at ? $w->processed_at->format('Y-m-d H:i') : 'Pending',
                    $w->completed_at ? $w->completed_at->format('Y-m-d H:i') : 'Pending',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stats()
    {
        $stats = [
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'processing' => Withdrawal::where('status', 'processing')->count(),
            'completed_today' => Withdrawal::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->sum('amount'),
            'completed_this_month' => Withdrawal::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('amount'),
            'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
            'total_fees' => Withdrawal::where('status', 'completed')->sum('fee'),
            'avg_amount' => Withdrawal::where('status', 'completed')->avg('amount'),
            'total_withdrawals' => Withdrawal::where('status', 'completed')->count(),
            'total_amount' => Withdrawal::where('status', 'completed')->sum('amount'),
        ];

        $byMethod = Withdrawal::where('status', 'completed')
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'by_method' => $byMethod,
        ]);
    }
}