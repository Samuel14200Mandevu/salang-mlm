{{-- resources/views/cashier/consultations/create.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Nouvelle Consultation')

@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="flex items-center mb-6">
        <a href="{{ route('cashier.consultations.index') }}" class="mr-4 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="text-2xl font-bold">Nouvelle Fiche de Consultation</h2>
    </div>

    <!-- Message d'information -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>Information :</strong> Vous enregistrez uniquement les informations du patient.
                    L'administrateur complétera la consultation, les produits et les services.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('cashier.consultations.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- ============================================================ -->
        <!-- INFORMATIONS PATIENT -->
        <!-- ============================================================ -->
        <div class="bg-[var(--bg-card)] rounded-xl shadow-sm border border-[var(--border-color)] p-6">
            <h3 class="text-lg font-semibold mb-4 text-blue-600">Informations du Patient</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Code ID</label>
                    <input type="text" name="code_id" 
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('code_id', $codeId ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Numero de dossier</label>
                    <input type="text" name="numero" 
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('numero', $numero ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="nom_complet" 
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5 @error('nom_complet') border-red-500 @enderror"
                           value="{{ old('nom_complet') }}" required>
                    @error('nom_complet') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Genre</label>
                    <select name="genre" class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5">
                        <option value="">Selectionner</option>
                        <option value="masculin" @selected(old('genre') == 'masculin')>Masculin</option>
                        <option value="feminin" @selected(old('genre') == 'feminin')>Feminin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Age (ans)</label>
                    <input type="number" name="age" min="0" max="150"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('age') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Poids (kg)</label>
                    <input type="number" name="poids" step="0.1" min="0"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('poids') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Taille (cm)</label>
                    <input type="number" name="taille" step="0.1" min="0"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('taille') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date de l'examen</label>
                    <input type="date" name="date_examen" 
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-4 py-2.5"
                           value="{{ old('date_examen', date('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- BOUTONS -->
        <!-- ============================================================ -->
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('cashier.consultations.index') }}" class="btn btn-outline px-6 py-2.5">
                Annuler
            </a>
            <button type="submit" class="btn btn-primary px-8 py-2.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Envoyer à l'administrateur
            </button>
        </div>
    </form>
</div>

<style>
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
    }
    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
    }
</style>
@endsection