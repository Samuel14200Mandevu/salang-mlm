<?php
// app/Http/Controllers/Cashier/ConsultationController.php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Notifications\ConsultationReviewedNotification;
use App\Models\User;
use App\Models\Product;
use App\Services\ConsultationSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConsultationController extends Controller
{
    /**
     * Liste des consultations du caissier avec recherche
     */
    public function index(Request $request)
    {
        $query = Consultation::with(['cashier', 'admin'])
            ->where('cashier_id', Auth::id());

        // Recherche par nom de patient
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('code_id', 'like', "%{$search}%")
                  ->orWhere('numero', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $consultations = $query->orderBy('created_at', 'desc')->paginate(20);

        auth()->user()->unreadNotifications()
            ->where('type', ConsultationReviewedNotification::class)
            ->get()
            ->each->markAsRead();

        // Calculer les statistiques
        $stats = [
            'total' => Consultation::where('cashier_id', Auth::id())->count(),
            'pending' => Consultation::where('cashier_id', Auth::id())->where('status', 'pending')->count(),
            'processing' => Consultation::where('cashier_id', Auth::id())->where('status', 'processing')->count(),
            'completed' => Consultation::where('cashier_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => Consultation::where('cashier_id', Auth::id())->where('status', 'cancelled')->count(),
        ];
        
        return view('cashier.consultations.index', compact('consultations', 'stats'));
    }

    /**
     * Formulaire de création (Caissier - Partie Patient seulement)
     */
    public function create()
    {
        // Générer un code ID et un numéro de dossier automatiques
        $codeId = 'PAT-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $numero = 'DOS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return view('cashier.consultations.create', compact('codeId', 'numero'));
    }

    /**
     * Enregistrer une nouvelle consultation (Caissier - Partie Patient seulement)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_id' => 'nullable|string|max:50',
            'numero' => 'nullable|string|max:50',
            'nom_complet' => 'required|string|max:255',
            'genre' => 'nullable|in:masculin,feminin',
            'age' => 'nullable|integer|min:0|max:150',
            'poids' => 'nullable|numeric|min:0|max:500',
            'taille' => 'nullable|numeric|min:0|max:300',
            'date_examen' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        // Créer la consultation avec seulement les informations patient
        $consultation = Consultation::create([
            'cashier_id' => Auth::id(),
            'code_id' => $request->code_id ?? 'PAT-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'numero' => $request->numero ?? 'DOS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'nom_complet' => $request->nom_complet,
            'genre' => $request->genre,
            'age' => $request->age,
            'poids' => $request->poids,
            'taille' => $request->taille,
            'date_examen' => $request->date_examen ?? now(),
            'phone' => $request->phone,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'status' => 'pending', // En attente de traitement par l'admin
            'recommended_products' => [],
            'seances_ceragem' => 0,
            'prix_ceragem' => 0,
            'seances_detox' => 0,
            'prix_detox' => 0,
            'total_produits' => 0,
            'total_services' => 0,
            'total_general' => 0,
        ]);

        return redirect()
            ->route('cashier.consultations.index')
            ->with('success', 'Fiche de consultation envoyée à l\'administrateur avec succès !');
    }

    /**
     * Afficher une consultation (Caissier - Lecture seule)
     */
    public function show(Consultation $consultation)
    {
        // Vérifier que le caissier est propriétaire
        if ($consultation->cashier_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à consulter cette fiche.');
        }

        $consultation->load(['orders' => fn ($q) => $q->latest()->limit(10)]);
        $productLines = ConsultationSaleService::normalizedLines($consultation);
        $productStats = ConsultationSaleService::productStats($consultation);
        $canSell = ConsultationSaleService::canCashierSell($consultation);

        return view('cashier.consultations.show', compact(
            'consultation',
            'productLines',
            'productStats',
            'canSell'
        ));
    }

    /**
     * Encaisser une sélection de produits recommandés (vente partielle).
     */
    public function checkoutSelection(Request $request, Consultation $consultation)
    {
        if ($consultation->cashier_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à encaisser cette fiche.');
        }

        if (! ConsultationSaleService::canCashierSell($consultation)) {
            return redirect()
                ->route('cashier.consultations.show', $consultation)
                ->with('error', 'Cette consultation n\'est pas encore prête pour l\'encaissement.');
        }

        $validated = $request->validate([
            'line_keys' => 'required|array|min:1',
            'line_keys.*' => 'required|string|max:64',
        ]);

        try {
            $lines = ConsultationSaleService::resolvePendingLines($consultation, $validated['line_keys']);
            $cart = ConsultationSaleService::buildCartItems($consultation, $lines);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('cashier.consultations.show', $consultation)
                ->with('error', $e->getMessage());
        }

        session()->put('pos_cart', $cart);
        session()->put('consultation_checkout', [
            'consultation_id' => $consultation->id,
            'line_keys' => array_column($lines, 'line_key'),
            'patient_name' => $consultation->nom_complet,
        ]);

        return redirect()
            ->route('cashier.checkout')
            ->with('success', count($cart) . ' produit(s) ajoutés au panier pour encaissement.');
    }

    /**
     * Imprimer une consultation (Caissier)
     */
    public function print(Consultation $consultation)
    {
        // Vérifier que le caissier est propriétaire
        if ($consultation->cashier_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à imprimer cette fiche.');
        }
        
        // Vérifier que la consultation est complète (statut completed)
        if ($consultation->status !== 'completed') {
            return redirect()
                ->route('cashier.consultations.show', $consultation)
                ->with('warning', 'Cette consultation n\'a pas encore été traitée par l\'administrateur.');
        }
        
        return view('cashier.consultations.print', compact('consultation'));
    }
}