@extends('cashier.layouts.app')

@section('title', 'Membres')

@section('content')
@php
    $membersSubtitleHtml = $members->total() . ' membres enregistrés'
        . (request('search')
            ? ' <span class="text-xs opacity-90">· Résultats pour "' . e(request('search')) . '"</span>'
            : '');
    $searchOpen = filled(request('search'));
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : titre + loupe + Nouveau dans le div --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Gestion des membres</h1>
                <p id="membersSubtitleMobile" class="page-subtitle">{!! $membersSubtitleHtml !!}</p>
            </div>
            @include('cashier.members.partials.index-mobile-banner-actions')
        </div>
        <div id="adminSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <div class="search-wrapper admin-shop-search">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Rechercher un membre"
                       autocomplete="off"
                       value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Gestion des membres
            </h1>
            <p id="membersSubtitle" class="text-sm text-[var(--text-secondary)] mt-0.5">
                {!! $membersSubtitleHtml !!}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInputDesktop"
                           class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <a href="{{ route('cashier.members.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nouveau membre</span>
            </a>
        </div>
    </div>

    <x-ui.stat-grid class="mb-4">
        <x-ui.stat title="Total" :value="$members->total()" id="kpi-total" />
        <x-ui.stat title="Actifs" :value="$stats['active'] ?? 0" id="kpi-active" />
        <x-ui.stat title="Inactifs" :value="$stats['inactive'] ?? 0" id="kpi-inactive" />
        <x-ui.stat title="Commissions" :value="$stats['with_commissions'] ?? 0" id="kpi-with-commissions" />
    </x-ui.stat-grid>

    @include('cashier.members._mobile_list', ['members' => $members])

    {{-- TABLEAU DESKTOP --}}
    <div class="card p-3 sm:p-4 hidden md:block">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="membersTable">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Code</th>
                        <th>Grade</th>
                        <th>Code parrain</th>
                        <th>PV</th>
                        <th>Statut</th>
                        <th>Total CASH</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('cashier.members._table_rows', ['members' => $members])
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination unique (desktop + mobile, hors wrapper table) --}}
    <div id="paginationContainer" class="mt-3 sm:mt-4">
        @if($members->hasPages())
            <x-salang-pagination :paginator="$members" />
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = [
        document.getElementById('searchInput'),
        document.getElementById('searchInputDesktop'),
    ].filter(Boolean);
    let searchTimeout;

    function syncSearchInputs(value, source) {
        searchInputs.forEach((input) => {
            if (input !== source) {
                input.value = value;
            }
        });
    }

    function bindSearch(input) {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncSearchInputs(this.value, this);

            searchTimeout = setTimeout(() => {
                fetchMembers(query);
            }, 300);
        });
    }

    searchInputs.forEach(bindSearch);

    function updateSubtitles(doc) {
        const newDesktop = doc.getElementById('membersSubtitle');
        const newMobile = doc.getElementById('membersSubtitleMobile');
        const html = (newDesktop || newMobile) ? (newDesktop || newMobile).innerHTML : null;
        if (!html) {
            return;
        }
        ['membersSubtitle', 'membersSubtitleMobile'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.innerHTML = html;
            }
        });
    }

    function fetchMembers(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--cashier-stat-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Recherche en cours…</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.text())
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newTableBody = doc.getElementById('tableBody');
                const newPagination = doc.getElementById('paginationContainer');

                if (newTableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                const newMobile = doc.getElementById('cashierMemberMobileList');
                const currentMobile = document.getElementById('cashierMemberMobileList');
                if (newMobile && currentMobile) {
                    currentMobile.innerHTML = newMobile.innerHTML;
                }

                const paginationContainer = document.getElementById('paginationContainer');
                if (paginationContainer && newPagination) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                } else if (paginationContainer && !newPagination) {
                    paginationContainer.innerHTML = '';
                }

                updateSubtitles(doc);

                ['kpi-total', 'kpi-active', 'kpi-inactive', 'kpi-with-commissions'].forEach(id => {
                    const newEl = doc.getElementById(id);
                    const currentEl = document.getElementById(id);
                    if (newEl && currentEl) {
                        currentEl.textContent = newEl.textContent;
                    }
                });

                if (typeof window.initSalangPagination === 'function') {
                    window.initSalangPagination(document.getElementById('paginationContainer') || document);
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url.toString());
                }
            })
            .catch(() => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-8 text-[var(--cashier-stat-danger)]">
                            Une erreur est survenue lors de la recherche
                        </td>
                    </tr>
                `;
            });
    }
});
</script>
@endpush
@endsection
