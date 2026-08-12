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
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">Conditions Generales d'Utilisation</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Derniere mise a jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4 md:p-6">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Acceptation des conditions</h2>
            <p>
                En creant un compte sur Salang MLM, vous acceptez pleinement les presentes 
                conditions generales d'utilisation.
            </p>

            <h2>2. Description du service</h2>
            <p>
                Salang MLM est une plateforme de vente en ligne et de marketing de reseau. 
                Les membres peuvent :
            </p>
            <ul>
                <li>Acheter des produits et des packages</li>
                <li>Parrainer de nouveaux membres</li>
                <li>Gagner des commissions sur leur reseau</li>
                <li>Demander des retraits de leurs gains</li>
            </ul>

            <h2>3. Inscription et compte</h2>
            <ul>
                <li>L'inscription est gratuite</li>
                <li>Vous devez avoir au moins 18 ans</li>
                <li>Vous etes responsable de la confidentialite de vos identifiants</li>
                <li>Vous devez fournir des informations exactes et a jour</li>
            </ul>

            <h2>4. Packages et commissions</h2>
            <ul>
                <li>L'achat d'un package est requis pour commencer a gagner des commissions</li>
                <li>Les commissions sont calculees automatiquement selon le systeme Unilevel</li>
                <li>Les taux de commission sont definis dans la section des packages</li>
                <li>Les commissions sont creditees sur votre portefeuille virtuel</li>
            </ul>

            <h2>5. Retraits</h2>
            <ul>
                <li>Le montant minimum de retrait est de 10$</li>
                <li>Des frais de 2.5% sont appliques sur chaque retrait</li>
                <li>Les retraits sont soumis a validation par l'admin</li>
                <li>Les retraits superieurs a 5000$ necessitent une verification KYC</li>
            </ul>

            <h2>6. Comportement prohibe</h2>
            <ul>
                <li>Utiliser plusieurs comptes</li>
                <li>Creer des reseaux pyramidaux illegaux</li>
                <li>Utiliser des moyens frauduleux pour generer des commissions</li>
                <li>Harceler ou spamer d'autres membres</li>
                <li>Vendre ou partager votre compte</li>
            </ul>

            <h2>7. Resolution</h2>
            <p>
                Nous nous reservons le droit de suspendre ou resilier votre compte en cas de :
            </p>
            <ul>
                <li>Violation des presentes conditions</li>
                <li>Activite frauduleuse</li>
                <li>Non-respect des lois et reglementations</li>
            </ul>

            <h2>8. Limitations de responsabilite</h2>
            <p>
                Salang Group ne peut etre tenu responsable des :
            </p>
            <ul>
                <li>Pertes indirectes ou consecutives</li>
                <li>Interruptions de service</li>
                <li>Actions des autres membres</li>
                <li>Problemes techniques independants de notre volonte</li>
            </ul>

            <h2>9. Modifications</h2>
            <p>
                Nous nous reservons le droit de modifier ces conditions a tout moment. 
                Les modifications seront notifiees par email ou via la plateforme.
            </p>

            <h2>10. Contact</h2>
            <p>
                Pour toute question concernant ces conditions :
            </p>
            <ul>
                <li>Email : <a href="mailto:legal@salang.com" class="text-primary-500">legal@salang.com</a></li>
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