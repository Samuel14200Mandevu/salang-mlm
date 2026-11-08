@php
    $searchOpen = filled(request('search'));
    $currentStatus = request('status');
    $filterActive = in_array($currentStatus, ['active', 'inactive'], true);
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
@endphp
<div class="admin-title-banner__tools admin-title-banner__tools--members-index">
    <button type="button"
            class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
            data-admin-search-toggle="1"
            aria-controls="adminSearchPanel"
            aria-expanded="{{ $searchOpen ? 'true' : 'false' }}"
            aria-label="Rechercher">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>
    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="admin-banner-icon-btn{{ $filterActive ? ' is-active' : '' }}"
                :aria-expanded="open"
                aria-label="Filtrer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </button>
        <div x-show="open"
             x-cloak
             x-transition
             @click.outside="open = false"
             class="users-filter-menu">
            <a href="{{ route('cashier.members', $filterQueryBase) }}"
               class="filter-link {{ ! $filterActive ? 'is-active' : '' }}">
                <span>Tous</span>
            </a>
            <a href="{{ route('cashier.members', array_merge($filterQueryBase, ['status' => 'active'])) }}"
               class="filter-link {{ $currentStatus === 'active' ? 'is-active' : '' }}">
                <span>Actifs</span>
                <span class="filter-count">{{ $stats['active'] ?? 0 }}</span>
            </a>
            <a href="{{ route('cashier.members', array_merge($filterQueryBase, ['status' => 'inactive'])) }}"
               class="filter-link {{ $currentStatus === 'inactive' ? 'is-active' : '' }}">
                <span>Inactifs</span>
                <span class="filter-count">{{ $stats['inactive'] ?? 0 }}</span>
            </a>
        </div>
    </div>
    <a href="{{ route('cashier.members.create') }}" class="btn btn-primary btn-sm btn-round-icon" title="Nouveau membre" aria-label="Nouveau membre">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </a>
</div>
