<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierReport;
use App\Models\Commission;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CashierReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);

        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers et administrateurs.');
            }
            return $next($request);
        });
    }

    /**
     * Liste des rapports
     */
    public function index(Request $request)
    {
        $query = CashierReport::with(['user', 'approver'])
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        if ($request->filled('user_id') && auth()->user()->hasRole('admin')) {
            $query->where('user_id', $request->user_id);
        }

        $reports = $query->paginate(20)->withQueryString();

        $baseQuery = CashierReport::query();
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $baseQuery->where('user_id', auth()->id());
        }

        $stats = [
            'today' => (clone $baseQuery)->today()->count(),
            'month' => (clone $baseQuery)->thisMonth()->count(),
            'pending' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
        ];

        $hasReportToday = CashierReport::hasReportToday(auth()->id());

        return view('cashier.reports.index', compact('reports', 'stats', 'hasReportToday'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $reportDate = Carbon::parse($date);

        $existing = CashierReport::where('user_id', auth()->id())
            ->whereDate('report_date', $reportDate)
            ->whereIn('status', ['submitted', 'approved'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('cashier.reports.show', $existing->id)
                ->with('info', 'Un rapport existe déjà pour cette date.');
        }

        $data = $this->calculateReportData(auth()->id(), $reportDate);

        return view('cashier.reports.create', array_merge($data, [
            'reportDate' => $reportDate,
        ]));
    }

    /**
     * Enregistrer un nouveau rapport (avec support USD + CDF)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => 'required|date',
            'opening_balance' => 'nullable|numeric|min:0',
            'closing_balance' => 'nullable|numeric|min:0',
            'opening_balance_cdf' => 'nullable|numeric|min:0',
            'closing_balance_cdf' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'signature_name' => 'required|string|max:255',
            'action' => 'required|in:draft,submit',
        ]);

        $reportDate = Carbon::parse($validated['report_date']);

        $existing = CashierReport::where('user_id', auth()->id())
            ->whereDate('report_date', $reportDate)
            ->whereIn('status', ['submitted', 'approved'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('cashier.reports.show', $existing->id)
                ->with('error', 'Un rapport existe déjà pour cette date.');
        }

        try {
            DB::beginTransaction();

            $data = $this->calculateReportData(auth()->id(), $reportDate);

            // Calculs caisse USD
            $opening = $validated['opening_balance'] ?? 0;
            $closing = $validated['closing_balance'] ?? 0;
            $theoretical = $opening + $data['cash_amount'] - $data['total_expenses'];
            $difference = $closing - $theoretical;

            // Calculs caisse CDF
            $openingCdf = $validated['opening_balance_cdf'] ?? 0;
            $closingCdf = $validated['closing_balance_cdf'] ?? 0;
            $theoreticalCdf = $openingCdf + $data['cash_amount_cdf'] - $data['total_expenses_cdf'];
            $differenceCdf = $closingCdf - $theoreticalCdf;

            $report = CashierReport::create([
                'user_id' => auth()->id(),
                'report_date' => $reportDate,
                'report_number' => CashierReport::generateReportNumber(),

                // USD
                'total_sales' => $data['total_sales'],
                'total_pos' => $data['total_pos'],
                'total_mlm' => $data['total_mlm'],
                'total_expenses' => $data['total_expenses'],
                'total_commissions' => $data['total_commissions'],
                'net_balance' => $data['net_balance'],
                'cash_amount' => $data['cash_amount'],
                'mobile_money_amount' => $data['mobile_money_amount'],
                'bank_amount' => $data['bank_amount'],
                'opening_balance' => $opening,
                'closing_balance' => $closing,
                'theoretical_balance' => $theoretical,
                'difference' => $difference,

                // CDF
                'total_sales_cdf' => $data['total_sales_cdf'],
                'total_pos_cdf' => $data['total_pos_cdf'],
                'total_mlm_cdf' => $data['total_mlm_cdf'],
                'total_expenses_cdf' => $data['total_expenses_cdf'],
                'total_commissions_cdf' => $data['total_commissions_cdf'],
                'net_balance_cdf' => $data['net_balance_cdf'],
                'cash_amount_cdf' => $data['cash_amount_cdf'],
                'mobile_money_amount_cdf' => $data['mobile_money_amount_cdf'],
                'bank_amount_cdf' => $data['bank_amount_cdf'],
                'opening_balance_cdf' => $openingCdf,
                'closing_balance_cdf' => $closingCdf,
                'theoretical_balance_cdf' => $theoreticalCdf,
                'difference_cdf' => $differenceCdf,

                // Divers
                'total_orders' => $data['total_orders'],
                'total_pv' => $data['total_pv'],
                'total_bv' => $data['total_bv'],
                'new_members' => $data['new_members'],
                'new_clients' => $data['new_clients'],

                // Statut + signature
                'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
                'notes' => $validated['notes'] ?? null,
                'signature_name' => $validated['signature_name'],
                'signature_at' => now(),
                'details' => $data['details'],
            ]);

            DB::commit();

            if ($validated['action'] === 'submit') {
                return redirect()
                    ->route('cashier.reports.show', $report->id)
                    ->with('success', 'Rapport soumis avec succès ! En attente de validation admin.');
            }

            return redirect()
                ->route('cashier.reports.show', $report->id)
                ->with('success', 'Rapport enregistré en brouillon.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création rapport: ' . $e->getMessage());
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Détails d'un rapport
     */
    public function show($id)
    {
        $report = CashierReport::with(['user', 'approver'])->findOrFail($id);

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($report->user_id !== auth()->id()) abort(403);
        }

        return view('cashier.reports.show', compact('report'));
    }

    /**
     * Télécharger le PDF
     */
    public function pdf($id)
    {
        $report = CashierReport::with(['user', 'approver'])->findOrFail($id);

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($report->user_id !== auth()->id()) abort(403);
        }

        $logoBase64 = '';
        $logoPath = public_path('images/salang_logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('cashier.reports.pdf', compact('report', 'logoBase64'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->download('rapport_caisse_' . $report->report_number . '.pdf');
    }

    /**
     * Imprimer le rapport (version HTML)
     */
    public function print($id)
    {
        $report = CashierReport::with(['user', 'approver'])->findOrFail($id);

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($report->user_id !== auth()->id()) abort(403);
        }

        $logoBase64 = '';
        $logoPath = public_path('images/salang_logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return view('cashier.reports.print', compact('report', 'logoBase64'));
    }

    /**
     * Admin : Approuver un rapport
     */
    public function approve($id)
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        $report = CashierReport::findOrFail($id);

        if ($report->status !== 'submitted') {
            return back()->with('error', 'Ce rapport ne peut pas être approuvé.');
        }

        $report->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Rapport approuvé avec succès.');
    }

    /**
     * Admin : Rejeter un rapport
     */
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $report = CashierReport::findOrFail($id);

        $report->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Rapport rejeté.');
    }

    /**
     * Supprimer un rapport (brouillon uniquement)
     */
    public function destroy($id)
    {
        $report = CashierReport::findOrFail($id);

        // Vérification des droits
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($report->user_id !== auth()->id()) {
                abort(403, 'Vous ne pouvez supprimer que vos propres rapports.');
            }
        }

        // Seuls les brouillons peuvent être supprimés
        if ($report->status !== 'draft') {
            return back()->with('error', 'Seuls les brouillons peuvent être supprimés. Les rapports soumis font partie de l\'historique comptable.');
        }

        $reportNumber = $report->report_number;
        $report->delete();

        return redirect()
            ->route('cashier.reports.index')
            ->with('success', "Rapport {$reportNumber} supprimé avec succès.");
    }

    /**
     * Calculer les données du rapport pour une date donnée.
     * Toutes les ventes POS/MLM sont en USD (pas de colonne currency sur orders).
     * Seules les dépenses peuvent être en USD ou CDF.
     */
    protected function calculateReportData($userId, Carbon $date): array
    {
        $ordersQuery = Order::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('source', 'pos')->orWhere('source', 'mlm');
            });

        $orders = $ordersQuery->get();

        // ============================================================
        // VENTES (USD)
        // ============================================================
        $totalPosUsd = $orders->where('source', 'pos')->sum('total');
        $totalMlmUsd = $orders->where('source', 'mlm')->sum('total');
        $totalSalesUsd = $totalPosUsd + $totalMlmUsd;

        // CDF = 0
        $totalPosCdf = 0;
        $totalMlmCdf = 0;
        $totalSalesCdf = 0;

        $totalOrders = $orders->count();
        $totalPv = $orders->sum('total_pv');
        $totalBv = $orders->sum('total_bv');

        // ============================================================
        // MODES DE PAIEMENT (USD)
        // ============================================================
        $cashUsd = $orders->where('payment_method', 'cash')->sum('total');
        $mobileUsd = $orders->where('payment_method', 'mobile_money')->sum('total');
        $bankUsd = $orders->where('payment_method', 'bank')->sum('total');

        $cashCdf = 0;
        $mobileCdf = 0;
        $bankCdf = 0;

        // ============================================================
        // DÉPENSES (USD + CDF)
        // ============================================================
        $expensesUsd = Expense::where('user_id', $userId)
            ->whereDate('expense_date', $date)
            ->where('status', 'approved')
            ->where('currency', 'USD')
            ->sum('amount');

        $expensesCdf = Expense::where('user_id', $userId)
            ->whereDate('expense_date', $date)
            ->where('status', 'approved')
            ->where('currency', 'CDF')
            ->sum('amount');

        // ============================================================
        // COMMISSIONS CASH POS (USD)
        // ============================================================
        $commissionsUsd = Commission::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('type', 'cash_pos')
            ->where('status', 'paid')
            ->sum('amount');

        $commissionsCdf = 0;

        // ============================================================
        // NOUVEAUX INSCRITS
        // ============================================================
        $newMembers = User::where('user_type', 'member')
            ->whereDate('created_at', $date)
            ->where('registered_by', $userId)
            ->count();

        $newClients = User::where('user_type', 'client')
            ->whereDate('created_at', $date)
            ->where('parrain_id', '!=', null)
            ->count();

        // ============================================================
        // PRODUITS VENDUS
        // ============================================================
        $productsSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $date)
            ->where('orders.status', 'completed')
            ->whereIn('orders.source', ['pos', 'mlm'])
            ->select(
                'order_items.name',
                DB::raw("'USD' as currency"),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_amount')
            )
            ->groupBy('order_items.name')
            ->get();

        // ============================================================
        // SOLDES NETS
        // ============================================================
        $netBalanceUsd = $totalSalesUsd - $expensesUsd - $commissionsUsd;
        $netBalanceCdf = $totalSalesCdf - $expensesCdf - $commissionsCdf;

        // ============================================================
        // RETOUR COMPLET
        // ============================================================
        return [
            // Ventes USD
            'total_sales' => $totalSalesUsd,
            'total_pos' => $totalPosUsd,
            'total_mlm' => $totalMlmUsd,

            // Ventes CDF
            'total_sales_cdf' => $totalSalesCdf,
            'total_pos_cdf' => $totalPosCdf,
            'total_mlm_cdf' => $totalMlmCdf,

            // Divers
            'total_orders' => $totalOrders,
            'total_pv' => $totalPv,
            'total_bv' => $totalBv,

            // Modes paiement USD
            'cash_amount' => $cashUsd,
            'mobile_money_amount' => $mobileUsd,
            'bank_amount' => $bankUsd,

            // Modes paiement CDF
            'cash_amount_cdf' => $cashCdf,
            'mobile_money_amount_cdf' => $mobileCdf,
            'bank_amount_cdf' => $bankCdf,

            // Dépenses USD + CDF
            'total_expenses' => $expensesUsd,
            'total_expenses_cdf' => $expensesCdf,

            // Commissions USD + CDF
            'total_commissions' => $commissionsUsd,
            'total_commissions_cdf' => $commissionsCdf,

            // Soldes nets
            'net_balance' => $netBalanceUsd,
            'net_balance_cdf' => $netBalanceCdf,

            // Nouveaux
            'new_members' => $newMembers,
            'new_clients' => $newClients,

            // Détails
            'details' => [
                'products_sold' => $productsSold,
            ],
        ];
    }
}