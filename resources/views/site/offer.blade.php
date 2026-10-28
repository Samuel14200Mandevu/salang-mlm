@extends('layouts.public')

@section('title', 'Offre & parcours — Salang Group')

@php
    $site = config('salang-site');
    $services = $site['offer_services'] ?? [];
    $products = collect($site['offer_products'] ?? [])
        ->filter(fn ($p) => ! empty($p['image']) && is_file(public_path($p['image'])))
        ->values();
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Adhérer à Salang</p>
        <h1 class="public-page-hero__title">Une opportunité en or — {{ $site['membership_fee_usd'] }}&nbsp;$ d’adhésion</h1>
        <p class="public-page-hero__lead">
            Produits naturels, séances Ceragem &amp; Détox, et un plan de carrière clair — avec seulement {{ $site['membership_fee_usd'] }}&nbsp;$ d’adhésion.
        </p>
        <div class="public-contact-hero-actions">
            <a href="{{ route('register') }}" class="public-btn-primary">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
            <a href="#produits" class="public-btn-secondary">Voir les produits</a>
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Promesse',
            'title' => 'Trois richesses, une adhésion',
            'lead' => 'Santé, prospérité et liberté — le cœur de l’offre Salang.',
            'center' => true,
        ])
        <div class="public-pillars public-pillars--3">
            @foreach ($site['three_pillars'] as $pillar)
                <article class="public-pillar">
                    @include('partials.public.pillar-icon', ['icon' => $pillar['icon'] ?? 'health'])
                    <h2 class="public-pillar__title">{{ $pillar['title'] }}</h2>
                    <p class="public-pillar__text">{{ $pillar['text'] }}</p>
                </article>
            @endforeach
        </div>
        <p class="public-prose mt-10 max-w-3xl mx-auto text-center">{{ $site['community'] }}</p>
    </div>
</section>

@if (count($services))
<section class="public-section-alt" id="seances">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Services',
            'title' => 'Séances Ceragem &amp; Détox',
            'lead' => 'Des séances proposées lors des consultations pour compléter votre parcours bien-être.',
            'center' => true,
        ])

        <div class="public-offer-services">
            @foreach ($services as $service)
                @php
                    $img = ! empty($service['image']) && is_file(public_path($service['image']))
                        ? asset($service['image'])
                        : null;
                @endphp
                <article class="public-offer-service">
                    <div class="public-offer-service__media">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $service['title'] }}" class="public-offer-service__img" loading="lazy" decoding="async">
                        @endif
                    </div>
                    <div class="public-offer-service__body">
                        <p class="public-kicker">{{ $service['kicker'] }}</p>
                        <h3 class="public-offer-service__title">{{ $service['title'] }}</h3>
                        <p class="public-offer-service__text">{{ $service['text'] }}</p>
                        @if (! empty($service['points']))
                            <ul class="public-check-list public-offer-service__list">
                                @foreach ($service['points'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($products->isNotEmpty())
<section class="public-section" id="produits">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Catalogue',
            'title' => 'Produits naturels Salang',
            'lead' => 'Une sélection de compléments et soins 100 % orientés santé — disponibles pour les membres.',
            'center' => true,
        ])

        <div class="public-offer-products">
            @foreach ($products as $product)
                <article class="public-offer-product">
                    <div class="public-offer-product__media">
                        <img
                            src="{{ asset($product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="public-offer-product__img"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="public-offer-product__body">
                        <p class="public-offer-product__cat">{{ $product['category'] }}</p>
                        <h3 class="public-offer-product__name">{{ $product['name'] }}</h3>
                        <p class="public-offer-product__blurb">{{ $product['blurb'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="public-section-alt" id="parcours">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Comment ça fonctionne',
            'title' => 'Un modèle simple, accessible et puissant',
            'lead' => 'Cinq étapes pour démarrer avec Salang.',
            'center' => true,
        ])

        <ol class="public-steps-grid">
            @foreach ($site['how_it_works'] as $i => $step)
                <li class="public-step-card">
                    <span class="public-step-card__num">{{ $i + 1 }}</span>
                    <p class="public-step-card__text">{{ $step }}</p>
                </li>
            @endforeach
        </ol>

        <div class="public-body-actions">
            <a href="{{ route('register') }}" class="public-btn-primary">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
            <a href="{{ route('site.remuneration') }}" class="public-btn-secondary">Voir la rémunération</a>
            <a href="{{ asset('docs/livret-salang-international.pdf') }}" class="public-btn-secondary" target="_blank" rel="noopener">Livret PDF</a>
        </div>
    </div>
</section>

<section class="public-cta-banner">
    <div class="public-shell public-cta-banner__inner">
        <div>
            <p class="public-kicker">Décision</p>
            <h2 class="public-heading">{{ $site['promise'] }}</h2>
            <p class="public-cta-banner__lead">Rejoignez la communauté Salang et accédez aux produits, séances et outils membre.</p>
        </div>
        <div class="public-cta-banner__actions">
            <a href="{{ route('register') }}" class="public-btn-primary">Créer un compte</a>
            <a href="{{ route('contact') }}" class="public-btn-hero-secondary">Nous contacter</a>
        </div>
    </div>
</section>
@endsection
