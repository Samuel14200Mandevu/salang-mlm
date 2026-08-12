@extends('layouts.legal', [
    'backRoute' => $back['route'] ?? route('home'),
    'backLabel' => $back['label'] ?? 'Retour à l\'accueil'
])

@section('title', $pageTitle . ' - Salang MLM')

@section('content')
<div class="legal-page">
    <div class="legal-card">
        <h1>{{ $pageTitle }}</h1>
        <p class="last-update"> Dernière mise à jour : 27 Juillet 2021</p>

        <h2>1. Collecte des informations</h2>
        <p>
            Nous collectons les informations que vous nous fournissez directement, notamment lors de votre inscription,
            de l'achat de produits ou de la participation à nos programmes. Ces informations peuvent inclure :
        </p>
        <ul>
            <li>Nom et prénom</li>
            <li>Adresse email</li>
            <li>Numéro de téléphone</li>
            <li>Adresse postale</li>
            <li>Informations de paiement</li>
        </ul>

        <h2>2. Utilisation des informations</h2>
        <p>Les informations collectées sont utilisées pour :</p>
        <ul>
            <li>Gérer votre compte et votre adhésion</li>
            <li>Traiter vos commandes et paiements</li>
            <li>Vous informer sur nos produits et services</li>
            <li>Vous envoyer des communications marketing (avec votre consentement)</li>
            <li>Améliorer nos services et notre site web</li>
        </ul>

        <h2>3. Confidentialité et sécurité</h2>
        <p>
            Conformément à l'Article 5 du règlement intérieur de Salang Group, tous les membres sont tenus de respecter
            la confidentialité des informations relatives à Salang Group, à ses partenaires et à ses clients.
        </p>
        <p>
            Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger
            vos données contre tout accès non autorisé, toute modification, divulgation ou destruction.
        </p>

        <div class="highlight-box">
            <p><strong>Engagement :</strong> Toute divulgation non autorisée des informations confidentielles sera sanctionnée conformément au règlement intérieur de Salang Group.</p>
        </div>

        <h2>4. Partage des informations</h2>
        <p>
            Nous ne partageons vos informations personnelles avec des tiers que dans les cas suivants :
        </p>
        <ul>
            <li>Avec votre consentement explicite</li>
            <li>Pour le traitement des paiements</li>
            <li>Pour respecter nos obligations légales</li>
            <li>Avec les membres de votre réseau (dans le cadre du programme de parrainage)</li>
        </ul>

        <h2>5. Vos droits</h2>
        <p>Conformément à la législation applicable, vous disposez des droits suivants :</p>
        <ul>
            <li>Droit d'accès à vos données</li>
            <li>Droit de rectification</li>
            <li>Droit à l'effacement</li>
            <li>Droit à la limitation du traitement</li>
            <li>Droit à la portabilité des données</li>
            <li>Droit d'opposition au traitement</li>
        </ul>

        <h2>6. Cookies</h2>
        <p>
            Notre site utilise des cookies pour améliorer votre expérience de navigation. Vous pouvez configurer votre
            navigateur pour refuser les cookies si vous le souhaitez.
        </p>

        <h2>7. Contact</h2>
        <p>
            Pour toute question concernant notre politique de confidentialité ou pour exercer vos droits, vous pouvez nous contacter à :
        </p>
        <ul>
            <li><strong>Email :</strong> support@salanggroup.com</li>
            <li><strong>Téléphone :</strong> +243 999 086 990</li>
            <li><strong>Adresse :</strong> 382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo</li>
        </ul>

        <a href="{{ $back['route'] ?? route('home') }}" class="back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $back['label'] ?? 'Retour à l\'accueil' }}
        </a>
    </div>
</div>
@endsection