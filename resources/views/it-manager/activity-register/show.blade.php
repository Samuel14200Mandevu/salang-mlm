@extends('layouts.app')

@section('title', 'Registre du '.$day->format('d/m/Y'))

@section('content')
@php
    $entries = $sheet->entries;
    $grouped = $entries->groupBy('member_id');
    $prev = $day->copy()->subDay()->toDateString();
    $next = $day->copy()->addDay()->toDateString();
@endphp

{{-- Desktop --}}
<div class="it-desktop-head it-desktop-only mb-6 items-end justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <p class="text-xs uppercase tracking-wide text-[var(--text-muted)] mb-1">
            <a href="{{ route('it-manager.activity-register.index') }}" class="hover:text-primary-600">Historique</a>
            · Registre
        </p>
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">{{ $day->translatedFormat('l d F Y') }}</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">
            {{ $entries->count() }} participant(s) ·
            {{ $entries->where('adhesion_done', true)->count() }} adhésion(s) ·
            {{ $entries->where('consultation_done', true)->count() }} consultation(s) ·
            {{ $entries->where('purchase_done', true)->count() }} achat(s)
        </p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('it-manager.activity-register.show', ['date' => $prev]) }}" class="btn btn-outline px-3" title="Jour précédent">‹</a>
        <a href="{{ route('it-manager.activity-register.show') }}" class="btn btn-outline text-sm">Aujourd’hui</a>
        <a href="{{ route('it-manager.activity-register.show', ['date' => $next]) }}" class="btn btn-outline px-3" title="Jour suivant">›</a>
    </div>
</div>

{{-- Mobile --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">
                <a href="{{ route('it-manager.activity-register.index') }}" class="hover:opacity-100 opacity-90 underline-offset-2 hover:underline">Historique</a>
                · Registre
            </p>
            <p class="shop-catalog-banner__title">{{ $day->translatedFormat('l d F Y') }}</p>
            <p class="shop-catalog-banner__sub">
                {{ $entries->count() }} participant(s) ·
                {{ $entries->where('adhesion_done', true)->count() }} adhésion(s) ·
                {{ $entries->where('consultation_done', true)->count() }} consult. ·
                {{ $entries->where('purchase_done', true)->count() }} achat(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('it-manager.activity-register.show', ['date' => $prev]) }}"
               class="shop-banner-icon-btn" aria-label="Jour précédent" title="Jour précédent">‹</a>
            <a href="{{ route('it-manager.activity-register.show') }}"
               class="shop-banner-icon-btn" aria-label="Aujourd’hui" title="Aujourd’hui">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </a>
            <a href="{{ route('it-manager.activity-register.show', ['date' => $next]) }}"
               class="shop-banner-icon-btn" aria-label="Jour suivant" title="Jour suivant">›</a>
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

<section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 mb-6">
    <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Ajouter un participant</h2>

    <form method="POST" action="{{ route('it-manager.activity-register.store') }}" class="space-y-4" id="activity-participant-form">
        @csrf
        <input type="hidden" name="activity_date" value="{{ $day->toDateString() }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label for="member_code" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Code membre (parrain) <span class="text-rose-600">*</span>
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
                <label for="invite_type" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Type d’invitation</label>
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
                            Prénom <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="participant_prenom" name="participant_prenom" value="{{ old('participant_prenom') }}" required
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
                        Téléphone <span class="text-rose-600">*</span>
                    </label>
                    <input type="tel" id="participant_phone" name="participant_phone" value="{{ old('participant_phone') }}" required
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

@forelse ($grouped as $memberId => $memberEntries)
    @php $member = $memberEntries->first()->member; @endphp
    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-4">
        <header class="px-4 py-3 border-b border-[var(--border-color)] bg-[var(--bg-secondary)] flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-[var(--text-primary)]">
                    {{ $member?->name ?? 'Membre' }}
                </h2>
                <p class="text-xs text-[var(--text-muted)]">
                    Code {{ $memberEntries->first()->member_code ?? $member?->sponsor_id ?? '—' }}
                    · {{ $memberEntries->count() }} invité(s)
                </p>
            </div>
        </header>

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
                                    <form method="POST" action="{{ route('it-manager.activity-register.entries.update', $entry) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="{{ $flag }}" value="{{ $done ? '0' : '1' }}">
                                        <button type="submit"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md text-xs font-bold
                                                    {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}"
                                                title="{{ $done ? 'Fait — cliquer pour annuler' : 'Pas fait — cliquer pour marquer' }}">
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
                            <td class="px-3 py-2.5 text-right">
                                <form method="POST" action="{{ route('it-manager.activity-register.entries.destroy', $entry) }}"
                                      onsubmit="return confirm('Supprimer cette ligne ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Suppr.</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@empty
    <div class="rounded-lg border border-dashed border-[var(--border-color)] px-4 py-12 text-center text-sm text-[var(--text-muted)]">
        Aucun participant pour cette date. Utilisez le formulaire ci-dessus.
    </div>
@endforelse
@endsection

@push('scripts')
<script>
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
</script>
@endpush
