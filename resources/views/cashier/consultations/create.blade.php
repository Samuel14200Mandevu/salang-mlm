{{-- resources/views/cashier/consultations/create.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .form-label {
        display: block;
        font-size: 0.813rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .form-input {
        width: 100%;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: var(--text-primary);
        transition: border-color 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-input-error {
        border-color: #b32a2a;
    }

    .form-error {
        color: #b32a2a;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .alert-info {
        background: rgba(59, 130, 246, 0.06);
        border: 1px solid rgba(59, 130, 246, 0.15);
        border-radius: var(--radius-md, 8px);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert-info .alert-icon {
        color: #2563eb;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    .alert-info .alert-text {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .alert-info .alert-text strong {
        color: var(--text-primary);
    }

    /* ===== BOUTONS AMÉLIORÉS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.5rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #091E3B);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    @media (max-width: 640px) {
        .form-card { padding: 0.875rem; }
        .form-input { padding: 0.375rem 0.5rem; font-size: 0.813rem; }
        .btn { padding: 0.375rem 1rem; font-size: 0.813rem; }
        .alert-info .alert-text { font-size: 0.813rem; }
    }

    @media (max-width: 480px) {
        .form-card { padding: 0.75rem; }
        .form-input { padding: 0.25rem 0.375rem; font-size: 0.75rem; }
        .btn { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Nouvelle Consultation')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- EN-TÊTE --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('cashier.consultations.index') }}" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Nouvelle Fiche de Consultation</h2>
    </div>

    {{-- ALERTE INFO --}}
    <div class="alert-info mb-6">
        <svg class="alert-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="alert-text">
            <strong>Information :</strong> Vous enregistrez uniquement les informations du patient.
            L'administrateur complétera la consultation, les produits et les services.
        </div>
    </div>

    {{-- FORMULAIRE --}}
    <form action="{{ route('cashier.consultations.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="form-card">
            <h3 class="text-lg font-semibold text-[var(--primary)] mb-4">Informations du Patient</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Code ID --}}
                <div>
                    <label class="form-label">Code ID</label>
                    <input type="text" name="code_id"
                           class="form-input"
                           value="{{ old('code_id', $codeId ?? '') }}">
                </div>

                {{-- Numero de dossier --}}
                <div>
                    <label class="form-label">Numero de dossier</label>
                    <input type="text" name="numero"
                           class="form-input"
                           value="{{ old('numero', $numero ?? '') }}">
                </div>

                {{-- Nom complet --}}
                <div>
                    <label class="form-label">Nom complet <span class="text-[#b32a2a]">*</span></label>
                    <input type="text" name="nom_complet"
                           class="form-input @error('nom_complet') form-input-error @enderror"
                           value="{{ old('nom_complet') }}" required>
                    @error('nom_complet') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Genre --}}
                <div>
                    <label class="form-label">Genre</label>
                    <select name="genre" class="form-input">
                        <option value="">Selectionner</option>
                        <option value="masculin" @selected(old('genre') == 'masculin')>Masculin</option>
                        <option value="feminin" @selected(old('genre') == 'feminin')>Feminin</option>
                    </select>
                </div>

                {{-- Age --}}
                <div>
                    <label class="form-label">Age (ans)</label>
                    <input type="number" name="age" min="0" max="150"
                           class="form-input"
                           value="{{ old('age') }}">
                </div>

                {{-- Poids --}}
                <div>
                    <label class="form-label">Poids (kg)</label>
                    <input type="number" name="poids" step="0.1" min="0"
                           class="form-input"
                           value="{{ old('poids') }}">
                </div>

                {{-- Taille --}}
                <div>
                    <label class="form-label">Taille (cm)</label>
                    <input type="number" name="taille" step="0.1" min="0"
                           class="form-input"
                           value="{{ old('taille') }}">
                </div>

                {{-- Date de l'examen --}}
                <div>
                    <label class="form-label">Date de l'examen</label>
                    <input type="date" name="date_examen"
                           class="form-input"
                           value="{{ old('date_examen', date('Y-m-d')) }}">
                </div>
            </div>
        </div><br>

        {{-- BOUTONS --}}
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('cashier.consultations.index') }}" class="btn btn-outline">
                Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Envoyer à l'administrateur
            </button>
        </div>
    </form>
</div>
@endsection