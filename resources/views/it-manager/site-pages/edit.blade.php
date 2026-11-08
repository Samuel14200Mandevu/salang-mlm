@extends('layouts.app')

@section('title', 'Pages site')

@push('styles')
<style>
/* Pages site desktop — mêmes primitives que l’accueil IT (.it-dash) */
.it-manager-shell .it-site-desk .card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.it-manager-shell .it-site-desk .card + .card {
    margin-top: 0.75rem;
}
.it-manager-shell .it-site-desk .card-head {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--border-color);
}
.it-manager-shell .it-site-desk .card-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.it-manager-shell .it-site-desk .card-icon.is-primary {
    background: rgba(30, 93, 173, 0.14);
    color: #1E5DAD;
}
.it-manager-shell .it-site-desk .card-icon.is-success {
    background: rgba(28, 126, 74, 0.14);
    color: #1C7E4A;
}
.it-manager-shell .it-site-desk .card-icon.is-warning {
    background: rgba(245, 166, 35, 0.18);
    color: #B54708;
}
.it-manager-shell .it-site-desk .card-title {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
}
.it-manager-shell .it-site-desk .card-sub {
    margin: 0.15rem 0 0;
    font-size: 0.75rem;
    color: var(--text-secondary);
}
.it-manager-shell .it-site-desk .field + .field {
    margin-top: 0.85rem;
}
.it-manager-shell .it-site-desk .field label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-site-desk .field input,
.it-manager-shell .it-site-desk .field textarea {
    width: 100%;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.813rem;
    padding: 0.5rem 0.75rem;
    outline: none;
}
.it-manager-shell .it-site-desk .field textarea {
    resize: vertical;
    min-height: 4.5rem;
}
.it-manager-shell .it-site-desk .field input:focus,
.it-manager-shell .it-site-desk .field textarea:focus {
    border-color: var(--primary-blue, #1E5DAD);
    box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.12);
    background: var(--bg-card);
}
.it-manager-shell .it-site-desk .field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}
.it-manager-shell .it-site-desk .form-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 0.25rem;
    padding: 1rem 1.25rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.it-manager-shell .it-site-desk .dest-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}
.it-manager-shell .it-site-desk .dest-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
}
.it-manager-shell .it-site-desk .dest-label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-site-desk .dest-value {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
}
@media (max-width: 768px) {
    .it-manager-shell .it-site-desk .field-grid,
    .it-manager-shell .it-site-desk .dest-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
@php
    $emailPreview = old('support_email', $supportEmail);
    $phonePreview = old('support_phone', $supportPhone);
    $feePreview = old('membership_fee_usd', $membershipFee);
@endphp

{{-- Desktop : langage accueil IT --}}
<div class="it-site-desk it-desktop-only space-y-4 max-w-3xl">
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Pages site</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            À propos, offre et coordonnées contact.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="dest-grid animate-fadeInUp delay-1">
        <div class="dest-card">
            <p class="dest-label">Contact</p>
            <p class="dest-value truncate" title="{{ $emailPreview }}">{{ $emailPreview ?: '—' }}</p>
            <p class="text-xs text-[var(--text-tertiary)] mt-0.5">{{ $phonePreview ?: 'Téléphone non renseigné' }}</p>
        </div>
        <div class="dest-card">
            <p class="dest-label">À propos</p>
            <p class="dest-value">Qui sommes-nous</p>
            <p class="text-xs text-[var(--text-tertiary)] mt-0.5">Page publique · Mission</p>
        </div>
        <div class="dest-card">
            <p class="dest-label">Offre</p>
            <p class="dest-value">{{ $feePreview !== null && $feePreview !== '' ? number_format((float) $feePreview, 0) . ' USD' : '—' }}</p>
            <p class="text-xs text-[var(--text-tertiary)] mt-0.5">Frais d’adhésion</p>
        </div>
    </div>

    <form method="POST" action="{{ route('it-manager.site-pages.update') }}" class="animate-fadeInUp delay-2 space-y-3">
        @csrf
        @method('PUT')

        <section class="card">
            <div class="card-head">
                <span class="card-icon is-primary" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="card-title">Contact</h2>
                    <p class="card-sub">Coordonnées affichées sur le site public</p>
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="desk-support-email">Email *</label>
                    <input id="desk-support-email" type="email" name="support_email"
                           value="{{ old('support_email', $supportEmail) }}" required>
                    @error('support_email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="desk-support-phone">Téléphone</label>
                    <input id="desk-support-phone" type="text" name="support_phone"
                           value="{{ old('support_phone', $supportPhone) }}">
                    @error('support_phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="card-icon is-success" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <div>
                    <h2 class="card-title">À propos</h2>
                    <p class="card-sub">Présentation de Salang sur le site</p>
                </div>
            </div>
            <div class="field">
                <label for="desk-about-who">Qui sommes-nous *</label>
                <textarea id="desk-about-who" name="about_who_we_are" rows="4" required>{{ old('about_who_we_are', $aboutWhoWeAre) }}</textarea>
                @error('about_who_we_are')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="desk-about-mission">Mission *</label>
                <textarea id="desk-about-mission" name="about_mission" rows="3" required>{{ old('about_mission', $aboutMission) }}</textarea>
                @error('about_mission')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="card-icon is-warning" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="card-title">Offre</h2>
                    <p class="card-sub">Accroche commerciale et frais d’adhésion</p>
                </div>
            </div>
            <div class="field">
                <label for="desk-offer-lead">Accroche *</label>
                <textarea id="desk-offer-lead" name="offer_lead" rows="2" required>{{ old('offer_lead', $offerLead) }}</textarea>
                @error('offer_lead')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="field max-w-xs">
                <label for="desk-membership-fee">Frais d’adhésion (USD) *</label>
                <input id="desk-membership-fee" type="number" name="membership_fee_usd" min="1" max="1000"
                       value="{{ old('membership_fee_usd', $membershipFee) }}" required>
                @error('membership_fee_usd')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="form-actions">
            <p class="text-xs text-[var(--text-tertiary)] mr-auto">Modifications visibles sur le site public après enregistrement.</p>
            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>
    </form>
</div>

{{-- Mobile : formulaire simple inchangé --}}
<div class="it-mobile-only">
    <div class="member-page-intro mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Pages site</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">À propos, offre et coordonnées contact.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('it-manager.site-pages.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">Contact</h2>
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Email *</label>
                    <input type="email" name="support_email" value="{{ old('support_email', $supportEmail) }}" required
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="text" name="support_phone" value="{{ old('support_phone', $supportPhone) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">À propos</h2>
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Qui sommes-nous *</label>
                <textarea name="about_who_we_are" rows="4" required
                          class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ old('about_who_we_are', $aboutWhoWeAre) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Mission *</label>
                <textarea name="about_mission" rows="3" required
                          class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ old('about_mission', $aboutMission) }}</textarea>
            </div>
        </section>

        <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">Offre</h2>
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Accroche *</label>
                <textarea name="offer_lead" rows="2" required
                          class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ old('offer_lead', $offerLead) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Frais d’adhésion (USD) *</label>
                <input type="number" name="membership_fee_usd" min="1" max="1000" value="{{ old('membership_fee_usd', $membershipFee) }}" required
                       class="w-full max-w-xs rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
            </div>
        </section>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
@endsection
