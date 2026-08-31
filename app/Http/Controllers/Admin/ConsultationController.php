<?php
// app/Http/Controllers/Admin/ConsultationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    /**
     * Liste des consultations (Admin)
     */
    public function index()
    {
        $consultations = Consultation::with(['cashier', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $pendingCount = Consultation::where('status', 'pending')->count();
        
        return view('admin.consultations.index', compact('consultations', 'pendingCount'));
    }

    /**
     * Afficher et traiter une consultation (Admin)
     */
    public function show(Consultation $consultation)
    {
        // Récupérer tous les produits actifs pour le sélecteur
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
        $validated = $request->validate([
            'reason' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'observations' => 'nullable|string',
            'seances_ceragem' => 'nullable|integer|min:0',
            'prix_ceragem' => 'nullable|numeric|min:0',
            'seances_detox' => 'nullable|integer|min:0',
            'prix_detox' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        // ============================================================
        // TRAITER LES PRODUITS RECOMMANDÉS
        // ============================================================
        $recommendedProducts = [];
        if ($request->has('recommended_products') && is_array($request->recommended_products)) {
            foreach ($request->recommended_products as $item) {
                // Ne garder que les lignes où un produit est sélectionné
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $recommendedProducts[] = [
                            'product_id' => (int)$product->id,
                            'produit' => (string)$product->name,
                            'posologie' => (string)($item['posologie'] ?? ''),
                            'prix' => (float)$product->price,
                            'observation' => (string)($item['observation'] ?? ''),
                        ];
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
        
        $totalServices = ($request->seances_ceragem * $request->prix_ceragem) + 
                        ($request->seances_detox * $request->prix_detox);
        $totalGeneral = $totalProduits + $totalServices;

        // ============================================================
        // METTRE À JOUR LA CONSULTATION
        // ============================================================
        $consultation->update([
            // Consultation
            'reason' => $request->reason,
            'symptoms' => $request->symptoms,
            'observations' => $request->observations,
            
            // Produits recommandés
            'recommended_products' => $recommendedProducts,
            
            // Services supplémentaires
            'seances_ceragem' => $request->seances_ceragem ?? 0,
            'prix_ceragem' => $request->prix_ceragem ?? 0,
            'seances_detox' => $request->seances_detox ?? 0,
            'prix_detox' => $request->prix_detox ?? 0,
            
            // Totaux
            'total_produits' => $totalProduits,
            'total_services' => $totalServices,
            'total_general' => $totalGeneral,
            
            // Statut et admin
            'status' => $request->status,
            'admin_id' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.consultations.index')
            ->with('success', 'Fiche de consultation mise à jour avec succès !');
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