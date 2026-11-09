<div class="profile-about-content">
    <div class="card profile-about-hero p-4 sm:p-6 text-center sm:text-left">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="profile-about-logo-wrap sm:m-0">
                <img src="{{ asset('images/salang_logo.png') }}" alt="Salang" class="profile-about-logo logo-themeable">
            </div>
            <div class="min-w-0">
                <p class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Salang Group</p>
                <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-1">
                    Marque de santé et plateforme MLM pour gérer votre réseau, vos PV, commissions et portefeuille —
                    sans quitter l’application membre.
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-2">Présence internationale depuis 2015</p>
            </div>
        </div>
    </div>

    <div class="card profile-about-block p-4 sm:p-5">
        <h2 class="profile-about-block__title">Notre modèle</h2>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Salang combine distribution de produits de bien-être naturels et rémunération sur plusieurs niveaux.
            Les membres achètent à tarif préférentiel, recommandent la marque et font évoluer leur grade grâce aux volumes (PV / BV).
        </p>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Bonus direct, indirect, leadership et récompenses par grade sont calculés de façon transparente dans votre espace membre.
        </p>
    </div>

    <div class="profile-about-pillars grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <div class="card profile-about-pillar p-3 sm:p-4">
            <p class="text-xs sm:text-sm font-bold text-[var(--text-primary)]">Produits</p>
            <p class="text-[11px] sm:text-xs text-[var(--text-secondary)] mt-1 leading-snug">Gammes naturelles · médecine complémentaire</p>
        </div>
        <div class="card profile-about-pillar p-3 sm:p-4">
            <p class="text-xs sm:text-sm font-bold text-[var(--text-primary)]">Réseau</p>
            <p class="text-[11px] sm:text-xs text-[var(--text-secondary)] mt-1 leading-snug">Structure encadrée · grades et parrainage</p>
        </div>
        <div class="card profile-about-pillar p-3 sm:p-4">
            <p class="text-xs sm:text-sm font-bold text-[var(--text-primary)]">Outils membre</p>
            <p class="text-[11px] sm:text-xs text-[var(--text-secondary)] mt-1 leading-snug">Boutique, PV, commissions, portefeuille</p>
        </div>
        <div class="card profile-about-pillar p-3 sm:p-4">
            <p class="text-xs sm:text-sm font-bold text-[var(--text-primary)]">Transparence</p>
            <p class="text-[11px] sm:text-xs text-[var(--text-secondary)] mt-1 leading-snug">Historique des gains et règles MLM</p>
        </div>
    </div>

    @if(file_exists(public_path('images/team.png')))
        <figure class="card profile-about-figure overflow-hidden p-0">
            <img src="{{ asset('images/team.png') }}" alt="Équipe Salang Group" class="w-full aspect-[16/7] sm:aspect-[21/8] object-cover">
            <figcaption class="text-[10px] text-[var(--text-tertiary)] px-3 py-2 text-center">Salang Group — équipe et réseau international</figcaption>
        </figure>
    @endif

    <div class="card profile-about-block p-4 sm:p-5">
        <h2 class="profile-about-block__title">Dans l’application</h2>
        <ul class="profile-about-list text-sm text-[var(--text-secondary)] mt-2 space-y-1.5">
            <li>Accueil et suivi de votre activité (PV, rang, réseau)</li>
            <li>Boutique et packages pour développer votre volume</li>
            <li>Commissions détaillées par type et par niveau</li>
            <li>Portefeuille, retraits et vérification KYC</li>
        </ul>
        <p class="text-[10px] text-[var(--text-tertiary)] mt-3">{{ config('app.name') }} · espace membre sécurisé</p>
    </div>
</div>
