<?php
// app/Http/Controllers/Cashier/ConsultationController.php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConsultationController extends Controller
{
    /**
     * Liste des consultations du caissier
     */
    public function index()
    {
        $consultations = Consultation::with(['cashier', 'admin'])
            ->where('cashier_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
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
            'status' => 'pending', // En attente de traitement par l'admin
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
            abort(403);
        }
        
        return view('cashier.consultations.show', compact('consultation'));
    }

    /**
     * Imprimer une consultation (Caissier)
     */
    public function print(Consultation $consultation)
    {
        // Vérifier que le caissier est propriétaire
        if ($consultation->cashier_id !== Auth::id()) {
            abort(403);
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