<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Package;
use App\Models\Withdrawal;
use App\Models\Product;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\TcpdfFpdi;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $period = $request->period ?? 'month';

            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_commissions' => Commission::where('status', 'paid')->sum('amount') ?? 0,
                'pending_commissions' => Commission::where('status', 'pending')->sum('amount') ?? 0,
                'total_sales' => Order::where('status', 'completed')->sum('total') ?? 0,
                'total_withdrawn' => Withdrawal::where('status', 'completed')->sum('amount') ?? 0,
                'total_packages_sold' => Order::whereHas('items', function($q) {
                    $q->whereNotNull('package_id');
                })->count() ?? 0,
                'total_products' => Product::count() ?? 0,
                'total_orders' => Order::count() ?? 0,
                'avg_order_value' => Order::where('status', 'completed')->avg('total') ?? 0,
            ];

            $monthlySales = $this->getMonthlyData();

            $commissionByType = Commission::where('status', 'paid')
                ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get();

            $usersByRank = User::select('rank_id', DB::raw('count(*) as count'))
                ->whereNotNull('rank_id')
                ->with('rank')
                ->groupBy('rank_id')
                ->get()
                ->map(function($item) {
                    return (object) [
                        'rank' => $item->rank ? $item->rank->name : 'Non défini',
                        'count' => $item->count,
                    ];
                });

            $topSponsors = User::orderBy('total_sponsors', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_sponsors', 'total_earnings']);

            $topEarners = User::orderBy('total_earnings', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_earnings', 'total_sponsors']);

            $packageRevenue = Package::withCount('users')
                ->get()
                ->map(function($package) {
                    return (object) [
                        'name' => $package->name,
                        'users_count' => $package->users_count ?? 0,
                        'price' => $package->price ?? 0,
                        'total_revenue' => ($package->price ?? 0) * ($package->users_count ?? 0),
                    ];
                });

            $recentActivity = $this->getRecentActivity();

            return view('admin.reports.index', compact(
                'stats',
                'monthlySales',
                'commissionByType',
                'usersByRank',
                'topSponsors',
                'topEarners',
                'packageRevenue',
                'recentActivity',
                'period'
            ));

        } catch (\Exception $e) {
            Log::error('Reports error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return view('admin.reports.index', [
                'error' => 'Erreur: ' . $e->getMessage(),
                'stats' => [],
                'monthlySales' => [],
                'commissionByType' => collect(),
                'usersByRank' => collect(),
                'topSponsors' => collect(),
                'topEarners' => collect(),
                'packageRevenue' => collect(),
                'recentActivity' => [],
                'period' => 'month'
            ]);
        }
    }

    public function sales(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }

        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'avg_order_value' => $query->avg('total') ?? 0,
            'total_tax' => $query->sum('tax'),
            'total_shipping' => $query->sum('shipping'),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'completed', 'failed'];

        return view('admin.reports.sales', compact('orders', 'stats', 'statuses', 'paymentStatuses'));
    }

    public function commissions(Request $request)
    {
        $query = Commission::with(['user', 'fromUser']);

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

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => $query->sum('amount'),
            'average' => $query->avg('amount') ?? 0,
            'count' => $query->count(),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get(),
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
        ];

        $types = Commission::distinct()->pluck('type');
        $statuses = ['pending', 'paid', 'cancelled'];
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.reports.commissions', compact('commissions', 'stats', 'types', 'statuses', 'users'));
    }

    public function users(Request $request)
    {
        $query = User::with(['rank', 'package', 'wallet']);

        if ($request->filled('rank_id')) {
            $query->where('rank_id', $request->rank_id);
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_pv')) {
            $query->where('pv_balance', '>=', $request->min_pv);
        }

        if ($request->filled('max_pv')) {
            $query->where('pv_balance', '<=', $request->max_pv);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'avg_pv' => User::avg('pv_balance') ?? 0,
            'avg_bv' => User::avg('bv_balance') ?? 0,
            'total_pv' => User::sum('pv_balance') ?? 0,
            'total_bv' => User::sum('bv_balance') ?? 0,
            'total_earnings' => User::sum('total_earnings') ?? 0,
            'with_package' => User::whereNotNull('package_id')->count(),
            'without_package' => User::whereNull('package_id')->count(),
            'kyc_verified' => User::where('kyc_status', 'verified')->count(),
            'kyc_pending' => User::where('kyc_status', 'pending')->count(),
        ];

        $ranks = Rank::orderBy('min_pv', 'asc')->get();
        $packages = Package::where('is_active', true)->get();
        $kycStatuses = ['not_submitted', 'pending', 'partial', 'verified', 'rejected'];

        return view('admin.reports.users', compact(
            'users',
            'stats',
            'ranks',
            'packages',
            'kycStatuses'
        ));
    }

    /**
     * ✅ Exporter un rapport en PDF - VERSION OPTIMISÉE AVEC TCPDF
     */
    public function exportPdf(Request $request, $type)
    {
        try {
            // Configurations pour éviter les timeouts
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '512M');
            
            switch ($type) {
                case 'users':
                    return $this->exportUsersPdf($request);
                    
                case 'commissions':
                    return $this->exportCommissionsPdf($request);
                    
                case 'sales':
                    return $this->exportSalesPdf($request);
                    
                case 'withdrawals':
                    return $this->exportWithdrawalsPdf($request);
                    
                default:
                    return redirect()->back()->with('error', 'Type de rapport invalide.');
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF: ' . $e->getMessage(), [
                'type' => $type,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Export des utilisateurs PDF avec CHUNK
     */
    private function exportUsersPdf($request)
    {
        // Récupérer le nombre total d'utilisateurs
        $query = $this->buildUserQuery($request);
        $totalUsers = $query->count();
        
        if ($totalUsers === 0) {
            return redirect()->back()->with('error', 'Aucun utilisateur trouvé.');
        }
        
        // Si moins de 500 utilisateurs, générer un seul PDF
        if ($totalUsers <= 500) {
            return $this->generateSingleUsersPdf($request, $query);
        }
        
        // Sinon, générer par lots
        return $this->generateChunkedUsersPdf($request, $query, $totalUsers);
    }

    /**
     * ✅ Construire la requête utilisateur
     */
    private function buildUserQuery($request)
    {
        $query = User::with(['rank', 'parrain'])
            ->select([
                'id',
                'name',
                'email',
                'sponsor_id',
                'parrain_id',
                'rank_id',
                'rank',
                'rank_level',
                'pv_balance',
                'team_pv',
                'is_active',
                'kyc_status',
                'created_at',
                'user_type',
                'package_id'
            ]);

        // Appliquer les filtres
        if ($request->filled('rank_id')) {
            $query->where('rank_id', $request->rank_id);
        }
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }
        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        if ($request->filled('min_pv')) {
            $query->where('pv_balance', '>=', $request->min_pv);
        }
        if ($request->filled('max_pv')) {
            $query->where('pv_balance', '<=', $request->max_pv);
        }

        return $query;
    }

    /**
     * ✅ Générer un seul PDF (pour < 500 utilisateurs)
     */
    private function generateSingleUsersPdf($request, $query)
    {
        $users = $query->orderBy('id', 'asc')->get();
        
        $stats = [
            'total' => $users->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
            'members' => $users->where('user_type', 'member')->count(),
            'clients' => $users->where('user_type', 'client')->count(),
            'totalPv' => $users->sum('pv_balance') ?? 0,
            'totalTeamPv' => $users->sum('team_pv') ?? 0,
            'withPackage' => $users->whereNotNull('package_id')->count(),
            'withoutPackage' => $users->whereNull('package_id')->count(),
        ];

        $data = compact('users', 'stats');
        
        $pdf = Pdf::loadView('admin.reports.pdf.users', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);
        
        return $pdf->download('rapport_utilisateurs_' . date('Y-m-d') . '.pdf');
    }

    /**
     * ✅ Générer un PDF par lots (pour > 500 utilisateurs)
     */
    private function generateChunkedUsersPdf($request, $query, $totalUsers)
    {
        $chunkSize = 250; // 250 utilisateurs par lot
        
        // Statistiques globales (une seule requête)
        $allUsers = $query->get();
        $stats = [
            'total' => $allUsers->count(),
            'active' => $allUsers->where('is_active', true)->count(),
            'inactive' => $allUsers->where('is_active', false)->count(),
            'members' => $allUsers->where('user_type', 'member')->count(),
            'clients' => $allUsers->where('user_type', 'client')->count(),
            'totalPv' => $allUsers->sum('pv_balance') ?? 0,
            'totalTeamPv' => $allUsers->sum('team_pv') ?? 0,
            'withPackage' => $allUsers->whereNotNull('package_id')->count(),
            'withoutPackage' => $allUsers->whereNull('package_id')->count(),
        ];
        
        // Libérer la mémoire
        unset($allUsers);
        
        // Créer le dossier temporaire s'il n'existe pas
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Générer des PDF temporaires par lots
        $tempFiles = [];
        $page = 1;
        $totalPages = ceil($totalUsers / $chunkSize);
        
        $query->orderBy('id', 'asc')->chunk($chunkSize, function($chunk) use (&$tempFiles, &$page, $stats, $totalPages, $totalUsers, $chunkSize, $tempDir) {
            $users = $chunk;
            
            // Ajouter des informations de pagination
            $pagination = [
                'page' => $page,
                'total_pages' => $totalPages,
                'total_users' => $totalUsers,
                'start' => (($page - 1) * $chunkSize) + 1,
                'end' => min($page * $chunkSize, $totalUsers)
            ];
            
            $data = compact('users', 'stats', 'pagination');
            
            // Générer le PDF temporaire
            $pdf = Pdf::loadView('admin.reports.pdf.users_chunk', $data);
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);
            
            // Sauvegarder temporairement
            $tempFile = $tempDir . "/users_page_{$page}.pdf";
            file_put_contents($tempFile, $pdf->output());
            $tempFiles[] = $tempFile;
            
            $page++;
            
            // Libérer la mémoire
            unset($users);
            unset($pdf);
            gc_collect_cycles();
        });
        
        // Si un seul fichier, le retourner directement
        if (count($tempFiles) === 1) {
            $content = file_get_contents($tempFiles[0]);
            unlink($tempFiles[0]);
            return response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="rapport_utilisateurs_' . date('Y-m-d') . '.pdf"',
            ]);
        }
        
        // ✅ Fusionner tous les PDF avec TCPDF
        try {
            $finalPdf = $this->mergePdfFilesWithTcpdf($tempFiles);
            
            // Nettoyer les fichiers temporaires
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            return response($finalPdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="rapport_utilisateurs_complet_' . date('Y-m-d') . '.pdf"',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur fusion PDF: ' . $e->getMessage());
            
            // ✅ En cas d'échec, proposer un ZIP
            return $this->downloadAsZip($tempFiles, 'rapport_utilisateurs_' . date('Y-m-d'));
        }
    }

    /**
     * ✅ Fusionner plusieurs PDF avec TCPDF
     */
    private function mergePdfFilesWithTcpdf($files)
    {
        $pdf = new TcpdfFpdi();
        
        // Configuration du PDF
        $pdf->SetCreator('Salang Group');
        $pdf->SetAuthor('Salang Group');
        $pdf->SetTitle('Rapport des utilisateurs');
        $pdf->SetSubject('Liste des utilisateurs');
        $pdf->SetKeywords('Salang, MLM, Ecommerce, Utilisateurs');
        
        // Supprimer les marges par défaut
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            
            $pageCount = $pdf->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage('L', 'A4'); // 'L' pour paysage
                $tpl = $pdf->importPage($i);
                $pdf->useTemplate($tpl);
            }
        }
        
        return $pdf->Output('S');
    }

    /**
     * ✅ Télécharger plusieurs PDF en ZIP
     */
    private function downloadAsZip($files, $name)
    {
        $zip = new \ZipArchive();
        $zipFileName = storage_path("app/temp/{$name}.zip");
        
        if ($zip->open($zipFileName, \ZipArchive::CREATE) !== true) {
            throw new \Exception('Impossible de créer le ZIP');
        }
        
        foreach ($files as $index => $file) {
            if (file_exists($file)) {
                $zip->addFile($file, "page_" . ($index + 1) . ".pdf");
            }
        }
        $zip->close();
        
        // Nettoyer les fichiers temporaires
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        $content = file_get_contents($zipFileName);
        unlink($zipFileName);
        
        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $name . '.zip"',
        ]);
    }

    /**
     * ✅ Export des commissions PDF
     */
    private function exportCommissionsPdf($request)
    {
        $query = Commission::with(['user', 'fromUser']);
        
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

        $commissions = $query->orderBy('created_at', 'desc')->limit(2000)->get();
        
        $stats = [
            'total' => $commissions->sum('amount') ?? 0,
            'count' => $commissions->count(),
            'avg' => $commissions->avg('amount') ?? 0,
            'totalPending' => $commissions->where('status', 'pending')->sum('amount') ?? 0,
            'totalPaid' => $commissions->where('status', 'paid')->sum('amount') ?? 0,
        ];

        $data = compact('commissions', 'stats');
        
        $pdf = Pdf::loadView('admin.reports.pdf.commissions', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);
        
        return $pdf->download('rapport_commissions_' . date('Y-m-d') . '.pdf');
    }

    /**
     * ✅ Export des ventes PDF
     */
    private function exportSalesPdf($request)
    {
        $query = Order::with(['user', 'items']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->limit(2000)->get();
        
        $stats = [
            'total' => $orders->sum('total') ?? 0,
            'count' => $orders->count(),
            'avg' => $orders->avg('total') ?? 0,
        ];

        $data = compact('orders', 'stats');
        
        $pdf = Pdf::loadView('admin.reports.pdf.sales', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);
        
        return $pdf->download('rapport_ventes_' . date('Y-m-d') . '.pdf');
    }

    /**
     * ✅ Export des retraits PDF
     */
    private function exportWithdrawalsPdf($request)
    {
        $query = Withdrawal::with(['user']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->limit(2000)->get();
        
        $stats = [
            'total' => $withdrawals->sum('amount') ?? 0,
            'count' => $withdrawals->count(),
            'pending' => $withdrawals->where('status', 'pending')->sum('amount') ?? 0,
            'completed' => $withdrawals->where('status', 'completed')->sum('amount') ?? 0,
            'failed' => $withdrawals->where('status', 'failed')->sum('amount') ?? 0,
        ];

        $data = compact('withdrawals', 'stats');
        
        $pdf = Pdf::loadView('admin.reports.pdf.withdrawals', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);
        
        return $pdf->download('rapport_retraits_' . date('Y-m-d') . '.pdf');
    }

    public function exportUsers($request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($user) {
            return [
                'ID' => $user->id,
                'Nom' => $user->name,
                'Email' => $user->email,
                'Téléphone' => $user->phone ?? '',
                'Code Parrainage' => $user->sponsor_id,
                'Grade' => $user->rank?->name ?? 'Distributeur',
                'Package' => $user->package?->name ?? 'Aucun',
                'PV' => $user->pv_balance ?? 0,
                'BV' => $user->bv_balance ?? 0,
                'PV Mensuel' => $user->monthly_pv ?? 0,
                'BV Mensuel' => $user->monthly_bv ?? 0,
                'PV Equipe' => $user->team_pv ?? 0,
                'BV Equipe' => $user->team_bv ?? 0,
                'Gains Totaux' => number_format($user->total_earnings ?? 0, 2),
                'Parrainages' => $user->total_sponsors ?? 0,
                'Equipe' => $user->total_team ?? 0,
                'Solde Portefeuille' => number_format($user->wallet?->balance ?? 0, 2),
                'Statut' => $user->is_active ? 'Actif' : 'Inactif',
                'KYC' => $user->kyc_status ?? 'Non soumis',
                'Inscrit le' => $user->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    public function withdrawals(Request $request)
    {
        $query = Withdrawal::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => $query->sum('amount'),
            'count' => $query->count(),
            'avg_amount' => $query->avg('amount') ?? 0,
            'total_fees' => $query->sum('fee'),
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'completed' => (clone $query)->where('status', 'completed')->sum('amount'),
            'failed' => (clone $query)->where('status', 'failed')->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
        ];

        $statuses = ['pending', 'processing', 'completed', 'failed'];
        $methods = ['crypto', 'mobile_money', 'bank'];

        return view('admin.reports.withdrawals', compact('withdrawals', 'stats', 'statuses', 'methods'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,commissions,orders,withdrawals',
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $data = [];

        switch ($request->type) {
            case 'users':
                $data = $this->exportUsers($request);
                break;
            case 'commissions':
                $data = $this->exportCommissions($request);
                break;
            case 'orders':
                $data = $this->exportOrders($request);
                break;
            case 'withdrawals':
                $data = $this->exportWithdrawals($request);
                break;
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $request->type . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, array_values($row));
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCommissions($request)
    {
        $query = Commission::with(['user', 'fromUser']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($commission) {
            return [
                'ID' => $commission->id,
                'Utilisateur' => $commission->user->name ?? 'N/A',
                'De' => $commission->fromUser->name ?? 'N/A',
                'Type' => $commission->type,
                'Montant' => number_format($commission->amount, 2),
                'Pourcentage' => $commission->percentage . '%',
                'Description' => $commission->description ?? '',
                'Statut' => $commission->status,
                'Payé le' => $commission->paid_at ? $commission->paid_at->format('Y-m-d H:i') : 'En attente',
                'Date' => $commission->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    public function exportOrders($request)
    {
        $query = Order::with(['user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($order) {
            return [
                'ID' => $order->id,
                'N° Commande' => $order->order_number,
                'Client' => $order->user->name ?? 'N/A',
                'Email' => $order->user->email ?? 'N/A',
                'Sous-total' => number_format($order->subtotal, 2),
                'TVA' => number_format($order->tax, 2),
                'Livraison' => number_format($order->shipping, 2),
                'Total' => number_format($order->total, 2),
                'Statut Commande' => $order->status,
                'Statut Paiement' => $order->payment_status,
                'Méthode Paiement' => $order->payment_method ?? 'N/A',
                'Date' => $order->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    public function exportWithdrawals($request)
    {
        $query = Withdrawal::with(['user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($withdrawal) {
            return [
                'ID' => $withdrawal->id,
                'Utilisateur' => $withdrawal->user->name ?? 'N/A',
                'Email' => $withdrawal->user->email ?? 'N/A',
                'Montant Demandé' => number_format($withdrawal->amount, 2),
                'Frais (2.5%)' => number_format($withdrawal->fee, 2),
                'Net' => number_format($withdrawal->net_amount, 2),
                'Méthode' => $withdrawal->method,
                'Statut' => $withdrawal->status,
                'Date' => $withdrawal->created_at->format('Y-m-d H:i'),
                'Complété le' => $withdrawal->completed_at ? $withdrawal->completed_at->format('Y-m-d H:i') : 'En attente',
            ];
        })->toArray();
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return ['start' => now()->startOfDay(), 'end' => now()->endOfDay()];
            case 'week':
                return ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()];
            case 'month':
                return ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()];
            case 'quarter':
                return ['start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()];
            case 'year':
                return ['start' => now()->startOfYear(), 'end' => now()->endOfYear()];
            default:
                return ['start' => now()->subMonth(), 'end' => now()];
        }
    }

    private function getMonthlyData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = [
                'month' => $month->format('M Y'),
                'sales' => (float) Order::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
                'commissions' => (float) Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'withdrawals' => (float) Withdrawal::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }
        return $data;
    }

    private function getRecentActivity()
    {
        $activities = [];

        // Nouveaux utilisateurs
        $users = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($users as $user) {
            $activities[] = [
                'type' => 'user_registered',
                'user' => $user->name,
                'description' => "Nouvel utilisateur inscrit: {$user->name}",
                'time' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'success',
            ];
        }

        // Commissions payées
        $commissions = Commission::where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($commissions as $commission) {
            $activities[] = [
                'type' => 'commission_paid',
                'user' => $commission->user->name ?? 'N/A',
                'description' => "Commission de $" . number_format($commission->amount, 2) . " payée à {$commission->user->name}",
                'time' => $commission->created_at,
                'icon' => 'coins',
                'color' => 'warning',
            ];
        }

        // Retraits traités
        $withdrawals = Withdrawal::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($withdrawals as $withdrawal) {
            $activities[] = [
                'type' => 'withdrawal_processed',
                'user' => $withdrawal->user->name ?? 'N/A',
                'description' => "Retrait de $" . number_format($withdrawal->amount, 2) . " traité pour {$withdrawal->user->name}",
                'time' => $withdrawal->created_at,
                'icon' => 'credit-card',
                'color' => 'info',
            ];
        }

        // Commandes complétées
        $orders = Order::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($orders as $order) {
            $activities[] = [
                'type' => 'order_completed',
                'user' => $order->user->name ?? 'N/A',
                'description' => "Commande #{$order->order_number} de $" . number_format($order->total, 2) . " complétée",
                'time' => $order->created_at,
                'icon' => 'shopping-cart',
                'color' => 'primary',
            ];
        }

        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 10);
    }
}