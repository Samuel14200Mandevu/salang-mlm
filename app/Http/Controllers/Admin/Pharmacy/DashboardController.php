<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\CashEntry;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Setting;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->toDateString()
            ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString()
            ?? now()->toDateString();

        $purchases = Purchase::query()
            ->whereBetween('purchased_at', [$from, $to]);
        $sales = Sale::query()
            ->whereBetween('sold_at', [$from, $to]);

        $purchasesTotal = (float) (clone $purchases)->sum('total_amount');
        $purchasesPaid = (float) (clone $purchases)->sum('paid_amount');
        $salesTotal = (float) (clone $sales)->sum('total_amount');
        $patientCollected = (float) (clone $sales)->sum('patient_amount');
        $insuranceBilled = (float) (clone $sales)->sum('insurance_amount');
        $salesLines = (int) \App\Models\Pharmacy\SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to]))
            ->count();
        $cogs = (float) (clone $sales)->sum('cost_amount');
        $profit = (float) (clone $sales)->sum('profit_amount');

        $stockValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(stock * COALESCE(cost, 0)), 0) as v')
            ->value('v');
        $stockAlert = Product::query()->belowMinStock()->count()
            + Product::query()->expiringSoon()->count();

        $settings = Setting::current();
        $cashIn = (float) CashEntry::query()
            ->whereBetween('entry_date', [$from, $to])
            ->sum('amount_in');
        $cashOut = (float) CashEntry::query()
            ->whereBetween('entry_date', [$from, $to])
            ->sum('amount_out');
        $cashBalance = round((float) $settings->cash_float + $cashIn - $cashOut, 2);

        $recentSales = Sale::query()
            ->with(['patient', 'insurer'])
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin.pharmacy.dashboard.index', compact(
            'from', 'to',
            'purchasesTotal', 'purchasesPaid',
            'salesTotal', 'patientCollected', 'insuranceBilled', 'salesLines',
            'cogs', 'profit', 'stockValue', 'stockAlert',
            'cashBalance', 'recentSales'
        ));
    }
}
