@php
    $hasSearch = filled($q ?? null);
    $filtersActive = ($status ?? 'all') !== 'all' || ($category ?? 'all') !== 'all';
@endphp

<div class="notifications-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1"
     id="notificationsCatalogHead">
    <div class="shop-catalog-banner notifications-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Compte</p>
            <p class="shop-catalog-banner__title">Notifications</p>
            <p class="shop-catalog-banner__sub">
                {{ $unreadCount ?? 0 }} non lue(s) · {{ $totalCount ?? $notifications->total() }} notification(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ $hasSearch ? 'is-active' : '' }}"
                    id="notificationsSearchToggle"
                    aria-expanded="{{ $hasSearch ? 'true' : 'false' }}"
                    aria-controls="notificationsSearchPanel"
                    aria-label="Rechercher">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <button type="button"
                    class="shop-banner-icon-btn {{ $filtersActive ? 'is-active' : '' }}"
                    id="notificationsFilterToggle"
                    aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                    aria-controls="notificationsFiltersPanel"
                    aria-label="Filtrer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </button>
            @if(($unreadCount ?? 0) > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="shop-banner-icon-btn" aria-label="Tout marquer comme lu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="shop-catalog-search-panel {{ $hasSearch ? 'is-open' : '' }}"
         id="notificationsSearchPanel"
         aria-hidden="{{ $hasSearch ? 'false' : 'true' }}">
        <form method="GET" action="{{ route('notifications.index') }}" class="member-shop-search search-wrapper">
            @if(($status ?? 'all') !== 'all')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if(($category ?? 'all') !== 'all')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <span class="member-shop-search__icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   name="q"
                   value="{{ $q ?? '' }}"
                   placeholder="Titre, message…"
                   inputmode="search"
                   autocomplete="off"
                   class="input search-input text-sm"
                   aria-label="Rechercher une notification">
        </form>
    </div>

    <div class="shop-catalog-search-panel notifications-filters-panel {{ $filtersActive ? 'is-open' : '' }}"
         id="notificationsFiltersPanel"
         aria-hidden="{{ $filtersActive ? 'false' : 'true' }}">
        <form method="GET" action="{{ route('notifications.index') }}" class="notifications-filters-panel__inner space-y-2">
            @if(filled($q ?? null))
                <input type="hidden" name="q" value="{{ $q }}">
            @endif
            <div>
                <label for="notif-status-mobile" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">État</label>
                <select id="notif-status-mobile" name="status" class="form-control w-full text-sm">
                    <option value="all" @selected(($status ?? 'all') === 'all')>Toutes</option>
                    <option value="unread" @selected(($status ?? '') === 'unread')>Non lues</option>
                    <option value="read" @selected(($status ?? '') === 'read')>Lues</option>
                </select>
            </div>
            <div>
                <label for="notif-category-mobile" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Catégorie</label>
                <select id="notif-category-mobile" name="category" class="form-control w-full text-sm">
                    @foreach(($categories ?? \App\Support\NotificationPresenter::CATEGORIES) as $key => $label)
                        <option value="{{ $key }}" @selected(($category ?? 'all') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm">Effacer</a>
            </div>
        </form>
    </div>
</div>
