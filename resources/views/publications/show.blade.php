@extends('layouts.app')

@section('title', $publication->title)

@section('content')
@php
    $publicationSub = ($publication->type === 'event' ? 'Événement' : 'Promotion');
    if ($publication->start_date) {
        $publicationSub .= ' · '.$publication->start_date->format('d/m/Y');
        if ($publication->end_date) {
            $publicationSub .= ' — '.$publication->end_date->format('d/m/Y');
        }
    }
@endphp
<div class="supervision-page space-y-4 sm:space-y-6 max-w-4xl mx-auto">
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
            <a href="{{ route('publications.index') }}" class="text-sm text-primary-500 hover:underline">← Galerie</a>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] mt-1">{{ $publication->title }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-2">
                <span class="badge {{ $publication->type === 'event' ? 'badge-info' : 'badge-purple' }}">
                    {{ $publication->type === 'event' ? 'Événement' : 'Promotion' }}
                </span>
                @if($publication->start_date)
                    <span class="text-xs text-[var(--text-secondary)]">
                        {{ $publication->start_date->format('d/m/Y') }}
                        @if($publication->end_date) — {{ $publication->end_date->format('d/m/Y') }} @endif
                    </span>
                @endif
                @if($isSupervision && ! $publication->is_published)
                    <span class="badge badge-neutral text-[10px]">Non publié</span>
                @endif
            </div>
        </div>
        @if($isSupervision)
            <a href="{{ route('publications.edit', $publication) }}" class="btn btn-outline btn-sm shrink-0">Modifier</a>
        @endif
    </div>

    <div class="supervision-detail-stack">

    @if($publication->description)
        <div class="card p-4 text-sm sm:text-base text-[var(--text-primary)] whitespace-pre-wrap animate-fadeInUp delay-1">
            {{ $publication->description }}
        </div>
    @endif

    @if($publication->medias->isNotEmpty())
        <div class="animate-fadeInUp delay-2">
            <h2 class="text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wide mb-3">Galerie</h2>
            <div class="publication-show-photos grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 mb-4">
                @foreach($publication->medias->where('type', 'photo') as $photo)
                    <a href="{{ $photo->url }}" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-[var(--border-color)]">
                        <img src="{{ $photo->url }}" alt="" class="w-full aspect-square object-cover hover:opacity-95 transition-opacity" loading="lazy">
                    </a>
                @endforeach
            </div>
            <div class="space-y-4">
                @foreach($publication->medias->where('type', 'video') as $video)
                    <div class="card p-2 sm:p-3 overflow-hidden">
                        <video controls class="w-full max-h-[480px] rounded-md bg-black" poster="{{ $video->thumbnail_url }}" preload="metadata">
                            <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                        </video>
                        <p class="text-xs text-[var(--text-tertiary)] mt-2 truncate">{{ $video->original_name }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card p-8 text-center text-[var(--text-secondary)]">Aucun média attaché.</div>
    @endif
    </div>
</div>
@endsection
