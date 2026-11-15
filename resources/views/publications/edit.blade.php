@extends('layouts.app')

@section('title', 'Modifier — '.$publication->title)

@section('content')
<div class="supervision-page publication-form-page publication-form-page--social max-w-xl mx-auto">
    <header class="publication-social-topbar publication-social-topbar--outside">
        <a href="{{ route('publications.show', $publication) }}" class="publication-social-topbar__back" aria-label="Retour">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="publication-social-topbar__title">
            <p class="publication-social-topbar__eyebrow">Supervision · Salang</p>
            <h1>Modifier</h1>
        </div>
        <button type="submit" form="publicationComposerForm" class="publication-social-topbar__post btn btn-primary btn-sm">
            Enregistrer
        </button>
    </header>

    @if($errors->any())
        <div class="publication-form-flash member-alert member-alert--error">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($publication->medias->isNotEmpty())
        <section class="publication-social-existing" aria-label="Médias actuels">
            <p class="publication-composer-label">Déjà joints · {{ $publication->medias->count() }}</p>
            <div class="publication-form-existing-media__grid">
                @foreach($publication->medias as $media)
                    <div class="publication-form-existing-media__item">
                        @if($media->type === 'photo')
                            <img src="{{ $media->url }}" alt="" class="publication-form-existing-media__thumb" loading="lazy">
                        @elseif($media->type === 'video')
                            <video class="publication-form-existing-media__thumb" controls preload="metadata" poster="{{ $media->thumbnail_url }}">
                                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                            </video>
                        @else
                            <a href="{{ $media->url }}" target="_blank" rel="noopener" class="publication-form-existing-media__doc">
                                <span class="publication-form-existing-media__doc-icon" aria-hidden="true">PDF</span>
                                <span class="publication-form-existing-media__doc-name">{{ $media->original_name }}</span>
                            </a>
                        @endif
                        <form action="{{ route('publications.media.destroy', $media) }}"
                              method="POST"
                              class="publication-form-existing-media__actions"
                              onsubmit="return confirm('Supprimer ce fichier ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm publication-form-existing-media__delete">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <form action="{{ route('publications.update', $publication) }}"
          method="POST"
          enctype="multipart/form-data"
          id="publicationComposerForm"
          class="publication-social-composer">
        @csrf
        @method('PUT')
        @include('publications.partials.form', ['publication' => $publication])
    </form>

    <form action="{{ route('publications.destroy', $publication) }}"
          method="POST"
          onsubmit="return confirm('Supprimer toute la publication ?');"
          class="publication-form-danger">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline btn-sm publication-form-danger__btn">
            Supprimer la publication
        </button>
    </form>
</div>
@endsection
