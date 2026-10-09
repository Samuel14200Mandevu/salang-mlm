@extends('layouts.app')

@section('title', 'Nouvelle publication')

@section('content')
<div class="supervision-page publication-form-page space-y-4 sm:space-y-6 max-w-2xl mx-auto">
    <x-member.catalog-banner
        eyebrow="Supervision · Salang"
        title="Nouvelle publication"
        sub="Rédigez, ajoutez des médias et publiez pour les membres"
        banner-class="supervision-catalog-banner">
        <x-slot:tools>
            <a href="{{ route('publications.index') }}" class="shop-banner-icon-btn" aria-label="Retour à la galerie">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </x-slot:tools>
    </x-member.catalog-banner>

    <div class="supervision-page-header-desktop member-page-intro animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Nouvelle publication</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Créez un événement ou une promotion avec photos et vidéos.</p>
    </div>

    @if($errors->any())
        <div class="publication-form-flash member-alert member-alert--error">
            <p class="font-semibold text-sm mb-1">Corrigez les champs suivants :</p>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('publications.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="publication-form-shell animate-fadeInUp delay-1">
        @csrf

        <div class="card publication-form-card p-4 sm:p-6">
            @include('publications.partials.form')
        </div>

        <div class="publication-form-actions">
            <button type="submit" class="btn btn-primary publication-form-actions__primary">
                Créer la publication
            </button>
            <a href="{{ route('publications.index') }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
@endsection
