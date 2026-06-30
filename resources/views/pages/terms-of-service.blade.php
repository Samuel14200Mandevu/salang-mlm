@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <div class="animate-fadeInUp">
        <h1 class="text-3xl font-bold text-[var(--text-primary)]">📜 Conditions Générales d'Utilisation</h1>
        <p class="text-[var(--text-secondary)] mt-2">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Acceptation des conditions</h2>
            <p>
                En créant un compte sur Salang MLM, vous acceptez pleinement les présentes 
                conditions générales d'utilisation.
            </p>

            <h2>2. Description du service</h2>
            <p>
                Salang MLM est une plateforme de vente en ligne et de marketing de réseau. 
                Les membres peuvent :
            </p>
            <ul>
                <li>Acheter des produits et des packages</li>
                <li>Parrainer de nouveaux membres</li>
                <li>Gagner des commissions sur leur réseau</li>
                <li>Demander des retraits de leurs gains</li>
            </ul>

            <h2>3. Inscription et compte</h2>
            <ul>
                <li>L'inscription est gratuite</li>
                <li>Vous devez avoir au moins 18 ans</li>
                <li>Vous êtes responsable de la confidentialité de vos identifiants</li>
                <li>Vous devez fournir des informations exactes et à jour</li>
            </ul>

            <h2>4. Packages et commissions</h2>
            <ul>
                <li>L'achat d'un package est requis pour commencer à gagner des commissions</li>
                <li>Les commissions sont calculées automatiquement selon le système Unilevel</li>
                <li>Les taux de commission sont définis dans la section des packages</li>
                <li>Les commissions sont créditées sur votre portefeuille virtuel</li>
            </ul>

            <h2>5. Retraits</h2>
            <ul>
                <li>Le montant minimum de retrait est de 10$</li>
                <li>Des frais de 2.5% sont appliqués sur chaque retrait</li>
                <li>Les retraits sont soumis à validation par l'admin</li>
                <li>Les retraits supérieurs à 5000$ nécessitent une vérification KYC</li>
            </ul>

            <h2>6. Comportement prohibé</h2>
            <ul>
                <li>Utiliser plusieurs comptes</li>
                <li>Créer des réseaux pyramidaux illégaux</li>
                <li>Utiliser des moyens frauduleux pour générer des commissions</li>
                <li>Harceler ou spamer d'autres membres</li>
                <li>Vendre ou partager votre compte</li>
            </ul>

            <h2>7. Résiliation</h2>
            <p>
                Nous nous réservons le droit de suspendre ou résilier votre compte en cas de :
            </p>
            <ul>
                <li>Violation des présentes conditions</li>
                <li>Activité frauduleuse</li>
                <li>Non-respect des lois et réglementations</li>
            </ul>

            <h2>8. Limitations de responsabilité</h2>
            <p>
                Salang Group ne peut être tenu responsable des :
            </p>
            <ul>
                <li>Pertes indirectes ou consécutives</li>
                <li>Interruptions de service</li>
                <li>Actions des autres membres</li>
                <li>Problèmes techniques indépendants de notre volonté</li>
            </ul>

            <h2>9. Modifications</h2>
            <p>
                Nous nous réservons le droit de modifier ces conditions à tout moment. 
                Les modifications seront notifiées par email ou via la plateforme.
            </p>

            <h2>10. Contact</h2>
            <p>
                Pour toute question concernant ces conditions :
            </p>
            <ul>
                <li>Email : <a href="mailto:legal@salang.com" class="text-primary-500">legal@salang.com</a></li>
                <li>Adresse : Abidjan, Côte d'Ivoire</li>
            </ul>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-2">
        <a href="{{ route('home') }}" class="btn btn-outline btn-sm">← Retour à l'accueil</a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Imprimer</button>
    </div>
</div>
@endsection