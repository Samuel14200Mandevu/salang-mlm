@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <div class="animate-fadeInUp">
        <h1 class="text-3xl font-bold text-[var(--text-primary)]">🍪 Politique des Cookies</h1>
        <p class="text-[var(--text-secondary)] mt-2">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Qu'est-ce qu'un cookie ?</h2>
            <p>
                Un cookie est un petit fichier texte stocké sur votre ordinateur ou appareil mobile 
                lorsque vous visitez un site web. Il permet au site de mémoriser vos actions et 
                préférences pendant une certaine période.
            </p>

            <h2>2. Pourquoi utilisons-nous des cookies ?</h2>
            <p>
                Chez Salang MLM, nous utilisons des cookies pour :
            </p>
            <ul>
                <li><strong>Authentification :</strong> Vous maintenir connecté(e) à votre compte</li>
                <li><strong>Préférences :</strong> Mémoriser vos paramètres (langue, thème, etc.)</li>
                <li><strong>Performance :</strong> Analyser comment vous utilisez notre plateforme</li>
                <li><strong>Sécurité :</strong> Protéger votre compte contre les accès non autorisés</li>
                <li><strong>Fonctionnalités :</strong> Activer des fonctionnalités comme le panier d'achat</li>
            </ul>

            <h2>3. Types de cookies que nous utilisons</h2>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-4 my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">🍪 Cookies essentiels</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Nécessaires au fonctionnement de la plateforme. Ils ne peuvent pas être désactivés.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-2">
                    <li><strong>session_id :</strong> Maintient votre session active</li>
                    <li><strong>csrf_token :</strong> Protège contre les attaques CSRF</li>
                    <li><strong>auth_token :</strong> Gère votre authentification</li>
                </ul>
            </div>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-4 my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">⚙️ Cookies fonctionnels</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Améliorent votre expérience en mémorisant vos préférences.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-2">
                    <li><strong>theme_preference :</strong> Mémorise votre choix de thème (clair/sombre)</li>
                    <li><strong>language :</strong> Mémorise votre langue préférée</li>
                    <li><strong>cart_items :</strong> Sauvegarde votre panier d'achat</li>
                </ul>
            </div>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-4 my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">📊 Cookies de performance</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Nous aident à comprendre comment vous interagissez avec notre site.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-2">
                    <li><strong>_ga :</strong> Statistiques Google Analytics</li>
                    <li><strong>_gid :</strong> Statistiques Google Analytics</li>
                    <li><strong>_gat :</strong> Limitation des requêtes Google Analytics</li>
                </ul>
            </div>

            <h2>4. Comment gérer vos cookies ?</h2>
            <p>
                Vous pouvez gérer vos préférences de cookies à tout moment :
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
                <div class="p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="font-semibold text-[var(--text-primary)]">🌐 Navigateurs</p>
                    <ul class="text-sm text-[var(--text-secondary)] mt-1">
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-primary-500 hover:underline">Google Chrome</a></li>
                        <li><a href="https://support.mozilla.org/fr/kb/gerer-les-cookies" target="_blank" class="text-primary-500 hover:underline">Firefox</a></li>
                        <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" class="text-primary-500 hover:underline">Safari</a></li>
                        <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge" target="_blank" class="text-primary-500 hover:underline">Edge</a></li>
                    </ul>
                </div>
                <div class="p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="font-semibold text-[var(--text-primary)]">📱 Mobile</p>
                    <ul class="text-sm text-[var(--text-secondary)] mt-1">
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-primary-500 hover:underline">Chrome Android</a></li>
                        <li><a href="https://support.apple.com/fr-fr/HT201265" target="_blank" class="text-primary-500 hover:underline">Safari iOS</a></li>
                    </ul>
                </div>
            </div>

            <h2>5. Consentement</h2>
            <p>
                En utilisant notre plateforme, vous acceptez l'utilisation de cookies conformément 
                à cette politique. Vous pouvez à tout moment modifier vos préférences via la bannière 
                de cookies ou les paramètres de votre navigateur.
            </p>

            <h2>6. Durée de conservation</h2>
            <ul>
                <li><strong>Cookies de session :</strong> Expirent à la fermeture de votre navigateur</li>
                <li><strong>Cookies persistants :</strong> Restent valables jusqu'à 30 jours</li>
                <li><strong>Cookies de performance :</strong> Restent valables jusqu'à 2 ans (Google Analytics)</li>
            </ul>

            <h2>7. Contact</h2>
            <p>
                Pour toute question concernant notre politique des cookies :
            </p>
            <ul>
                <li>Email : <a href="mailto:privacy@salang.com" class="text-primary-500">privacy@salang.com</a></li>
                <li>Adresse : Abidjan, Côte d'Ivoire</li>
            </ul>

            <div class="mt-6 p-4 bg-primary-500/10 border border-primary-500/20 rounded-lg">
                <p class="text-sm text-[var(--text-secondary)] text-center">
                    🔄 Vous pouvez à tout moment modifier vos préférences de cookies 
                    en cliquant sur le lien ci-dessous.
                </p>
                <div class="text-center mt-2">
                    <button onclick="resetCookieConsent()" class="btn btn-primary btn-sm">
                        🔄 Gérer mes préférences
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-2">
        <a href="{{ route('home') }}" class="btn btn-outline btn-sm">← Retour à l'accueil</a>
        <a href="{{ route('privacy-policy') }}" class="btn btn-outline btn-sm">🔒 Politique de confidentialité</a>
        <a href="{{ route('terms-of-service') }}" class="btn btn-outline btn-sm">📜 CGU</a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Imprimer</button>
    </div>
</div>

@push('scripts')
<script>
function resetCookieConsent() {
    localStorage.removeItem('cookie_consent');
    location.reload();
}
</script>
@endpush
@endsection