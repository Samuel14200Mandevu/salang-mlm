@extends('layouts.app')

@section('title', 'Modifier invité')

@section('content')
@php
    $fromRegister = ($from ?? null) === 'register';
    $backUrl = $fromRegister
        ? route('it-manager.activity-register.show', [
            'date' => $returnDate ?? $entry->sheet?->activity_date?->toDateString(),
        ])
        : route('it-manager.follow-up.show', array_filter([
            'member' => $entry->member_id,
            'date' => $entry->sheet?->activity_date?->toDateString(),
            'filter' => $filter,
            'member_code' => ($memberCode ?? null) ?: null,
        ]));
@endphp

<div class="mb-4">
    <a href="{{ $backUrl }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ $fromRegister ? 'Retour au registre' : 'Retour au détail' }}
    </a>
</div>

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="member-page-intro mb-4">
    <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">
        {{ $member?->name ?: 'Membre' }}
        @if ($entry->sheet?->activity_date)
            · {{ $entry->sheet->activity_date->format('d/m/Y') }}
        @endif
    </p>
    <h1 class="text-2xl font-bold text-[var(--text-primary)] mt-0.5">Modifier l’invité</h1>
</div>

<section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 mb-6">
    <form method="POST" action="{{ route('it-manager.activity-register.entries.update', $entry) }}" class="space-y-4">
        @csrf
        @method('PATCH')
        @if ($fromRegister)
            <input type="hidden" name="return_to" value="register">
            <input type="hidden" name="return_date" value="{{ $returnDate ?? $entry->sheet?->activity_date?->toDateString() }}">
        @else
            <input type="hidden" name="return_member" value="{{ $entry->member_id }}">
            <input type="hidden" name="return_date" value="{{ $entry->sheet?->activity_date?->toDateString() }}">
            <input type="hidden" name="return_filter" value="{{ $filter }}">
            <input type="hidden" name="return_member_code" value="{{ $memberCode }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label for="invite_type" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Type d’invitation</label>
                <select id="invite_type" name="invite_type"
                        class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    <option value="formation" @selected(old('invite_type', $entry->invite_type) === 'formation')>Formation</option>
                    <option value="autre" @selected(old('invite_type', $entry->invite_type) === 'autre')>Autre</option>
                </select>
            </div>
        </div>

        <div class="border-t border-[var(--border-light)] pt-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)] mb-3">Informations de l’invité</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="participant_nom" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                            Nom <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="participant_nom" name="participant_nom"
                               value="{{ old('participant_nom', $entry->participant_nom) }}" required
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="participant_postnom" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                            Post-Nom <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="participant_postnom" name="participant_postnom"
                               value="{{ old('participant_postnom', $entry->participant_postnom) }}" required
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="participant_prenom" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                            Prénom
                        </label>
                        <input type="text" id="participant_prenom" name="participant_prenom"
                               value="{{ old('participant_prenom', $entry->participant_prenom) }}"
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label for="participant_birth_date" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Date de naissance
                    </label>
                    <input type="date" id="participant_birth_date" name="participant_birth_date"
                           value="{{ old('participant_birth_date', optional($entry->participant_birth_date)->toDateString()) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_gender" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Sexe</label>
                    <select id="participant_gender" name="participant_gender"
                            class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                        <option value="">Sélectionner</option>
                        <option value="male" @selected(old('participant_gender', $entry->participant_gender) === 'male')>Masculin</option>
                        <option value="female" @selected(old('participant_gender', $entry->participant_gender) === 'female')>Féminin</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="participant_address" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <input type="text" id="participant_address" name="participant_address"
                           value="{{ old('participant_address', $entry->participant_address) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_phone" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="tel" id="participant_phone" name="participant_phone"
                           value="{{ old('participant_phone', $entry->participant_phone) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_email" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Email</label>
                    <input type="email" id="participant_email" name="participant_email"
                           value="{{ old('participant_email', $entry->participant_email) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_profession" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Profession</label>
                    <input type="text" id="participant_profession" name="participant_profession"
                           value="{{ old('participant_profession', $entry->participant_profession) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_identity_number" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Numéro d’identité
                    </label>
                    <input type="text" id="participant_identity_number" name="participant_identity_number"
                           value="{{ old('participant_identity_number', $entry->participant_identity_number) }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="border-t border-[var(--border-light)] pt-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)] mb-3">Parcours</h3>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ([
                    'formation_done' => ['Formation', (bool) $entry->formation_done],
                    'adhesion_done' => ['Adhésion', (bool) $entry->adhesion_done],
                    'consultation_done' => ['Consultation', (bool) $entry->consultation_done],
                    'purchase_done' => ['Achat', (bool) $entry->purchase_done],
                ] as $flag => [$label, $done])
                    <label class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-2.5 py-1.5 text-xs font-semibold text-[var(--text-secondary)] cursor-pointer">
                        <input type="hidden" name="{{ $flag }}" value="0">
                        <input type="checkbox"
                               name="{{ $flag }}"
                               value="1"
                               @if ($flag === 'purchase_done') id="purchase_done" data-purchase-toggle @endif
                               class="rounded border-[var(--border-color)] text-[var(--primary)] focus:ring-[var(--primary)]"
                               @checked((string) old($flag, $done ? '1' : '0') === '1')>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="border-t border-[var(--border-light)] pt-4" id="purchaseAmountField">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)] mb-3">Montant d’achat</h3>
            <div class="max-w-xs">
                <label for="purchases_amount" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Montant ($) <span id="purchaseAmountRequiredMark" class="text-rose-600 {{ (string) old('purchase_done', $entry->purchase_done ? '1' : '0') === '1' ? '' : 'hidden' }}">*</span>
                </label>
                <input type="number" step="0.01" min="0" id="purchases_amount" name="purchases_amount"
                       value="{{ old('purchases_amount', $entry->purchases_amount) }}"
                       @if ((string) old('purchase_done', $entry->purchase_done ? '1' : '0') === '1') required @endif
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('purchases_amount') border-rose-400 @enderror">
                @error('purchases_amount')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pt-2">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ $backUrl }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</section>

<script>
(function () {
    const toggle = document.querySelector('[data-purchase-toggle]');
    const input = document.getElementById('purchases_amount');
    const requiredMark = document.getElementById('purchaseAmountRequiredMark');
    if (!toggle || !input) return;

    function sync() {
        const on = toggle.checked;
        input.required = on;
        if (requiredMark) {
            requiredMark.classList.toggle('hidden', !on);
        }
        if (!on) {
            // garder la valeur affichée pour correction, mais non obligatoire
            input.removeAttribute('required');
        }
    }

    toggle.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
