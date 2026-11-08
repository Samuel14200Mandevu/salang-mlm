@extends('layouts.app')

@section('title', $isSupervision ? 'Publications — Supervision' : 'Événements & promos')

@section('content')
@php
    $q = $q ?? '';
    $filtersActive = filled($typeFilter) || $activeFilter;
    $filterButtonLabel = match (true) {
        ($typeFilter ?? null) === 'event' => 'Événements',
        ($typeFilter ?? null) === 'promotion' => 'Promotions',
        $activeFilter => 'Actifs',
        default => 'Filtrer',
    };
    $filterQueryBase = array_filter([
        'q' => $q !== '' ? $q : null,
        'active' => $activeFilter ? '1' : null,
    ], fn ($v) => filled($v));
@endphp
<div class="supervision-page space-y-4 sm:space-y-6">
    @include('publications.partials.catalog-head', [
        'publications' => $publications,
        'isSupervision' => $isSupervision,
        'typeFilter' => $typeFilter,
        'activeFilter' => $activeFilter,
        'q' => $q,
    ])

    <div class="supervision-page-header-desktop animate-fadeInUp">
        <div class="supervision-page-header-desktop__row">
            <div class="member-page-intro">
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                    {{ $isSupervision ? 'Publications' : 'Événements & promos' }}
                </h1>
                <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                    Photos et vidéos de l’équipe Salang
                    @if($q !== '')
                        <span class="text-xs text-[var(--text-tertiary)]">· « {{ $q }} »</span>
                    @endif
                </p>
            </div>

            <div class="supervision-header-actions">
                <form method="GET" action="{{ route('publications.index') }}" class="header-search publications-desktop-search">
                    @if(filled($typeFilter ?? null))
                        <input type="hidden" name="type" value="{{ $typeFilter }}">
                    @endif
                    @if($activeFilter)
                        <input type="hidden" name="active" value="1">
                    @endif
                    <div class="search-wrapper">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               name="q"
                               class="search-input"
                               placeholder="Rechercher une publication"
                               autocomplete="off"
                               value="{{ $q }}"
                               aria-label="Rechercher une publication">
                    </div>
                </form>

                <div class="supervision-filter-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="btn btn-outline btn-sm {{ $filtersActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                            :aria-expanded="open"
                            aria-haspopup="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>{{ $filterButtonLabel }}</span>
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition
                         @click.outside="open = false"
                         class="users-filter-menu">
                        <a href="{{ route('publications.index', array_filter(['q' => $q !== '' ? $q : null, 'active' => $activeFilter ? '1' : null], fn ($v) => filled($v))) }}"
                           class="{{ ! filled($typeFilter) ? 'is-active' : '' }}">
                            <span>Tous</span>
                        </a>
                        <a href="{{ route('publications.index', array_merge($filterQueryBase, ['type' => 'event'])) }}"
                           class="{{ ($typeFilter ?? null) === 'event' ? 'is-active' : '' }}">
                            <span>Événements</span>
                        </a>
                        <a href="{{ route('publications.index', array_merge($filterQueryBase, ['type' => 'promotion'])) }}"
                           class="{{ ($typeFilter ?? null) === 'promotion' ? 'is-active' : '' }}">
                            <span>Promotions</span>
                        </a>
                        @php
                            $activeToggle = array_filter([
                                'q' => $q !== '' ? $q : null,
                                'type' => filled($typeFilter) ? $typeFilter : null,
                            ], fn ($v) => filled($v));
                            if (! $activeFilter) {
                                $activeToggle['active'] = '1';
                            }
                        @endphp
                        <a href="{{ route('publications.index', $activeToggle) }}"
                           class="{{ $activeFilter ? 'is-active' : '' }}">
                            <span>Actifs seulement</span>
                        </a>
                    </div>
                </div>

                @if($isSupervision)
                    <a href="{{ route('publications.create') }}" class="btn btn-primary btn-sm sm:btn-md supervision-header-cta">Nouvelle publication</a>
                @endif
            </div>
        </div>
    </div>

    @if(! $isSupervision)
        @include('partials.member.services-featured-carousel')
    @endif

    <div class="publication-grid animate-fadeInUp delay-1">
        @forelse($publications as $publication)
            @php
                $cover = $publication->medias->firstWhere('type', 'photo') ?? $publication->medias->first();
            @endphp
            <a href="{{ route('publications.show', $publication) }}" class="publication-card card overflow-hidden hover:border-primary-500 transition-colors">
                <div class="publication-card__media aspect-[4/3] bg-[var(--bg-secondary)] relative">
                    @if($cover && $cover->type === 'photo')
                        <img src="{{ $cover->url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    @elseif($cover && $cover->type === 'video')
                        <video class="w-full h-full object-cover" muted playsinline preload="metadata" poster="{{ $cover->thumbnail_url }}">
                            <source src="{{ $cover->url }}" type="{{ $cover->mime_type }}">
                        </video>
                        <span class="publication-card__play" aria-hidden="true">▶</span>
                    @else
                        <div class="flex items-center justify-center h-full text-[var(--text-tertiary)] text-sm">Sans média</div>
                    @endif
                    <span class="publication-card__badge badge {{ $publication->type === 'event' ? 'badge-info' : 'badge-purple' }}">
                        {{ $publication->type === 'event' ? 'Événement' : 'Promo' }}
                    </span>
                </div>
                <div class="p-3">
                    <p class="font-semibold text-sm text-[var(--text-primary)] line-clamp-2">{{ $publication->title }}</p>
                    @if($publication->start_date)
                        <p class="text-xs text-[var(--text-secondary)] mt-1">{{ $publication->start_date->format('d/m/Y') }}
                            @if($publication->end_date) — {{ $publication->end_date->format('d/m/Y') }} @endif
                        </p>
                    @endif
                    @if($isSupervision && ! $publication->is_published)
                        <span class="badge badge-neutral text-[10px] mt-2">Brouillon</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full card p-8 text-center text-[var(--text-secondary)]">
                <p class="font-medium">Aucune publication</p>
                @if($q !== '' || $filtersActive)
                    <p class="text-sm mt-1">Aucun résultat pour ces filtres.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if($publications->hasPages())
        <div class="mt-4">{{ $publications->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var head = document.getElementById('publicationsCatalogHead');
    var searchToggle = document.getElementById('publicationsSearchToggle');
    var searchPanel = document.getElementById('publicationsSearchPanel');
    var filterToggle = document.getElementById('publicationsFilterToggle');
    var filterPanel = document.getElementById('publicationsFiltersPanel');

    function setPanel(panel, toggle, open) {
        if (!panel || !toggle) return;
        panel.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    function syncHead() {
        if (!head) return;
        var open = (searchPanel && searchPanel.classList.contains('is-open'))
            || (filterPanel && filterPanel.classList.contains('is-open'));
        head.classList.toggle('is-filters-open', open);
    }

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function () {
            var open = !searchPanel.classList.contains('is-open');
            setPanel(searchPanel, searchToggle, open);
            if (open) {
                setPanel(filterPanel, filterToggle, false);
                var input = searchPanel.querySelector('input[type="search"]');
                if (input) input.focus();
            }
            syncHead();
        });
    }

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function () {
            var open = !filterPanel.classList.contains('is-open');
            setPanel(filterPanel, filterToggle, open);
            if (open) setPanel(searchPanel, searchToggle, false);
            syncHead();
        });
    }
});
</script>
@endpush
