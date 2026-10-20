@php
    $searchOpen = filled(request('search'));
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
    <a href="{{ route('cashier.members.create') }}" class="btn btn-primary btn-sm btn-round-icon" title="Nouveau membre" aria-label="Nouveau membre">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </a>
</div>
