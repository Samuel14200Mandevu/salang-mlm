@extends('layouts.app')

@section('title', 'Pages site')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Pages site</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">À propos, offre et coordonnées contact.</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('it-manager.site-pages.update') }}" class="space-y-5 max-w-3xl">
    @csrf
    @method('PUT')

    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
        <h2 class="text-sm font-semibold text-[var(--text-primary)]">Contact</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
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
@endsection
