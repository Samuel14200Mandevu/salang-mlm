@extends('layouts.app')

@push('styles')
<style>
    .prose h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .prose p {
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: 0.75rem;
    }
    .prose ul {
        list-style: disc;
        padding-left: 1.5rem;
        margin-bottom: 0.75rem;
        color: var(--text-secondary);
    }
    .prose ul li {
        margin-bottom: 0.25rem;
    }
    .prose a {
        color: var(--primary-500);
        text-decoration: underline;
    }
    .prose a:hover {
        color: var(--primary-600);
    }
    
    @media (max-width: 640px) {
        .prose h2 { font-size: 1.1rem; }
        .prose p, .prose ul { font-size: 0.875rem; }
        .card { padding: 0.75rem; }
        .text-3xl { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:py-8 space-y-4 sm:space-y-6 px-3 sm:px-4">
    <div class="animate-fadeInUp">
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">Politique de Confidentialite</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Derniere mise a jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4 md:p-6">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Introduction</h2>
            <p>
                Salang Group ("nous", "notre", "nos") s'engage a proteger votre vie privee. 
                Cette politique de confidentialite explique comment nous collectons, utilisons, 
                partageons et protegeons vos donnees personnelles.
            </p>

            <h2>2. Donnees que nous collectons</h2>
            <ul>
                <li><strong>Informations d'identification :</strong> Nom, email, telephone, adresse</li>
                <li><strong>Donnees de transaction :</strong> Achats, commissions, retraits</li>
                <li><strong>Donnees techniques :</strong> IP, navigateur, appareil</li>
                <li><strong>Donnees de reseau :</strong> Parrainages, arbre genealogique</li>
            </ul>

            <h2>3. Utilisation des donnees</h2>
            <ul>
                <li>Gestion de votre compte et de vos transactions</li>
                <li>Calcul et versement des commissions</li>
                <li>Communication concernant vos activites sur la plateforme</li>
                <li>Amelioration de nos services</li>
                <li>Conformite legale et reglementaire</li>
            </ul>

            <h2>4. Partage des donnees</h2>
            <p>
                Nous ne vendons pas vos donnees personnelles. Nous pouvons partager vos donnees avec :
            </p>
            <ul>
                <li><strong>Prestataires de services :</strong> Paiements, hebergement, emails</li>
                <li><strong>Autorites legales :</strong> Si requis par la loi</li>
                <li><strong>Votre parrain :</strong> Informations basiques pour le reseau</li>
            </ul>

            <h2>5. Securite des donnees</h2>
            <p>
                Nous utilisons des mesures de securite avancees, incluant :
            </p>
            <ul>
                <li>Chiffrement AES-256 des donnees sensibles</li>
                <li>Authentification securisee (HTTPS, 2FA)</li>
                <li>Controle d'acces base sur les roles</li>
                <li>Logs d'activite pour toutes les actions</li>
            </ul>

            <h2>6. Vos droits (RGPD)</h2>
            <p>Conformement au RGPD, vous avez le droit de :</p>
            <ul>
                <li><strong>Acces :</strong> Consulter vos donnees personnelles</li>
                <li><strong>Rectification :</strong> Modifier vos donnees inexactes</li>
                <li><strong>Effacement :</strong> Demander la suppression de vos donnees</li>
                <li><strong>Opposition :</strong> Vous opposer au traitement de vos donnees</li>
                <li><strong>Portabilite :</strong> Recuperer vos donnees</li>
            </ul>

            <h2>7. Cookies</h2>
            <p>
                Nous utilisons des cookies pour ameliorer votre experience sur notre plateforme.
                Vous pouvez gerer vos preferences de cookies a tout moment.
            </p>

            <h2>8. Contact</h2>
            <p>
                Pour toute question concernant cette politique de confidentialite :
            </p>
            <ul>
                <li>Email : <a href="mailto:privacy@salang.com" class="text-primary-500">privacy@salang.com</a></li>
                <li>Adresse : Abidjan, Cote d'Ivoire</li>
            </ul>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <a href="{{ route('home') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour a l'accueil
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>
</div>
@endsection