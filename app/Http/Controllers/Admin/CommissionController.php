<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    /**
     * Liste des commissions
     */
    public function index(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'order', 'package']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistiques
        $stats = [
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->sum('amount'),
            'total_count' => Commission::count(),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
        ];

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $types = Commission::distinct()->pluck('type');

        return view('admin.commissions.index', compact(
            'commissions', 
            'stats',
            'users',
            'types'
        ));
    }

    /**
     * Détails d'une commission
     */
    public function show($id)
    {
        $commission = Commission::with(['user', 'fromUser', 'order', 'package'])
            ->findOrFail($id);
        
        // ✅ Récupérer le parrain du bénéficiaire
        $parrain = User::find($commission->user->parrain_id ?? null);
        
        // ✅ Commissions similaires
        $similarCommissions = Commission::where('user_id', $commission->user_id)
            ->where('type', $commission->type)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.commissions.show', compact(
            'commission', 
            'parrain',
            'similarCommissions'
        ));
    }

    /**
     * Approuver une commission
     */
    public function approve($id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Cette commission ne peut pas être approuvée.');
        }
        
        DB::beginTransaction();
        
        try {
            $commission->status = 'paid';
            $commission->paid_at = now();
            $commission->save();
            
            // ✅ Créditer le portefeuille
            $wallet = Wallet::where('user_id', $commission->user_id)->first();
            if ($wallet) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $commission->amount;
                $wallet->save();
                
                // ✅ Créer la transaction
                Transaction::create([
                    'user_id' => $commission->user_id,
                    'wallet_id' => $wallet->id,
                    'type' => 'commission',
                    'amount' => $commission->amount,
                    'fee' => 0,
                    'net_amount' => $commission->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'description' => "Commission {$commission->type} approuvée",
                    'metadata' => json_encode([
                        'commission_id' => $commission->id,
                        'admin_id' => auth()->id(),
                    ]),
                    'completed_at' => now(),
                ]);
            }
            
            DB::commit();

            // ✅ Envoyer la notification
            try {
                $commission->user->notify(new CommissionPaidNotification(
                    $commission->amount,
                    $commission->type,
                    $commission->id
                ));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification commission', [
                    'commission_id' => $commission->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Commission approuvée', [
                'commission_id' => $commission->id,
                'user_id' => $commission->user_id,
                'amount' => $commission->amount,
                'admin_id' => auth()->id(),
            ]);
            
            return redirect()->route('admin.commissions')
                ->with('success', "✅ Commission #{$id} approuvée avec succès.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur approbation commission', [
                'commission_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une commission
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Cette commission ne peut pas être rejetée.');
        }
        
        $commission->status = 'cancelled';
        $commission->notes = $request->reason ?? 'Rejetée par l\'admin';
        $commission->save();

        Log::info('Commission rejetée', [
            'commission_id' => $commission->id,
            'user_id' => $commission->user_id,
            'amount' => $commission->amount,
            'admin_id' => auth()->id(),
            'reason' => $request->reason,
        ]);
        
        return redirect()->route('admin.commissions')
            ->with('success', "❌ Commission #{$id} rejetée.");
    }

    /**
     * Approuver plusieurs commissions en lot
     */
    public function batchApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:commissions,id',
        ]);

        $count = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            try {
                $this->approve($id);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "ID {$id}: " . $e->getMessage();
            }
        }

        $message = "✅ {$count} commissions approuvées avec succès.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $count,
            'errors' => $errors,
        ]);
    }

    /**
     * Exporter les commissions
     */
    public function export(Request $request)
    {
        $query = Commission::with(['user', 'fromUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'De', 'Type', 'Montant', 'Pourcentage',
                'Description', 'Statut', 'Payé le', 'Créé le'
            ]);

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->user->name ?? 'N/A',
                    $c->user->email ?? 'N/A',
                    $c->fromUser->name ?? 'N/A',
                    $c->type,
                    number_format($c->amount, 2),
                    $c->percentage . '%',
                    $c->description ?? 'N/A',
                    $c->status,
                    $c->paid_at ? $c->paid_at->format('Y-m-d H:i') : 'En attente',
                    $c->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques des commissions
     */
    public function stats()
    {
        $stats = [
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->sum('amount'),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'))
                ->where('status', 'paid')
                ->groupBy('type')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type,
                        'total' => (float) $item->total,
                    ];
                }),
            'today' => Commission::whereDate('created_at', today())->sum('amount'),
            'this_month' => Commission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_count' => Commission::count(),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
        ];

        // ✅ Top 5 des utilisateurs avec le plus de commissions
        $topUsers = Commission::where('status', 'paid')
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'user_name' => $item->user->name ?? 'N/A',
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'top_users' => $topUsers,
        ]);
    }

    /**
     * Voir le réseau de parrainage d'un utilisateur
     */
    public function viewNetwork($userId)
    {
        $user = User::with(['rank', 'package'])->findOrFail($userId);
        
        // ✅ Récupérer le parrain
        $parrain = User::find($user->parrain_id);
        
        // ✅ Récupérer les filleuls
        $filleuls = User::where('parrain_id', $user->id)->with(['rank', 'package'])->get();
        
        // ✅ Récupérer les commissions du réseau
        $networkCommissions = Commission::whereIn('user_id', $filleuls->pluck('id'))
            ->where('status', 'paid')
            ->sum('amount');

        return view('admin.commissions.network', compact(
            'user', 
            'parrain', 
            'filleuls', 
            'networkCommissions'
        ));
    }
}