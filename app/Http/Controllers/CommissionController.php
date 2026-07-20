<?php
// app/Http/Controllers/CommissionController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class CommissionController extends Controller
{
    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Afficher la liste des commissions
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'product', 'period', 'order']);

        // Filtres
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

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = $this->getUserStats($user->id);

        // Distribution par type
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

        // Données mensuelles
        $monthly = Commission::where('user_id', $user->id)
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
        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');
        $types = Commission::where('user_id', $user->id)->distinct()->pluck('type');

        return view('commissions.index', compact(
            'commissions',
            'stats',
            'byType',
            'monthly',
            'periods',
            'types'
        ));
    }

    /**
     * Tableau de bord des commissions
     */
    public function dashboard()
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

        return view('commissions.dashboard', compact(
            'stats',
            'byType',
            'monthly',
            'recent',
            'topSponsors'
        ));
    }

        /**
     * Exporter les commissions en PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'product', 'period'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
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

        // Statistiques pour le PDF
        $stats = [
            'total' => $commissions->sum('amount'),
            'count' => $commissions->count(),
            'pending' => $commissions->where('status', 'pending')->sum('amount'),
            'paid' => $commissions->where('status', 'paid')->sum('amount'),
        ];

        // Regrouper par type
        $byType = $commissions->groupBy('type')->map(function($group) {
            return [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'label' => ucfirst($group->first()->type ?? 'N/A'),
            ];
        });

        $data = [
            'user' => $user,
            'commissions' => $commissions,
            'stats' => $stats,
            'byType' => $byType,
            'generated_at' => now(),
            'filters' => [
                'status' => $request->status ?? 'Tous',
                'type' => $request->type ?? 'Tous',
                'period' => $request->period ?? 'Tous',
                'date_from' => $request->date_from ?? 'Début',
                'date_to' => $request->date_to ?? 'Fin',
            ],
        ];

        $pdf = PDF::loadView('commissions.export-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('rapport_commissions_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Afficher les commissions par niveau (getLevelCommissions)
     */
    public function getLevelCommissions()
    {
        $user = Auth::user();

        $levels = [];
        $total = 0;

        // Niveau 1 - Sponsor Bonus
        $level1 = Commission::where('user_id', $user->id)
            ->where('type', 'sponsor')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[1] = [
            'label' => 'Sponsor Bonus',
            'description' => 'Commission de parrainage directe',
            'amount' => (float) $level1,
            'percentage' => 30,
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'sponsor')
                ->where('status', 'paid')
                ->count(),
            'color' => 'primary',
            'icon' => 'S',
        ];
        $total += $level1;

        // Niveau 2 - Commission Directe
        $level2 = Commission::where('user_id', $user->id)
            ->where('type', 'direct')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[2] = [
            'label' => 'Commission Directe',
            'description' => 'Commission sur les achats des filleuls directs',
            'amount' => (float) $level2,
            'percentage' => $this->getUserCommissionRate($user, 2),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'direct')
                ->where('status', 'paid')
                ->count(),
            'color' => 'primary',
            'icon' => 'D',
        ];
        $total += $level2;

        // Niveau 3 - Commission Indirecte
        $level3 = Commission::where('user_id', $user->id)
            ->where('type', 'indirect')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[3] = [
            'label' => 'Commission Indirecte',
            'description' => 'Commission sur les niveaux inferieurs',
            'amount' => (float) $level3,
            'percentage' => $this->getUserCommissionRate($user, 3),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'indirect')
                ->where('status', 'paid')
                ->count(),
            'color' => 'warning',
            'icon' => 'I',
        ];
        $total += $level3;

        // Niveau 4 - Leadership Bonus
        $level4 = Commission::where('user_id', $user->id)
            ->where('type', 'leadership')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[4] = [
            'label' => 'Leadership Bonus',
            'description' => 'Bonus de leadership sur le reseau',
            'amount' => (float) $level4,
            'percentage' => $this->getUserCommissionRate($user, 4),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'leadership')
                ->where('status', 'paid')
                ->count(),
            'color' => 'success',
            'icon' => 'L',
        ];
        $total += $level4;

        // Niveau 5 - Retail Bonus
        $level5 = Commission::where('user_id', $user->id)
            ->where('type', 'retail')
            ->where('status', 'paid')
            ->sum('amount');
        if ($level5 > 0) {
            $levels[5] = [
                'label' => 'Retail Bonus',
                'description' => 'Bonus sur les ventes au detail',
                'amount' => (float) $level5,
                'percentage' => 25,
                'count' => Commission::where('user_id', $user->id)
                    ->where('type', 'retail')
                    ->where('status', 'paid')
                    ->count(),
                'color' => 'info',
                'icon' => 'R',
            ];
            $total += $level5;
        }

        // Niveau 6 - Global Bonus (si existe)
        $level6 = Commission::where('user_id', $user->id)
            ->where('type', 'global')
            ->where('status', 'paid')
            ->sum('amount');
        if ($level6 > 0) {
            $levels[6] = [
                'label' => 'Global Bonus',
                'description' => 'Bonus global sur le volume total',
                'amount' => (float) $level6,
                'percentage' => 5,
                'count' => Commission::where('user_id', $user->id)
                    ->where('type', 'global')
                    ->where('status', 'paid')
                    ->count(),
                'color' => 'purple',
                'icon' => 'G',
            ];
            $total += $level6;
        }

        // Ajouter le total global
        $levels['total'] = $total;

        // Récupérer les dernières commissions par niveau
        $recentByLevel = [];
        foreach (array_keys($levels) as $level) {
            if (is_numeric($level)) {
                $typeMap = [
                    1 => 'sponsor',
                    2 => 'direct',
                    3 => 'indirect',
                    4 => 'leadership',
                    5 => 'retail',
                    6 => 'global',
                ];
                if (isset($typeMap[$level])) {
                    $recentByLevel[$level] = Commission::where('user_id', $user->id)
                        ->where('type', $typeMap[$level])
                        ->where('status', 'paid')
                        ->with(['fromUser', 'package', 'product'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                }
            }
        }

        return view('commissions.levels', compact('levels', 'recentByLevel'));
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
            ->firstOrFail();

        $similar = Commission::where('user_id', $user->id)
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
            ->findOrFail($id);
        
        // Récupérer le PV et le prix depuis le package ou le produit
        $pv = 0;
        $price = 0;
        $itemName = 'N/A';
        $itemType = 'unknown';
        $quantity = 1;
        
        if ($commission->package) {
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
        
        return response()->json([
            'id' => $commission->id,
            'type' => $commission->type,
            'amount' => number_format($commission->amount, 2),
            'percentage' => $commission->percentage ?? 0,
            'from_user' => $commission->fromUser?->name ?? 'Systeme',
            'package_id' => $commission->package_id,
            'product_id' => $commission->product_id,
            'package_name' => $commission->package?->name ?? null,
            'product_name' => $commission->product?->name ?? null,
            'item_name' => $itemName,
            'item_type' => $itemType,
            'pv' => $pv,
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
                    return 'PV (' . $pv . ' PV) × Taux (' . ($commission->percentage ?? 'N/A') . '%)';
                }
                return 'Montant : ' . $commission->amount . '$';
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
            ->with(['fromUser', 'package', 'product', 'period'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
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
        $stats = Commission::where('user_id', $userId)
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count')
            )
            ->first();

        $byType = Commission::where('user_id', $userId)
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
            ->where('status', 'paid')
            ->with(['fromUser', 'package', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => (float) ($stats->total ?? 0),
            'pending' => (float) ($stats->pending ?? 0),
            'paid' => (float) ($stats->paid ?? 0),
            'total_count' => (int) ($stats->total_count ?? 0),
            'pending_count' => (int) ($stats->pending_count ?? 0),
            'paid_count' => (int) ($stats->paid_count ?? 0),
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