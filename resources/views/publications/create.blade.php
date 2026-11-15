@extends('layouts.app')

@section('title', 'Nouvelle publication')

@section('content')
<div class="supervision-page publication-form-page publication-form-page--social max-w-xl mx-auto">
    <form action="{{ route('publications.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="publicationComposerForm"
          class="publication-social-composer">
        @csrf

        <header class="publication-social-topbar">
            <a href="{{ route('publications.index') }}" class="publication-social-topbar__back" aria-label="Retour">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="publication-social-topbar__title">
                <p class="publication-social-topbar__eyebrow">Supervision · Salang</p>
                <h1>Nouvelle publication</h1>
            </div>
            <button type="submit" class="publication-social-topbar__post btn btn-primary btn-sm">
                Publier
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

        @include('publications.partials.form')
    </form>
</div>
@endsection
