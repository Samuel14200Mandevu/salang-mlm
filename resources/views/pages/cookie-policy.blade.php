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
    .prose h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 1rem;
        margin-bottom: 0.5rem;
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
        .prose h3 { font-size: 0.9rem; }
        .prose p, .prose ul { font-size: 0.875rem; }
        .card { padding: 0.75rem; }
        .text-3xl { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:py-8 space-y-4 sm:space-y-6 px-3 sm:px-4">
    <div class="animate-fadeInUp">
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">Politique des Cookies</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Derniere mise a jour : {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4 md:p-6">
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <h2>1. Qu'est-ce qu'un cookie ?</h2>
            <p>
                Un cookie est un petit fichier texte stocke sur votre ordinateur ou appareil mobile 
                lorsque vous visitez un site web. Il permet au site de memoriser vos actions et 
                preferences pendant une certaine periode.
            </p>

            <h2>2. Pourquoi utilisons-nous des cookies ?</h2>
            <p>
                Chez Salang MLM, nous utilisons des cookies pour :
            </p>
            <ul>
                <li><strong>Authentification :</strong> Vous maintenir connecte(e) a votre compte</li>
                <li><strong>Preferences :</strong> Memoriser vos parametres (langue, theme, etc.)</li>
                <li><strong>Performance :</strong> Analyser comment vous utilisez notre plateforme</li>
                <li><strong>Securite :</strong> Proteger votre compte contre les acces non autorises</li>
                <li><strong>Fonctionnalites :</strong> Activer des fonctionnalites comme le panier d'achat</li>
            </ul>

            <h2>3. Types de cookies que nous utilisons</h2>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-3 sm:p-4 my-3 sm:my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">Cookies essentiels</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Necessaires au fonctionnement de la plateforme. Ils ne peuvent pas etre desactives.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-1 sm:mt-2">
                    <li><strong>session_id :</strong> Maintient votre session active</li>
                    <li><strong>csrf_token :</strong> Protege contre les attaques CSRF</li>
                    <li><strong>auth_token :</strong> Gere votre authentification</li>
                </ul>
            </div>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-3 sm:p-4 my-3 sm:my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">Cookies fonctionnels</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Ameliorent votre experience en memorisant vos preferences.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-1 sm:mt-2">
                    <li><strong>theme_preference :</strong> Memorise votre choix de theme (clair/sombre)</li>
                    <li><strong>language :</strong> Memorise votre langue preferee</li>
                    <li><strong>cart_items :</strong> Sauvegarde votre panier d'achat</li>
                </ul>
            </div>

            <div class="bg-[var(--bg-secondary)] rounded-lg p-3 sm:p-4 my-3 sm:my-4">
                <h3 class="font-semibold text-[var(--text-primary)]">Cookies de performance</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    Nous aident a comprendre comment vous interagissez avec notre site.
                </p>
                <ul class="text-sm text-[var(--text-secondary)] mt-1 sm:mt-2">
                    <li><strong>_ga :</strong> Statistiques Google Analytics</li>
                    <li><strong>_gid :</strong> Statistiques Google Analytics</li>
                    <li><strong>_gat :</strong> Limitation des requetes Google Analytics</li>
                </ul>
            </div>

            <h2>4. Comment gerer vos cookies ?</h2>
            <p>
                Vous pouvez gerer vos preferences de cookies a tout moment :
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 my-3 sm:my-4">
                <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="font-semibold text-[var(--text-primary)] text-sm">Navigateurs</p>
                    <ul class="text-xs sm:text-sm text-[var(--text-secondary)] mt-1">
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-primary-500 hover:underline">Google Chrome</a></li>
                        <li><a href="https://support.mozilla.org/fr/kb/gerer-les-cookies" target="_blank" class="text-primary-500 hover:underline">Firefox</a></li>
                        <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" class="text-primary-500 hover:underline">Safari</a></li>
                        <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge" target="_blank" class="text-primary-500 hover:underline">Edge</a></li>
                    </ul>
                </div>
                <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="font-semibold text-[var(--text-primary)] text-sm">Mobile</p>
                    <ul class="text-xs sm:text-sm text-[var(--text-secondary)] mt-1">
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-primary-500 hover:underline">Chrome Android</a></li>
                        <li><a href="https://support.apple.com/fr-fr/HT201265" target="_blank" class="text-primary-500 hover:underline">Safari iOS</a></li>
                    </ul>
                </div>
            </div>

            <h2>5. Consentement</h2>
            <p>
                En utilisant notre plateforme, vous acceptez l'utilisation de cookies conformement 
                a cette politique. Vous pouvez a tout moment modifier vos preferences via la banniere 
                de cookies ou les parametres de votre navigateur.
            </p>

            <h2>6. Duree de conservation</h2>
            <ul>
                <li><strong>Cookies de session :</strong> Expirent a la fermeture de votre navigateur</li>
                <li><strong>Cookies persistants :</strong> Restent valables jusqu'a 30 jours</li>
                <li><strong>Cookies de performance :</strong> Restent valables jusqu'a 2 ans (Google Analytics)</li>
            </ul>

            <h2>7. Contact</h2>
            <p>
                Pour toute question concernant notre politique des cookies :
            </p>
            <ul>
                <li>Email : <a href="mailto:privacy@salang.com" class="text-primary-500">privacy@salang.com</a></li>
                <li>Adresse : Abidjan, Cote d'Ivoire</li>
            </ul>

            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-primary-500/10 border border-primary-500/20 rounded-lg">
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] text-center">
                    Vous pouvez a tout moment modifier vos preferences de cookies 
                    en cliquant sur le lien ci-dessous.
                </p>
                <div class="text-center mt-2">
                    <button onclick="resetCookieConsent()" class="btn btn-primary btn-sm">
                        Gerer mes preferences
                    </button>
                </div>
            </div>
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

@push('scripts')
<script>
function resetCookieConsent() {
    localStorage.removeItem('cookie_consent');
    location.reload();
}
</script>
@endpush
@endsection