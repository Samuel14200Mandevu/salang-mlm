@php
    $hasSearch = $isSupervision && filled($q ?? null);
    $filtersActive = $isSupervision && filled($statusFilter ?? null);
@endphp

<div class="questions-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1" id="questionsCatalogHead">
    <div class="shop-catalog-banner supervision-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">{{ $isSupervision ? 'Supervision · Salang' : 'Services · Salang' }}</p>
            <p class="shop-catalog-banner__title">{{ $isSupervision ? 'Questions reçues' : 'Mes questions' }}</p>
            <p class="shop-catalog-banner__sub">
                {{ ($isSupervision ? 'Support membres & caissiers' : 'Suivi de vos demandes').' · '.$questions->total().' conversation(s)' }}
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            @if($isSupervision)
                <button type="button"
                        class="shop-banner-icon-btn {{ $hasSearch ? 'is-active' : '' }}"
                        id="questionsSearchToggle"
                        aria-expanded="{{ $hasSearch ? 'true' : 'false' }}"
                        aria-controls="questionsSearchPanel"
                        aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <button type="button"
                        class="shop-banner-icon-btn {{ $filtersActive ? 'is-active' : '' }}"
                        id="questionsFilterToggle"
                        aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                        aria-controls="questionsFiltersPanel"
                        aria-label="Filtrer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            @else
                <a href="{{ auth()->user()?->memberQuestionsEntryUrl() ?? route('questions.create') }}" class="shop-banner-icon-btn" aria-label="Conversation avec le support">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    @if($isSupervision)
        <div class="shop-catalog-search-panel {{ $hasSearch ? 'is-open' : '' }}"
             id="questionsSearchPanel"
             aria-hidden="{{ $hasSearch ? 'false' : 'true' }}">
            <form method="GET" action="{{ route('questions.index') }}" class="member-shop-search search-wrapper">
                @if(filled($statusFilter ?? null))
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <span class="member-shop-search__icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="search"
                       name="q"
                       value="{{ $q ?? '' }}"
                       placeholder="Nom, email, sujet…"
                       inputmode="search"
                       autocomplete="off"
                       class="input search-input text-sm"
                       aria-label="Rechercher une question">
            </form>
        </div>

        <div class="shop-catalog-search-panel questions-filters-panel {{ $filtersActive ? 'is-open' : '' }}"
             id="questionsFiltersPanel"
             aria-hidden="{{ $filtersActive ? 'false' : 'true' }}">
            <div class="questions-filters-panel__inner">
                @include('questions.partials.filters-chips')
            </div>
        </div>
    @endif
</div>
