@extends('layouts.app')

@section('title', $isSupervision ? 'Publications — Supervision' : 'Événements & promos')

@section('content')
<div class="supervision-page space-y-4 sm:space-y-6">
    @include('publications.partials.catalog-head', compact('publications', 'isSupervision', 'typeFilter', 'activeFilter'))

    <div class="supervision-page-header-desktop flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                {{ $isSupervision ? 'Publications' : 'Événements & promos' }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Photos et vidéos de l’équipe Salang</p>
        </div>
        @if($isSupervision)
            <a href="{{ route('publications.create') }}" class="btn btn-primary btn-sm sm:btn-md shrink-0">Nouvelle publication</a>
        @endif
    </div>

    @if(! $isSupervision)
        @include('partials.member.services-featured-carousel')
    @endif

    <div class="publications-filters-desktop supervision-filters-card card p-3 animate-fadeInUp delay-1 hidden md:block">
        @include('publications.partials.filters-form', ['filterFormId' => 'publications-type-filter-desktop'])
    </div>

    <div class="publication-grid animate-fadeInUp delay-2">
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
                        {{ $publication->type === 'event' ? 'Événement' : 'Promotion' }}
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
    var toggle = document.getElementById('publicationsFilterToggle');
    var panel = document.getElementById('publicationsFiltersPanel');
    var head = document.getElementById('publicationsCatalogHead');
    if (!toggle || !panel) return;

    function setFiltersOpen(open) {
        panel.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (head) {
            head.classList.toggle('is-filters-open', open);
        }
    }

    toggle.addEventListener('click', function () {
        setFiltersOpen(!panel.classList.contains('is-open'));
    });
});
</script>
@endpush
