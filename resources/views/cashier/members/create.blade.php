@extends('cashier.layouts.app')

@push('styles')
<style>
    .form-section {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title svg {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--primary-500);
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-group .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .form-group .form-control:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .form-group .form-control.is-invalid {
        border-color: #ef4444;
    }
    .form-group .form-control.is-valid {
        border-color: #22c55e;
    }
    .form-group .form-control:disabled {
        background: var(--bg-secondary);
        cursor: not-allowed;
        opacity: 0.8;
    }
    .form-group .invalid-feedback {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    .form-group .help-text {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
    }
    .form-group .help-text .text-success {
        color: #22c55e;
    }
    .form-group .help-text .text-danger {
        color: #ef4444;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }
    
    /* Sélection du parrain */
    .sponsor-select-wrapper {
        position: relative;
    }
    .sponsor-select-wrapper .sponsor-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .sponsor-select-wrapper .sponsor-results.show {
        display: block;
    }
    .sponsor-select-wrapper .sponsor-results .result-item {
        padding: 0.6rem 0.75rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-light);
    }
    .sponsor-select-wrapper .sponsor-results .result-item:hover {
        background: var(--bg-hover);
    }
    .sponsor-select-wrapper .sponsor-results .result-item .avatar-sm {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.7rem;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-info {
        flex: 1;
        min-width: 0;
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-info .result-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-info .result-detail {
        font-size: 0.75rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-code {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--primary-500);
        background: rgba(90, 182, 56, 0.1);
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    
    /* Parrain sélectionné */
    .sponsor-selected {
        background: rgba(90, 182, 56, 0.08);
        border: 2px solid var(--primary-500);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        display: none;
        align-items: center;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    .sponsor-selected.active {
        display: flex;
    }
    .sponsor-selected .sponsor-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .sponsor-selected .sponsor-info-text {
        flex: 1;
        min-width: 0;
    }
    .sponsor-selected .sponsor-info-text .sponsor-name-text {
        font-weight: 600;
        color: var(--text-primary);
    }
    .sponsor-selected .sponsor-info-text .sponsor-detail-text {
        font-size: 0.813rem;
        color: var(--text-secondary);
    }
    .sponsor-selected .btn-remove-sponsor {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .sponsor-selected .btn-remove-sponsor:hover {
        background: rgba(239, 68, 68, 0.1);
    }
    .sponsor-selected .btn-remove-sponsor svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    /* Badge pour le statut */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    /* Bouton de soumission */
    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .info-banner {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    .info-banner svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #3b82f6;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
    
    /* Section entreprise */
    .section-company {
        background: linear-gradient(135deg, rgba(90, 182, 56, 0.05), rgba(90, 182, 56, 0.02));
        border: 2px solid rgba(90, 182, 56, 0.2);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
    }
    .section-company::before {
        content: '🏢';
        position: absolute;
        top: -0.75rem;
        right: 1.5rem;
        font-size: 1.5rem;
        background: var(--bg-card);
        padding: 0 0.5rem;
    }
    .section-company .form-section-title {
        border-bottom-color: rgba(90, 182, 56, 0.3);
        color: var(--primary-700);
    }
    .section-company .form-group label {
        color: var(--text-secondary);
    }
    .section-company .form-control:disabled {
        background: rgba(90, 182, 56, 0.05);
        border-color: rgba(90, 182, 56, 0.2);
        color: var(--text-primary);
    }
    .cashier-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(90, 182, 56, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-500);
    }
    
    /* Validation du code en temps réel */
    .code-validation {
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.813rem;
        display: none;
    }
    .code-validation.show {
        display: block;
    }
    .code-validation.valid {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    .code-validation.invalid {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    .code-validation .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(90, 182, 56, 0.2);
        border-top-color: var(--primary-500);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-right: 0.5rem;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    @media (max-width: 640px) {
        .form-row, .form-row-3 {
            grid-template-columns: 1fr;
        }
        .form-section {
            padding: 1rem;
        }
        .section-company {
            padding: 1rem;
        }
        .section-company::before {
            right: 0.75rem;
            font-size: 1.2rem;
        }
        .sponsor-selected {
            flex-wrap: wrap;
        }
        .sponsor-select-wrapper .sponsor-results {
            max-height: 180px;
        }
        .sponsor-select-wrapper .sponsor-results .result-item .result-detail {
            white-space: normal;
        }
    }
</style>
@endpush

@section('title', 'Nouveau Membre - Adhésion')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Enregistrer un nouveau membre
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Saisie des informations du distributeur dans le système
            </p>
        </div>
        <div class="flex gap-2">
            <span class="cashier-badge">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ auth()->user()->name }}
            </span>
            <a href="{{ route('cashier.members') }}" class="btn btn-secondary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Info banner -->
    <div class="info-banner animate-fadeInUp delay-1">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <strong>Formulaire d'adhésion physique :</strong> 
            Le membre doit remplir et signer le document papier. Ce formulaire sert à enregistrer ses informations dans le système.
            <br>
            <span class="text-xs text-[var(--text-tertiary)]">
                Tous les champs marqués d'un <span class="text-danger">*</span> sont obligatoires.
            </span>
        </div>
    </div>

    <!-- Formulaire -->
    <form action="{{ route('cashier.members.store') }}" method="POST" id="memberForm" class="animate-fadeInUp delay-2">
        @csrf

        <!-- Informations personnelles -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations personnelles du distributeur
            </div>

            <div class="form-group">
                <label for="name">Nom complet <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Date de naissance</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                </div>
                <div class="form-group">
                    <label for="gender">Sexe</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Sélectionner</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculin</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Féminin</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Adresse complète</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Téléphone <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" >
                    <div class="help-text">Si non renseigné, un email sera généré automatiquement.</div>
                </div>
            </div>

            <div class="form-group">
                <label for="profession">Profession</label>
                <input type="text" id="profession" name="profession" class="form-control" value="{{ old('profession') }}" >
            </div>

            <div class="form-group">
                <label for="identity_number">Numéro d'identité</label>
                <input type="text" id="identity_number" name="identity_number" class="form-control" value="{{ old('identity_number') }}" >
            </div>
        </div>

        <!-- Coordonnées bancaires -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Coordonnées bancaires
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bank_name">Nom de la banque</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name') }}" 
                           >
                </div>
                <div class="form-group">
                    <label for="account_number">Numéro de compte</label>
                    <input type="text" id="account_number" name="account_number" class="form-control" value="{{ old('account_number') }}" 
                          >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="account_holder">Nom du titulaire</label>
                    <input type="text" id="account_holder" name="account_holder" class="form-control" value="{{ old('account_holder') }}" 
                           >
                </div>
                <div class="form-group">
                    <label for="mobile_money">Mobile Money</label>
                    <input type="text" id="mobile_money" name="mobile_money" class="form-control" value="{{ old('mobile_money') }}" 
                           >
                </div>
            </div>
        </div>

        <!-- Sélection du parrain -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Informations sur le parrain <span class="required">*</span>
            </div>

            <p class="text-sm text-[var(--text-secondary)] mb-3">
                Recherchez et sélectionnez le parrain du nouveau membre par code ou son nom.
            </p>

            <div class="form-group sponsor-select-wrapper">
                <label for="sponsor_search">Rechercher un parrain</label>
                <div>
                    <input type="text" id="sponsor_search" class="form-control"  
                           autocomplete="off">
                    <div id="sponsorResults" class="sponsor-results"></div>
                </div>
                
                <input type="hidden" id="sponsor_id" name="sponsor_id" value="{{ old('sponsor_id') }}">
                
                @error('sponsor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Parrain sélectionné -->
            <div id="sponsorSelected" class="sponsor-selected {{ old('sponsor_id') ? 'active' : '' }}">
                <div class="sponsor-avatar" id="selectedAvatar">S</div>
                <div class="sponsor-info-text">
                    <div class="sponsor-name-text" id="selectedName">-</div>
                    <div class="sponsor-detail-text" id="selectedDetail">-</div>
                </div>
                <button type="button" class="btn-remove-sponsor" onclick="clearSponsor()" title="Changer de parrain">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Informations de signature (document physique) -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Informations de signature (document physique)
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label for="signature_name">Nom du signataire <span class="required">*</span></label>
                    <input type="text" id="signature_name" name="signature_name" class="form-control @error('signature_name') is-invalid @enderror" 
                           value="{{ old('signature_name') }}" required>
                    @error('signature_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="signature_date">Date de signature <span class="required">*</span></label>
                    <input type="date" id="signature_date" name="signature_date" class="form-control @error('signature_date') is-invalid @enderror" 
                           value="{{ old('signature_date', date('Y-m-d')) }}" required>
                    @error('signature_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="signature_location">Lieu de signature</label>
                    <input type="text" id="signature_location" name="signature_location" class="form-control" 
                           value="{{ old('signature_location', '') }}">
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.75rem;">
                <div class="text-sm text-[var(--text-tertiary)]" style="padding: 0.5rem; background: var(--bg-secondary); border-radius: var(--radius-sm);">
                    <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Le membre a signé le document physique. Ces informations sont enregistrées pour référence.
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- RÉSERVÉ POUR L'ENTREPRISE - Attribution du code membre -->
        <!-- ============================================================ -->
        <div class="section-company">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Réservé pour l'entreprise
                <span style="margin-left: auto; font-size: 0.7rem; font-weight: 400; color: var(--text-tertiary);">
                    Attribution par le caissier
                </span>
            </div>

            <!-- CODE MEMBRE - Attribué par le caissier -->
            <div class="form-group">
                <label for="member_code">Code membre <span class="required">*</span></label>
                <div class="form-row" style="gap: 0.5rem;">
                    <div style="flex: 1;">
                        <input type="text" id="member_code" name="member_code" 
                               class="form-control @error('member_code') is-invalid @enderror" 
                               value="{{ old('member_code') }}" 
                               required 
                               style="text-transform: uppercase; font-family: monospace; letter-spacing: 0.5px;">
                        @error('member_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="generateMemberCode()" style="white-space: nowrap; padding: 0.5rem 1rem;">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Générer
                    </button>
                </div>
                <div id="codeValidation" class="code-validation">
                    <span id="codeValidationMessage">Vérification en cours...</span>
                </div>
                <div class="help-text">
                    <svg class="inline-block w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Le code doit être unique. Une validation en temps réel vérifie sa disponibilité.
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Date d'activation</label>
                    <input type="text" class="form-control" value="{{ date('d/m/Y') }}" disabled>
                </div>
                <div class="form-group">
                    <label>Validation</label>
                    <input type="text" class="form-control" value=" En attente" disabled style="color: #f59e0b; font-weight: 600;">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <input type="text" class="form-control" value=" Actif" disabled style="color: #22c55e; font-weight: 600;">
                </div>
            </div>

            <!-- Attribué au caissier connecté -->
            <div class="form-group" style="margin-top: 0.5rem;">
                <label>Responsable de l'enregistrement</label>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; background: rgba(90, 182, 56, 0.08); border-radius: var(--radius-md); border: 1px solid rgba(90, 182, 56, 0.2);">
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem; flex-shrink: 0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--text-primary);">
                            {{ auth()->user()->name }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                            {{ auth()->user()->email }}
                            @if(auth()->user()->phone)
                                · {{ auth()->user()->phone }}
                            @endif
                        </div>
                    </div>
                    <span style="font-size: 0.65rem; font-weight: 600; color: var(--primary-500); background: rgba(90, 182, 56, 0.1); padding: 0.125rem 0.5rem; border-radius: 9999px;">
                        CAISSIER
                    </span>
                </div>
                <input type="hidden" name="cashier_id" value="{{ auth()->id() }}">
                <input type="hidden" name="cashier_name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="cashier_email" value="{{ auth()->user()->email }}">
                <div class="help-text">
                    <svg class="inline-block w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Le caissier connecté est automatiquement enregistré comme responsable de cette adhésion.
                </div>
            </div>
        </div>

        <!-- Bouton de soumission -->
        <button type="submit" class="btn-submit" id="submitBtn">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Enregistrer le membre dans le système
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // ============================================================
    // GÉNÉRATION DU CODE MEMBRE
    // ============================================================
    function generateMemberCode() {
    // Générer un nombre aléatoire à 6 chiffres
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += Math.floor(Math.random() * 10);
    }
    
    document.getElementById('member_code').value = code;
    document.getElementById('member_code').focus();
    document.getElementById('member_code').select();
    
    // Vérifier automatiquement le code
    checkMemberCode(code);
}

    // ============================================================
    // VALIDATION DU CODE MEMBRE EN TEMPS RÉEL
    // ============================================================
    const memberCodeInput = document.getElementById('member_code');
    const codeValidation = document.getElementById('codeValidation');
    const codeValidationMessage = document.getElementById('codeValidationMessage');
    let codeCheckTimeout = null;
    let isCodeValid = false;

    memberCodeInput.addEventListener('input', function() {
    const code = this.value.trim();
    this.value = code; // Garder tel quel (pas de majuscules)
    
    clearTimeout(codeCheckTimeout);
    
    if (code.length === 0) {
        codeValidation.classList.remove('show', 'valid', 'invalid');
        this.classList.remove('is-valid', 'is-invalid');
        isCodeValid = false;
        validateForm();
        return;
    }
    
    //  Vérifier que c'est un nombre à 6 chiffres
    if (!/^\d{6}$/.test(code)) {
        codeValidation.className = 'code-validation show invalid';
        codeValidationMessage.innerHTML = ' Le code doit contenir exactement 6 chiffres (ex: 514548)';
        memberCodeInput.classList.remove('is-valid');
        memberCodeInput.classList.add('is-invalid');
        isCodeValid = false;
        validateForm();
        return;
    }
    
    codeValidation.className = 'code-validation show';
    codeValidationMessage.innerHTML = '<span class="spinner"></span> Vérification du code...';
    this.classList.remove('is-valid', 'is-invalid');
    isCodeValid = false;
    
    codeCheckTimeout = setTimeout(() => {
        checkMemberCode(code);
    }, 500);
});
    function checkMemberCode(code) {
        if (code.length < 3) {
            codeValidation.className = 'code-validation show invalid';
            codeValidationMessage.innerHTML = ' Le code est trop court (minimum 3 caractères)';
            memberCodeInput.classList.remove('is-valid');
            memberCodeInput.classList.add('is-invalid');
            isCodeValid = false;
            validateForm();
            return;
        }
        
        fetch('{{ route("cashier.check-member-code") }}?code=' + encodeURIComponent(code))
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    codeValidation.className = 'code-validation show valid';
                    codeValidationMessage.innerHTML = ' ' + data.message;
                    memberCodeInput.classList.remove('is-invalid');
                    memberCodeInput.classList.add('is-valid');
                    isCodeValid = true;
                } else {
                    codeValidation.className = 'code-validation show invalid';
                    codeValidationMessage.innerHTML = ' ' + data.message;
                    memberCodeInput.classList.remove('is-valid');
                    memberCodeInput.classList.add('is-invalid');
                    isCodeValid = false;
                }
                validateForm();
            })
            .catch(error => {
                console.error('Erreur:', error);
                codeValidation.className = 'code-validation show invalid';
                codeValidationMessage.innerHTML = ' Erreur de vérification du code';
                memberCodeInput.classList.remove('is-valid');
                memberCodeInput.classList.add('is-invalid');
                isCodeValid = false;
                validateForm();
            });
    }

    // ============================================================
    // RECHERCHE DE PARRAIN
    // ============================================================
    const searchInput = document.getElementById('sponsor_search');
    const resultsContainer = document.getElementById('sponsorResults');
    const selectedContainer = document.getElementById('sponsorSelected');
    const selectedAvatar = document.getElementById('selectedAvatar');
    const selectedName = document.getElementById('selectedName');
    const selectedDetail = document.getElementById('selectedDetail');
    const sponsorIdInput = document.getElementById('sponsor_id');
    let searchTimeout = null;

    function searchSponsors(query) {
        if (query.length < 2) {
            resultsContainer.classList.remove('show');
            return;
        }

        fetch('{{ route("cashier.search-sponsors") }}?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="result-item" style="justify-content:center; color: var(--text-tertiary);">
                            Aucun parrain trouvé
                        </div>
                    `;
                    resultsContainer.classList.add('show');
                    return;
                }

                let html = '';
                data.forEach(sponsor => {
                    const initial = sponsor.name.charAt(0).toUpperCase();
                    const details = [];
                    if (sponsor.email) details.push(sponsor.email);
                    if (sponsor.phone) details.push(sponsor.phone);
                    
                    html += `
                        <div class="result-item" onclick="selectSponsor(${sponsor.id}, '${sponsor.name.replace(/'/g, "\\'")}', '${sponsor.sponsor_id || ''}', '${sponsor.email || ''}', '${sponsor.phone || ''}')">
                            <div class="avatar-sm">${initial}</div>
                            <div class="result-info">
                                <div class="result-name">${sponsor.name}</div>
                                <div class="result-detail">${details.join(' | ')}</div>
                            </div>
                            <span class="result-code">${sponsor.sponsor_id || 'N/A'}</span>
                        </div>
                    `;
                });
                resultsContainer.innerHTML = html;
                resultsContainer.classList.add('show');
            })
            .catch(error => {
                console.error('Erreur:', error);
                resultsContainer.innerHTML = `
                    <div class="result-item" style="justify-content:center; color: #ef4444;">
                        Erreur lors de la recherche
                    </div>
                `;
                resultsContainer.classList.add('show');
            });
    }

    function selectSponsor(id, name, sponsorId, email, phone) {
        sponsorIdInput.value = id;
        selectedAvatar.textContent = name.charAt(0).toUpperCase();
        selectedName.textContent = name;
        
        const details = [];
        if (sponsorId) details.push('Code: ' + sponsorId);
        if (email) details.push(email);
        if (phone) details.push(phone);
        selectedDetail.textContent = details.join(' | ');
        
        selectedContainer.classList.add('active');
        resultsContainer.classList.remove('show');
        searchInput.value = name;
        searchInput.classList.add('is-valid');
        searchInput.classList.remove('is-invalid');
        
        validateForm();
    }

    function clearSponsor() {
        sponsorIdInput.value = '';
        selectedContainer.classList.remove('active');
        searchInput.value = '';
        searchInput.classList.remove('is-valid');
        searchInput.classList.remove('is-invalid');
        resultsContainer.classList.remove('show');
        validateForm();
    }

    // Écouter la saisie
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length === 0) {
            resultsContainer.classList.remove('show');
            if (!sponsorIdInput.value) {
                searchInput.classList.remove('is-valid');
            }
            return;
        }
        
        if (sponsorIdInput.value && selectedName.textContent.toLowerCase() === query.toLowerCase()) {
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchSponsors(query);
        }, 300);
    });

    // Fermer les résultats en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sponsor-select-wrapper')) {
            resultsContainer.classList.remove('show');
        }
    });

    // ============================================================
    // VALIDATION DU FORMULAIRE
    // ============================================================
    function validateForm() {
        const sponsorId = document.getElementById('sponsor_id').value;
        const memberCode = document.getElementById('member_code').value.trim();
        const submitBtn = document.getElementById('submitBtn');
        
        // Le formulaire est valide si : parrain sélectionné + code valide + code non vide
        const isValid = sponsorId && memberCode && isCodeValid;
        submitBtn.disabled = !isValid;
    }

    document.addEventListener('DOMContentLoaded', function() {
        validateForm();
        
        // Vérifier si un code est déjà présent (old value)
        const oldCode = document.getElementById('member_code').value;
        if (oldCode) {
            checkMemberCode(oldCode);
        }
    });

    // ============================================================
    // SOUMISSION DU FORMULAIRE
    // ============================================================
    document.getElementById('memberForm').addEventListener('submit', function(e) {
        const sponsorId = document.getElementById('sponsor_id').value;
        const memberCode = document.getElementById('member_code').value.trim();
        
        if (!sponsorId) {
            e.preventDefault();
            alert('Veuillez sélectionner un parrain.');
            return false;
        }
        
        if (!memberCode) {
            e.preventDefault();
            alert('Veuillez saisir un code membre.');
            return false;
        }
        
        if (!isCodeValid) {
            e.preventDefault();
            alert('Le code membre n\'est pas valide ou déjà utilisé.');
            return false;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="inline-block w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Enregistrement en cours...
        `;
    });
</script>
@endpush
