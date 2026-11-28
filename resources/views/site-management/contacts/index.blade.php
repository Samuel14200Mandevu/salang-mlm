@extends('layouts.app')

@section('title', 'Messages du site')

@section('content')
@php $hasSearch = $q !== ''; @endphp

{{-- Desktop — titre à gauche, recherche à droite (comme Utilisateurs admin) --}}
<div class="it-desktop-head it-desktop-only mb-6 items-center justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Messages du site officiel</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Demandes des visiteurs — accessible admin &amp; responsable IT</p>
    </div>
    <div class="flex items-center gap-2 min-w-0 flex-1 justify-end ml-4">
        <div class="header-search admin-search-full">
            <div class="search-wrapper">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="contactsSearchInputDesktop"
                       class="search-input"
                       placeholder="Nom, email, sujet…"
                       autocomplete="off"
                       value="{{ $q }}">
            </div>
        </div>
    </div>
</div>

{{-- Mobile --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Site officiel</p>
            <p class="shop-catalog-banner__title">Messages du site</p>
            <p class="shop-catalog-banner__sub">Demandes des visiteurs — admin &amp; responsable IT</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ $hasSearch ? 'is-active' : '' }}"
                    id="contactsSearchToggle"
                    aria-expanded="{{ $hasSearch ? 'true' : 'false' }}"
                    aria-controls="contactsSearchPanel"
                    aria-label="Rechercher un message">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="shop-catalog-search-panel {{ $hasSearch ? 'is-open' : '' }}"
         id="contactsSearchPanel"
         aria-hidden="{{ $hasSearch ? 'false' : 'true' }}">
        <div class="member-shop-search search-wrapper">
            <span class="member-shop-search__icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   id="contactsSearchInput"
                   value="{{ $q }}"
                   placeholder="Nom, email, sujet…"
                   inputmode="search"
                   autocomplete="off"
                   class="input search-input text-sm">
        </div>
    </div>
</div>

<div id="contactsStatGrid" class="follow-up-stat-grid mb-5">
    @foreach ([
        ['all', 'Tous', $counts['all']],
        ['new', 'Nouveaux', $counts['new']],
        ['read', 'Lus', $counts['read']],
        ['archived', 'Archivés', $counts['archived']],
    ] as [$key, $label, $count])
        <a href="{{ route('site-management.contacts.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => ($q ?? null) ?: null])) }}"
           class="rounded-lg border px-3 py-3 {{ ($status === $key || ($key === 'all' && $status === '')) ? 'border-[var(--color-primary-600)] bg-[var(--bg-secondary)]' : 'border-[var(--border-color)] bg-[var(--bg-card)]' }}">
            <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]">{{ $count }}</p>
            <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">{{ $label }}</p>
        </a>
    @endforeach
</div>

{{-- Mobile : liste type WhatsApp --}}
<div id="contactsMobileList" class="contact-convo-list it-mobile-only mb-4">
    @include('site-management.contacts._mobile_list')
</div>
<div id="contactsPaginationMobile" class="it-mobile-only mt-4">{{ $messages->links() }}</div>

{{-- Desktop : liste seule (lecture sur la page show) --}}
<div class="contact-gmail contact-gmail--list-only it-desktop-only">
    <aside class="contact-gmail__list-pane">
        <div id="contactsDesktopList" class="contact-gmail__list">
            @include('site-management.contacts._desktop_list', ['selectedMessage' => $selectedMessage ?? null])
        </div>
        <div id="contactsPagination" class="contact-gmail__pager">{{ $messages->links() }}</div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = [
        document.getElementById('contactsSearchInput'),
        document.getElementById('contactsSearchInputDesktop'),
    ].filter(Boolean);
    const searchToggle = document.getElementById('contactsSearchToggle');
    const searchPanel = document.getElementById('contactsSearchPanel');
    let searchTimeout;
    let requestId = 0;

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function () {
            const open = searchPanel.classList.toggle('is-open');
            searchToggle.classList.toggle('is-active', open);
            searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            searchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            const mobileInput = document.getElementById('contactsSearchInput');
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
                fetchContacts(query);
            }, 300);
        });
    });

    function fetchContacts(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.set('page', '1');

        const currentRequest = ++requestId;
        const desktopList = document.getElementById('contactsDesktopList');
        const mobileList = document.getElementById('contactsMobileList');

        if (desktopList) {
            desktopList.innerHTML = '<div class="contact-gmail-empty">Recherche en cours…</div>';
        }
        if (mobileList) {
            mobileList.innerHTML = '<div class="contact-convo-empty">Recherche en cours…</div>';
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

                const newDesktopList = doc.getElementById('contactsDesktopList');
                if (desktopList && newDesktopList) {
                    desktopList.innerHTML = newDesktopList.innerHTML;
                }

                const newMobileList = doc.getElementById('contactsMobileList');
                if (mobileList && newMobileList) {
                    mobileList.innerHTML = newMobileList.innerHTML;
                }

                ['contactsPagination', 'contactsPaginationMobile'].forEach(function (id) {
                    const el = document.getElementById(id);
                    const neu = doc.getElementById(id);
                    if (el) {
                        el.innerHTML = neu ? neu.innerHTML : '';
                    }
                });

                const statGrid = document.getElementById('contactsStatGrid');
                const newStatGrid = doc.getElementById('contactsStatGrid');
                if (statGrid && newStatGrid) {
                    statGrid.innerHTML = newStatGrid.innerHTML;
                }

                if (searchToggle) {
                    searchToggle.classList.toggle('is-active', !!query || (searchPanel && searchPanel.classList.contains('is-open')));
                }

                window.history.replaceState({}, '', url.toString());
            })
            .catch(function () {
                if (currentRequest !== requestId) {
                    return;
                }
                if (desktopList) {
                    desktopList.innerHTML = '<div class="contact-gmail-empty text-[var(--danger)]">Erreur de recherche</div>';
                }
                if (mobileList) {
                    mobileList.innerHTML = '<div class="contact-convo-empty text-[var(--danger)]">Erreur de recherche</div>';
                }
            });
    }
});
</script>
@endpush
