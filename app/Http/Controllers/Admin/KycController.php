<?php
// app/Http/Controllers/Admin/KycController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    private function getRequiredKycDocuments(): array
    {
        return config('kyc.required_documents', ['id_card', 'proof_of_address']);
    }

    /**
     * Liste des documents KYC avec recherche et filtres
     */
    public function index(Request $request)
    {
        $query = KycDocument::with('user');

        // Recherche par nom d'utilisateur
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par type de document
        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $pendingCount = KycDocument::where('status', 'pending')->count();
        $verifiedCount = KycDocument::where('status', 'verified')->count();
        $rejectedCount = KycDocument::where('status', 'rejected')->count();
        $verifiedUsersCount = User::where('kyc_status', 'verified')->count();

        return view('admin.kyc.index', compact(
            'documents',
            'pendingCount',
            'verifiedCount',
            'rejectedCount',
            'verifiedUsersCount'
        ));
    }

    /**
     * Vérifier un document KYC
     */
    public function verify(Request $request, $id)
    {
        $document = KycDocument::findOrFail($id);

        if ($document->status !== 'pending') {
            return redirect()->route('admin.kyc.index')
                ->with('error', 'Ce document a déjà été traité.');
        }

        $document->status = 'verified';
        $document->verified_by = Auth::id();
        $document->verified_at = now();
        $document->save();

        $user = $document->user;
        $requiredDocs = $this->getRequiredKycDocuments();

        $verifiedDocs = KycDocument::where('user_id', $user->id)
            ->where('status', 'verified')
            ->whereIn('document_type', $requiredDocs)
            ->count();

        if ($verifiedDocs >= count($requiredDocs)) {
            $user->kyc_status = 'verified';
            $user->kyc_verified_at = now();
        } else {
            $user->kyc_status = 'partial';
        }
        $user->save();

        return redirect()->route('admin.kyc.index')
            ->with('success', "Document de {$user->name} vérifié avec succès.");
    }

    /**
     * Rejeter un document KYC
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $document = KycDocument::findOrFail($id);

        if ($document->status !== 'pending') {
            return redirect()->route('admin.kyc.index')
                ->with('error', 'Ce document a déjà été traité.');
        }

        $document->status = 'rejected';
        $document->rejection_reason = $request->reason;
        $document->verified_by = Auth::id();
        $document->verified_at = now();
        $document->save();

        $user = $document->user;
        $user->kyc_status = 'rejected';
        $user->save();

        return redirect()->route('admin.kyc.index')
            ->with('success', "Document de {$user->name} rejeté.");
    }
}