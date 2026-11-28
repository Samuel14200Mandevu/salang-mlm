<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Insurer;
use App\Models\Pharmacy\Patient;
use App\Models\Pharmacy\Sale;
use App\Models\Product;
use App\Services\Pharmacy\PharmacyOpsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::query()->with(['patient', 'insurer', 'items']);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }
        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->date('to'));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%");
            });
        }

        $sales = $query->orderByDesc('sold_at')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.pharmacy.sales.index', compact('sales'));
    }

    public function create(): View
    {
        $patients = Patient::query()->where('is_active', true)->with('insurer')->orderBy('name')->get();
        $insurers = Insurer::query()->where('is_active', true)->orderBy('name')->get();
        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'cost', 'stock']);

        return view('admin.pharmacy.sales.create', compact('patients', 'insurers', 'products'));
    }

    public function store(Request $request, PharmacyOpsService $ops): RedirectResponse
    {
        $data = $request->validate([
            'sold_at' => ['required', 'date'],
            'patient_id' => ['nullable', 'exists:pharmacy_patients,id'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'insurer_id' => ['nullable', 'exists:pharmacy_insurers,id'],
            'coverage_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (($data['payment_method'] ?? '') === 'insurance') {
            if (empty($data['insurer_id'])) {
                return back()->withInput()->with('error', 'Sélectionnez une assurance pour un paiement tiers payant.');
            }
            if (! isset($data['coverage_rate'])) {
                $insurer = Insurer::query()->find($data['insurer_id']);
                $data['coverage_rate'] = $insurer?->coverage_rate ?? 0;
            }
        }

        try {
            $sale = $ops->createSale($data, $request->user());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pharmacy.sales.show', $sale)
            ->with('success', 'Vente '.$sale->ticket_number.' enregistrée.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['patient', 'insurer', 'items.product', 'user']);

        return view('admin.pharmacy.sales.show', compact('sale'));
    }
}
