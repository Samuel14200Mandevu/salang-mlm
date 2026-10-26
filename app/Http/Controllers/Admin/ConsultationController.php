<?php
// app/Http/Controllers/Admin/ConsultationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ConsultationReviewedNotification;
use App\Services\ConsultationSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    /**
     * Liste des consultations (Admin) avec recherche
     */
    public function index(Request $request)
    {
        $query = Consultation::with(['cashier', 'admin']);

        // Recherche par nom de patient
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $consultations = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $pendingCount = Consultation::where('status', 'pending')->count();
        $statusCounts = [
            'all' => Consultation::count(),
            'pending' => Consultation::where('status', 'pending')->count(),
            'processing' => Consultation::where('status', 'processing')->count(),
            'completed' => Consultation::where('status', 'completed')->count(),
            'cancelled' => Consultation::where('status', 'cancelled')->count(),
        ];

        return view('admin.consultations.index', compact('consultations', 'pendingCount', 'statusCounts'));
    }

    /**
     * Afficher et traiter une consultation (Admin)
     */
    public function show(Consultation $consultation)
    {
        $consultation->load(['cashier', 'client']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.consultations.show', compact('consultation', 'products'));
    }

    /**
     * Mettre à jour une consultation (Admin - Partie Consultation + Produits + Services)
     */
    public function update(Request $request, Consultation $consultation)
    {
        $request->validate([
            'code_id' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $member = User::where('sponsor_id', $value)->where('is_active', true)->first();
                    if (! $member) {
                        $fail('Le Code ID doit être un code membre actif (destinataire des PV).');
                    }
                },
            ],
            'numero' => 'nullable|string|max:50',
            'nom_complet' => 'required|string|max:255',
            'genre' => 'nullable|in:masculin,feminin',
            'age' => 'nullable|integer|min:0|max:150',
            'poids' => 'nullable|numeric|min:0|max:500',
            'taille' => 'nullable|numeric|min:0|max:300',
            'date_examen' => 'nullable|date',
            'reason' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'observations' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'seances_ceragem' => 'nullable|integer|min:0',
            'prix_ceragem' => 'nullable|numeric|min:0',
            'seances_detox' => 'nullable|integer|min:0',
            'prix_detox' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $pvMember = User::where('sponsor_id', $request->code_id)
            ->where('is_active', true)
            ->firstOrFail();

        // ============================================================
        // TRAITER LES PRODUITS RECOMMANDÉS
        // ============================================================
        $existingRows = $consultation->recommended_products ?? [];
        $recommendedProducts = [];
        if ($request->has('recommended_products') && is_array($request->recommended_products)) {
            foreach ($request->recommended_products as $item) {
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $recommendedProducts[] = ConsultationSaleService::mergeAdminProductRow(
                            $existingRows,
                            (int) $product->id,
                            ConsultationSaleService::buildAdminProductRow($product, $item)
                        );
                    }
                }
            }
        }

        // ============================================================
        // CALCULER LES TOTAUX
        // ============================================================
        $totalProduits = 0;
        foreach ($recommendedProducts as $product) {
            $totalProduits += $product['prix'];
        }

        $totalServices = ((float) ($request->seances_ceragem ?? 0) * (float) ($request->prix_ceragem ?? 0))
            + ((float) ($request->seances_detox ?? 0) * (float) ($request->prix_detox ?? 0));
        $totalGeneral = $totalProduits + $totalServices;

        $previousStatus = $consultation->status;

        // ============================================================
        // METTRE À JOUR LA CONSULTATION
        // ============================================================
        $consultation->update([
            'client_id' => $pvMember->id,
            'code_id' => $pvMember->sponsor_id,
            'numero' => $request->numero,
            'nom_complet' => $request->nom_complet,
            'genre' => $request->genre,
            'age' => $request->age,
            'poids' => $request->poids,
            'taille' => $request->taille,
            'date_examen' => $request->date_examen,
            'reason' => $request->reason,
            'symptoms' => $request->symptoms,
            'observations' => $request->observations,
            'admin_notes' => $request->admin_notes,
            'recommended_products' => $recommendedProducts,
            'seances_ceragem' => $request->seances_ceragem ?? 0,
            'prix_ceragem' => $request->prix_ceragem ?? 0,
            'seances_detox' => $request->seances_detox ?? 0,
            'prix_detox' => $request->prix_detox ?? 0,
            'total_produits' => $totalProduits,
            'total_services' => $totalServices,
            'total_general' => $totalGeneral,
            'status' => $request->status,
            'admin_id' => Auth::id(),
        ]);

        if ($consultation->cashier_id && $request->status !== 'pending') {
            if ($previousStatus === 'pending' || $previousStatus !== $request->status) {
                $consultation->load('cashier');
                $consultation->cashier?->notify(new ConsultationReviewedNotification($consultation->fresh()));
            }
        }

        return redirect()
            ->route('admin.consultations.index')
            ->with('success', 'Fiche de consultation mise à jour avec succès !');
    }

    /**
     * Supprimer une consultation (Admin)
     */
    public function destroy(Consultation $consultation)
    {
        if ($consultation->orders()->exists()) {
            return redirect()
                ->route('admin.consultations.index')
                ->with('error', 'Impossible de supprimer : des commandes sont liées à cette consultation.');
        }

        $consultation->delete();

        return redirect()
            ->route('admin.consultations.index')
            ->with('success', 'Consultation supprimée avec succès.');
    }

    /**
     * Imprimer la fiche de consultation (Admin)
     */
    public function print(Consultation $consultation)
    {
        $consultation->load(['cashier', 'admin']);

        return view('admin.consultations.print', compact('consultation'));
    }

    /**
     * Compter les consultations en attente (API)
     */
    public function countPending()
    {
        return response()->json([
            'count' => Consultation::where('status', 'pending')->count()
        ]);
    }
}