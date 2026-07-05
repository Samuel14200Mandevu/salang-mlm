<?php
// app/Http/Controllers/Admin/WalletController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $query = Wallet::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $wallets = $query->orderBy('balance', 'desc')->paginate(20);
            
        $totalBalance = Wallet::sum('balance');
        $totalWallets = Wallet::count();
        $activeWallets = Wallet::where('is_active', true)->count();
        
        return view('admin.wallets.index', compact('wallets', 'totalBalance', 'totalWallets', 'activeWallets'));
    }

    /**
     * Détails d'un portefeuille - ✅ AJOUTÉ
     */
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
        ];
        
        return view('admin.wallets.show', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Ajuster un solde - ✅ AJOUTÉ
     */
    public function adjust(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|min:5',
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
                    return back()->with('error', 'Solde insuffisant pour ce débit.');
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
                'metadata' => json_encode(['admin_id' => auth()->id()]),
                'completed_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.wallets')
                ->with('success', "💰 Solde ajusté avec succès.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les portefeuilles - ✅ AJOUTÉ
     */
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
            
            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'Solde', 'Solde en attente',
                'Total retiré', 'Total déposé', 'Devise', 'Statut', 'Créé le'
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
                    $w->is_active ? 'Actif' : 'Inactif',
                    $w->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}