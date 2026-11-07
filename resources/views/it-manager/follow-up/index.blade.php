@extends('layouts.app')

@section('title', 'Suivi registre')

@section('content')
@php $hasMemberCodeSearch = $memberCode !== ''; @endphp

{{-- Desktop --}}
<div class="it-desktop-head it-desktop-only mb-6 items-end justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Suivi adhésions registre</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Invités des 30 derniers jours sans étape complétée.</p>
    </div>
    <div class="member-shop-search search-wrapper w-full max-w-xs shrink-0">
        <span class="member-shop-search__icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </span>
        <input type="search"
               id="followUpSearchInputDesktop"
               value="{{ $memberCode }}"
               placeholder="Code membre (parrain)…"
               autocomplete="off"
               class="input search-input text-sm font-mono">
    </div>
</div>

{{-- Mobile --}}
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

<div id="followUpStatGrid" class="follow-up-stat-grid mb-5">
    @foreach ([
        ['formation', 'Formation', $counts['formation']],
        ['adhesion', 'Sans adhésion', $counts['adhesion']],
        ['consultation', 'Sans consult.', $counts['consultation']],
        ['purchase', 'Sans achat', $counts['purchase']],
    ] as [$key, $label, $count])
        <a href="{{ route('it-manager.follow-up.index', array_filter(['filter' => $key, 'member_code' => $memberCode ?: null])) }}"
           class="rounded-lg border px-3 py-3 {{ $filter === $key ? 'border-[var(--color-primary-600)] bg-[var(--bg-secondary)]' : 'border-[var(--border-color)] bg-[var(--bg-card)]' }}">
            <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]" data-follow-up-count="{{ $key }}">{{ $count }}</p>
            <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">{{ $label }}</p>
        </a>
    @endforeach
</div>

{{-- Mobile : Date / Invité / Parrain → détail au clic --}}
<div id="followUpMobileList" class="follow-up-mobile-list it-mobile-only space-y-2 mb-4">
    @include('it-manager.follow-up._mobile_list')
</div>

{{-- Desktop : tableau complet --}}
<div class="it-desktop-only rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[var(--bg-secondary)] text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                    <th class="px-3 py-2.5">Date</th>
                    <th class="px-3 py-2.5">Invité</th>
                    <th class="px-3 py-2.5">Parrain</th>
                    <th class="px-3 py-2.5 text-center">Form.</th>
                    <th class="px-3 py-2.5 text-center">Adh.</th>
                    <th class="px-3 py-2.5 text-center">Cons.</th>
                    <th class="px-3 py-2.5 text-center">Achat</th>
                    <th class="px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody id="followUpTableBody">
                @include('it-manager.follow-up._table_rows')
            </tbody>
        </table>
    </div>
</div>

<div id="followUpPagination" class="mt-4">{{ $entries->links() }}</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
                    <td colspan="8" class="px-3 py-8 text-center text-sm text-[var(--text-secondary)]">
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
                            <td colspan="8" class="px-3 py-8 text-center text-sm text-[var(--danger)]">
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
