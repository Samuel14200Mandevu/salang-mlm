@php
    $filtersActive = filled($typeFilter) || $activeFilter;
@endphp

<div class="publications-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1" id="publicationsCatalogHead">
    <div class="shop-catalog-banner supervision-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">{{ $isSupervision ? 'Supervision · Salang' : 'Services · Salang' }}</p>
            <p class="shop-catalog-banner__title">{{ $isSupervision ? 'Publications' : 'Événements & promos' }}</p>
            <p class="shop-catalog-banner__sub">{{ $publications->total() }} publication(s) · Galerie photos & vidéos</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ $filtersActive ? 'is-active' : '' }}"
                    id="publicationsFilterToggle"
                    aria-expanded="false"
                    aria-controls="publicationsFiltersPanel"
                    aria-label="Filtrer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </button>
            @if($isSupervision)
                <a href="{{ route('publications.create') }}" class="shop-banner-icon-btn" aria-label="Nouvelle publication">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    <div class="shop-catalog-search-panel publications-filters-panel"
         id="publicationsFiltersPanel"
         aria-hidden="true">
        <div class="publications-filters-panel__inner">
            @include('publications.partials.filters-chips')
        </div>
    </div>
</div>
