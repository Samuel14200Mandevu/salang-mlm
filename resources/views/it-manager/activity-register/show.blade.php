@extends('layouts.app')

@section('title', 'Registre d’activité')

@section('content')
@php
    $formOpen = $errors->any()
        || old('member_code') !== null
        || old('participant_nom') !== null
        || old('participant_postnom') !== null;
    $totalEntries = $allEntries->count();
    $totalAdhesion = $allEntries->where('adhesion_done', true)->count();
    $totalConsultation = $allEntries->where('consultation_done', true)->count();
    $totalPurchase = $allEntries->where('purchase_done', true)->count();
@endphp

{{-- Desktop — comme Suivi : titre | icône + --}}
<div class="it-desktop-head it-desktop-only mb-6 !flex !flex-row !flex-nowrap items-center justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <p class="text-xs uppercase tracking-wide text-[var(--text-muted)] mb-1">
            <a href="{{ route('it-manager.activity-register.index') }}" class="hover:text-primary-600">Historique</a>
            · Registre
        </p>
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Registre d’activité</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">
            {{ $totalEntries }} participant(s) ·
            {{ $totalAdhesion }} adhésion(s) ·
            {{ $totalConsultation }} consultation(s) ·
            {{ $totalPurchase }} achat(s)
        </p>
    </div>
    <div class="follow-up-desktop-tools ml-auto !flex !flex-row !flex-nowrap items-center gap-2">
        <button type="button"
                class="btn btn-primary btn-sm follow-up-desktop-action shrink-0 {{ $formOpen ? 'is-open' : '' }}"
                data-register-form-toggle
                aria-expanded="{{ $formOpen ? 'true' : 'false' }}"
                aria-controls="registerParticipantPanel"
                title="Ajouter un participant"
                aria-label="Ajouter un participant">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>
</div>

{{-- Mobile — comme Suivi : bandeau + outils --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">
                <a href="{{ route('it-manager.activity-register.index') }}" class="hover:opacity-100 opacity-90 underline-offset-2 hover:underline">Historique</a>
                · Registre
            </p>
            <p class="shop-catalog-banner__title">Registre d’activité</p>
            <p class="shop-catalog-banner__sub">
                {{ $totalEntries }} participant(s) ·
                {{ $totalAdhesion }} adh. ·
                {{ $totalConsultation }} consult. ·
                {{ $totalPurchase }} achat(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn !bg-[var(--primary)] !border-[var(--primary)] {{ $formOpen ? 'is-active' : '' }}"
                    data-register-form-toggle
                    aria-expanded="{{ $formOpen ? 'true' : 'false' }}"
                    aria-controls="registerParticipantPanel"
                    title="Ajouter un participant"
                    aria-label="Ajouter un participant">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section id="registerParticipantPanel"
         class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 mb-6 {{ $formOpen ? '' : 'hidden' }}"
         @unless($formOpen) hidden @endunless>
    <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Ajouter un participant</h2>

    <form method="POST" action="{{ route('it-manager.activity-register.store') }}" class="space-y-4" id="activity-participant-form">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="activity_date" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Date <span class="text-rose-600">*</span>
                </label>
                <input type="date" id="activity_date" name="activity_date"
                       value="{{ old('activity_date', $day->toDateString()) }}" required
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('activity_date') border-rose-400 @enderror">
                @error('activity_date')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="member_code" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Code membre <span class="text-rose-600">*</span>
                </label>
                <input type="text" id="member_code" name="member_code" value="{{ old('member_code') }}" required
                       autocomplete="off"
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm font-mono tracking-wide uppercase @error('member_code') border-rose-400 @enderror">
                <div id="member-lookup-result" class="mt-1.5 text-sm min-h-[1.25rem]" aria-live="polite">
                    @if (old('member_code'))
                        <span class="text-[var(--text-muted)]">Vérification du code…</span>
                    @endif
                </div>
                @error('member_code')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="invite_type" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Type</label>
                <select id="invite_type" name="invite_type"
                        class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    <option value="formation" @selected(old('invite_type', 'formation') === 'formation')>Formation</option>
                    <option value="autre" @selected(old('invite_type') === 'autre')>Autre</option>
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
                        <input type="text" id="participant_nom" name="participant_nom" value="{{ old('participant_nom') }}" required
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('participant_nom') border-rose-400 @enderror">
                        @error('participant_nom')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="participant_postnom" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                            Post-Nom <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="participant_postnom" name="participant_postnom" value="{{ old('participant_postnom') }}" required
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('participant_postnom') border-rose-400 @enderror">
                        @error('participant_postnom')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="participant_prenom" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                            Prénom
                        </label>
                        <input type="text" id="participant_prenom" name="participant_prenom" value="{{ old('participant_prenom') }}"
                               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('participant_prenom') border-rose-400 @enderror">
                        @error('participant_prenom')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="participant_birth_date" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Date de naissance
                    </label>
                    <input type="date" id="participant_birth_date" name="participant_birth_date"
                           value="{{ old('participant_birth_date') }}"
                           placeholder="jj/mm/aaaa"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    <p class="mt-1 text-[0.65rem] text-[var(--text-muted)]">Format jj/mm/aaaa</p>
                </div>

                <div>
                    <label for="participant_gender" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Sexe</label>
                    <select id="participant_gender" name="participant_gender"
                            class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                        <option value="">Sélectionner</option>
                        <option value="male" @selected(old('participant_gender') === 'male')>Masculin</option>
                        <option value="female" @selected(old('participant_gender') === 'female')>Féminin</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="participant_address" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <input type="text" id="participant_address" name="participant_address" value="{{ old('participant_address') }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_phone" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Téléphone
                    </label>
                    <input type="tel" id="participant_phone" name="participant_phone" value="{{ old('participant_phone') }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('participant_phone') border-rose-400 @enderror">
                    @error('participant_phone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="participant_email" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Email</label>
                    <input type="email" id="participant_email" name="participant_email" value="{{ old('participant_email') }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_profession" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Profession</label>
                    <input type="text" id="participant_profession" name="participant_profession" value="{{ old('participant_profession') }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="participant_identity_number" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Numéro d’identité
                    </label>
                    <input type="text" id="participant_identity_number" name="participant_identity_number"
                           value="{{ old('participant_identity_number') }}"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                </div>

            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Enregistrer l’invité</button>
        </div>
    </form>
</section>

<div id="registerListPanel" class="{{ $formOpen ? 'hidden' : '' }}" @if ($formOpen) hidden @endif>
@forelse ($membersGrouped as $memberId => $entriesByDate)
    @php
        $flatEntries = $entriesByDate->flatten();
        $member = $flatEntries->first()?->member;
        $memberCode = $flatEntries->first()?->member_code ?? $member?->sponsor_id ?? '—';
        $hasFocusDay = $entriesByDate->has($day->toDateString());
    @endphp
    <section id="register-member-{{ $memberId }}"
             class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-4 {{ $hasFocusDay ? 'ring-1 ring-[var(--primary)]' : '' }}">
        <header class="px-4 py-3 border-b border-[var(--border-color)] bg-[var(--bg-secondary)] flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-semibold text-[var(--text-primary)]">
                    {{ $member?->name ?? 'Membre' }}
                </h2>
                <p class="text-xs text-[var(--text-muted)]">
                    Code {{ $memberCode }}
                    · {{ $flatEntries->count() }} invité(s)
                    · {{ $entriesByDate->count() }} date(s)
                </p>
            </div>
            <p class="text-xs tabular-nums text-[var(--text-muted)]">
                Form {{ $flatEntries->where('formation_done', true)->count() }}
                · Adh {{ $flatEntries->where('adhesion_done', true)->count() }}
                · Cons {{ $flatEntries->where('consultation_done', true)->count() }}
                · Achat {{ $flatEntries->where('purchase_done', true)->count() }}
            </p>
            <button type="button"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-secondary)] hover:border-[var(--primary)] hover:text-[var(--primary)]"
                    data-register-member-toggle
                    aria-expanded="true"
                    aria-controls="register-member-body-{{ $memberId }}"
                    title="Réduire le détail"
                    aria-label="Réduire le détail de {{ $member?->name ?? 'membre' }}">
                <svg class="w-4 h-4 transition-transform duration-200" data-register-member-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </header>

        <div id="register-member-body-{{ $memberId }}" data-register-member-body>
        @foreach ($entriesByDate as $sheetDayKey => $memberEntries)
            @php
                $dayLabel = $sheetDayKey === 'unknown'
                    ? 'Date inconnue'
                    : \Illuminate\Support\Carbon::parse($sheetDayKey)->translatedFormat('l d F Y');
                $isFocusDay = $sheetDayKey === $day->toDateString();
            @endphp
            <div id="register-day-{{ $memberId }}-{{ $sheetDayKey }}"
                 class="{{ ! $loop->first ? 'border-t border-[var(--border-color)]' : '' }} {{ $isFocusDay ? 'bg-[var(--bg-secondary)]/40' : '' }}">
                <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                    <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                        {{ $dayLabel }}
                    </p>
                    <p class="text-xs tabular-nums text-[var(--text-muted)]">
                        {{ $memberEntries->count() }} invité(s)
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Participant</th>
                                <th class="px-3 py-2 text-center">Form.</th>
                                <th class="px-3 py-2 text-center">Adhésion</th>
                                <th class="px-3 py-2 text-center">Consult.</th>
                                <th class="px-3 py-2 text-center">Achat</th>
                                <th class="px-3 py-2">Montant</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memberEntries as $entry)
                                <tr class="border-t border-[var(--border-light)] align-top">
                                    <td class="px-3 py-2.5 tabular-nums text-[var(--text-muted)]">{{ $entry->sort_order }}</td>
                                    <td class="px-3 py-2.5">
                                        <p class="font-medium text-[var(--text-primary)]">{{ $entry->displayName() }}</p>
                                        @if ($entry->participant_phone)
                                            <p class="text-xs text-[var(--text-muted)]">{{ $entry->participant_phone }}</p>
                                        @endif
                                        <p class="text-[0.65rem] text-[var(--text-tertiary)]">
                                            {{ $entry->inviteLabel() }}
                                            @if ($entry->participant_gender)
                                                · {{ $entry->genderLabel() }}
                                            @endif
                                            @if ($entry->participant_birth_date)
                                                · {{ $entry->participant_birth_date->format('d/m/Y') }}
                                            @endif
                                        </p>
                                        @if ($entry->participant_profession || $entry->participant_identity_number)
                                            <p class="text-[0.65rem] text-[var(--text-muted)]">
                                                {{ $entry->participant_profession }}
                                                @if ($entry->participant_identity_number)
                                                    · ID {{ $entry->participant_identity_number }}
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                    @foreach ([
                                        'formation_done' => $entry->formation_done,
                                        'adhesion_done' => $entry->adhesion_done,
                                        'consultation_done' => $entry->consultation_done,
                                        'purchase_done' => $entry->purchase_done,
                                    ] as $flag => $done)
                                        <td class="px-3 py-2.5 text-center">
                                            <form method="POST"
                                                  action="{{ route('it-manager.activity-register.entries.update', $entry) }}"
                                                  class="inline {{ $flag === 'purchase_done' && ! $done ? 'register-purchase-enable-form' : '' }}"
                                                  @if ($flag === 'purchase_done' && ! $done)
                                                      data-entry-name="{{ $entry->displayName() }}"
                                                      data-current-amount="{{ $entry->purchases_amount !== null ? number_format((float) $entry->purchases_amount, 2, '.', '') : '' }}"
                                                  @endif>
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="{{ $flag }}" value="{{ $done ? '0' : '1' }}">
                                                <button type="submit"
                                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md text-xs font-bold
                                                            {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}"
                                                        title="{{ $done ? 'Fait — cliquer pour annuler' : ($flag === 'purchase_done' ? 'Marquer achat + montant' : 'Pas fait — cliquer pour marquer') }}">
                                                    {{ $done ? '✓' : '·' }}
                                                </button>
                                            </form>
                                            @if ($flag === 'adhesion_done' && $entry->adhesionUser)
                                                <p class="text-[0.6rem] text-[var(--text-muted)] mt-1 truncate max-w-[5rem]" title="{{ $entry->adhesionUser->name }}">
                                                    {{ $entry->adhesionUser->sponsor_id }}
                                                </p>
                                            @endif
                                            @if ($flag === 'consultation_done' && $entry->consultation)
                                                <p class="text-[0.6rem] text-[var(--text-muted)] mt-1">{{ $entry->consultation->numero }}</p>
                                            @endif
                                            @if ($flag === 'purchase_done' && $entry->order_id)
                                                <p class="text-[0.6rem] text-[var(--text-muted)] mt-1">#{{ $entry->order_id }}</p>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2.5 whitespace-nowrap tabular-nums text-[var(--text-secondary)]">
                                        @if ($entry->purchases_amount !== null)
                                            {{ number_format((float) $entry->purchases_amount, 2) }} $
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('it-manager.follow-up.entry.edit', [
                                                    'entry' => $entry,
                                                    'from' => 'register',
                                                    'return_date' => $entry->sheet?->activity_date?->toDateString() ?? $day->toDateString(),
                                                ]) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[var(--primary)] text-white hover:opacity-90"
                                               title="Modifier"
                                               aria-label="Modifier {{ $entry->displayName() }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('it-manager.activity-register.entries.destroy', $entry) }}"
                                                  class="register-entry-delete-form inline"
                                                  data-entry-name="{{ $entry->displayName() }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[var(--danger)] text-white hover:bg-[var(--danger-hover)] border-0 shadow-none"
                                                        style="background: var(--danger); color: #fff;"
                                                        title="Supprimer"
                                                        aria-label="Supprimer {{ $entry->displayName() }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        </div>
    </section>
@empty
    <div class="rounded-lg border border-dashed border-[var(--border-color)] px-4 py-12 text-center text-sm text-[var(--text-muted)]">
        Aucun participant enregistré. Utilisez le bouton + pour saisir un invité.
    </div>
@endforelse
</div>

<div id="registerPurchaseAmountDialog" class="confirm-overlay" hidden>
    <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="registerPurchaseAmountTitle">
        <div class="icon success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <h3 id="registerPurchaseAmountTitle">Montant d’achat</h3>
        <p id="registerPurchaseAmountMessage">Saisissez le montant d’achat pour cet invité.</p>
        <div class="mt-3 mb-1 text-left">
            <label for="registerPurchaseAmountInput" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                Montant ($) <span class="text-rose-600">*</span>
            </label>
            <input type="number"
                   id="registerPurchaseAmountInput"
                   step="0.01"
                   min="0"
                   inputmode="decimal"
                   class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm text-[var(--text-primary)]">
            <p id="registerPurchaseAmountError" class="mt-1 text-xs text-rose-600 hidden">Le montant est obligatoire.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-cancel" id="registerPurchaseAmountCancel">Annuler</button>
            <button type="button" class="btn btn-confirm success" id="registerPurchaseAmountConfirm">Valider</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const toggleBtns = document.querySelectorAll('[data-register-form-toggle]');
    const panel = document.getElementById('registerParticipantPanel');
    const listPanel = document.getElementById('registerListPanel');
    const memberInput = document.getElementById('member_code');

    function setFormOpen(open) {
        if (!panel) return;
        panel.classList.toggle('hidden', !open);
        if (open) {
            panel.removeAttribute('hidden');
        } else {
            panel.setAttribute('hidden', '');
        }
        if (listPanel) {
            listPanel.classList.toggle('hidden', open);
            if (open) {
                listPanel.setAttribute('hidden', '');
            } else {
                listPanel.removeAttribute('hidden');
            }
        }
        toggleBtns.forEach(function (btn) {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (btn.closest('.shop-catalog-banner')) {
                btn.classList.toggle('is-active', open);
            } else {
                btn.classList.toggle('is-open', open);
            }
        });
        if (open && memberInput) {
            memberInput.focus();
        }
    }

    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const open = panel && (panel.classList.contains('hidden') || panel.hasAttribute('hidden'));
            setFormOpen(open);
        });
    });
})();

(function () {
    const input = document.getElementById('member_code');
    const result = document.getElementById('member-lookup-result');
    if (!input || !result) return;

    const lookupUrl = @json(route('it-manager.activity-register.lookup-member'));
    let timer = null;
    let lastCode = '';

    function renderIdle() {
        result.innerHTML = '';
    }

    function renderLoading() {
        result.innerHTML = '<span class="text-[var(--text-muted)]">Vérification…</span>';
    }

    function renderValid(member) {
        const nom = member.nom || '';
        const postnom = member.postnom || '';
        const label = postnom
            ? `<span class="font-semibold text-emerald-800">${escapeHtml(nom)}</span>
               <span class="text-emerald-700"> ${escapeHtml(postnom)}</span>`
            : `<span class="font-semibold text-emerald-800">${escapeHtml(member.name || nom)}</span>`;

        result.innerHTML = `<p class="text-sm">${label}</p>`;
    }

    function renderInvalid(message) {
        result.innerHTML = `<span class="text-rose-600 text-xs">${escapeHtml(message || 'Code introuvable')}</span>`;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function lookup(code) {
        if (!code) {
            lastCode = '';
            renderIdle();
            return;
        }
        if (code === lastCode) return;
        lastCode = code;
        renderLoading();

        fetch(`${lookupUrl}?code=${encodeURIComponent(code)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then((res) => res.json())
            .then((data) => {
                if (input.value.trim() !== code) return;
                if (data.valid) {
                    renderValid(data.member);
                } else {
                    renderInvalid(data.message);
                }
            })
            .catch(() => {
                if (input.value.trim() !== code) return;
                renderInvalid('Erreur de vérification');
            });
    }

    input.addEventListener('input', function () {
        const code = input.value.trim();
        clearTimeout(timer);
        if (!code) {
            lastCode = '';
            renderIdle();
            return;
        }
        timer = setTimeout(() => lookup(code), 350);
    });

    input.addEventListener('blur', function () {
        lookup(input.value.trim());
    });

    if (input.value.trim()) {
        lookup(input.value.trim());
    }
})();

(function () {
    document.querySelectorAll('form.register-entry-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (typeof window.showConfirmDialog !== 'function') {
                if (window.confirm('Supprimer cet invité ?')) {
                    form.submit();
                }
                return;
            }
            const name = (form.dataset.entryName || 'cet invité').trim();
            window.showConfirmDialog({
                type: 'danger',
                title: 'Supprimer l’invité',
                message: 'Êtes-vous sûr de vouloir supprimer « ' + name + ' » ? Cette action est irréversible.',
                confirmText: 'Supprimer',
                form: form,
                onConfirm: function () {
                    form.submit();
                    if (typeof window.closeConfirmDialog === 'function') {
                        window.closeConfirmDialog();
                    }
                },
            });
        });
    });
})();

(function () {
    document.querySelectorAll('[data-register-member-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const section = btn.closest('section');
            const body = section && section.querySelector('[data-register-member-body]');
            const chevron = btn.querySelector('[data-register-member-chevron]');
            if (!body) return;

            const open = btn.getAttribute('aria-expanded') !== 'false';
            const nextOpen = !open;
            btn.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
            btn.setAttribute('title', nextOpen ? 'Réduire le détail' : 'Afficher le détail');
            body.classList.toggle('hidden', !nextOpen);
            if (chevron) {
                chevron.classList.toggle('rotate-180', !nextOpen);
            }
        });
    });
})();

(function () {
    const dialog = document.getElementById('registerPurchaseAmountDialog');
    const input = document.getElementById('registerPurchaseAmountInput');
    const error = document.getElementById('registerPurchaseAmountError');
    const message = document.getElementById('registerPurchaseAmountMessage');
    const cancelBtn = document.getElementById('registerPurchaseAmountCancel');
    const confirmBtn = document.getElementById('registerPurchaseAmountConfirm');
    let pendingForm = null;

    function closeDialog() {
        if (!dialog) return;
        dialog.classList.remove('active');
        dialog.setAttribute('hidden', '');
        pendingForm = null;
        if (error) error.classList.add('hidden');
    }

    function openDialog(form) {
        if (!dialog || !input) return;
        pendingForm = form;
        const name = (form.dataset.entryName || 'cet invité').trim();
        if (message) {
            message.textContent = 'Saisissez le montant d’achat pour « ' + name + ' ».';
        }
        input.value = form.dataset.currentAmount || '';
        if (error) error.classList.add('hidden');
        dialog.removeAttribute('hidden');
        dialog.classList.add('active');
        setTimeout(function () { input.focus(); input.select(); }, 50);
    }

    function submitWithAmount() {
        if (!pendingForm || !input) return;
        const raw = (input.value || '').trim().replace(',', '.');
        const amount = Number(raw);
        if (raw === '' || Number.isNaN(amount) || amount < 0) {
            if (error) error.classList.remove('hidden');
            input.focus();
            return;
        }

        let amountInput = pendingForm.querySelector('input[name="purchases_amount"]');
        if (!amountInput) {
            amountInput = document.createElement('input');
            amountInput.type = 'hidden';
            amountInput.name = 'purchases_amount';
            pendingForm.appendChild(amountInput);
        }
        amountInput.value = String(amount);

        const form = pendingForm;
        closeDialog();
        form.submit();
    }

    document.querySelectorAll('form.register-purchase-enable-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            openDialog(form);
        });
    });

    if (cancelBtn) cancelBtn.addEventListener('click', closeDialog);
    if (confirmBtn) confirmBtn.addEventListener('click', submitWithAmount);
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitWithAmount();
            }
        });
    }
    if (dialog) {
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) closeDialog();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && dialog && dialog.classList.contains('active')) {
            closeDialog();
        }
    });
})();

</script>
@endpush
