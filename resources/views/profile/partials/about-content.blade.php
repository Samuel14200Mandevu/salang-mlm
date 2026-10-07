<div class="profile-about-content">
    <div class="card profile-about-hero p-4 text-center">
        <div class="profile-about-logo-wrap">
            <img src="{{ asset('images/salang_logo.png') }}" alt="Salang" class="profile-about-logo logo-themeable">
        </div>
        <p class="text-sm font-semibold text-[var(--text-primary)]">Salang Group</p>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Marque de santé et plateforme MLM pour gérer votre réseau, vos PV, commissions et portefeuille —
            sans quitter l’application membre.
        </p>
        <p class="text-[10px] text-[var(--text-tertiary)] mt-3">Présence internationale depuis 2015</p>
    </div>

    <div class="card profile-about-block p-4">
        <h2 class="profile-about-block__title">Notre modèle</h2>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Salang combine distribution de produits de bien-être naturels et rémunération sur plusieurs niveaux.
            Les membres achètent à tarif préférentiel, recommandent la marque et font évoluer leur grade grâce aux volumes (PV / BV).
        </p>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Bonus direct, indirect, leadership et récompenses par grade sont calculés de façon transparente dans votre espace membre.
        </p>
    </div>

    <div class="profile-about-pillars grid grid-cols-2 gap-2">
        <div class="card profile-about-pillar p-3">
            <p class="text-xs font-bold text-[var(--text-primary)]">Produits</p>
            <p class="text-[11px] text-[var(--text-secondary)] mt-1 leading-snug">Gammes naturelles · médecine complémentaire</p>
        </div>
        <div class="card profile-about-pillar p-3">
            <p class="text-xs font-bold text-[var(--text-primary)]">Réseau</p>
            <p class="text-[11px] text-[var(--text-secondary)] mt-1 leading-snug">Structure encadrée · grades et parrainage</p>
        </div>
        <div class="card profile-about-pillar p-3">
            <p class="text-xs font-bold text-[var(--text-primary)]">Outils membre</p>
            <p class="text-[11px] text-[var(--text-secondary)] mt-1 leading-snug">Boutique, PV, commissions, portefeuille</p>
        </div>
        <div class="card profile-about-pillar p-3">
            <p class="text-xs font-bold text-[var(--text-primary)]">Transparence</p>
            <p class="text-[11px] text-[var(--text-secondary)] mt-1 leading-snug">Historique des gains et règles MLM</p>
        </div>
    </div>

    @if(file_exists(public_path('images/team.png')))
        <figure class="card profile-about-figure overflow-hidden p-0">
            <img src="{{ asset('images/team.png') }}" alt="Équipe Salang Group" class="w-full aspect-[4/3] object-cover">
            <figcaption class="text-[10px] text-[var(--text-tertiary)] px-3 py-2 text-center">Salang Group — équipe et réseau international</figcaption>
        </figure>
    @endif

    <div class="card profile-about-block p-4">
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
