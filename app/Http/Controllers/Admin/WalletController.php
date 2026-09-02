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
    /**
     * Liste des portefeuilles avec recherche et filtres
     */
    public function index(Request $request)
    {
        $query = Wallet::with('user');

        // Recherche par nom d'utilisateur
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtre par solde minimum
        if ($request->filled('min_balance')) {
            $query->where('balance', '>=', $request->min_balance);
        }

        // Filtre par solde maximum
        if ($request->filled('max_balance')) {
            $query->where('balance', '<=', $request->max_balance);
        }

        $wallets = $query->orderBy('balance', 'desc')->paginate(20);

        // Statistiques globales
        $totalWallets = Wallet::count();
        $totalBalance = Wallet::sum('balance');
        $pendingBalance = Wallet::sum('pending_balance');
        $activeWallets = Wallet::where('is_active', true)->count();

        return view('admin.wallets.index', compact(
            'wallets',
            'totalWallets',
            'totalBalance',
            'pendingBalance',
            'activeWallets'
        ));
    }

    /**
     * Afficher les détails d'un portefeuille
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
            'total_adjustment' => Transaction::where('wallet_id', $id)
                ->where('type', 'adjustment')
                ->where('status', 'completed')
                ->sum('amount'),
            'transaction_count' => Transaction::where('wallet_id', $id)->count(),
        ];

        return view('admin.wallets.show', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Ajuster le solde d'un portefeuille
     */
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
                'metadata' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name,
                ]),
                'completed_at' => now(),
            ]);

            Log::info('Ajustement de solde', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'amount' => $amount,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ]);

            DB::commit();

            $action = $request->type === 'credit' ? 'crédité' : 'débité';
            return redirect()->route('admin.wallets.index')
                ->with('success', "Solde {$action} de " . number_format(abs($amount), 2) . " $ avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajustement solde', [
                'wallet_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver un portefeuille
     */
    public function toggleStatus($id)
    {
        $wallet = Wallet::findOrFail($id);
        $wallet->is_active = !$wallet->is_active;
        $wallet->save();

        $status = $wallet->is_active ? 'débloqué' : 'bloqué';

        Log::info('Portefeuille ' . $status, [
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.wallets.index')
            ->with('success', "Portefeuille {$status} avec succès.");
    }

    /**
     * Exporter les portefeuilles en CSV
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
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="portefeuilles_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($wallets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

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
                    $w->currency ?? 'USD',
                    $w->is_active ? 'Actif' : 'Inactif',
                    $w->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques en JSON pour l'API
     */
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

        return response()->json(['success' => true, 'stats' => $stats]);
    }
}