<?php
// app/Http/Controllers/Admin/WithdrawalController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    /**
     * Liste des retraits avec recherche et filtres
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with(['user', 'wallet']);

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
            $query->where('status', $request->status);
        }

        // Filtre par méthode
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filtre par montant min/max
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'processing' => Withdrawal::where('status', 'processing')->count(),
            'total_amount' => Withdrawal::where('status', 'completed')->sum('amount'),
            'total_fees' => Withdrawal::where('status', 'completed')->sum('fee'),
        ];

        // Méthodes disponibles pour le filtre
        $methods = Withdrawal::distinct()->pluck('method')->filter()->values();

        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'methods'));
    }

    /**
     * Afficher les détails d'un retrait
     */
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

    /**
     * Approuver un retrait
     */
    public function approve(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return redirect()->route('admin.withdrawals.index')
                ->with('error', 'Ce retrait ne peut pas être approuvé.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if (!$wallet) {
                return back()->with('error', 'Portefeuille non trouvé.');
            }

            if ($wallet->balance < $withdrawal->amount) {
                return back()->with('error', 'Solde insuffisant pour ce retrait.');
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
                'description' => "Retrait approuvé via {$withdrawal->method}",
                'metadata' => json_encode([
                    'withdrawal_id' => $withdrawal->id,
                    'admin_id' => auth()->id(),
                ]),
                'completed_at' => now(),
            ]);

            $withdrawal->status = 'completed';
            $withdrawal->processed_at = now();
            $withdrawal->completed_at = now();
            $withdrawal->notes = $request->notes ?? 'Retrait approuvé par l\'administrateur';
            $withdrawal->save();

            DB::commit();

            Log::info('Retrait approuvé', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.withdrawals.index')
                ->with('success', "Retrait #{$withdrawal->id} approuvé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur approbation retrait', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter un retrait
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return redirect()->route('admin.withdrawals.index')
                ->with('error', 'Ce retrait ne peut pas être rejeté.');
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
                    'description' => 'Remboursement pour retrait rejeté #' . $withdrawal->id,
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
            $withdrawal->notes = 'Rejeté: ' . $request->reason;
            $withdrawal->save();

            DB::commit();

            Log::info('Retrait rejeté', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.withdrawals.index')
                ->with('success', "Retrait #{$withdrawal->id} rejeté.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur rejet retrait', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les retraits en CSV
     */
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
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="retraits_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'Montant', 'Frais', 'Net',
                'Méthode', 'Statut', 'Créé le', 'Traité le', 'Terminé le'
            ]);

            $statusLabels = [
                'pending' => 'En attente',
                'processing' => 'En traitement',
                'completed' => 'Terminé',
                'failed' => 'Échoué',
            ];

            foreach ($withdrawals as $w) {
                fputcsv($file, [
                    $w->id,
                    $w->user->name ?? 'N/A',
                    $w->user->email ?? 'N/A',
                    number_format($w->amount, 2),
                    number_format($w->fee, 2),
                    number_format($w->net_amount, 2),
                    $w->method,
                    $statusLabels[$w->status] ?? $w->status,
                    $w->created_at->format('d/m/Y H:i'),
                    $w->processed_at ? $w->processed_at->format('d/m/Y H:i') : 'En attente',
                    $w->completed_at ? $w->completed_at->format('d/m/Y H:i') : 'En attente',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}