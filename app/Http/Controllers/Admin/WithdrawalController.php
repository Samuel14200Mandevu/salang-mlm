<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\WithdrawalApprovedNotification;
use App\Notifications\WithdrawalRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    /**
     * Liste des retraits
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with(['user', 'wallet']);

        // Filtres
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

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => Withdrawal::count(),
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'processing' => Withdrawal::where('status', 'processing')->count(),
            'completed' => Withdrawal::where('status', 'completed')->sum('amount'),
            'failed' => Withdrawal::where('status', 'failed')->count(),
            'total_amount' => Withdrawal::where('status', 'completed')->sum('amount'),
            'total_fees' => Withdrawal::where('status', 'completed')->sum('fee'),
        ];

        $methods = Withdrawal::distinct()->pluck('method');

        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'methods'));
    }

    /**
     * Détails d'un retrait
     */
    public function show($id)
    {
        $withdrawal = Withdrawal::with(['user', 'user.wallet', 'wallet'])
            ->findOrFail($id);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approuver un retrait
     */
    public function approve(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->with('error', 'Ce retrait ne peut pas être approuvé.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::find($withdrawal->wallet_id);

            if (!$wallet) {
                return back()->with('error', 'Portefeuille introuvable.');
            }

            if ($wallet->balance < $withdrawal->amount) {
                return back()->with('error', 'Solde insuffisant pour ce retrait.');
            }

            // Débiter le portefeuille
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $withdrawal->amount;
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();

            // Créer la transaction
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

            // Mettre à jour le retrait
            $withdrawal->status = 'completed';
            $withdrawal->processed_at = now();
            $withdrawal->completed_at = now();
            $withdrawal->notes = $request->notes ?? 'Retrait approuvé par l\'admin';
            $withdrawal->save();

            DB::commit();

            // Envoyer la notification d'approbation
            try {
                $withdrawal->user->notify(new WithdrawalApprovedNotification(
                    $withdrawal->amount,
                    $withdrawal->method,
                    $withdrawal->net_amount,
                    $withdrawal->id
                ));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification retrait approuvé: ' . $e->getMessage());
            }

            return redirect()->route('admin.withdrawals')
                ->with('success', "✅ Retrait #{$withdrawal->id} approuvé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur approbation retrait', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter un retrait
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->with('error', 'Ce retrait ne peut pas être rejeté.');
        }

        DB::beginTransaction();

        try {
            // Rembourser le montant dans le portefeuille
            $wallet = Wallet::find($withdrawal->wallet_id);

            if ($wallet) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $withdrawal->amount;
                $wallet->save();

                // Créer une transaction de remboursement
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
                    'description' => 'Remboursement suite au rejet du retrait #' . $withdrawal->id,
                    'metadata' => json_encode([
                        'withdrawal_id' => $withdrawal->id,
                        'admin_id' => auth()->id(),
                        'reason' => $request->reason,
                    ]),
                    'completed_at' => now(),
                ]);
            }

            // Mettre à jour le retrait
            $withdrawal->status = 'failed';
            $withdrawal->processed_at = now();
            $withdrawal->notes = 'Rejeté: ' . $request->reason;
            $withdrawal->save();

            DB::commit();

            // Envoyer la notification de rejet
            try {
                $withdrawal->user->notify(new WithdrawalRejectedNotification(
                    $withdrawal->amount,
                    $request->reason,
                    $withdrawal->id
                ));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification retrait rejeté: ' . $e->getMessage());
            }

            return redirect()->route('admin.withdrawals')
                ->with('success', "❌ Retrait #{$withdrawal->id} rejeté.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur rejet retrait', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors du rejet: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les retraits
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
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="withdrawals_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'Montant', 'Frais', 'Net',
                'Méthode', 'Statut', 'Créé le', 'Complété le'
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
                    $w->completed_at ? $w->completed_at->format('Y-m-d H:i') : 'En attente',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques API pour le dashboard
     */
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
                ->sum('amount'),
            'total_fees' => Withdrawal::where('status', 'completed')->sum('fee'),
            'avg_amount' => Withdrawal::where('status', 'completed')->avg('amount'),
        ];

        return response()->json($stats);
    }
}