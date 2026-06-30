@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <div class="animate-fadeInUp">
        <h1 class="text-3xl font-bold text-[var(--text-primary)]">🔒 Politique de Confidentialité</h1>
        <p class="text-[var(--text-secondary)] mt-2">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Introduction</h2>
            <p>
                Salang Group ("nous", "notre", "nos") s'engage à protéger votre vie privée. 
                Cette politique de confidentialité explique comment nous collectons, utilisons, 
                partageons et protégeons vos données personnelles.
            </p>

            <h2>2. Données que nous collectons</h2>
            <ul>
                <li><strong>Informations d'identification :</strong> Nom, email, téléphone, adresse</li>
                <li><strong>Données de transaction :</strong> Achats, commissions, retraits</li>
                <li><strong>Données techniques :</strong> IP, navigateur, appareil</li>
                <li><strong>Données de réseau :</strong> Parrainages, arbre généalogique</li>
            </ul>

            <h2>3. Utilisation des données</h2>
            <ul>
                <li>Gestion de votre compte et de vos transactions</li>
                <li>Calcul et versement des commissions</li>
                <li>Communication concernant vos activités sur la plateforme</li>
                <li>Amélioration de nos services</li>
                <li>Conformité légale et réglementaire</li>
            </ul>

            <h2>4. Partage des données</h2>
            <p>
                Nous ne vendons pas vos données personnelles. Nous pouvons partager vos données avec :
            </p>
            <ul>
                <li><strong>Prestataires de services :</strong> Paiements, hébergement, emails</li>
                <li><strong>Autorités légales :</strong> Si requis par la loi</li>
                <li><strong>Votre parrain :</strong> Informations basiques pour le réseau</li>
            </ul>

            <h2>5. Sécurité des données</h2>
            <p>
                Nous utilisons des mesures de sécurité avancées, incluant :
            </p>
            <ul>
                <li>Chiffrement AES-256 des données sensibles</li>
                <li>Authentification sécurisée (HTTPS, 2FA)</li>
                <li>Contrôle d'accès basé sur les rôles</li>
                <li>Logs d'activité pour toutes les actions</li>
            </ul>

            <h2>6. Vos droits (RGPD)</h2>
            <p>Conformément au RGPD, vous avez le droit de :</p>
            <ul>
                <li><strong>Accès :</strong> Consulter vos données personnelles</li>
                <li><strong>Rectification :</strong> Modifier vos données inexactes</li>
                <li><strong>Effacement :</strong> Demander la suppression de vos données</li>
                <li><strong>Opposition :</strong> Vous opposer au traitement de vos données</li>
                <li><strong>Portabilité :</strong> Récupérer vos données</li>
            </ul>

            <h2>7. Cookies</h2>
            <p>
                Nous utilisons des cookies pour améliorer votre expérience sur notre plateforme.
                Vous pouvez gérer vos préférences de cookies à tout moment.
            </p>

            <h2>8. Contact</h2>
            <p>
                Pour toute question concernant cette politique de confidentialité :
            </p>
            <ul>
                <li>Email : <a href="mailto:privacy@salang.com" class="text-primary-500">privacy@salang.com</a></li>
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