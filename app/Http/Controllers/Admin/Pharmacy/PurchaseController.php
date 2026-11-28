<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Supplier;
use App\Models\Product;
use App\Services\Pharmacy\PharmacyOpsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::query()->with(['supplier', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('purchased_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('purchased_at', '<=', $request->date('to'));
        }

        $purchases = $query->orderByDesc('purchased_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $suppliers = Supplier::query()->orderBy('name')->get(['id', 'name']);
        $debtTotal = (float) Purchase::query()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as d')
            ->value('d');

        return view('admin.pharmacy.purchases.index', compact('purchases', 'suppliers', 'debtTotal'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();
        $products = Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost', 'stock']);

        return view('admin.pharmacy.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request, PharmacyOpsService $ops): RedirectResponse
    {
        $data = $request->validate([
            'purchased_at' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'supplier_id' => ['required', 'exists:pharmacy_suppliers,id'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = $ops->createPurchase($data, $request->user());

        return redirect()
            ->route('admin.pharmacy.purchases.show', $purchase)
            ->with('success', 'Achat enregistré — stock mis à jour.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items.product', 'user']);

        return view('admin.pharmacy.purchases.show', compact('purchase'));
    }

    public function updatePayment(Request $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $paid = round((float) $data['paid_amount'], 2);
        $purchase->update([
            'paid_amount' => $paid,
            'status' => Purchase::resolveStatus((float) $purchase->total_amount, $paid),
        ]);

        return back()->with('success', 'Paiement fournisseur mis à jour.');
    }
}
