@extends('layouts.app')

@section('title', $isSupervision ? 'Questions reçues' : 'Mes questions')

@section('content')
@php
    $q = $q ?? '';
    $filtersActive = filled($statusFilter ?? null);
    $filterButtonLabel = match ($statusFilter ?? null) {
        'open' => 'Ouvertes',
        'resolved' => 'Résolues',
        default => 'Filtrer',
    };
    $filterQueryBase = array_filter([
        'q' => $q !== '' ? $q : null,
    ], fn ($v) => filled($v));
@endphp
<div class="supervision-page space-y-4 sm:space-y-6">
    @include('questions.partials.catalog-head', [
        'questions' => $questions,
        'isSupervision' => $isSupervision,
        'statusFilter' => $statusFilter ?? null,
        'q' => $q,
    ])

    <div class="supervision-page-header-desktop flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                {{ $isSupervision ? 'Questions reçues' : 'Mes questions' }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $isSupervision ? 'Répondez aux membres et caissiers' : 'Suivez vos demandes à l’équipe' }}
                @if($isSupervision && $q !== '')
                    <span class="text-xs text-[var(--text-tertiary)]">· « {{ $q }} »</span>
                @endif
            </p>
        </div>

        @if($isSupervision)
            <div class="flex items-center gap-2 flex-wrap justify-end min-w-0">
                <form method="GET" action="{{ route('questions.index') }}" class="header-search admin-search-full questions-desktop-search">
                    @if(filled($statusFilter ?? null))
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    <div class="search-wrapper">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               name="q"
                               class="search-input"
                               placeholder="Rechercher (nom, email, sujet)"
                               autocomplete="off"
                               value="{{ $q }}"
                               aria-label="Rechercher une question">
                    </div>
                </form>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
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
                        <a href="{{ route('questions.index', $filterQueryBase) }}"
                           class="{{ ! $filtersActive ? 'is-active' : '' }}">
                            <span>Tous</span>
                        </a>
                        <a href="{{ route('questions.index', array_merge($filterQueryBase, ['status' => 'open'])) }}"
                           class="{{ ($statusFilter ?? null) === 'open' ? 'is-active' : '' }}">
                            <span>Ouvertes</span>
                        </a>
                        <a href="{{ route('questions.index', array_merge($filterQueryBase, ['status' => 'resolved'])) }}"
                           class="{{ ($statusFilter ?? null) === 'resolved' ? 'is-active' : '' }}">
                            <span>Résolues</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <a href="{{ route('questions.create') }}" class="btn btn-primary btn-sm sm:btn-md shrink-0">
                Poser une question
            </a>
        @endif
    </div>

    <div class="question-convo-list supervision-questions-list card overflow-hidden animate-fadeInUp delay-1">
        @forelse($questions as $question)
            @include('questions.partials.conversation-row', compact('question', 'isSupervision'))
        @empty
            <div class="p-8 text-center text-[var(--text-secondary)]">
                <p class="font-medium">Aucune conversation</p>
                <p class="text-sm mt-1">
                    @if($isSupervision && ($q !== '' || $filtersActive))
                        Aucun résultat pour ces filtres.
                    @elseif($isSupervision)
                        Les questions des membres apparaîtront ici.
                    @else
                        Utilisez « Poser une question » pour contacter l’équipe.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
        <div class="mt-4">{{ $conversations->links() }}</div>
    @endif
</div>

@if($isSupervision)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var head = document.getElementById('questionsCatalogHead');
    var searchToggle = document.getElementById('questionsSearchToggle');
    var searchPanel = document.getElementById('questionsSearchPanel');
    var filterToggle = document.getElementById('questionsFilterToggle');
    var filterPanel = document.getElementById('questionsFiltersPanel');

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
@endif
@endsection
