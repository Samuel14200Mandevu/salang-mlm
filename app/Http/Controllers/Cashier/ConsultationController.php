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
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasRole('cashier') && ! auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers.');
            }

            return $next($request);
        });
    }

    /**
     * Liste des consultations du caissier avec recherche
     */
    public function index(Request $request)
    {
        $query = Consultation::with(['cashier', 'admin'])
            ->where('cashier_id', Auth::id());

        // Recherche par Code ID ou Numéro de dossier
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code_id', 'like', "%{$search}%")
                    ->orWhere('numero', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $consultations = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

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
        $numero = 'DOS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return view('cashier.consultations.create', compact('numero'));
    }

    /**
     * Enregistrer une nouvelle consultation (Caissier - Partie Patient seulement)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        $pvMember = User::where('sponsor_id', $validated['code_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $consultation = Consultation::create([
            'cashier_id' => Auth::id(),
            'client_id' => $pvMember->id,
            'code_id' => $pvMember->sponsor_id,
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
            'status' => 'pending',
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

        $consultation->load([
            'client',
            'orders' => fn ($q) => $q->latest()->limit(10),
        ]);
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
            'portions' => 'nullable|array',
            'portions.*' => 'nullable|in:full,half',
        ]);

        try {
            $lines = ConsultationSaleService::resolvePendingLines(
                $consultation,
                $validated['line_keys'],
                $validated['portions'] ?? []
            );
            $cart = ConsultationSaleService::buildCartItems($consultation, $lines);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('cashier.consultations.show', $consultation)
                ->with('error', $e->getMessage());
        }

        $purchaseDetails = [];
        foreach ($lines as $line) {
            $purchaseDetails[$line['line_key']] = [
                'portion' => $line['portion'] ?? ConsultationSaleService::PORTION_FULL,
                'prix' => $line['prix'] ?? 0,
            ];
        }

        session()->put('pos_cart', $cart);
        session()->put('consultation_checkout', [
            'consultation_id' => $consultation->id,
            'line_keys' => array_column($lines, 'line_key'),
            'purchase_details' => $purchaseDetails,
            'patient_name' => $consultation->nom_complet,
            'pv_member_code' => $consultation->code_id,
            'pv_member_name' => $consultation->client?->name
                ?? User::where('sponsor_id', $consultation->code_id)->value('name'),
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