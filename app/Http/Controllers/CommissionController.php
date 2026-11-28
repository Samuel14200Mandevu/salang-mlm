<?php
// app/Http/Controllers/CommissionController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use App\Models\CommissionPeriod;
use App\Models\Withdrawal;
use App\Services\MLM\MemberCommissionDetailedPdfService;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\UserCommissionDisplayService;
use App\Services\WalletCommissionBreakdown;
use App\Support\CareerQualificationPrizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class CommissionController extends Controller
{
    use GeneratesPdfSafely;

    protected MonthlyCommissionService $commissionService;

    public function __construct(
        MonthlyCommissionService $commissionService,
        protected UserCommissionDisplayService $commissionDisplay,
        protected WalletCommissionBreakdown $commissionBreakdown,
        protected MemberCommissionDetailedPdfService $memberCommissionPdf,
    ) {
        $this->commissionService = $commissionService;
    }

    /**
     * Afficher la liste des commissions
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $bonusMonths = array_slice(
            $this->commissionBreakdown->bonusMonthOptions(),
            -4,
            4,
            true
        );
        $defaultBonusMonth = array_key_last($bonusMonths) ?: '';
        $sessionBonusMonth = (string) $request->session()->get('wallet_bonus_month', '');
        $bonusMonth = (string) $request->query(
            'period',
            array_key_exists($sessionBonusMonth, $bonusMonths) ? $sessionBonusMonth : $defaultBonusMonth
        );
        if ($bonusMonth !== '' && ! array_key_exists($bonusMonth, $bonusMonths)) {
            $bonusMonth = $defaultBonusMonth;
        }
        if ($bonusMonth !== '') {
            $request->session()->put('wallet_bonus_month', $bonusMonth);
        }
        $bonusMonthLabel = $bonusMonths[$bonusMonth] ?? $bonusMonth;

        // Hub : Direct / Indirect / Leadership / POS (même payé cash)
        $allowedTypes = array_keys(CareerQualificationPrizes::walletBonusTypes());
        $selectedType = (string) $request->query('type', '');
        if ($selectedType !== '' && ! in_array($selectedType, $allowedTypes, true)) {
            $selectedType = '';
        }

        $bonusByType = $this->commissionBreakdown->paidByType($user->id, $bonusMonth ?: null);

        $query = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->with(['fromUser', 'package', 'product', 'period', 'order']);

        // Filtres
        if ($selectedType !== '') {
            $query->where('type', $selectedType);
        } else {
            $query->whereIn('type', $allowedTypes);
        }

        // Les pending ne sont jamais exposés aux membres (uniquement après envoi portefeuille).

        if ($bonusMonth !== '') {
            $query->where('period', $bonusMonth);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistiques
        $stats = $this->getUserStats($user->id);
        $user->loadMissing('wallet');
        $stats['wallet_balance'] = (float) ($user->wallet?->balance ?? 0);
        // « Payé » = déjà retiré (retraits complétés)
        $stats['paid'] = (float) Withdrawal::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Distribution par type
        $byType = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'sponsor' => 'Sponsor',
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                $colors = [
                    'sponsor' => 'primary',
                    'direct' => 'primary',
                    'indirect' => 'warning',
                    'leadership' => 'success',
                    'retail' => 'info',
                    'global' => 'purple',
                    'consumer' => 'teal',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => $colors[$item->type] ?? 'secondary',
                    ]
                ];
            });

        // Données mensuelles
        $monthly = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Périodes disponibles pour les filtres
        $periods = CommissionPeriod::visibleToMember()->orderBy('period', 'desc')->pluck('period');
        $types = collect($allowedTypes);

        return view('commissions.index', compact(
            'commissions',
            'stats',
            'byType',
            'monthly',
            'periods',
            'types',
            'bonusByType',
            'bonusMonths',
            'bonusMonth',
            'bonusMonthLabel',
            'selectedType',
        ));
    }

    /**
     * Tableau de bord des commissions (redirigé vers Mes commissions)
     */
    public function dashboard()
    {
        return redirect()->route('commissions.index');
    }

    /**
     * Exporter les commissions en PDF (même rapport détaillé que la caisse).
     * Sans ?pdf=1 → page d’aperçu (desktop + mobile) ; avec ?pdf=1 → fichier PDF.
     */
    public function exportPdf(Request $request)
    {
        if (! $request->boolean('pdf')) {
            $query = collect($request->except(['pdf', 'download']))->filter(fn ($v) => $v !== null && $v !== '')->all();

            return view('partials.pdf.preview-shell', [
                'title' => 'Rapport des Commissions',
                'backUrl' => route('commissions.index', $query),
                'pdfUrl' => $request->url().'?'.http_build_query(array_merge($query, ['pdf' => 1])),
                'downloadUrl' => $request->url().'?'.http_build_query(array_merge($query, ['pdf' => 1, 'download' => 1])),
            ]);
        }

        $user = Auth::user();
        $coreTypes = $this->memberCommissionPdf->coreTypes();
        $allowedTypes = array_keys(CareerQualificationPrizes::walletBonusTypes());
        $types = array_values(array_intersect($allowedTypes, $coreTypes));
        if ($types === []) {
            $types = $coreTypes;
        }

        $selectedType = (string) $request->query('type', '');
        if ($selectedType !== '' && ! in_array($selectedType, $types, true)) {
            $selectedType = '';
        }

        $period = $request->filled('period') ? (string) $request->period : null;
        if ($period === 'all') {
            $period = null;
        }

        $query = Commission::query()
            ->where('user_id', $user->id)
            ->visibleToMember()
            ->with(['fromUser:id,name,sponsor_id'])
            ->whereIn('type', $selectedType !== '' ? [$selectedType] : $types);

        if ($period) {
            $query->where('period', $period);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query
            ->orderByRaw("CASE type WHEN 'direct' THEN 1 WHEN 'indirect' THEN 2 WHEN 'leadership' THEN 3 WHEN 'cash_pos' THEN 4 ELSE 5 END")
            ->orderBy('id')
            ->get();

        if ($commissions->isEmpty()) {
            return back()->with('error', 'Aucune commission à exporter.');
        }

        try {
            $built = $this->memberCommissionPdf->build($user, $commissions, $period);
            $args = [
                'admin.pv.commission-pdf',
                $built['data'],
                $built['filename'],
                'a4',
                'portrait',
                300,
                '1024M',
            ];

            // Aperçu iframe (desktop + mobile) = inline ; bouton Télécharger = attachment
            return $request->boolean('download')
                ? $this->downloadPdfView(...$args)
                : $this->streamPdfView(...$args);
        } catch (\Throwable $e) {
            $this->logPdfFailure('member.commissions.exportPDF', $e);

            return back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }

    /**
     * Ancienne page « Commissions par niveau » — redirigée vers Mes commissions.
     */
    public function getLevelCommissions()
    {
        return redirect()->route('commissions.index');
    }

    /**
     * Afficher les détails d'une commission
     */
    public function show($id)
    {
        $user = Auth::user();

        $commission = Commission::with(['user', 'fromUser', 'package', 'product', 'period', 'order'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->visibleToMember()
            ->firstOrFail();

        $similar = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->where('type', $commission->type)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('commissions.show', compact('commission', 'similar'));
    }

    /**
     * Récupérer les détails d'une commission en JSON (pour le modal)
     */
    public function getJsonDetails($id)
    {
        $user = Auth::user();
        
        $commission = Commission::with(['fromUser', 'package', 'product', 'period', 'order'])
            ->where('user_id', $user->id)
            ->visibleToMember()
            ->findOrFail($id);
        
        // PV / prix : bonus direct = PV perso de la période (pv_used) × taux grade
        $pv = 0;
        $price = 0;
        $itemName = 'N/A';
        $itemType = 'unknown';
        $quantity = 1;

        if ($commission->type === 'direct' && (float) ($commission->pv_used ?? 0) > 0) {
            $pv = (float) $commission->pv_used;
            $itemType = 'personal_pv';
            $itemName = 'PV personnels du mois';
        } elseif ($commission->package) {
            $pv = $commission->package->pv_value ?? 0;
            $price = $commission->package->price ?? 0;
            $itemName = $commission->package->name ?? 'N/A';
            $itemType = 'package';
            
            if ($commission->order) {
                $orderItem = $commission->order->items()
                    ->where('package_id', $commission->package_id)
                    ->first();
                if ($orderItem) {
                    $quantity = $orderItem->quantity ?? 1;
                }
            }
        } elseif ($commission->product) {
            $pv = $commission->product->pv_value ?? 0;
            $price = $commission->product->price ?? 0;
            $itemName = $commission->product->name ?? 'N/A';
            $itemType = 'product';
            
            if ($commission->order) {
                $orderItem = $commission->order->items()
                    ->where('product_id', $commission->product_id)
                    ->first();
                if ($orderItem) {
                    $quantity = $orderItem->quantity ?? 1;
                }
            }
        } elseif ((float) ($commission->pv_used ?? 0) > 0) {
            $pv = (float) $commission->pv_used;
        }
        
        // Si le prix n'est pas trouvé, essayer de l'extraire de la description
        if ($price == 0 && $commission->description) {
            preg_match('/achat de ([^\(]+)\(?(\d+)?\)?/', $commission->description, $matches);
            if (isset($matches[2])) {
                $price = (float) $matches[2];
            }
        }
        
        // Si toujours pas de prix, estimer à partir du montant pour sponsor
        if ($price == 0 && $commission->type === 'sponsor' && $commission->percentage > 0) {
            $price = ($commission->amount / $commission->percentage) * 100;
        }
        
        $isPersonalDirect = $commission->type === 'direct'
            && (
                (int) ($commission->from_user_id ?? 0) === (int) $user->id
                || $itemType === 'personal_pv'
            );

        return response()->json([
            'id' => $commission->id,
            'type' => $commission->type,
            'amount' => number_format($commission->amount, 2),
            'percentage' => $commission->percentage ?? 0,
            'from_user' => $isPersonalDirect
                ? 'Achats personnels'
                : ($commission->fromUser?->name ?? 'Systeme'),
            'from_user_is_self' => $isPersonalDirect,
            'package_id' => $commission->package_id,
            'product_id' => $commission->product_id,
            'package_name' => $commission->package?->name ?? null,
            'product_name' => $commission->product?->name ?? null,
            'item_name' => $itemName,
            'item_type' => $itemType,
            'pv' => $pv,
            'pv_used' => (float) ($commission->pv_used ?? 0),
            'price' => $price,
            'quantity' => $quantity,
            'period' => $commission->period ?? 'N/A',
            'description' => $commission->description ?? '',
            'status' => $commission->status,
            'created_at' => $commission->created_at->format('d/m/Y H:i'),
            'formula' => $this->getFormulaForCommission($commission, $pv, $price),
        ]);
    }

    /**
     * Obtenir la formule de calcul pour une commission
     */
    private function getFormulaForCommission($commission, $pv, $price): string
    {
        switch ($commission->type) {
            case 'sponsor':
                if ($price > 0) {
                    return 'Prix de l\'item (' . $price . '$) × 30%';
                }
                return 'Montant fixe : ' . $commission->amount . '$';
            case 'direct':
                if ($pv > 0 && $commission->percentage) {
                    return 'PV personnels du mois ('.$pv.' PV) × taux MLM du grade ('.($commission->percentage ?? 'N/A').'%)';
                }
                return 'PV personnels × taux MLM du grade = '.$commission->amount.'$';
            case 'indirect':
                if ($pv > 0 && $commission->percentage) {
                    return 'PV (' . $pv . ' PV) × Différence (' . ($commission->percentage ?? 'N/A') . '%)';
                }
                return 'Montant : ' . $commission->amount . '$';
            case 'leadership':
                if ($pv > 0 && $commission->percentage) {
                    return 'PV (' . $pv . ' PV) × Leadership (' . ($commission->percentage ?? 'N/A') . '%)';
                }
                return 'Montant : ' . $commission->amount . '$';
            case 'retail':
                return 'Vente × 25%';
            default:
                return 'Montant : ' . $commission->amount . '$';
        }
    }

    /**
     * Statistiques des commissions (API)
     */
    public function stats()
    {
        $user = Auth::user();
        $stats = $this->getUserStats($user->id);

        $byType = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'sponsor' => 'Sponsor',
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                $colors = [
                    'sponsor' => 'primary',
                    'direct' => 'primary',
                    'indirect' => 'warning',
                    'leadership' => 'success',
                    'retail' => 'info',
                    'global' => 'purple',
                    'consumer' => 'teal',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => $colors[$item->type] ?? 'secondary',
                    ]
                ];
            });

        $monthly = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $recent = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->with(['fromUser', 'package', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topSponsors = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('from_user_id', DB::raw('SUM(amount) as total'))
            ->with('fromUser')
            ->groupBy('from_user_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->fromUser?->name ?? 'Unknown',
                    'total' => (float) $item->total,
                ];
            });

        return view('commissions.stats', compact(
            'stats',
            'byType',
            'monthly',
            'recent',
            'topSponsors'
        ));
    }

    /**
     * Exporter les commissions en CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->with(['fromUser', 'package', 'product', 'period'])
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('period'), function ($query) use ($request) {
                return $query->where('period', $request->period);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'commissions_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Type', 'Amount', 'Rate (%)', 'Status',
                'Period', 'Description', 'From', 'Item', 'PV', 'Price', 'Date'
            ]);

            foreach ($commissions as $commission) {
                $itemName = $commission->package?->name ?? $commission->product?->name ?? 'N/A';
                $itemPV = $commission->package?->pv_value ?? $commission->product?->pv_value ?? 0;
                $itemPrice = $commission->package?->price ?? $commission->product?->price ?? 0;

                fputcsv($file, [
                    $commission->id,
                    ucfirst($commission->type),
                    number_format($commission->amount, 2),
                    $commission->percentage,
                    ucfirst($commission->status),
                    $commission->period ?? 'N/A',
                    $commission->description ?? '',
                    $commission->fromUser?->name ?? 'N/A',
                    $itemName,
                    $itemPV,
                    $itemPrice,
                    $commission->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API - Liste des commissions
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'product', 'period'])
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            }, function ($query) {
                return $query->where('status', '!=', 'cancelled');
            })
            ->when($request->filled('period'), function ($query) use ($request) {
                return $query->where('period', $request->period);
            })
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 50))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commissions,
            'total' => Commission::where('user_id', $user->id)->count(),
        ]);
    }

    /**
     * API - Statistiques des commissions
     */
    public function apiStats()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'stats' => $this->getUserStats($user->id),
        ]);
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    private function getUserStats($userId)
    {
        $bands = $this->commissionDisplay->summarize($userId);

        $stats = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as total_count')
            )
            ->first();

        $byType = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'sponsor' => 'Sponsor',
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                $colors = [
                    'sponsor' => 'primary',
                    'direct' => 'primary',
                    'indirect' => 'warning',
                    'leadership' => 'success',
                    'retail' => 'info',
                    'global' => 'purple',
                    'consumer' => 'teal',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => $colors[$item->type] ?? 'secondary',
                    ]
                ];
            });

        $monthly = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        $recent = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->with(['fromUser', 'package', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => (float) ($stats->total ?? 0),
            'pending' => (float) $bands['pending_payable'],
            'paid' => (float) $bands['paid_total_visible'],
            'total_count' => (int) ($stats->total_count ?? 0),
            'pending_count' => (int) $bands['pending_payable_count'],
            'paid_count' => (int) $bands['paid_visible_count'],
            'paid_offline_historical' => (float) $bands['paid_offline_historical'],
            'paid_system' => (float) $bands['paid_system'],
            'bands' => [
                'historical_paid' => (float) $bands['paid_offline_historical'],
                'system_paid' => (float) $bands['paid_system'],
                'payable_pending' => (float) $bands['pending_payable'],
            ],
            'by_type' => $byType,
            'monthly' => $monthly,
            'recent' => $recent,
        ];
    }

    /**
     * Obtenir le taux de commission d'un utilisateur
     */
    private function getUserCommissionRate($user, $level)
    {
        $rates = [
            1 => 30,
            2 => 30,
            3 => 15,
            4 => 10,
            5 => 25,
            6 => 5,
        ];

        $rankLevel = $user->rank_level ?? 1;

        if ($rankLevel >= 5 && $level == 1) {
            $rates[1] = 30 + ($rankLevel - 5) * 2;
        }

        return min($rates[$level] ?? 0, 45);
    }
}