@extends('layouts.app')

@section('title', $publication->title)

@section('content')
@php
    $isEvent = $publication->type === 'event';
    $typeLabel = $isEvent ? 'Événement' : 'Promotion';
    $photos = $publication->medias->where('type', 'photo')->values();
    $videos = $publication->medias->where('type', 'video')->values();
    $documents = $publication->medias->where('type', 'document')->values();
    $cover = $photos->first() ?? $videos->first();
    $galleryPhotos = $photos->slice($cover && ($cover->type ?? null) === 'photo' ? 1 : 0)->values();

    $dateLabel = null;
    if ($publication->start_date) {
        $dateLabel = $publication->start_date->format('d/m/Y');
        if ($publication->end_date && ! $publication->start_date->equalTo($publication->end_date)) {
            $dateLabel .= ' — '.$publication->end_date->format('d/m/Y');
        }
    }

    $publicationSub = $typeLabel;
    if ($dateLabel) {
        $publicationSub .= ' · '.$dateLabel;
    }
@endphp
<div class="supervision-page publication-show-page space-y-4 sm:space-y-5 max-w-4xl mx-auto">
    <x-member.catalog-banner
        :eyebrow="$isSupervision ? 'Supervision · Salang' : 'Services · Salang'"
        :title="$publication->title"
        :sub="$publicationSub"
        banner-class="supervision-catalog-banner">
        <x-slot:tools>
            <a href="{{ route('publications.index') }}" class="shop-banner-icon-btn" aria-label="Retour à la galerie">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            @if($isSupervision)
                <a href="{{ route('publications.edit', $publication) }}" class="shop-banner-icon-btn" aria-label="Modifier">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
            @endif
        </x-slot:tools>
    </x-member.catalog-banner>

    <div class="supervision-page-header-desktop flex flex-wrap items-start justify-between gap-3 animate-fadeInUp">
        <div class="min-w-0">
            <a href="{{ route('publications.index') }}" class="publication-show-back">← Galerie</a>
            <h1 class="publication-show-title">{{ $publication->title }}</h1>
        </div>
        @if($isSupervision)
            <a href="{{ route('publications.edit', $publication) }}" class="btn btn-outline btn-sm shrink-0">Modifier</a>
        @endif
    </div>

    <div class="supervision-detail-stack publication-show-stack">
        @if($cover)
            <figure class="publication-show-cover animate-fadeInUp delay-1">
                @if(($cover->type ?? '') === 'video')
                    <video
                        class="publication-show-cover__media"
                        controls
                        poster="{{ $cover->thumbnail_url }}"
                        preload="metadata"
                    >
                        <source src="{{ $cover->url }}" type="{{ $cover->mime_type }}">
                    </video>
                @else
                    <button
                        type="button"
                        class="publication-show-cover__btn"
                        data-lightbox-src="{{ $cover->url }}"
                        data-lightbox-alt="{{ $publication->title }}"
                        aria-label="Agrandir la photo"
                    >
                        <img
                            src="{{ $cover->url }}"
                            alt="{{ $publication->title }}"
                            class="publication-show-cover__media"
                            loading="eager"
                            decoding="async"
                        >
                    </button>
                @endif
                <figcaption class="publication-show-cover__meta">
                    <span class="publication-show-badge {{ $isEvent ? 'is-event' : 'is-promo' }}">{{ $typeLabel }}</span>
                    @if($dateLabel)
                        <span class="publication-show-cover__date">{{ $dateLabel }}</span>
                    @endif
                    @if($isSupervision && ! $publication->is_published)
                        <span class="publication-show-badge is-draft">Non publié</span>
                    @endif
                </figcaption>
            </figure>
        @else
            <div class="publication-show-meta-bar animate-fadeInUp delay-1">
                <span class="publication-show-badge {{ $isEvent ? 'is-event' : 'is-promo' }}">{{ $typeLabel }}</span>
                @if($dateLabel)
                    <span class="publication-show-meta-bar__date">{{ $dateLabel }}</span>
                @endif
                @if($isSupervision && ! $publication->is_published)
                    <span class="publication-show-badge is-draft">Non publié</span>
                @endif
            </div>
        @endif

        @if($publication->description)
            <article class="publication-show-body card animate-fadeInUp delay-2">
                <h2 class="publication-show-section-label">À propos</h2>
                <div class="publication-show-body__text text-pretty">{{ $publication->description }}</div>
            </article>
        @endif

        @if($galleryPhotos->isNotEmpty())
            <section class="publication-show-gallery animate-fadeInUp delay-2" aria-labelledby="pubGalleryHeading">
                <div class="publication-show-section-head">
                    <h2 id="pubGalleryHeading" class="publication-show-section-label">Galerie</h2>
                    <span class="publication-show-section-count">{{ $galleryPhotos->count() }}</span>
                </div>
                <div class="publication-show-photos">
                    @foreach($galleryPhotos as $photo)
                        <button
                            type="button"
                            class="publication-show-photo"
                            data-lightbox-src="{{ $photo->url }}"
                            data-lightbox-alt="{{ $publication->title }}"
                            aria-label="Voir la photo {{ $loop->iteration }}"
                        >
                            <img
                                src="{{ $photo->url }}"
                                alt=""
                                class="publication-show-photo__img"
                                loading="lazy"
                                decoding="async"
                            >
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        @if($videos->isNotEmpty() && ! (($cover->type ?? null) === 'video' && $videos->count() === 1 && $videos->first()->is($cover)))
            <section class="publication-show-videos animate-fadeInUp delay-3" aria-labelledby="pubVideosHeading">
                <div class="publication-show-section-head">
                    <h2 id="pubVideosHeading" class="publication-show-section-label">Vidéos</h2>
                    <span class="publication-show-section-count">{{ $videos->count() }}</span>
                </div>
                <div class="publication-show-videos__list">
                    @foreach($videos as $video)
                        @if($cover && ($cover->type ?? null) === 'video' && $video->is($cover))
                            @continue
                        @endif
                        <div class="publication-show-video card">
                            <video
                                controls
                                class="publication-show-video__player"
                                poster="{{ $video->thumbnail_url }}"
                                preload="metadata"
                            >
                                <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                            </video>
                            @if($video->original_name)
                                <p class="publication-show-video__name">{{ $video->original_name }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($documents->isNotEmpty())
            <section class="publication-show-docs animate-fadeInUp delay-3" aria-labelledby="pubDocsHeading">
                <div class="publication-show-section-head">
                    <h2 id="pubDocsHeading" class="publication-show-section-label">Documents</h2>
                    <span class="publication-show-section-count">{{ $documents->count() }}</span>
                </div>
                <ul class="publication-show-docs__list">
                    @foreach($documents as $doc)
                        <li>
                            <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="publication-show-doc">
                                <span class="publication-show-doc__badge" aria-hidden="true">PDF</span>
                                <span class="publication-show-doc__meta">
                                    <span class="publication-show-doc__name">{{ $doc->original_name ?: 'Document PDF' }}</span>
                                    <span class="publication-show-doc__hint">Ouvrir / télécharger</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($photos->isEmpty() && $videos->isEmpty() && $documents->isEmpty() && ! $cover)
            <div class="publication-show-empty card">Aucun média attaché.</div>
        @endif
    </div>
</div>

<div class="publication-lightbox" id="publicationLightbox" hidden>
    <button type="button" class="publication-lightbox__backdrop" data-lightbox-close aria-label="Fermer"></button>
    <div class="publication-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Photo en grand">
        <button type="button" class="publication-lightbox__close" data-lightbox-close aria-label="Fermer">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img src="" alt="" class="publication-lightbox__img" id="publicationLightboxImg">
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('publicationLightbox');
    var img = document.getElementById('publicationLightboxImg');
    if (!box || !img) return;

    function openLightbox(src, alt) {
        img.src = src;
        img.alt = alt || '';
        box.hidden = false;
        document.documentElement.classList.add('publication-lightbox-open');
    }

    function closeLightbox() {
        box.hidden = true;
        img.removeAttribute('src');
        img.alt = '';
        document.documentElement.classList.remove('publication-lightbox-open');
    }

    document.querySelectorAll('[data-lightbox-src]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLightbox(btn.getAttribute('data-lightbox-src'), btn.getAttribute('data-lightbox-alt'));
        });
    });

    box.querySelectorAll('[data-lightbox-close]').forEach(function (el) {
        el.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !box.hidden) closeLightbox();
    });
});
</script>
@endpush
@endsection
