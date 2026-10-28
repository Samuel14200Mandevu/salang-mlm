@extends('layouts.public')

@section('title', 'Salang Group — Health Care International')

@section('bodyClass', 'public-welcome-page')

@php
    $heroImage = file_exists(public_path('images/site.webp')) ? asset('images/site.webp') : asset('images/site.png');
    $site = config('salang-site');
    $homeProductKeys = [
        'images/produits/0c7a5b36-ba87-4acc-9e6b-75781177adcd.jpeg', // Vision Kit
        'images/produits/a40666ab-94bd-41e4-9248-21395bab1387.jpeg', // Gluta Glow
        'images/produits/d94dbceb-f0f2-4fc8-a9c6-6568922fff8d.jpeg', // Bilymarin
        'images/produits/408c03b3-c5dd-4a8c-836b-0a6a93e4a1f3.jpeg', // Menopause Support
    ];
    $byImage = collect($site['offer_products'] ?? [])->keyBy('image');
    $homeProducts = collect($homeProductKeys)
        ->map(fn ($img) => $byImage->get($img))
        ->filter(fn ($p) => $p && is_file(public_path($p['image'])))
        ->values();
    $homeServices = collect($site['offer_services'] ?? [])->take(2);
@endphp

@section('bodyAttributes')
    data-login-url="{{ route('login') }}"
    data-register-url="{{ route('register') }}"
@endsection

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}">
    {{-- Avant paint : site officiel OU parcours app mobile (jamais de flash du site) --}}
    <script>
        (function () {
            try {
                var q = new URLSearchParams(window.location.search);
                if (q.get('site') === '1') {
                    sessionStorage.setItem('salang_force_website', '1');
                }
                var desktop = window.matchMedia('(min-width: 768px)').matches;
                var official = q.get('site') === '1'
                    || sessionStorage.getItem('salang_force_website') === '1'
                    || desktop;
                if (official) {
                    document.documentElement.classList.add('salang-no-splash');
                } else {
                    // Mobile app : masquer le site dès le premier frame
                    document.documentElement.classList.add('salang-mobile-app');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        html.salang-mobile-app body.public-welcome-page {
            overflow: hidden !important;
            height: 100dvh !important;
            background: var(--bg-page, #0b1220);
        }
        html.salang-mobile-app .public-nav,
        html.salang-mobile-app .public-footer,
        html.salang-mobile-app #publicOfficialSiteContent {
            display: none !important;
        }
    </style>
@endpush

@push('body-start')
    @unless(request('site') === '1')
        @include('partials.public.mobile-splash')
    @endunless
@endpush

@section('content')
    @include('partials.public.mobile-onboarding')

    <div class="public-onboarding-chrome" id="publicOnboardingChrome" hidden>
        <div class="public-onboarding-chrome-inner">
            <p class="public-onboarding-step" aria-live="polite">
                <span class="public-onboarding-step-label">Étape</span>
                <span id="publicOnboardingStepNum">1</span><span class="public-onboarding-step-total">/4</span>
            </p>
            <div class="public-onboarding-dots" id="publicOnboardingDots" role="tablist" aria-label="Étapes de découverte"></div>
            <div class="public-onboarding-actions">
                <button type="button" class="public-onboarding-skip" id="publicOnboardingSkip">Passer</button>
                <button type="button" class="public-btn-primary public-onboarding-next" id="publicOnboardingNext">Suivant</button>
            </div>
        </div>
    </div>

    <div class="public-official-site-content" id="publicOfficialSiteContent">
    <section class="public-hero" id="onboarding-slide-1" aria-labelledby="hero-title">
        <div class="public-hero-bg" style="background-image: url('{{ $heroImage }}');"></div>
        <div class="public-hero-overlay" aria-hidden="true"></div>

        <div class="public-hero-grid">
            <div class="public-hero-copy">
                <p class="public-hero-brand">{{ $site['brand'] }}</p>
                <h1 id="hero-title" class="public-hero-title">
                    Your health <span class="public-hero-title__em">our priority</span>
                </h1>
                <p class="public-hero-desc">{{ $site['mission'] }}</p>
                <div class="public-hero-actions">
                    <a href="{{ route('register') }}" class="public-btn-primary">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
                    <a href="{{ route('site.offer') }}#produits" class="public-btn-hero-secondary">Voir les produits</a>
                </div>
            </div>
        </div>
    </section>

    <section class="public-stats-bar" aria-label="Chiffres clés">
        <div class="public-shell public-stats-bar__grid">
            @foreach ([
                [(string) $site['founded'], 'Fondation'],
                ['Hong Kong', 'Siège social'],
                [$site['membership_fee_usd'].' USD', 'Adhésion unique'],
                ['100 %', 'Naturel'],
            ] as [$value, $label])
                <div class="public-stats-bar__item">
                    <p class="public-stats-bar__value">{{ $value }}</p>
                    <p class="public-stats-bar__label">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="pillars" class="public-section">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Promesse',
                'title' => 'Trois richesses, une adhésion',
                'lead' => 'Avec seulement '.$site['membership_fee_usd'].' $ d’adhésion, vous ouvrez la porte à la santé, la prospérité et la liberté.',
                'center' => true,
            ])

            <div class="public-pillars public-pillars--3 public-reveal public-reveal--delay-1">
                @foreach ($site['three_pillars'] as $pillar)
                    <article class="public-pillar">
                        @include('partials.public.pillar-icon', ['icon' => $pillar['icon'] ?? 'health'])
                        <h3 class="public-pillar__title">{{ $pillar['title'] }}</h3>
                        <p class="public-pillar__text">{{ $pillar['text'] }}</p>
                        <a href="{{ route('site.offer') }}" class="public-pillar__link">En savoir plus →</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @if ($homeProducts->isNotEmpty())
    <section id="produits" class="public-section-alt">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Produits',
                'title' => 'Une sélection Salang',
                'lead' => 'Compléments naturels pour la vision, la beauté, le foie et le bien-être au quotidien.',
                'center' => true,
            ])

            <div class="public-offer-products public-offer-products--home public-reveal public-reveal--delay-1">
                @foreach ($homeProducts as $product)
                    <article class="public-offer-product">
                        <a href="{{ route('site.offer') }}#produits" class="public-offer-product__link">
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
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="public-body-actions">
                <a href="{{ route('site.offer') }}#produits" class="public-btn-primary">Voir tous les produits</a>
                <a href="{{ route('site.offer') }}#seances" class="public-btn-secondary">Séances Ceragem &amp; Détox</a>
            </div>
        </div>
    </section>
    @endif

    @if ($homeServices->isNotEmpty())
    <section class="public-section" id="seances-home">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Services',
                'title' => 'Ceragem &amp; Détox',
                'lead' => 'Des séances en consultation pour compléter votre parcours bien-être.',
                'center' => true,
            ])
            <div class="public-offer-services public-reveal public-reveal--delay-1">
                @foreach ($homeServices as $service)
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
                            <a href="{{ route('site.offer') }}#seances" class="public-pillar__link">Découvrir →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section id="about" class="public-mission">
        <div class="public-mission__media">
            <x-ui.image :src="asset('images/team.png')" alt="Équipe Salang Group" class="public-mission__img" />
        </div>
        <div class="public-mission__copy">
            <p class="public-kicker">Qui sommes-nous</p>
            <h2 class="public-heading">{{ $site['promise'] }}</h2>
            <p class="public-mission__lead">{{ $site['who_we_are'] }}</p>
            <ul class="public-mission__list">
                <li>Fondé en {{ $site['founded'] }} par {{ $site['founder'] }}</li>
                <li>Siège social · {{ $site['hq'] }}</li>
                <li>{{ $site['subtitle'] }}</li>
            </ul>
            <div class="public-mission__actions">
                <a href="{{ route('site.about') }}" class="public-btn-primary">Lire notre histoire</a>
                <a href="{{ route('register') }}" class="public-btn-hero-secondary">Rejoindre Salang</a>
            </div>
        </div>
    </section>

    <section class="public-section-alt">
        <div class="public-shell">
            <div class="public-why public-reveal public-reveal--delay-1">
                <div class="public-why__copy">
                    @include('partials.public.section-head', [
                        'kicker' => 'Pourquoi Salang',
                        'title' => 'Ce qui nous distingue',
                        'lead' => 'Une entreprise solide, transparente, tournée vers la santé naturelle et la croissance.',
                    ])
                    <ul class="public-check-list">
                        @foreach ($site['why'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                        <li>Adhésion unique à {{ $site['membership_fee_usd'] }}&nbsp;USD — plan consultable avant inscription</li>
                    </ul>
                </div>
                <figure class="public-why__figure">
                    <x-ui.image :src="asset('images/natural-products.jpeg')" alt="Produits naturels Salang" class="public-why__img" />
                    <span class="public-why__badge" aria-hidden="true">{{ $site['membership_fee_usd'] }}&nbsp;USD<br><small>adhésion</small></span>
                </figure>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="public-section">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Parcours',
                'title' => 'Comment ça fonctionne',
                'lead' => 'Un modèle simple, accessible et puissant.',
                'center' => true,
            ])

            <ol class="public-steps-grid public-reveal public-reveal--delay-1">
                @foreach ($site['how_it_works'] as $i => $step)
                    <li class="public-step-card">
                        <span class="public-step-card__num">{{ $i + 1 }}</span>
                        <p class="public-step-card__text">{{ $step }}</p>
                    </li>
                @endforeach
            </ol>

            <div class="public-body-actions">
                <a href="{{ route('site.offer') }}" class="public-btn-primary">Voir l’offre complète</a>
                <a href="{{ asset('docs/livret-salang-international.pdf') }}" class="public-btn-secondary" target="_blank" rel="noopener">Livret PDF</a>
            </div>
        </div>
    </section>

    <section id="remuneration" class="public-section-alt">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Rémunération',
                'title' => 'Commencer à gagner',
                'lead' => 'Bonus de parrainage, détail, direct, indirect… un plan clair pour progresser.',
                'center' => true,
            ])

            <div class="public-income-grid public-reveal public-reveal--delay-1">
                @foreach (array_slice($site['income_types'], 0, 4) as [$name, $detail])
                    <article class="public-income-card">
                        <p class="public-income-card__label">Commission</p>
                        <h3 class="public-income-card__title">{{ $name }}</h3>
                        <p class="public-income-card__text">{{ $detail }}</p>
                    </article>
                @endforeach
            </div>

            <div class="public-body-actions">
                <a href="{{ route('site.remuneration') }}" class="public-btn-primary">Grades &amp; pourcentages</a>
                <a href="{{ route('contact') }}" class="public-btn-secondary">Poser une question</a>
            </div>
        </div>
    </section>

    <section id="faq" class="public-section">
        <div class="public-shell">
            <div class="public-faq public-reveal public-reveal--delay-1">
                <div class="public-faq__copy">
                    @include('partials.public.section-head', [
                        'kicker' => 'FAQ',
                        'title' => 'Questions fréquentes',
                        'lead' => 'Les réponses essentielles avant de créer votre compte.',
                    ])
                    <div class="public-faq__list" x-data="{ open: 0 }">
                        @foreach ([
                            ['Combien coûte l’adhésion ?', 'L’adhésion est un paiement unique de '.$site['membership_fee_usd'].' USD. Elle ouvre l’accès à la boutique, au réseau et au tableau de bord.'],
                            ['Ai-je besoin d’un code parrain ?', 'Oui. Un code ou lien de parrainage est requis à l’inscription pour rattacher votre compte à la structure.'],
                            ['Comment sont calculées les commissions ?', 'Selon votre grade et les volumes : bonus direct, indirect et, selon qualification, leadership. Le détail est sur la page Rémunération.'],
                            ['Puis-je consulter le plan avant de m’inscrire ?', 'Oui. Grades, pourcentages, produits et parcours sont publics — et le livret PDF est téléchargeable.'],
                        ] as $i => [$q, $a])
                            <div class="public-faq__item">
                                <button
                                    type="button"
                                    class="public-faq__question"
                                    @click="open = open === {{ $i }} ? null : {{ $i }}"
                                    :aria-expanded="open === {{ $i }}"
                                >
                                    <span>{{ $q }}</span>
                                    <span class="public-faq__chevron" aria-hidden="true" :class="{ 'is-open': open === {{ $i }} }"></span>
                                </button>
                                <div
                                    class="public-faq__answer"
                                    x-show="open === {{ $i }}"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                >
                                    <p>{{ $a }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <figure class="public-faq__figure">
                    <x-ui.image :src="asset('images/holistic-health.jpeg')" alt="Approche holistique Salang" class="public-faq__img" />
                </figure>
            </div>
        </div>
    </section>

    <section class="public-cta-banner public-onboarding-final">
        <div class="public-shell public-cta-banner__inner">
            <div>
                <p class="public-kicker">Décision</p>
                <h2 class="public-heading">Rejoignez-nous dès aujourd’hui</h2>
                <p class="public-cta-banner__lead">{{ $site['promise'] }} Préparez votre code parrain et une adresse email valide.</p>
            </div>
            <div class="public-cta-banner__actions">
                <a href="{{ route('register') }}" class="public-btn-primary">Créer un compte — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
                <a href="{{ route('login') }}" class="public-btn-hero-secondary">Se connecter</a>
            </div>
        </div>
    </section>
    </div>{{-- /#publicOfficialSiteContent --}}
@endsection
