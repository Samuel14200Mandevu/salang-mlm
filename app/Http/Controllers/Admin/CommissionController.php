<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    /**
     * Liste des commissions avec recherche et filtres
     */
    public function index(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'order', 'package', 'period']);

        // Recherche par nom d'utilisateur
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par période
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Exclure les types non souhaités pour l'affichage
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
        $query->whereNotIn('type', $excludedTypes);

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total_paid' => Commission::where('status', 'paid')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_pending' => Commission::where('status', 'pending')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_count' => Commission::whereNotIn('type', $excludedTypes)->count(),
        ];

        // Types disponibles pour le filtre
        $types = Commission::whereNotIn('type', $excludedTypes)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();

        // Périodes disponibles
        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');

        return view('admin.commissions.index', compact(
            'commissions',
            'stats',
            'types',
            'periods'
        ));
    }

    /**
     * Afficher les détails d'une commission
     */
    public function show($id)
    {
        $commission = Commission::with(['user', 'fromUser', 'order', 'package', 'period'])
            ->findOrFail($id);

        $parrain = User::find($commission->user->parrain_id ?? null);

        $similarCommissions = Commission::where('user_id', $commission->user_id)
            ->where('type', $commission->type)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $networkCommissions = Commission::where('user_id', $commission->user_id)
            ->where('status', 'paid')
            ->sum('amount');

        return view('admin.commissions.show', compact(
            'commission',
            'parrain',
            'similarCommissions',
            'networkCommissions'
        ));
    }

    /**
     * Approuver une commission
     */
    public function approve($id)
    {
        $commission = Commission::findOrFail($id);

        if ($commission->status !== 'pending') {
            return redirect()->route('admin.commissions.index')
                ->with('error', 'Cette commission ne peut pas être approuvée.');
        }

        DB::beginTransaction();

        try {
            $commission->status = 'paid';
            $commission->paid_at = now();
            $commission->save();

            $wallet = Wallet::where('user_id', $commission->user_id)->first();
            if ($wallet) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $commission->amount;
                $wallet->save();

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
                    'description' => "Commission {$commission->type} approuvée #{$commission->id}",
                    'metadata' => json_encode([
                        'commission_id' => $commission->id,
                        'admin_id' => auth()->id(),
                    ]),
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Commission approuvée', [
                'commission_id' => $commission->id,
                'user_id' => $commission->user_id,
                'amount' => $commission->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.commissions.index')
                ->with('success', "Commission #{$id} approuvée avec succès.");

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
            return redirect()->route('admin.commissions.index')
                ->with('error', 'Cette commission ne peut pas être rejetée.');
        }

        $commission->status = 'cancelled';
        $commission->notes = $request->reason ?? 'Rejeté par l\'administrateur';
        $commission->save();

        Log::info('Commission rejetée', [
            'commission_id' => $commission->id,
            'user_id' => $commission->user_id,
            'amount' => $commission->amount,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.commissions.index')
            ->with('success', "Commission #{$id} rejetée.");
    }

    /**
     * Exporter les commissions en CSV
     */
    public function export(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'period']);

        // Exclure les types non souhaités
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
        $query->whereNotIn('type', $excludedTypes);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'De', 'Type', 'Montant',
                'Période', 'Description', 'Statut', 'Payé le', 'Créé le'
            ]);

            $statusLabels = [
                'paid' => 'Payé',
                'pending' => 'En attente',
                'cancelled' => 'Annulé',
            ];

            $typeLabels = [
                'direct' => 'Direct',
                'indirect' => 'Indirect',
                'leadership' => 'Leadership',
                'retail' => 'Retail',
                'global' => 'Global',
                'binary' => 'Binaire',
            ];

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->user->name ?? 'N/A',
                    $c->user->email ?? 'N/A',
                    $c->fromUser->name ?? 'N/A',
                    $typeLabels[$c->type] ?? $c->type,
                    number_format($c->amount, 2),
                    $c->period ?? 'N/A',
                    $c->description ?? 'N/A',
                    $statusLabels[$c->status] ?? $c->status,
                    $c->paid_at ? $c->paid_at->format('d/m/Y H:i') : 'En attente',
                    $c->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques en JSON pour l'API
     */
    public function stats(Request $request)
    {
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];

        $stats = [
            'total_pending' => Commission::where('status', 'pending')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'today' => Commission::whereDate('created_at', today())->whereNotIn('type', $excludedTypes)->sum('amount'),
            'this_month' => Commission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereNotIn('type', $excludedTypes)
                ->sum('amount'),
            'total_count' => Commission::whereNotIn('type', $excludedTypes)->count(),
            'pending_count' => Commission::where('status', 'pending')->whereNotIn('type', $excludedTypes)->count(),
            'paid_count' => Commission::where('status', 'paid')->whereNotIn('type', $excludedTypes)->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }
}