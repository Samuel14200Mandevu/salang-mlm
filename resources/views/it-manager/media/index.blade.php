@extends('layouts.app')

@section('title', 'Médias')

@push('styles')
<style>
/* Médias desktop — mêmes primitives que l’accueil IT (.it-dash) */
.it-manager-shell .it-media-desk .card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.it-manager-shell .it-media-desk .stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem 1.25rem;
}
.it-manager-shell .it-media-desk .stat-label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-media-desk .stat-value {
    font-size: 1.375rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 0.125rem;
    color: var(--text-primary);
}
.it-manager-shell .it-media-desk .stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(30, 93, 173, 0.14);
    color: #1E5DAD;
}
.it-manager-shell .it-media-desk .stat-icon.is-success {
    background: rgba(28, 126, 74, 0.14);
    color: #1C7E4A;
}
.it-manager-shell .it-media-desk .upload-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}
.it-manager-shell .it-media-desk .upload-row input[type="file"] {
    flex: 1 1 14rem;
    min-width: 0;
    font-size: 0.813rem;
    color: var(--text-secondary);
}
.it-manager-shell .it-media-desk .media-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}
.it-manager-shell .it-media-desk .media-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
}
.it-manager-shell .it-media-desk .media-card__img {
    width: 100%;
    height: 8.5rem;
    object-fit: cover;
    background: var(--bg-secondary);
    display: block;
}
.it-manager-shell .it-media-desk .media-card__body {
    padding: 0.65rem 0.75rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
}
.it-manager-shell .it-media-desk .media-card__name {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.it-manager-shell .it-media-desk .media-card__meta {
    margin: 0;
    font-size: 0.625rem;
    color: var(--text-tertiary);
}
.it-manager-shell .it-media-desk .media-card__url {
    width: 100%;
    font-size: 0.65rem;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    padding: 0.35rem 0.5rem;
    outline: none;
}
.it-manager-shell .it-media-desk .media-card__url:focus {
    border-color: var(--primary-blue, #1E5DAD);
    box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.12);
}
.it-manager-shell .it-media-desk .media-card__actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-top: 0.15rem;
}
.it-manager-shell .it-media-desk .empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}
@media (max-width: 1100px) {
    .it-manager-shell .it-media-desk .media-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 900px) {
    .it-manager-shell .it-media-desk .media-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
@endpush

@section('content')
@php
    $totalBytes = $files->sum('size');
    $totalLabel = $totalBytes >= 1048576
        ? number_format($totalBytes / 1048576, 1).' Mo'
        : number_format(max($totalBytes / 1024, 0), 0).' Ko';
@endphp

{{-- Desktop : langage accueil IT --}}
<div class="it-media-desk it-desktop-only space-y-4">
    <div class="animate-fadeInUp flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Médias / galerie</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Images pour le site et les publications (max 4 Mo).
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 max-w-xl animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Fichiers</p>
                    <p class="stat-value">{{ $files->count() }}</p>
                </div>
                <div class="stat-icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Volume</p>
                    <p class="stat-value">{{ $totalLabel }}</p>
                </div>
                <div class="stat-icon is-success" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('it-manager.media.store') }}"
          enctype="multipart/form-data"
          class="card animate-fadeInUp delay-2">
        @csrf
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <p class="text-sm font-semibold text-[var(--text-primary)]">Ajouter une image</p>
                <p class="text-xs text-[var(--text-tertiary)] mt-0.5">JPEG, PNG, GIF, WebP ou SVG · 4 Mo max</p>
            </div>
        </div>
        <div class="upload-row">
            <input type="file" name="image" accept="image/*" required aria-label="Choisir une image">
            <button type="submit" class="btn btn-primary btn-sm shrink-0">Uploader</button>
        </div>
        @error('image')
            <p class="text-xs text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </form>

    <section class="animate-fadeInUp delay-3">
        <div class="flex items-center justify-between gap-2 mb-3">
            <p class="text-sm font-semibold text-[var(--text-primary)]">Galerie</p>
            <p class="text-xs text-[var(--text-tertiary)]">Cliquez l’URL pour la copier</p>
        </div>

        @if ($files->isEmpty())
            <div class="card empty-state">Aucune image pour l’instant.</div>
        @else
            <div class="media-grid">
                @foreach ($files as $file)
                    <figure class="media-card">
                        <img src="{{ $file['url'] }}"
                             alt="{{ $file['name'] }}"
                             class="media-card__img"
                             loading="lazy">
                        <figcaption class="media-card__body">
                            <p class="media-card__name" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
                            <p class="media-card__meta">
                                {{ $file['size'] >= 1048576
                                    ? number_format($file['size'] / 1048576, 1).' Mo'
                                    : number_format($file['size'] / 1024, 0).' Ko' }}
                                · {{ \Illuminate\Support\Carbon::createFromTimestamp($file['updated_at'])->diffForHumans() }}
                            </p>
                            <input type="text"
                                   readonly
                                   value="{{ $file['url'] }}"
                                   class="media-card__url"
                                   onclick="this.select()"
                                   aria-label="URL de {{ $file['name'] }}">
                            <div class="media-card__actions">
                                <a href="{{ $file['url'] }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="text-xs font-semibold text-[var(--primary-blue,#1E5DAD)]">Ouvrir</a>
                                <form method="POST"
                                      action="{{ route('it-manager.media.destroy') }}"
                                      onsubmit="return confirm('Supprimer cette image ?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $file['path'] }}">
                                    <button type="submit" class="text-xs font-semibold text-rose-600">Supprimer</button>
                                </form>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</div>

{{-- Mobile : inchangé --}}
<div class="it-mobile-only">
    <div class="member-page-intro mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Médias / galerie</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Images pour le site et les publications (max 4 Mo).</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('it-manager.media.store') }}" enctype="multipart/form-data"
          class="mb-6 flex flex-col sm:flex-row gap-2 items-start rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        @csrf
        <input type="file" name="image" accept="image/*" required
               class="text-sm text-[var(--text-secondary)]">
        <button type="submit" class="btn btn-primary">Uploader</button>
    </form>

    @if ($files->isEmpty())
        <p class="text-sm text-[var(--text-muted)]">Aucune image pour l’instant.</p>
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach ($files as $file)
                <figure class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="w-full h-32 object-cover" loading="lazy">
                    <figcaption class="p-2 space-y-1">
                        <p class="text-xs truncate text-[var(--text-secondary)]" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
                        <input type="text" readonly value="{{ $file['url'] }}"
                               class="w-full text-[0.65rem] rounded border border-[var(--border-light)] bg-[var(--bg-secondary)] px-1.5 py-1"
                               onclick="this.select()">
                        <form method="POST" action="{{ route('it-manager.media.destroy') }}"
                              onsubmit="return confirm('Supprimer cette image ?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="path" value="{{ $file['path'] }}">
                            <button type="submit" class="text-xs font-semibold text-rose-600">Supprimer</button>
                        </form>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</div>
@endsection
