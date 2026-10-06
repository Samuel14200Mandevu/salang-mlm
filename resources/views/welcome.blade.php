@extends('layouts.public')

@section('title', 'Salang Group — Health Care International')

@section('bodyClass', 'public-welcome-page')

@php
    $heroImage = file_exists(public_path('images/site.webp')) ? asset('images/site.webp') : asset('images/site.png');
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}">
@endpush

@section('content')
    <div class="public-onboarding-chrome" id="publicOnboardingChrome" hidden>
        <div class="public-onboarding-chrome-inner">
            <p class="public-onboarding-step" aria-live="polite">
                <span class="public-onboarding-step-label">Étape</span>
                <span id="publicOnboardingStepNum">1</span><span class="public-onboarding-step-total">/5</span>
            </p>
            <div class="public-onboarding-dots" id="publicOnboardingDots" role="tablist" aria-label="Étapes de découverte"></div>
            <div class="public-onboarding-actions">
                <button type="button" class="public-onboarding-skip" id="publicOnboardingSkip">Passer</button>
                <button type="button" class="public-btn-primary public-onboarding-next" id="publicOnboardingNext">Suivant</button>
            </div>
        </div>
    </div>

    <section class="public-hero public-mobile-slide" id="onboarding-slide-1" aria-labelledby="hero-title">
        <div class="public-hero-bg" style="background-image: url('{{ $heroImage }}');"></div>
        <div class="public-hero-overlay" aria-hidden="true"></div>

        <div class="public-hero-grid">
            <div class="public-hero-copy">
                <p class="public-hero-kicker">
                    Complementary &amp; Alternative Medicine
                </p>
                <h1 id="hero-title" class="public-hero-title">
                    Salang Group<br>Health Care International
                </h1>
                <p class="public-hero-tagline"><span class="public-hero-tagline-accent">Your health</span> our priority</p>
                <p class="public-hero-desc">
                    Formulations d’origine naturelle, fondées sur la médecine complémentaire asiatique.
                    Rejoignez un réseau international fondé en 2015 par le Dr&nbsp;Lou Jiancheng Salang.
                </p>
                <p class="public-hero-meta">Siège social · Hong Kong</p>

                <div class="public-hero-actions">
                    <a href="{{ route('register') }}" class="public-btn-primary">Adhérer — 30&nbsp;USD</a>
                    <a href="#how-it-works" class="public-btn-hero-secondary">
                        Voir le parcours membre
                    </a>
                </div>
            </div>

            <div class="public-hero-aside">
                <div class="public-hero-panel">
                    <p class="text-sm font-semibold">En bref</p>
                    <p class="mt-2 text-sm text-white/80 leading-relaxed">
                        Adhésion unique, accès produits, plan de bonus publié et espace membre pour suivre votre réseau.
                    </p>
                    <div class="public-stat-row">
                        <div>
                            <p class="public-stat-value">500+</p>
                            <p class="public-stat-label">Membres actifs</p>
                        </div>
                        <div>
                            <p class="public-stat-value public-stat-value--accent">50+</p>
                            <p class="public-stat-label">Pays</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="public-section-alt public-mobile-slide">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Entreprise',
                'title' => 'Une marque de santé, un modèle de réseau clair',
                'lead' => 'Salang Group combine distribution de produits et rémunération sur plusieurs niveaux, avec des règles accessibles avant l’inscription.',
            ])

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start public-reveal public-reveal--delay-1">
                <div class="lg:col-span-5 order-2 lg:order-1">
                    <figure class="public-figure">
                        <x-ui.image :src="asset('images/team.png')" alt="Équipe Salang Group" class="aspect-[4/3] object-cover" />
                        <figcaption class="public-figure-caption">Salang Group — présence internationale depuis 2015</figcaption>
                    </figure>
                </div>
                <div class="lg:col-span-7 order-1 lg:order-2 space-y-5 public-prose">
                    <p>
                        L’entreprise conçoit et distribue des produits de bien-être naturels. Les membres achètent à tarif préférentiel,
                        recommandent la marque et développent un réseau encadré par des grades et des volumes (PV / BV).
                    </p>
                    <p>
                        La transparence du plan de rémunération est centrale&nbsp;: bonus direct, indirect, leadership et récompenses
                        par grade sont détaillés ci-dessous, avant toute décision d’adhésion.
                    </p>
                    <div class="grid sm:grid-cols-2 gap-4 pt-2">
                        <div class="public-card--lift">
                            <p class="text-sm font-semibold text-[var(--text-primary)]">Produits</p>
                            <p class="text-sm text-[var(--text-secondary)] mt-1">Gammes naturelles, médecine complémentaire</p>
                        </div>
                        <div class="public-card--lift">
                            <p class="text-sm font-semibold text-[var(--text-primary)]">Réseau</p>
                            <p class="text-sm text-[var(--text-secondary)] mt-1">Structure binaire et évolution par grades</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-onboarding-extra">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Offre',
                'title' => 'Trois piliers, une adhésion',
                'lead' => '30 USD ouvrent l’accès membre, les catalogues et le calcul des commissions.',
            ])

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 public-reveal public-reveal--delay-1">
                <article class="public-card lg:col-span-3">
                    <x-ui.image :src="asset('images/natural-products.jpeg')" alt="Produits naturels Salang" class="w-full aspect-[16/10] object-cover rounded-lg mb-5" />
                    <h3 class="text-lg font-semibold text-[var(--text-primary)]">Qualité produit</h3>
                    <p class="public-prose mt-2">Sélection naturelle, traçabilité et usage orienté prévention.</p>
                </article>
                <div class="lg:col-span-2 flex flex-col gap-6">
                    <article class="public-card flex-1">
                        <x-ui.image :src="asset('images/holistic-health.jpeg')" alt="Approche holistique" class="w-full aspect-video object-cover rounded-lg mb-4" />
                        <h3 class="font-semibold text-[var(--text-primary)]">Approche holistique</h3>
                        <p class="text-sm text-[var(--text-secondary)] mt-1">Équilibre du corps et suivi dans le temps.</p>
                    </article>
                    <article class="public-card flex-1">
                        <x-ui.image :src="asset('images/global-growth.jpg')" alt="Réseau international" class="w-full aspect-video object-cover rounded-lg mb-4" />
                        <h3 class="font-semibold text-[var(--text-primary)]">Réseau international</h3>
                        <p class="text-sm text-[var(--text-secondary)] mt-1">Croissance maîtrisée et outils membre.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section-alt public-mobile-slide">
        <div class="public-shell">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center public-reveal public-reveal--delay-1">
                <div>
                    @include('partials.public.section-head', [
                        'kicker' => 'Adhésion',
                        'title' => '30 USD — frais uniques',
                        'lead' => 'Création de compte, code parrain, accès boutique et tableau de bord.',
                    ])
                    <ol class="public-prose space-y-3 list-decimal pl-5">
                        <li><span class="font-medium text-[var(--text-primary)]">Santé</span> — consommer et recommander les gammes Salang.</li>
                        <li><span class="font-medium text-[var(--text-primary)]">Revenus</span> — commissions liées aux volumes et à la profondeur du réseau.</li>
                        <li><span class="font-medium text-[var(--text-primary)]">Autonomie</span> — rythme personnel, règles documentées.</li>
                    </ol>
                </div>
                <div class="public-card-accent text-center py-8 px-6">
                    <p class="text-4xl font-bold tabular-nums text-[var(--text-primary)]">30&nbsp;<span class="text-xl font-semibold public-price-highlight">USD</span></p>
                    <p class="text-sm text-[var(--text-secondary)] mt-2">Paiement unique à l’inscription</p>
                    <a href="{{ route('register') }}" class="public-btn-primary mt-8 w-full sm:w-auto">Ouvrir mon compte</a>
                    <p class="text-xs text-[var(--text-muted)] mt-4">Code parrain requis · CGU acceptées à l’inscription</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="public-section public-mobile-slide">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Parcours',
                'title' => 'Comment avancer avec Salang',
                'lead' => 'Cinq étapes, de l’adhésion au développement du réseau.',
                'center' => true,
            ])

            <figure class="public-figure max-w-4xl mx-auto public-reveal public-reveal--delay-1">
                <x-ui.image
                    :src="asset('images/how-it-works.png')"
                    alt="Schéma des cinq étapes du parcours membre Salang"
                    class="w-full"
                />
            </figure>

            <ol class="public-steps max-w-xl mx-auto mt-12 public-reveal public-reveal--delay-2">
                @foreach ([
                    ['Adhérer', 'Régler 30 USD et valider votre parrain.'],
                    ['Utiliser', 'Découvrir les produits et cumuler du volume personnel.'],
                    ['Partager', 'Présenter l’opportunité avec votre lien ou code.'],
                    ['Percevoir', 'Bonus direct, indirect et leadership selon votre grade.'],
                    ['Évoluer', 'Atteindre les grades par PV/BV et structure d’équipe.'],
                ] as [$t, $d])
                    <li class="public-step">
                        <span class="public-step-marker" aria-hidden="true"></span>
                        <p class="public-step-title">{{ $t }}</p>
                        <p class="public-step-desc">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="remuneration" class="public-section-alt public-onboarding-extra">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Rémunération',
                'title' => 'Types de revenus',
                'lead' => 'Aperçu des composantes du plan. Les pourcentages exacts dépendent du grade atteint.',
            ])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 public-reveal public-reveal--delay-1">
                @foreach ([
                    ['Bénéfice de détail', '25 % sur le prix catalogue des produits achetés pour revente personnelle.'],
                    ['Bonus direct', 'Lié au volume personnel (PBV) des membres que vous parrainez directement.'],
                    ['Bonus indirect', 'De 2 % à 39 % selon la profondeur et le grade.'],
                    ['Bonus de leadership', 'À partir du 5e niveau, variable selon qualification.'],
                    ['Prix supplémentaires', 'Récompenses matérielles définies par grade (véhicule, logement selon paliers).'],
                    ['Bonus d’encouragement', 'À partir du grade Rubis, conditions spécifiques publiées.'],
                ] as [$name, $detail])
                    <div class="public-rank-card">
                        <p class="text-[0.6875rem] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Commission</p>
                        <p class="font-semibold text-[var(--text-primary)] mt-1">{{ $name }}</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-2">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-onboarding-extra">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Grades',
                'title' => 'Qualifications et progression',
                'lead' => '1 PV = 1 USD d’achats cumulés · BV = volume du mois en cours.',
            ])

            <figure class="public-figure max-w-5xl mx-auto mb-10 public-reveal public-reveal--delay-1">
                <x-ui.image
                    :src="asset('images/ranks-diagram.png')"
                    alt="Pyramide des grades Salang de Distributeur à Diamond Pearl"
                    class="w-full"
                />
            </figure>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 public-reveal public-reveal--delay-2">
                @php
                    $ranks = [
                        ['Distributeur', 'Adhésion 30 USD'],
                        ['Qualification', '≥ 100 BV (achat unique ou cumulé)'],
                        ['Cumul Directeur', '≥ 200 BV · 22 % + bonus indirect'],
                        ['Directeur', 'Conditions réseau / PV · 26 %'],
                        ['Manager Senior', 'Structure d’équipe · 30 %'],
                        ['Directeur de l’Envolée', 'Qualification avancée · 34 %'],
                        ['Saphire Manager', 'Qualification avancée · 40 %'],
                        ['Diamant Bleu', 'Qualification avancée · 43 %'],
                        ['Diamond Pearl', 'Sommet du plan · 45 %'],
                    ];
                @endphp
                @foreach ($ranks as [$name, $detail])
                    <div class="public-rank-card">
                        <p class="font-semibold text-[var(--text-primary)]">{{ $name }}</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-sm text-center text-[var(--text-muted)] mt-8 max-w-2xl mx-auto public-reveal">
                Les options détaillées (filleuls, PV groupe) sont disponibles dans la documentation membre et votre back-office après connexion.
            </p>
        </div>
    </section>

    <section class="public-section-alt public-onboarding-extra">
        <div class="public-shell">
            @include('partials.public.section-head', [
                'kicker' => 'Tableau',
                'title' => 'Pourcentages par grade',
                'center' => true,
            ])

            <figure class="public-figure max-w-4xl mx-auto mb-8">
                <x-ui.image :src="asset('images/bonus-chart.png')" alt="Graphique des taux de bonus Salang" class="w-full" />
            </figure>

            <div class="public-table-wrap public-reveal public-reveal--delay-2">
                <table class="public-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Direct</th>
                            <th>Indirect</th>
                            <th>Leadership</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Cumul Directeur', '22 %', '4 % – 20 %', '—'],
                            ['Directeur', '26 %', '4 % – 20 %', '—'],
                            ['Manager Senior', '30 %', '4 % – 24 %', '0,5 %'],
                            ['Directeur de l’Envolée', '34 %', '4 % – 28 %', '1,1 %'],
                            ['Saphire Manager', '40 %', '6 % – 34 %', '1,8 %'],
                            ['Diamant Bleu', '43 %', '3 % – 37 %', '2,6 %'],
                            ['Diamond Pearl', '45 %', '2 % – 39 %', '3,5 %'],
                        ] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="public-section public-mobile-slide public-onboarding-final">
        <div class="public-shell">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start public-reveal public-reveal--delay-1">
                <div>
                    @include('partials.public.section-head', [
                        'kicker' => 'Décision',
                        'title' => 'Prêt à commencer ?',
                        'lead' => 'L’inscription prend quelques minutes. Préparez votre code parrain et une adresse email valide.',
                    ])
                    <blockquote class="public-quote text-[var(--text-secondary)]">
                        « Santé, prospérité, liberté : les trois axes que nous partageons avec chaque membre engagé. »
                        <footer>Dr Franck KOWAVING</footer>
                    </blockquote>
                </div>
                <div class="public-card-accent p-8">
                    <h3 class="text-lg font-bold text-[var(--text-primary)]">Créer un compte membre</h3>
                    <p class="public-prose mt-3">
                        Accès boutique, arbre binaire, historique des commissions et retraits. Déjà inscrit ?
                        <a href="{{ route('login') }}" class="text-primary-700 font-semibold dark:text-primary-400">Connectez-vous</a>.
                    </p>
                    <a href="{{ route('register') }}" class="public-btn-primary mt-6 w-full text-center">Inscription — 30 USD</a>
                </div>
            </div>
        </div>
    </section>
@endsection
