<?php
// app/Http/Controllers/CommissionController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    /**
     * Afficher la liste des commissions de l'utilisateur connecté - DONNÉES RÉELLES
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ✅ Récupérer les commissions avec toutes les relations
        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'order']);
        
        // ✅ Filtres
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
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // ✅ Statistiques depuis la base de données
        $stats = $this->getUserStats($user->id);
        
        // ✅ Commissions par type
        $byType = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $colors = [
                    'direct' => 'primary',
                    'indirect' => 'warning',
                    'leadership' => 'danger',
                    'retail' => 'success',
                ];
                $labels = [
                    'direct' => 'Directes',
                    'indirect' => 'Indirectes',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                ];
                return [
                    $item->type => [
                        'total' => $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => $colors[$item->type] ?? 'secondary',
                    ]
                ];
            });
        
        // ✅ Commissions mensuelles
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
        
        return view('commissions.index', compact(
            'commissions',
            'stats',
            'byType',
            'monthly'
        ));
    }
    
    /**
     * Statistiques détaillées - DONNÉES RÉELLES
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = $this->getUserStats($user->id);
        
        // Commissions par type
        $byType = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'direct' => 'Directes',
                    'indirect' => 'Indirectes',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                ];
                return [
                    $item->type => [
                        'total' => $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => [
                            'direct' => 'primary',
                            'indirect' => 'warning',
                            'leadership' => 'danger',
                            'retail' => 'success',
                        ][$item->type] ?? 'secondary',
                    ]
                ];
            });
        
        // Commissions mensuelles
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
        
        // Dernières commissions
        $recent = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('commissions.stats', compact(
            'stats',
            'byType',
            'monthly',
            'recent'
        ));
    }
    
    /**
     * Afficher le détail d'une commission - DONNÉES RÉELLES
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // ✅ Récupérer la commission avec toutes les relations
        $commission = Commission::with(['user', 'fromUser', 'package', 'order'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        return view('commissions.show', compact('commission'));
    }
    
    /**
     * Commissions par niveau Unilevel - DONNÉES RÉELLES
     */
    public function getLevelCommissions()
    {
        $user = Auth::user();
        
        // ✅ Récupérer les commissions par niveau
        $levels = [];
        $total = 0;
        
        // Niveau 1 - Direct (30%)
        $level1 = Commission::where('user_id', $user->id)
            ->where('type', 'direct')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[1] = [
            'label' => 'Direct',
            'amount' => $level1,
            'percentage' => 30,
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'direct')
                ->where('status', 'paid')
                ->count(),
        ];
        $total += $level1;
        
        // Niveau 2 - Indirect (15%)
        $level2 = Commission::where('user_id', $user->id)
            ->where('type', 'indirect')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[2] = [
            'label' => 'Indirect',
            'amount' => $level2,
            'percentage' => 15,
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'indirect')
                ->where('status', 'paid')
                ->count(),
        ];
        $total += $level2;
        
        // Niveau 3-5 - Leadership (10%)
        $level3 = Commission::where('user_id', $user->id)
            ->where('type', 'leadership')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[3] = [
            'label' => 'Leadership',
            'amount' => $level3,
            'percentage' => 10,
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'leadership')
                ->where('status', 'paid')
                ->count(),
        ];
        $total += $level3;
        
        return view('commissions.levels', compact('levels', 'total'));
    }
    
    /**
     * Exporter les commissions en CSV - DONNÉES RÉELLES
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
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
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
        
        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            
            // En-têtes UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Type', 'Montant', 'Taux (%)', 'Statut',
                'Description', 'Parrain', 'Package', 'Date'
            ]);
            
            foreach ($commissions as $commission) {
                fputcsv($file, [
                    $commission->id,
                    ucfirst($commission->type),
                    number_format($commission->amount, 2),
                    $commission->percentage,
                    ucfirst($commission->status),
                    $commission->description ?? '',
                    $commission->fromUser?->name ?? 'N/A',
                    $commission->package?->name ?? 'N/A',
                    $commission->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Générer un PDF des commissions (utilise DomPDF)
     */
    public function pdf(Request $request)
    {
        $user = Auth::user();
        
        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = $this->getUserStats($user->id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('commissions.pdf', compact(
            'commissions',
            'stats',
            'user'
        ));
        
        return $pdf->download('commissions_' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * API - Récupérer les commissions pour AJAX
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package'])
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
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
     * API - Statistiques pour AJAX
     */
    public function apiStats(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'stats' => $this->getUserStats($user->id),
        ]);
    }
    
    /**
     * Récupérer les statistiques d'un utilisateur - DONNÉES RÉELLES
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
        
        // Statistiques par type
        $byType = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'direct' => 'Directes',
                    'indirect' => 'Indirectes',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                ];
                return [
                    $item->type => [
                        'total' => $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => [
                            'direct' => 'primary',
                            'indirect' => 'warning',
                            'leadership' => 'danger',
                            'retail' => 'success',
                        ][$item->type] ?? 'secondary',
                    ]
                ];
            });
        
        // Commissions mensuelles
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
        
        // Dernières commissions
        $recent = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return [
            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'paid' => $stats->paid ?? 0,
            'total_count' => $stats->total_count ?? 0,
            'pending_count' => $stats->pending_count ?? 0,
            'paid_count' => $stats->paid_count ?? 0,
            'by_type' => $byType,
            'monthly' => $monthly,
            'recent' => $recent,
        ];
    }
}