<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * @var CommissionService
     */
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Liste des commissions de l'utilisateur
     */
    public function index(Request $request)
{
    $user = Auth::user();

    $query = Commission::where('user_id', $user->id)
        ->with(['fromUser', 'order', 'package']);

    // Filtres...
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    
    $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

    $stats = $this->getUserStats($user->id);
    $types = Commission::where('user_id', $user->id)->distinct()->pluck('type');

    return view('commissions.index', compact('commissions', 'stats', 'types'));
}

    /**
     * Statistiques des commissions de l'utilisateur
     */
    public function stats(Request $request)
    {
        $user = Auth::user();

        $stats = $this->getUserStats($user->id);

        if ($request->wantsJson()) {
            return response()->json($stats);
        }

        return view('commissions.stats', compact('stats'));
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    private function getUserStats($userId)
    {
        $stats = [
            'total' => Commission::where('user_id', $userId)
                ->where('status', 'paid')
                ->sum('amount'),
            'pending' => Commission::where('user_id', $userId)
                ->where('status', 'pending')
                ->sum('amount'),
            'total_count' => Commission::where('user_id', $userId)
                ->where('status', 'paid')
                ->count(),
            'pending_count' => Commission::where('user_id', $userId)
                ->where('status', 'pending')
                ->count(),
            'by_type' => [],
            'monthly' => [],
            'recent' => Commission::where('user_id', $userId)
                ->with(['fromUser', 'order', 'package'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Par type
        $byType = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        foreach ($byType as $item) {
            $stats['by_type'][$item->type] = [
                'total' => $item->total,
                'count' => $item->count,
                'label' => $this->getTypeLabel($item->type),
                'icon' => $this->getTypeIcon($item->type),
                'color' => $this->getTypeColor($item->type),
            ];
        }

        // Mensuel (12 derniers mois)
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Commission::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');

            $stats['monthly'][] = [
                'month' => $month->format('M Y'),
                'amount' => $amount,
            ];
        }

        return $stats;
    }

    /**
     * Afficher les détails d'une commission
     */
    public function show($id)
    {
        $user = Auth::user();

        $commission = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'order', 'order.items', 'package', 'user'])
            ->findOrFail($id);

        // Vérifier que la commission appartient bien à l'utilisateur
        if ($commission->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        return view('commissions.show', compact('commission'));
    }

    /**
     * Exporter les commissions en CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'order', 'package']);

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

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');

            // En-têtes
            fputcsv($file, [
                'ID',
                'Type',
                'De',
                'Montant',
                'Pourcentage',
                'Description',
                'Statut',
                'Payé le',
                'Créé le'
            ]);

            // Données
            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $this->getTypeLabel($c->type),
                    $c->fromUser->name ?? 'N/A',
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
     * API: Obtenir les commissions en temps réel
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'order', 'package'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $commissions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $commissions,
            'total' => $commissions->count(),
            'total_amount' => $commissions->sum('amount'),
        ]);
    }

    /**
     * API: Obtenir les statistiques
     */
    public function apiStats(Request $request)
    {
        $user = Auth::user();

        $stats = $this->getUserStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Obtenir le libellé d'un type de commission
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'direct' => 'Commission Directe',
            'indirect' => 'Commission Indirecte',
            'leadership' => 'Commission Leadership',
            'retail' => 'Profit Retail',
            'bonus' => 'Bonus',
            'level' => 'Commission Niveau',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Obtenir l'icône d'un type de commission
     */
    private function getTypeIcon($type)
    {
        $icons = [
            'direct' => 'fa-user-plus',
            'indirect' => 'fa-users',
            'leadership' => 'fa-crown',
            'retail' => 'fa-shopping-cart',
            'bonus' => 'fa-gift',
            'level' => 'fa-layer-group',
        ];

        return $icons[$type] ?? 'fa-coins';
    }

    /**
     * Obtenir la couleur d'un type de commission
     */
    private function getTypeColor($type)
    {
        $colors = [
            'direct' => 'primary',
            'indirect' => 'info',
            'leadership' => 'warning',
            'retail' => 'success',
            'bonus' => 'danger',
            'level' => 'secondary',
        ];

        return $colors[$type] ?? 'primary';
    }

    /**
     * Recalculer les commissions pour un utilisateur (Admin)
     */
    public function recalculate(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'from_date' => 'nullable|date',
        ]);

        $user = User::find($request->user_id);

        // Récupérer les commandes de l'utilisateur
        $query = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('payment_status', 'completed');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        $orders = $query->get();

        $processed = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($item->package_id) {
                    $result = $this->commissionService->calculatePackageCommission(
                        $user->id,
                        $item->package_id,
                        $order->id
                    );
                    if ($result) {
                        $processed++;
                    }
                }
            }
        }

        return redirect()->route('admin.commissions')
            ->with('success', "Recalcul terminé. {$processed} commissions traitées.");
    }

    /**
     * Obtenir les commissions par niveau (Unilevel)
     */
    public function getLevelCommissions(Request $request)
    {
        $user = Auth::user();

        $levels = [];

        // Niveau 1 (Direct)
        $level1Users = User::where('sponsor_id', $user->sponsor_id)->pluck('id');
        $level1Commissions = Commission::whereIn('from_user_id', $level1Users)
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $levels[1] = [
            'label' => 'Niveau 1 - Direct',
            'count' => $level1Users->count(),
            'amount' => $level1Commissions,
            'percentage' => config('commission.levels.1', 30),
        ];

        // Niveau 2
        $level2Users = User::whereIn('sponsor_id', $level1Users)->pluck('id');
        $level2Commissions = Commission::whereIn('from_user_id', $level2Users)
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $levels[2] = [
            'label' => 'Niveau 2 - Indirect',
            'count' => $level2Users->count(),
            'amount' => $level2Commissions,
            'percentage' => config('commission.levels.2', 15),
        ];

        // Niveau 3
        $level3Users = User::whereIn('sponsor_id', $level2Users)->pluck('id');
        $level3Commissions = Commission::whereIn('from_user_id', $level3Users)
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $levels[3] = [
            'label' => 'Niveau 3 - Leadership',
            'count' => $level3Users->count(),
            'amount' => $level3Commissions,
            'percentage' => config('commission.levels.3', 10),
        ];

        // Niveau 4
        $level4Users = User::whereIn('sponsor_id', $level3Users)->pluck('id');
        $level4Commissions = Commission::whereIn('from_user_id', $level4Users)
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $levels[4] = [
            'label' => 'Niveau 4',
            'count' => $level4Users->count(),
            'amount' => $level4Commissions,
            'percentage' => config('commission.levels.4', 5),
        ];

        // Niveau 5
        $level5Users = User::whereIn('sponsor_id', $level4Users)->pluck('id');
        $level5Commissions = Commission::whereIn('from_user_id', $level5Users)
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $levels[5] = [
            'label' => 'Niveau 5',
            'count' => $level5Users->count(),
            'amount' => $level5Commissions,
            'percentage' => config('commission.levels.5', 5),
        ];

        $total = array_sum(array_column($levels, 'amount'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $levels,
                'total' => $total,
            ]);
        }

        return view('commissions.levels', compact('levels', 'total'));
    }

    /**
     * Générer un rapport PDF des commissions
     */
    public function pdf(Request $request)
    {
        $user = Auth::user();

        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->get();
        $total = $commissions->sum('amount');

        // Générer le PDF (à adapter selon votre package PDF)
        // Exemple avec DomPDF
        $pdf = \PDF::loadView('commissions.pdf', compact('commissions', 'total', 'user'));

        return $pdf->download('commissions_' . date('Y-m-d') . '.pdf');
    }
}