@extends('layouts.public')

@section('title', ($pageTitle ?? 'Conditions générales') . ' — Salang Group')

@php
    $site = config('salang-site');
    $backRoute = $back['route'] ?? route('home');
    $backLabel = $back['label'] ?? 'Retour à l\'accueil';
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Règlement</p>
        <h1 class="public-page-hero__title">{{ $pageTitle ?? 'Conditions générales d’utilisation' }}</h1>
        <p class="public-page-hero__lead">
            Droits, devoirs et engagement des membres au sein de {{ $site['brand'] }}.
        </p>
    </div>
</section>

<section class="public-section">
    <div class="public-shell public-shell--reading">
        @include('partials.public.legal-tabs')

        <article class="public-legal-doc">
            <p class="public-legal-doc__updated">Dernière mise à jour : 27 juillet 2021</p>

            <h2>Préambule</h2>
            <p>
                Le présent règlement d’ordre intérieur a pour but de définir les règles, les droits et les devoirs des
                collaborateurs de Salang Group dans le cadre de leurs activités au sein de l’entreprise. Chaque membre est
                tenu de respecter ces dispositions afin d’assurer un environnement de travail harmonieux et productif.
            </p>

            <h2>Article 1 : Objectifs de Salang Group</h2>
            <p>
                Salang Group est une entreprise de marketing de réseau relationnel. Son objectif est de promouvoir des
                produits et services éthiques tout en favorisant des relations de confiance entre les membres et en assurant un
                développement personnel et professionnel.
            </p>

            <h2>Article 2 : Adhésion et engagement</h2>
            <ol>
                <li>Tout membre souhaitant rejoindre Salang Group doit remplir un formulaire d’adhésion et s’engager à respecter le présent règlement.</li>
                <li>L’adhésion implique une participation active aux formations, aux réunions et aux événements organisés par l’entreprise.</li>
            </ol>

            <h2>Article 3 : Comportement et éthique</h2>
            <ol>
                <li>Chaque membre doit faire preuve de respect, d’intégrité et de professionnalisme dans ses interactions avec les autres membres, les clients et le grand public.</li>
                <li>Aucune forme de harcèlement, de discrimination ou de comportement nuisible ne sera tolérée.</li>
                <li>Les membres doivent promouvoir les produits et services de Salang Group de manière honnête et transparente.</li>
            </ol>

            <h2>Article 4 : Activités commerciales</h2>
            <ol>
                <li>Les membres doivent présenter les produits de manière fidèle et ne pas faire de promesses mensongères sur les résultats escomptés.</li>
                <li>Il est interdit de dénigrer d’autres membres ou concurrents.</li>
                <li>Les ventes doivent être réalisées dans le respect des conditions établies par la direction de Salang Group.</li>
            </ol>

            <h2>Article 5 : Confidentialité</h2>
            <p>
                Tous les membres sont tenus de respecter la confidentialité des informations relatives à Salang Group, à ses
                partenaires et à ses clients. Toute divulgation non autorisée sera sanctionnée.
            </p>

            <h2>Article 6 : Réunions et formations</h2>
            <ol>
                <li>Les réunions d’équipe se tiendront régulièrement et tous les membres doivent y assister.</li>
                <li>Des sessions de formation seront organisées pour assurer le développement des compétences des membres. La participation est non obligatoire mais nécessaire pour chaque membre.</li>
            </ol>

            <h2>Article 7 : Sanctions</h2>
            <p>
                Tout manquement au règlement d’ordre intérieur pourra entraîner des sanctions pouvant aller jusqu’à
                l’exclusion de l’entreprise. Les sanctions seront décidées par la direction et seront proportionnelles à la gravité
                de la faute.
            </p>

            <h3>7.1 Obligations de non-concurrence</h3>
            <ol>
                <li>Chaque membre s’engage à ne pas promouvoir ou s’associer avec des entreprises concurrentes sur des produits ou services similaires pendant la durée de leur adhésion à Salang Group et pour une période de 12 mois après la résiliation de leur adhésion.</li>
                <li>Il est interdit à tout membre d’utiliser les ressources, les contacts ou les informations acquises pendant leur affiliation à Salang Group pour établir une entreprise concurrente.</li>
            </ol>

            <h3>7.2 Manquements à l’obligation de non-concurrence</h3>
            <p>Les manquements aux obligations de non-concurrence seront traités avec la plus grande rigueur. Les situations suivantes seront considérées comme des violations graves&nbsp;:</p>
            <ol>
                <li>Promotion active de produits ou services d’une entreprise concurrente.</li>
                <li>Recrutement d’autres membres de Salang Group pour les inciter à quitter l’entreprise afin de créer ou de rejoindre une entreprise concurrente.</li>
                <li>Utilisation d’informations confidentielles ou de contacts obtenus durant l’affiliation pour un intérêt personnel ou commercial.</li>
            </ol>

            <h3>7.3 Sanctions encourues</h3>
            <ol>
                <li><strong>Avertissement écrit :</strong> pour un premier manquement avéré, un avertissement écrit sera délivré au membre avec explication de la violation.</li>
                <li><strong>Suspension :</strong> en cas de récidive ou de violation grave, le membre pourra être suspendu de ses activités au sein de Salang Group pour une période déterminée par la direction.</li>
                <li><strong>Exclusion :</strong> pour des violations graves ou répétées (comme le cas de la concurrence déloyale), l’exclusion de l’entreprise pourra être décidée. Cette exclusion se fera par vote de la direction et sera notifiée par écrit au membre concerné.</li>
                <li><strong>Poursuites légales :</strong> en cas de préjudice causé par les actions concurrentielles d’un membre, Salang Group se réserve le droit d’engager des poursuites légales pour récupérer les pertes financières et non financières subies.</li>
            </ol>

            <h3>7.4 Procédure</h3>
            <ol>
                <li>Toute allégation de violation sera examinée par un comité désigné au sein de Salang Group. Les membres concernés auront la possibilité de se défendre et de fournir des preuves à leur faveur.</li>
                <li>Les décisions du comité seront prises à la majorité et seront définitives.</li>
            </ol>

            <aside class="public-legal-callout">
                <p><strong>Non-concurrence.</strong> Le respect de ces obligations est essentiel pour maintenir la confiance et la loyauté entre les membres et envers Salang Group. Chaque membre est responsable de sa conformité à ces règles.</p>
            </aside>

            <h2>Article 8 : Modifications du règlement</h2>
            <p>
                Salang Group se réserve le droit de modifier le présent règlement d’ordre intérieur. Les membres seront
                informés de toute modification par écrit.
            </p>

            <h2>Article 9 : Acceptation du règlement</h2>
            <p>
                En signant le formulaire d’adhésion, chaque membre certifie avoir lu, compris et accepté le présent règlement
                d’ordre intérieur.
            </p>

            <aside class="public-legal-callout">
                <p><strong>Conclusion.</strong> La conformité à ces règles est essentielle pour le bon fonctionnement de Salang Group et pour le succès de chacun de ses membres. Ensemble, construisons un environnement de travail positif et solidaire.</p>
            </aside>

            <div class="public-legal-signoff">
                <p>
                    <strong>Salang Group</strong><br>
                    N° SARL IDN:22-M7 300-N63464 Q<br>
                    N° RCCM: CD/BKV/RCCM/20-B-00116<br>
                    Numéro import-export: 0024/CBX-21/I000439SK/Z<br>
                    Contact : +243 999 086 990 — 975 220 079<br>
                    Web : www.salanggroup.com<br>
                    Email : {{ $site['email'] }}<br>
                    Adresse : 382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo
                </p>
                <p class="public-legal-signoff__date">
                    <strong>Fait à Kinshasa le 27 juillet 2021</strong><br>
                    La direction générale<br>
                    Lou JIAN CHENG<br>
                    C.E.O Salang Group Health Care
                </p>
            </div>

            <div class="public-legal-actions">
                <a href="{{ $backRoute }}" class="public-btn-secondary">
                    <span aria-hidden="true">←</span>
                    {{ $backLabel }}
                </a>
                <a href="{{ route('register') }}" class="public-btn-primary">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
            </div>
        </article>
    </div>
</section>
@endsection
