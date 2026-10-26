<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Models\Commission;
use App\Models\User;
use App\Models\PVHistory;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Notifications\CommissionPaidNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommissionController extends Controller
{
    use GeneratesPdfSafely;

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

        if ($request->filled('band')) {
            $band = $request->string('band')->lower()->toString();
            $query->whereHas('period', function ($q) use ($band) {
                match ($band) {
                    'a', 'historical' => $q->where('is_historical', true),
                    'b', 'payable' => $q->where('is_historical', false)->where('is_hidden', false),
                    'c', 'hidden' => $q->where('is_hidden', true),
                    default => $q,
                };
            });
        }

        if ($request->boolean('exclude_hidden')) {
            $query->whereHas('period', fn ($q) => $q->where('is_hidden', false));
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

        // Périodes disponibles (admin : toutes)
        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');

        $periodBandCounts = [
            'historical' => CommissionPeriod::where('is_historical', true)->count(),
            'payable' => CommissionPeriod::where('is_historical', false)->where('is_hidden', false)->count(),
            'hidden' => CommissionPeriod::where('is_hidden', true)->count(),
        ];

        return view('admin.commissions.index', compact(
            'commissions',
            'stats',
            'types',
            'periods',
            'periodBandCounts'
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
            return redirect()->route('admin.commissions')
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

            return redirect()->route('admin.commissions')
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
            return redirect()->route('admin.commissions')
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

        return redirect()->route('admin.commissions')
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
     * Exporter les commissions en PDF (rapport individuel avec filtres)
     */
    public function exportPDF(Request $request)
    {
        try {
            $this->preparePdfExport(300, '1024M');

        $query = Commission::with(['user:id,name,email,sponsor_id', 'fromUser:id,name']);

        // Exclure les types non souhaités
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
        $query->whereNotIn('type', $excludedTypes);

        // Appliquer les filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        if ($commissions->isEmpty()) {
            return $this->downloadPdfHtml(
                '<h1 style="text-align:center; font-family:DejaVu Sans; margin-top:50px;">Aucune commission trouvée</h1>',
                'commissions_vide.pdf'
            );
        }

        $totals = [
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'retail' => $commissions->where('type', 'retail')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
            'sponsor' => $commissions->where('type', 'sponsor')->sum('amount'),
            'total' => $commissions->sum('amount'),
        ];

        $stats = [
            'total_paid' => $commissions->where('status', 'paid')->sum('amount'),
            'total_pending' => $commissions->where('status', 'pending')->sum('amount'),
            'total_cancelled' => $commissions->where('status', 'cancelled')->sum('amount'),
            'total_count' => $commissions->count(),
        ];

        $periods = $commissions->groupBy('period')->map(function ($items, $period) {
            return [
                'period' => $period,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'items' => $items->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'user' => $item->user?->name ?? 'N/A',
                        'from_user' => $item->fromUser?->name ?? 'Système',
                        'amount' => $item->amount,
                        'percentage' => $item->percentage,
                        'status' => $item->status,
                        'description' => Str::limit((string) ($item->description ?? ''), 45),
                    ];
                }),
            ];
        });

        $data = [
            'commissions' => $commissions,
            'totals' => $totals,
            'stats' => $stats,
            'periods' => $periods,
            'logoBase64' => $this->loadPdfLogoBase64(),
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
                'status' => $request->status,
                'period' => $request->period,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'date_generated' => now()->format('d/m/Y H:i'),
        ];

        return $this->downloadPdfView(
            'admin.commissions.commission-pdf',
            $data,
            'rapport_commissions_'.date('Y-m-d').'.pdf',
            'a4',
            'portrait',
            300,
            '1024M'
        );
        } catch (\Throwable $e) {
            $this->logPdfFailure('admin.commissions.exportPDF', $e);

            return back()->with('error', 'Erreur lors de la génération du PDF. Réessayez avec plus de filtres.');
        }
    }

    /**
     * Exporter le rapport GLOBAL des commissions en PDF
     * (Tous les bénéficiaires avec filtres)
     */
    public function exportGlobalPDF(Request $request)
    {
        try {
            $this->preparePdfExport(300, '512M');

            $query = Commission::query();

            $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
            $query->whereNotIn('type', $excludedTypes);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('sponsor_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
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

            $commissions = $query
                ->select(['id', 'user_id', 'type', 'amount', 'period'])
                ->orderBy('user_id')
                ->orderBy('type')
                ->get();

            if ($commissions->isEmpty()) {
                return $this->downloadPdfHtml(
                    '<h1 style="text-align:center; font-family:DejaVu Sans; margin-top:50px;">Aucune commission trouvée</h1>',
                    'rapport_global_commissions.pdf',
                    'a4',
                    'landscape'
                );
            }

            $totals = [
                'direct' => (float) $commissions->where('type', 'direct')->sum('amount'),
                'indirect' => (float) $commissions->where('type', 'indirect')->sum('amount'),
                'leadership' => (float) $commissions->where('type', 'leadership')->sum('amount'),
                'cash_pos' => (float) $commissions->where('type', 'cash_pos')->sum('amount'),
                'sponsor' => (float) $commissions->where('type', 'sponsor')->sum('amount'),
                'retail' => (float) $commissions->where('type', 'retail')->sum('amount'),
                'total' => (float) $commissions->sum('amount'),
            ];

            $userIds = $commissions->pluck('user_id')->unique()->values()->all();
            $pvByUser = [];

            if ($request->filled('period')) {
                $pvByUser = PVHistory::whereIn('user_id', $userIds)
                    ->where('period', $request->period)
                    ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                    ->groupBy('user_id')
                    ->pluck('total_pv', 'user_id')
                    ->toArray();
            }

            $allUsers = User::whereIn('id', $userIds)
                ->select(['id', 'name', 'sponsor_id', 'parrain_id', 'rank_level', 'pv_balance'])
                ->get()
                ->keyBy('id');
            $parrainIds = $allUsers->pluck('parrain_id')->filter()->unique()->all();
            $parrains = User::whereIn('id', $parrainIds)
                ->select(['id', 'sponsor_id'])
                ->get()
                ->keyBy('id');

            $beneficiaires = $commissions->groupBy('user_id')->map(function ($items, $userId) use ($pvByUser, $allUsers, $parrains, $request) {
                $user = $allUsers->get($userId);
                $parrain = $user?->parrain_id ? $parrains->get($user->parrain_id) : null;

                $userPV = $request->filled('period')
                    ? ($pvByUser[$userId] ?? 0)
                    : ($user?->pv_balance ?? 0);

                return [
                    'name' => $user?->name ?? 'N/A',
                    'id' => $userId,
                    'code' => $user?->sponsor_id ?? $user?->id ?? 'N/A',
                    'parrain_code' => $parrain?->sponsor_id ?? 'N/A',
                    'rank_level' => $user?->rank_level ?? 'N/A',
                    'pv' => (float) $userPV,
                    'direct' => (float) $items->where('type', 'direct')->sum('amount'),
                    'indirect' => (float) $items->where('type', 'indirect')->sum('amount'),
                    'leadership' => (float) $items->where('type', 'leadership')->sum('amount'),
                    'sponsor' => (float) $items->where('type', 'sponsor')->sum('amount'),
                    'retail' => (float) $items->where('type', 'retail')->sum('amount'),
                    'cash_pos' => (float) $items->where('type', 'cash_pos')->sum('amount'),
                    'total' => (float) $items->sum('amount'),
                ];
            })->sortByDesc('total');

            $commissionsCount = $commissions->count();
            unset($commissions);

            $data = [
                'period' => $request->period ?? 'Toutes les périodes',
                'commissionsCount' => $commissionsCount,
                'totals' => $totals,
                'beneficiaires' => $beneficiaires,
                'filters' => [
                    'search' => $request->search,
                    'type' => $request->type,
                    'status' => $request->status,
                    'period' => $request->period,
                ],
                'logoBase64' => $this->loadPdfLogoBase64(),
                'date_generated' => now()->format('d/m/Y H:i'),
            ];

            return $this->downloadPdfView(
                'admin.commissions.commission-global-pdf',
                $data,
                'rapport_global_commissions_'.now()->format('Y-m-d').'.pdf',
                'a4',
                'landscape',
                300,
                '512M'
            );
        } catch (\Throwable $e) {
            $this->logPdfFailure('admin.commissions.exportGlobalPDF', $e);

            return back()->with('error', 'Erreur lors de la génération du PDF global. Réessayez avec plus de filtres.');
        }
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