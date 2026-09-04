@extends('cashier.layouts.app')

@push('styles')
<style>
    :root {
        --primary-navy: #0F2B4F;
        --primary-navy-dark: #091E3B;
        --bg-base: #F4F5F7;
        --bg-card: #F8F9FA;
        --bg-secondary: #EEF0F3;
        --bg-hover: #E8EAEE;
        --text-primary: #1A1A1E;
        --text-secondary: #4A4A52;
        --text-tertiary: #7A7A82;
        --border-color: #DCDEE3;
        --border-light: #E8EAEE;
        --success: #1F7B4D;
        --danger: #B32A2A;
        --warning: #A65A0E;
        --info: #0A2A6C;
    }

    .form-section {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .form-section-title {
        font-size: 0.938rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title svg {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--primary-navy);
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
        color: var(--danger);
    }
    .form-group .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: border-color 0.15s ease;
        outline: none;
    }
    .form-group .form-control:focus {
        border-color: var(--primary-navy);
    }
    .form-group .form-control.is-invalid {
        border-color: var(--danger);
    }
    .form-group .form-control.is-valid {
        border-color: var(--success);
    }
    .form-group .form-control:disabled {
        background: var(--bg-secondary);
        cursor: not-allowed;
        opacity: 0.8;
    }
    .form-group .invalid-feedback {
        color: var(--danger);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    .form-group .help-text {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
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
        border-radius: 6px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.10);
    }
    .sponsor-select-wrapper .sponsor-results.show {
        display: block;
    }
    .sponsor-select-wrapper .sponsor-results .result-item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid var(--border-light);
    }
    .sponsor-select-wrapper .sponsor-results .result-item:hover {
        background: var(--bg-hover);
    }
    .sponsor-select-wrapper .sponsor-results .result-item .avatar-sm {
        width: 2rem;
        height: 2rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.7rem;
        background: var(--primary-navy);
        color: white;
        flex-shrink: 0;
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-info {
        flex: 1;
        min-width: 0;
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-detail {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .sponsor-select-wrapper .sponsor-results .result-item .result-code {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--primary-navy);
        background: rgba(15, 43, 79, 0.08);
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        white-space: nowrap;
    }

    .sponsor-selected {
        background: rgba(31, 123, 77, 0.06);
        border: 1.5px solid var(--success);
        border-radius: 6px;
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
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        background: var(--primary-navy);
        color: white;
        flex-shrink: 0;
    }
    .sponsor-selected .sponsor-info-text {
        flex: 1;
        min-width: 0;
    }
    .sponsor-selected .sponsor-name-text {
        font-weight: 600;
        color: var(--text-primary);
    }
    .sponsor-selected .sponsor-detail-text {
        font-size: 0.813rem;
        color: var(--text-secondary);
    }
    .sponsor-selected .btn-remove-sponsor {
        background: none;
        border: none;
        color: var(--danger);
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        flex-shrink: 0;
    }
    .sponsor-selected .btn-remove-sponsor:hover {
        background: rgba(179, 42, 42, 0.08);
    }
    .sponsor-selected .btn-remove-sponsor svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-warning {
        background: #FEF1E6;
        color: var(--warning);
        border-color: #FADCB8;
    }
    .badge-success {
        background: #E6F4EC;
        color: var(--success);
        border-color: #B8DFCC;
    }
    .badge-info {
        background: #E8EDF5;
        color: var(--primary-navy);
        border-color: #C8D4E3;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .btn-secondary:hover {
        background: var(--bg-hover);
    }

    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background: var(--primary-navy);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
    }
    .btn-submit:hover {
        background: var(--primary-navy-dark);
    }
    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .info-banner {
        background: rgba(10, 42, 108, 0.06);
        border: 1px solid rgba(10, 42, 108, 0.15);
        border-radius: 6px;
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
        color: var(--info);
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    .section-company {
        background: rgba(15, 43, 79, 0.03);
        border: 1.5px solid rgba(15, 43, 79, 0.15);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .section-company .form-section-title {
        border-bottom-color: rgba(15, 43, 79, 0.15);
        color: var(--primary-navy);
    }
    .section-company .form-control:disabled {
        background: rgba(15, 43, 79, 0.04);
        border-color: rgba(15, 43, 79, 0.10);
        color: var(--text-primary);
    }

    .cashier-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(15, 43, 79, 0.08);
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-navy);
    }

    .code-validation {
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        font-size: 0.813rem;
        display: none;
    }
    .code-validation.show {
        display: block;
    }
    .code-validation.valid {
        background: rgba(31, 123, 77, 0.06);
        border: 1px solid rgba(31, 123, 77, 0.15);
        color: var(--success);
    }
    .code-validation.invalid {
        background: rgba(179, 42, 42, 0.06);
        border: 1px solid rgba(179, 42, 42, 0.15);
        color: var(--danger);
    }
    .code-validation .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(15, 43, 79, 0.15);
        border-top-color: var(--primary-navy);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-right: 0.5rem;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .package-preview {
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        border-radius: 6px;
        padding: 1rem;
        margin-top: 0.5rem;
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
        .sponsor-selected {
            flex-wrap: wrap;
        }
        .sponsor-select-wrapper .sponsor-results {
            max-height: 180px;
        }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .form-group .form-control { font-size: 0.813rem; padding: 0.4rem 0.6rem; }
    }
</style>
@endpush

@section('title', 'Nouveau Membre - Adhésion')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Enregistrer un nouveau membre</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Saisie des informations du distributeur</p>
        </div>
        <div class="flex gap-2">
            <span class="cashier-badge">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ auth()->user()->name }}
            </span>
            <a href="{{ route('cashier.members') }}" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    {{-- INFO BANNER --}}
    <div class="info-banner">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <strong>Formulaire d'adhésion :</strong> Enregistrement des informations du nouveau distributeur.
            <br>
            <span class="text-xs text-[var(--text-tertiary)]">Les champs marqués d'un <span class="text-[var(--danger)]">*</span> sont obligatoires.</span>
        </div>
    </div>

    {{-- FORMULAIRE --}}
    <form action="{{ route('cashier.members.store') }}" method="POST" id="memberForm">
        @csrf

        <!-- Informations personnelles -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations personnelles
            </div>

            <div class="form-group">
                <label for="name">Nom complet <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
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
                <label for="address">Adresse</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Téléphone <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                    <div class="help-text">Si non renseigné, un email sera généré automatiquement.</div>
                </div>
            </div>

            <div class="form-group">
                <label for="profession">Profession</label>
                <input type="text" id="profession" name="profession" class="form-control" value="{{ old('profession') }}">
            </div>

            <div class="form-group">
                <label for="identity_number">Numéro d'identité</label>
                <input type="text" id="identity_number" name="identity_number" class="form-control" value="{{ old('identity_number') }}">
            </div>
        </div>

        <!-- Coordonnées bancaires -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Coordonnées bancaires
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bank_name">Nom de la banque</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                </div>
                <div class="form-group">
                    <label for="account_number">Numéro de compte</label>
                    <input type="text" id="account_number" name="account_number" class="form-control" value="{{ old('account_number') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="account_holder">Nom du titulaire</label>
                    <input type="text" id="account_holder" name="account_holder" class="form-control" value="{{ old('account_holder') }}">
                </div>
                <div class="form-group">
                    <label for="mobile_money">Mobile Money</label>
                    <input type="text" id="mobile_money" name="mobile_money" class="form-control" value="{{ old('mobile_money') }}">
                </div>
            </div>
        </div>

        <!-- Package d'adhésion -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Package d'adhésion <span class="required">*</span>
            </div>

            <p class="text-sm text-[var(--text-secondary)] mb-3">Sélectionnez le package pour déterminer le grade et les PV initiaux.</p>

            <div class="form-group">
                <label for="package_id">Package <span class="required">*</span></label>
                <select id="package_id" name="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                    <option value="">-- Sélectionnez --</option>
                    @foreach($packages as $package)
                        @php
                            $rank = \App\Models\Rank::where('min_pv', '<=', ($package->pv_value ?? 0))
                                ->where('is_active', true)
                                ->orderBy('min_pv', 'desc')
                                ->first();
                            $rankName = $rank->name ?? 'Distributeur';
                            $rankLevel = $rank->level ?? 1;
                        @endphp
                        <option value="{{ $package->id }}" 
                                data-pv="{{ $package->pv_value ?? 0 }}"
                                data-rank="{{ $rankName }}"
                                data-rank-level="{{ $rankLevel }}"
                                data-price="{{ $package->price ?? 0 }}"
                                {{ old('package_id') == $package->id ? 'selected' : '' }}>
                            {{ $package->name }} 
                            @if($package->price) - ${{ number_format($package->price, 2) }} @endif
                            @if($package->pv_value) - {{ $package->pv_value }} PV @endif
                            @if($rankName) - {{ $rankName }} @endif
                        </option>
                    @endforeach
                </select>
                @error('package_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="packagePreview" class="package-preview" style="display: none;">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div>
                        <span class="text-xs text-[var(--text-secondary)]">Package</span>
                        <p class="font-semibold text-sm" id="previewName">-</p>
                    </div>
                    <div>
                        <span class="text-xs text-[var(--text-secondary)]">Prix</span>
                        <p class="font-semibold text-sm text-[var(--success)]" id="previewPrice">-</p>
                    </div>
                    <div>
                        <span class="text-xs text-[var(--text-secondary)]">PV</span>
                        <p class="font-semibold text-sm text-[var(--primary-navy)]" id="previewPV">-</p>
                    </div>
                    <div>
                        <span class="text-xs text-[var(--text-secondary)]">Grade</span>
                        <p class="font-semibold text-sm text-[var(--info)]" id="previewRank">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sélection du parrain -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Parrain <span class="required">*</span>
            </div>

            <p class="text-sm text-[var(--text-secondary)] mb-3">Recherchez par code ou nom.</p>

            <div class="form-group sponsor-select-wrapper">
                <label for="sponsor_search">Rechercher</label>
                <div>
                    <input type="text" id="sponsor_search" class="form-control" autocomplete="off" placeholder="Code ou nom du parrain...">
                    <div id="sponsorResults" class="sponsor-results"></div>
                </div>
                <input type="hidden" id="sponsor_id" name="sponsor_code" value="{{ old('sponsor_code') }}">
                @error('sponsor_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="sponsorSelected" class="sponsor-selected {{ old('sponsor_code') ? 'active' : '' }}">
                <div class="sponsor-avatar" id="selectedAvatar">S</div>
                <div class="sponsor-info-text">
                    <div class="sponsor-name-text" id="selectedName">-</div>
                    <div class="sponsor-detail-text" id="selectedDetail">-</div>
                </div>
                <button type="button" class="btn-remove-sponsor" onclick="clearSponsor()" title="Changer">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Code membre -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Code membre <span class="required">*</span>
            </div>

            <div class="form-group">
                <label for="member_code">Code</label>
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
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Générer
                    </button>
                </div>
                <div id="codeValidation" class="code-validation">
                    <span id="codeValidationMessage">Vérification...</span>
                </div>
                <div class="help-text">Le code doit être unique et contenir 6 chiffres.</div>
            </div>
        </div>

        <!-- Signature -->
        <div class="form-section">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Signature <span class="required">*</span>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label for="signature_name">Nom du signataire <span class="required">*</span></label>
                    <input type="text" id="signature_name" name="signature_name" class="form-control @error('signature_name') is-invalid @enderror" value="{{ old('signature_name') }}" required>
                    @error('signature_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="signature_date">Date <span class="required">*</span></label>
                    <input type="date" id="signature_date" name="signature_date" class="form-control @error('signature_date') is-invalid @enderror" value="{{ old('signature_date', date('Y-m-d')) }}" required>
                    @error('signature_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="signature_location">Lieu</label>
                    <input type="text" id="signature_location" name="signature_location" class="form-control" value="{{ old('signature_location', '') }}">
                </div>
            </div>

            <div class="text-sm text-[var(--text-tertiary)]" style="padding: 0.5rem; background: var(--bg-secondary); border-radius: 4px;">
                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Le membre a signé le document physique.
            </div>
        </div>

        <!-- Entreprise -->
        <div class="section-company">
            <div class="form-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Réservé à l'entreprise
                <span style="margin-left: auto; font-size: 0.7rem; font-weight: 400; color: var(--text-tertiary);">
                    Attribution par le caissier
                </span>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Date d'activation</label>
                    <input type="text" class="form-control" value="{{ date('d/m/Y') }}" disabled>
                </div>
                <div class="form-group">
                    <label>Validation</label>
                    <input type="text" class="form-control" value="Validé" disabled style="color: var(--success); font-weight: 600;">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <input type="text" class="form-control" value="Actif" disabled style="color: var(--success); font-weight: 600;">
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.5rem;">
                <label>Responsable</label>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; background: rgba(15, 43, 79, 0.06); border-radius: 6px; border: 1px solid rgba(15, 43, 79, 0.10);">
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 4px; background: var(--primary-navy); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem; flex-shrink: 0;">
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
                    <span style="font-size: 0.65rem; font-weight: 600; color: var(--primary-navy); background: rgba(15, 43, 79, 0.08); padding: 0.125rem 0.5rem; border-radius: 4px;">
                        CAISSIER
                    </span>
                </div>
                <input type="hidden" name="cashier_id" value="{{ auth()->id() }}">
                <input type="hidden" name="cashier_name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="cashier_email" value="{{ auth()->user()->email }}">
                <div class="help-text">Le caissier est automatiquement enregistré comme responsable.</div>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Enregistrer
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
function generateMemberCode() {
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += Math.floor(Math.random() * 10);
    }
    document.getElementById('member_code').value = code;
    document.getElementById('member_code').focus();
    document.getElementById('member_code').select();
    checkMemberCode(code);
}

document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const preview = document.getElementById('packagePreview');
    const previewName = document.getElementById('previewName');
    const previewPrice = document.getElementById('previewPrice');
    const previewPV = document.getElementById('previewPV');
    const previewRank = document.getElementById('previewRank');

    if (packageSelect) {
        packageSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value) {
                preview.style.display = 'block';
                previewName.textContent = selectedOption.text.split(' - ')[0] || '-';
                previewPrice.textContent = '$' + parseFloat(selectedOption.dataset.price || 0).toFixed(2);
                previewPV.textContent = selectedOption.dataset.pv || '0';
                previewRank.textContent = selectedOption.dataset.rank || 'Distributeur';
            } else {
                preview.style.display = 'none';
            }
        });
    }
});

const memberCodeInput = document.getElementById('member_code');
const codeValidation = document.getElementById('codeValidation');
const codeValidationMessage = document.getElementById('codeValidationMessage');
let codeCheckTimeout = null;
let isCodeValid = false;

memberCodeInput.addEventListener('input', function() {
    const code = this.value.trim();
    this.value = code;
    clearTimeout(codeCheckTimeout);

    if (code.length === 0) {
        codeValidation.classList.remove('show', 'valid', 'invalid');
        this.classList.remove('is-valid', 'is-invalid');
        isCodeValid = false;
        validateForm();
        return;
    }

    if (!/^\d{6}$/.test(code)) {
        codeValidation.className = 'code-validation show invalid';
        codeValidationMessage.innerHTML = ' Le code doit contenir 6 chiffres';
        memberCodeInput.classList.remove('is-valid');
        memberCodeInput.classList.add('is-invalid');
        isCodeValid = false;
        validateForm();
        return;
    }

    codeValidation.className = 'code-validation show';
    codeValidationMessage.innerHTML = '<span class="spinner"></span> Vérification...';
    this.classList.remove('is-valid', 'is-invalid');
    isCodeValid = false;

    codeCheckTimeout = setTimeout(() => {
        checkMemberCode(code);
    }, 500);
});

function checkMemberCode(code) {
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
        .catch(() => {
            codeValidation.className = 'code-validation show invalid';
            codeValidationMessage.innerHTML = ' Erreur de vérification';
            memberCodeInput.classList.remove('is-valid');
            memberCodeInput.classList.add('is-invalid');
            isCodeValid = false;
            validateForm();
        });
}

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
                    <div class="result-item" onclick="selectSponsor('${sponsor.sponsor_id}', '${sponsor.name.replace(/'/g, "\\'")}', '${sponsor.email || ''}', '${sponsor.phone || ''}')">
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
        .catch(() => {
            resultsContainer.innerHTML = `
                <div class="result-item" style="justify-content:center; color: var(--danger);">
                    Erreur lors de la recherche
                </div>
            `;
            resultsContainer.classList.add('show');
        });
}

function selectSponsor(sponsorCode, name, email, phone) {
    document.getElementById('sponsor_id').value = sponsorCode;
    selectedAvatar.textContent = name.charAt(0).toUpperCase();
    selectedName.textContent = name;
    const details = [];
    if (sponsorCode) details.push('Code: ' + sponsorCode);
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

document.addEventListener('click', function(e) {
    if (!e.target.closest('.sponsor-select-wrapper')) {
        resultsContainer.classList.remove('show');
    }
});

function validateForm() {
    const sponsorCode = document.getElementById('sponsor_id').value;
    const memberCode = document.getElementById('member_code').value.trim();
    const packageId = document.getElementById('package_id').value;
    const submitBtn = document.getElementById('submitBtn');
    const isValid = sponsorCode && memberCode && isCodeValid && packageId;
    submitBtn.disabled = !isValid;
}

document.addEventListener('DOMContentLoaded', function() {
    validateForm();
    const oldCode = document.getElementById('member_code').value;
    if (oldCode) {
        checkMemberCode(oldCode);
    }
});

document.getElementById('memberForm').addEventListener('submit', function(e) {
    const sponsorCode = document.getElementById('sponsor_id').value;
    const memberCode = document.getElementById('member_code').value.trim();
    const packageId = document.getElementById('package_id').value;

    if (!sponsorCode) {
        e.preventDefault();
        alert('Veuillez sélectionner un parrain.');
        return false;
    }

    if (!memberCode) {
        e.preventDefault();
        alert('Veuillez saisir un code membre.');
        return false;
    }

    if (!packageId) {
        e.preventDefault();
        alert('Veuillez sélectionner un package.');
        return false;
    }

    if (!isCodeValid) {
        e.preventDefault();
        alert('Le code membre est invalide ou déjà utilisé.');
        return false;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="inline-block w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Enregistrement...
    `;
});
</script>
@endpush