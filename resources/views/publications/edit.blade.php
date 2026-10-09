@extends('layouts.app')

@section('title', 'Modifier — '.$publication->title)

@section('content')
<div class="supervision-page publication-form-page space-y-4 sm:space-y-6 max-w-2xl mx-auto">
    <x-member.catalog-banner
        eyebrow="Supervision · Salang"
        title="Modifier la publication"
        :sub="$publication->title"
        banner-class="supervision-catalog-banner">
        <x-slot:tools>
            <a href="{{ route('publications.show', $publication) }}" class="shop-banner-icon-btn" aria-label="Retour">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </x-slot:tools>
    </x-member.catalog-banner>

    <div class="supervision-page-header-desktop member-page-intro animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Modifier la publication</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">{{ $publication->title }}</p>
    </div>

    @if($errors->any())
        <div class="member-alert member-alert--error">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($publication->medias->isNotEmpty())
        <div class="card p-4 animate-fadeInUp delay-1">
            <h2 class="text-sm font-semibold mb-3">Médias actuels</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($publication->medias as $media)
                    <div class="relative rounded-lg overflow-hidden border border-[var(--border-color)]">
                        @if($media->type === 'photo')
                            <img src="{{ $media->url }}" alt="" class="w-full aspect-square object-cover">
                        @else
                            <video class="w-full aspect-square object-cover" controls preload="metadata" poster="{{ $media->thumbnail_url }}">
                                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                            </video>
                        @endif
                        <form action="{{ route('publications.media.destroy', $media) }}" method="POST" class="p-2 border-t border-[var(--border-light)]" onsubmit="return confirm('Supprimer ce média ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm w-full text-red-500">Supprimer</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('publications.update', $publication) }}"
          method="POST"
          enctype="multipart/form-data"
          class="publication-form-shell animate-fadeInUp delay-2">
        @csrf
        @method('PUT')

        <div class="card publication-form-card p-4 sm:p-6">
            @include('publications.partials.form', ['publication' => $publication])
        </div>

        <div class="publication-form-actions">
            <button type="submit" class="btn btn-primary publication-form-actions__primary">Enregistrer</button>
            <a href="{{ route('publications.show', $publication) }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>

    <form action="{{ route('publications.destroy', $publication) }}" method="POST" onsubmit="return confirm('Supprimer toute la publication ?');" class="text-right">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline btn-sm text-red-500 border-red-200">Supprimer la publication</button>
    </form>
</div>
@endsection
