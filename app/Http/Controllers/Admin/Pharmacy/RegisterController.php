<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Patient;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\Insurer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'suppliers')->toString();
        if (! in_array($tab, ['suppliers', 'patients'], true)) {
            $tab = 'suppliers';
        }

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get()
            ->map(function (Supplier $s) {
                $s->debt = $s->debtAmount();

                return $s;
            });

        $patients = Patient::query()
            ->with('insurer')
            ->orderBy('name')
            ->get()
            ->map(function (Patient $p) {
                $p->spent = $p->totalPurchases();

                return $p;
            });

        $insurers = Insurer::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'coverage_rate']);

        $supplierDebts = (float) Purchase::query()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as d')
            ->value('d');
        $patientSales = (float) Sale::query()->sum('total_amount');

        return view('admin.pharmacy.registers.index', compact(
            'tab', 'suppliers', 'patients', 'insurers', 'supplierDebts', 'patientSales'
        ));
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Supplier::query()->create(array_merge($data, ['is_active' => true]));

        return redirect()
            ->route('admin.pharmacy.registers.index', ['tab' => 'suppliers'])
            ->with('success', 'Fournisseur ajouté.');
    }

    public function updateSupplier(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update([
            'name' => $data['name'],
            'contact' => $data['contact'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => $request->boolean('is_active', $supplier->is_active),
        ]);

        return redirect()
            ->route('admin.pharmacy.registers.index', ['tab' => 'suppliers'])
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function storePatient(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'insurer_id' => ['nullable', 'exists:pharmacy_insurers,id'],
            'member_number' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Patient::query()->create(array_merge($data, ['is_active' => true]));

        return redirect()
            ->route('admin.pharmacy.registers.index', ['tab' => 'patients'])
            ->with('success', 'Patient ajouté.');
    }

    public function updatePatient(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'insurer_id' => ['nullable', 'exists:pharmacy_insurers,id'],
            'member_number' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $patient->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'insurer_id' => $data['insurer_id'] ?? null,
            'member_number' => $data['member_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => $request->boolean('is_active', $patient->is_active),
        ]);

        return redirect()
            ->route('admin.pharmacy.registers.index', ['tab' => 'patients'])
            ->with('success', 'Patient mis à jour.');
    }
}
