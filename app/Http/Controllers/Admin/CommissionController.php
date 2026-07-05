<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $totalCommissions = Commission::where('status', 'paid')->sum('amount');
        $pendingCommissions = Commission::where('status', 'pending')->sum('amount');
        $totalCancelled = Commission::where('status', 'cancelled')->sum('amount');

        $users = User::select('id', 'name')->orderBy('name')->get();
        $types = Commission::distinct()->pluck('type');

        return view('admin.commissions.index', compact(
            'commissions', 
            'totalCommissions', 
            'pendingCommissions',
            'totalCancelled',
            'users',
            'types'
        ));
    }

    /**
     * Détails d'une commission - ✅ AJOUTÉ
     */
    public function show($id)
    {
        $commission = Commission::with(['user', 'fromUser', 'order', 'package'])
            ->findOrFail($id);
        
        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Approuver une commission - ✅ AJOUTÉ
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
            
            // Créditer le portefeuille
            $wallet = Wallet::where('user_id', $commission->user_id)->first();
            if ($wallet) {
                $wallet->balance += $commission->amount;
                $wallet->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.commissions')
                ->with('success', "✅ Commission #{$id} approuvée avec succès.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une commission - ✅ AJOUTÉ
     */
    public function reject($id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Cette commission ne peut pas être rejetée.');
        }
        
        $commission->status = 'cancelled';
        $commission->save();
        
        return redirect()->route('admin.commissions')
            ->with('success', "❌ Commission #{$id} rejetée.");
    }

    /**
     * Exporter les commissions - ✅ AJOUTÉ
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
                'ID', 'Utilisateur', 'De', 'Type', 'Montant', 'Pourcentage',
                'Description', 'Statut', 'Payé le', 'Créé le'
            ]);

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->user->name ?? 'N/A',
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
     * Statistiques des commissions - ✅ AJOUTÉ
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
                ->get(),
            'today' => Commission::whereDate('created_at', today())->sum('amount'),
            'this_month' => Commission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_count' => Commission::count(),
        ];

        return response()->json($stats);
    }
}