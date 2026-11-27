@extends('layouts.app')

@section('title', 'Suivi registre')

@section('content')
@php
    $hasMemberCodeSearch = $memberCode !== '';
    $inviteFormOpen = $errors->any() || old('member_code') !== null || old('guests') !== null;
@endphp

{{-- Desktop — comme admin Membres : titre | recherche | icônes --}}
<div class="it-desktop-head it-desktop-only mb-6 !flex !flex-row !flex-nowrap items-center justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Suivi adhésions registre</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Invités des 30 derniers jours sans étape complétée.</p>
    </div>
    <div class="follow-up-desktop-tools ml-auto !flex !flex-row !flex-nowrap items-center gap-2">
        <div class="follow-up-desktop-search header-search shrink">
            <div class="search-wrapper">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="followUpSearchInputDesktop"
                       class="search-input font-mono"
                       placeholder="Code membre (parrain)…"
                       autocomplete="off"
                       value="{{ $memberCode }}">
            </div>
        </div>
        <a href="{{ route('it-manager.follow-up.global-pdf', array_filter(['member_code' => ($memberCode ?? null) ?: null])) }}"
           class="btn btn-outline btn-sm follow-up-desktop-action btn-pdf-icon shrink-0"
           title="Aperçu du rapport global"
           aria-label="Aperçu du rapport global PDF">
            <x-pdf-icon size="sm" />
        </a>
        <button type="button"
                class="btn btn-primary btn-sm follow-up-desktop-action shrink-0 {{ $inviteFormOpen ? 'is-open' : '' }}"
                data-follow-up-toggle
                aria-expanded="{{ $inviteFormOpen ? 'true' : 'false' }}"
                aria-controls="followUpInvitePanel"
                title="Ajouter des invités"
                aria-label="Ajouter des invités">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>
</div>

{{-- Mobile — comme admin Membres : recherche puis actions --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Registre · 30 jours</p>
            <p class="shop-catalog-banner__title">Suivi adhésions</p>
            <p class="shop-catalog-banner__sub">Invités sans étape complétée</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ $hasMemberCodeSearch ? 'is-active' : '' }}"
                    id="followUpSearchToggle"
                    aria-expanded="{{ $hasMemberCodeSearch ? 'true' : 'false' }}"
                    aria-controls="followUpSearchPanel"
                    aria-label="Rechercher par code membre">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <a href="{{ route('it-manager.follow-up.global-pdf', array_filter(['member_code' => ($memberCode ?? null) ?: null])) }}"
               class="shop-banner-icon-btn btn-pdf-icon"
               aria-label="Aperçu du rapport global PDF"
               title="Aperçu rapport global">
                <x-pdf-icon size="sm" />
            </a>
            <button type="button"
                    class="shop-banner-icon-btn !bg-[var(--primary)] !border-[var(--primary)] {{ $inviteFormOpen ? 'is-active' : '' }}"
                    data-follow-up-toggle
                    aria-expanded="{{ $inviteFormOpen ? 'true' : 'false' }}"
                    aria-controls="followUpInvitePanel"
                    title="Ajouter des invités"
                    aria-label="Ajouter des invités">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="shop-catalog-search-panel {{ $hasMemberCodeSearch ? 'is-open' : '' }}"
         id="followUpSearchPanel"
         aria-hidden="{{ $hasMemberCodeSearch ? 'false' : 'true' }}">
        <div class="member-shop-search search-wrapper">
            <span class="member-shop-search__icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   id="followUpSearchInput"
                   value="{{ $memberCode }}"
                   placeholder="Code membre (parrain)…"
                   inputmode="search"
                   autocomplete="off"
                   class="input search-input text-sm font-mono">
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section id="followUpInvitePanel"
         class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 mb-5 {{ $inviteFormOpen ? '' : 'hidden' }}"
         @unless($inviteFormOpen) hidden @endunless>
    <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Ajouter des invités</h2>
    <form method="POST" action="{{ route('it-manager.follow-up.store') }}" class="space-y-4" id="followUpInviteForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label for="follow_up_activity_date" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Date <span class="text-rose-600">*</span>
                    </label>
                    <input type="date"
                           id="follow_up_activity_date"
                           name="activity_date"
                           value="{{ old('activity_date', now()->toDateString()) }}"
                           required
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm @error('activity_date') border-rose-400 @enderror">
                </div>
                <div>
                    <label for="follow_up_member_code" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Code membre <span class="text-rose-600">*</span>
                    </label>
                    <input type="text"
                           id="follow_up_member_code"
                           name="member_code"
                           value="{{ old('member_code', $memberCode) }}"
                           required
                           autocomplete="off"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm font-mono tracking-wide uppercase @error('member_code') border-rose-400 @enderror">
                    <div id="followUpMemberLookup" class="mt-1.5 text-sm min-h-[1.25rem]" aria-live="polite"></div>
                </div>
                <div>
                    <label for="follow_up_invite_type" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                        Formation <span class="text-rose-600">*</span>
                    </label>
                    <select id="follow_up_invite_type"
                            name="invite_type"
                            class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                        <option value="formation" @selected(old('invite_type', 'formation') === 'formation')>Formation</option>
                        <option value="autre" @selected(old('invite_type') === 'autre')>Autre</option>
                    </select>
                </div>
            </div>

            <div id="followUpGuestsList" class="space-y-3">
                @php
                    $oldGuests = old('guests');
                    if (! is_array($oldGuests) || $oldGuests === []) {
                        $oldGuests = [['nom' => '', 'postnom' => '']];
                    }
                @endphp
                @foreach ($oldGuests as $index => $guest)
                    <div class="follow-up-guest-row rounded-lg border border-[var(--border-light)] bg-[var(--bg-secondary)]/40 p-3"
                         data-guest-row>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">
                                Invité <span data-guest-number>{{ $index + 1 }}</span>
                            </p>
                            @if ($index > 0)
                                <button type="button"
                                        class="text-xs text-rose-600 hover:underline"
                                        data-remove-guest>
                                    Retirer
                                </button>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                                    Nom <span class="text-rose-600">*</span>
                                </label>
                                <input type="text"
                                       name="guests[{{ $index }}][nom]"
                                       value="{{ $guest['nom'] ?? '' }}"
                                       required
                                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                                    Post-Nom <span class="text-rose-600">*</span>
                                </label>
                                <input type="text"
                                       name="guests[{{ $index }}][postnom]"
                                       value="{{ $guest['postnom'] ?? '' }}"
                                       required
                                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    id="followUpAddGuestBtn"
                    class="btn btn-outline btn-sm">
                Ajouter invité
            </button>
            <button type="submit" class="btn btn-primary btn-sm">
                Enregistrer
            </button>
        </div>
    </form>
</section>

<template id="followUpGuestTemplate">
    <div class="follow-up-guest-row rounded-lg border border-[var(--border-light)] bg-[var(--bg-secondary)]/40 p-3"
         data-guest-row>
        <div class="flex items-center justify-between gap-2 mb-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">
                Invité <span data-guest-number></span>
            </p>
            <button type="button"
                    class="text-xs text-rose-600 hover:underline"
                    data-remove-guest>
                Retirer
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Nom <span class="text-rose-600">*</span>
                </label>
                <input type="text"
                       data-guest-nom
                       required
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Post-Nom <span class="text-rose-600">*</span>
                </label>
                <input type="text"
                       data-guest-postnom
                       required
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
            </div>
        </div>
    </div>
</template>

<div id="followUpStatGrid" class="follow-up-stat-grid mb-5">
    @foreach ([
        ['formation', 'Formation', (int) $counts['formation']],
        ['adhesion', 'Adhésion', (int) $counts['adhesion']],
        ['consultation', 'Consult.', (int) $counts['consultation']],
        ['purchase', 'Achat', (int) $counts['purchase']],
    ] as [$key, $label, $count])
        <a href="{{ route('it-manager.follow-up.index', array_filter(['filter' => $key, 'member_code' => ($memberCode ?? null) ?: null])) }}"
           class="rounded-lg border px-3 py-3 {{ $filter === $key ? 'border-[var(--color-primary-600)] bg-[var(--bg-secondary)]' : 'border-[var(--border-color)] bg-[var(--bg-card)]' }}">
            <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]" data-follow-up-count="{{ $key }}">{{ $count }}</p>
            <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">{{ $label }}</p>
        </a>
    @endforeach
</div>

{{-- Mobile : N° / Invités / Membre → détail au clic --}}
<div id="followUpMobileList" class="follow-up-mobile-list it-mobile-only space-y-2 mb-4">
    @include('it-manager.follow-up._mobile_list')
</div>

{{-- Desktop : une ligne par membre (dates dans le détail) --}}
<div class="it-desktop-only rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[var(--bg-secondary)] text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                    <th class="px-3 py-2.5">N°</th>
                    <th class="px-3 py-2.5">Invités</th>
                    <th class="px-3 py-2.5">Membre</th>
                    <th class="px-3 py-2.5 text-center">Form.</th>
                    <th class="px-3 py-2.5 text-center">Adh.</th>
                    <th class="px-3 py-2.5 text-center">Cons.</th>
                    <th class="px-3 py-2.5 text-center">Achat</th>
                </tr>
            </thead>
            <tbody id="followUpTableBody">
                @include('it-manager.follow-up._table_rows')
            </tbody>
        </table>
    </div>
</div>

<div id="followUpPagination" class="mt-4">{{ $groups->links() }}</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    (function inviteForm() {
        const toggleBtns = document.querySelectorAll('[data-follow-up-toggle]');
        const panel = document.getElementById('followUpInvitePanel');
        const list = document.getElementById('followUpGuestsList');
        const addBtn = document.getElementById('followUpAddGuestBtn');
        const template = document.getElementById('followUpGuestTemplate');
        const memberInput = document.getElementById('follow_up_member_code');
        const memberResult = document.getElementById('followUpMemberLookup');

        function setInviteFormOpen(open) {
            if (!panel) return;
            panel.classList.toggle('hidden', !open);
            if (open) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', '');
            }
            toggleBtns.forEach(function (btn) {
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (btn.closest('.shop-catalog-banner')) {
                    btn.classList.toggle('is-active', open);
                    btn.classList.remove('is-open');
                } else {
                    btn.classList.toggle('is-open', open);
                    btn.classList.remove('is-active');
                }
            });
            if (open && memberInput) {
                memberInput.focus();
            }
        }

        toggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const open = panel && panel.classList.contains('hidden');
                setInviteFormOpen(open);
            });
        });

        if (!list || !addBtn || !template) return;

        const lookupUrl = @json(route('it-manager.activity-register.lookup-member'));
        let lookupTimer = null;
        let lastCode = '';

        function renumberGuests() {
            list.querySelectorAll('[data-guest-row]').forEach(function (row, index) {
                const numberEl = row.querySelector('[data-guest-number]');
                if (numberEl) numberEl.textContent = String(index + 1);

                const nom = row.querySelector('[name*="[nom]"], [data-guest-nom]');
                const postnom = row.querySelector('[name*="[postnom]"], [data-guest-postnom]');
                if (nom) {
                    nom.setAttribute('name', 'guests[' + index + '][nom]');
                    nom.removeAttribute('data-guest-nom');
                }
                if (postnom) {
                    postnom.setAttribute('name', 'guests[' + index + '][postnom]');
                    postnom.removeAttribute('data-guest-postnom');
                }

                const removeBtn = row.querySelector('[data-remove-guest]');
                if (removeBtn) {
                    removeBtn.style.display = index === 0 ? 'none' : '';
                }
            });
        }

        addBtn.addEventListener('click', function () {
            const node = template.content.cloneNode(true);
            list.appendChild(node);
            renumberGuests();
            const rows = list.querySelectorAll('[data-guest-row]');
            const last = rows[rows.length - 1];
            const firstInput = last ? last.querySelector('input') : null;
            if (firstInput) firstInput.focus();
        });

        list.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-remove-guest]');
            if (!btn) return;
            const row = btn.closest('[data-guest-row]');
            if (!row || list.querySelectorAll('[data-guest-row]').length <= 1) return;
            row.remove();
            renumberGuests();
        });

        renumberGuests();

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function lookupMember(code) {
            if (!memberResult) return;
            if (!code) {
                lastCode = '';
                memberResult.innerHTML = '';
                return;
            }
            if (code === lastCode) return;
            lastCode = code;
            memberResult.innerHTML = '<span class="text-[var(--text-muted)]">Vérification…</span>';

            fetch(lookupUrl + '?code=' + encodeURIComponent(code), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (memberInput.value.trim() !== code) return;
                    if (data.valid) {
                        const nom = data.member.nom || '';
                        const postnom = data.member.postnom || '';
                        const label = postnom
                            ? '<span class="font-semibold text-emerald-800">' + escapeHtml(nom) + '</span> <span class="text-emerald-700">' + escapeHtml(postnom) + '</span>'
                            : '<span class="font-semibold text-emerald-800">' + escapeHtml(data.member.name || nom) + '</span>';
                        memberResult.innerHTML = '<p class="text-sm">' + label + '</p>';
                    } else {
                        memberResult.innerHTML = '<span class="text-rose-600 text-xs">' + escapeHtml(data.message || 'Code introuvable') + '</span>';
                    }
                })
                .catch(function () {
                    if (memberInput.value.trim() !== code) return;
                    memberResult.innerHTML = '<span class="text-rose-600 text-xs">Erreur de vérification</span>';
                });
        }

        if (memberInput) {
            memberInput.addEventListener('input', function () {
                clearTimeout(lookupTimer);
                const code = memberInput.value.trim();
                if (!code) {
                    lastCode = '';
                    if (memberResult) memberResult.innerHTML = '';
                    return;
                }
                lookupTimer = setTimeout(function () { lookupMember(code); }, 350);
            });
            memberInput.addEventListener('blur', function () {
                lookupMember(memberInput.value.trim());
            });
            if (memberInput.value.trim()) {
                lookupMember(memberInput.value.trim());
            }
        }
    })();

    const searchInputs = [
        document.getElementById('followUpSearchInput'),
        document.getElementById('followUpSearchInputDesktop'),
    ].filter(Boolean);
    const searchToggle = document.getElementById('followUpSearchToggle');
    const searchPanel = document.getElementById('followUpSearchPanel');
    let searchTimeout;
    let requestId = 0;

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function () {
            const open = searchPanel.classList.toggle('is-open');
            searchToggle.classList.toggle('is-active', open);
            searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            searchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            const mobileInput = document.getElementById('followUpSearchInput');
            if (open && mobileInput) {
                mobileInput.focus();
            }
        });
    }

    if (!searchInputs.length) {
        return;
    }

    searchInputs.forEach(function (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            searchInputs.forEach(function (other) {
                if (other !== searchInput) {
                    other.value = this.value;
                }
            }.bind(this));
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                fetchFollowUp(query);
            }, 300);
        });
    });

    function fetchFollowUp(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('member_code', query);
        } else {
            url.searchParams.delete('member_code');
        }
        url.searchParams.set('page', '1');

        const currentRequest = ++requestId;
        const tableBody = document.getElementById('followUpTableBody');
        const mobileList = document.getElementById('followUpMobileList');

        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-sm text-[var(--text-secondary)]">
                        Recherche en cours…
                    </td>
                </tr>
            `;
        }
        if (mobileList) {
            mobileList.innerHTML = `
                <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-8 text-center text-sm text-[var(--text-secondary)]">
                    Recherche en cours…
                </div>
            `;
        }

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.text(); })
            .then(function (html) {
                if (currentRequest !== requestId) {
                    return;
                }

                const doc = new DOMParser().parseFromString(html, 'text/html');

                const newTableBody = doc.getElementById('followUpTableBody');
                if (tableBody && newTableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                const newMobileList = doc.getElementById('followUpMobileList');
                if (mobileList && newMobileList) {
                    mobileList.innerHTML = newMobileList.innerHTML;
                }

                const pagination = document.getElementById('followUpPagination');
                const newPagination = doc.getElementById('followUpPagination');
                if (pagination) {
                    pagination.innerHTML = newPagination ? newPagination.innerHTML : '';
                }

                const statGrid = document.getElementById('followUpStatGrid');
                const newStatGrid = doc.getElementById('followUpStatGrid');
                if (statGrid && newStatGrid) {
                    statGrid.innerHTML = newStatGrid.innerHTML;
                }

                window.history.replaceState({}, '', url.toString());
            })
            .catch(function () {
                if (currentRequest !== requestId) {
                    return;
                }
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-[var(--danger)]">
                                Une erreur est survenue lors de la recherche
                            </td>
                        </tr>
                    `;
                }
                if (mobileList) {
                    mobileList.innerHTML = `
                        <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-8 text-center text-sm text-[var(--danger)]">
                            Une erreur est survenue lors de la recherche
                        </div>
                    `;
                }
            });
    }
});
</script>
@endpush
