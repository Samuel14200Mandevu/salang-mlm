<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Insurer;
use App\Models\Pharmacy\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsurerController extends Controller
{
    public function index(): View
    {
        $insurers = Insurer::query()
            ->withCount('patients')
            ->orderBy('name')
            ->get()
            ->map(function (Insurer $insurer) {
                $insurer->billed = $insurer->billedAmount();

                return $insurer;
            });

        $totalBilled = (float) Sale::query()->sum('insurance_amount');

        return view('admin.pharmacy.insurers.index', compact('insurers', 'totalBilled'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'coverage_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'contact' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Insurer::query()->create([
            'name' => $data['name'],
            'coverage_rate' => round((float) $data['coverage_rate'], 4),
            'contact' => $data['contact'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Assurance ajoutée.');
    }

    public function update(Request $request, Insurer $insurer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'coverage_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'contact' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $insurer->update([
            'name' => $data['name'],
            'coverage_rate' => round((float) $data['coverage_rate'], 4),
            'contact' => $data['contact'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => $request->boolean('is_active', $insurer->is_active),
        ]);

        return back()->with('success', 'Assurance mise à jour.');
    }
}
