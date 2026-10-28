@extends('layouts.public')

@section('title', 'À propos — Salang Group')

@php
    $site = config('salang-site');
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Qui sommes-nous ?</p>
        <h1 class="public-page-hero__title">{{ $site['brand'] }} Health Care International</h1>
        <p class="public-page-hero__lead">
            <span class="font-semibold text-[var(--text-primary)]">{{ $site['tagline'] }}</span>
            — {{ $site['subtitle'] }}.
        </p>
    </div>
</section>

<section class="public-section">
    <div class="public-shell">
        <div class="public-why">
            <div>
                <h2 class="public-heading">Notre histoire</h2>
                <p class="public-prose mt-4">{{ $site['who_we_are'] }}</p>
                <p class="public-prose mt-4 font-medium text-[var(--text-primary)]">{{ $site['mission'] }}</p>
                <ul class="public-check-list">
                    <li>Fondé en {{ $site['founded'] }} par {{ $site['founder'] }}</li>
                    <li>Siège social · {{ $site['hq'] }}</li>
                    <li>{{ $site['promise'] }}</li>
                    <li>Produits de santé 100&nbsp;% naturels</li>
                </ul>
            </div>
            <figure class="public-why__figure">
                <x-ui.image :src="asset('images/team.png')" alt="Équipe Salang Group" class="public-why__img" />
            </figure>
        </div>
    </div>
</section>

<section class="public-section-alt">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Pourquoi Salang',
            'title' => 'Ce qui nous distingue',
            'lead' => 'Extrait du livret officiel Salang International.',
        ])
        <ul class="public-check-list max-w-3xl">
            @foreach ($site['why'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
        <p class="public-prose mt-8 max-w-3xl">{{ $site['community'] }}</p>
        <blockquote class="public-quote mt-8 max-w-2xl text-[var(--text-secondary)]">
            « {{ $site['promise'] }} »
            <footer>{{ $site['spokesperson'] }}</footer>
        </blockquote>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('site.offer') }}" class="public-btn-primary">Voir l’offre</a>
            <a href="{{ asset('docs/livret-salang-international.pdf') }}" class="public-btn-secondary" target="_blank" rel="noopener">
                Télécharger le livret PDF
            </a>
        </div>
    </div>
</section>
@endsection
