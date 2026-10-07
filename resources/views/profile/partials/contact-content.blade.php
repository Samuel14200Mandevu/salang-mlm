<div class="profile-about-content profile-contact-content">
    <div class="card profile-about-hero p-4 text-center">
        <div class="profile-about-logo-wrap">
            <img src="{{ asset('images/salang_logo.png') }}" alt="Salang Group" class="profile-about-logo logo-themeable">
        </div>
        <p class="text-sm font-semibold text-[var(--text-primary)]">Salang Group</p>
        <p class="text-[0.625rem] uppercase tracking-[0.12em] text-[var(--text-muted)] mt-1">Complementary &amp; Alternative Medicine</p>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-3 max-w-md mx-auto">
            Votre santé, notre priorité. Informations générales sur l’entreprise et le plan de rémunération&nbsp;;
            l’adhésion et les achats se font via votre espace membre.
        </p>
        <p class="text-[10px] text-[var(--text-tertiary)] mt-3">Siège social · Hong Kong</p>
    </div>

    <div class="card profile-about-block p-4">
        <h2 class="profile-about-block__title">Coordonnées</h2>
        <p class="text-sm text-[var(--text-secondary)] leading-relaxed mt-2">
            Pour toute question sur l’entreprise, l’adhésion ou le support membre, utilisez les contacts officiels Salang Group.
        </p>

        <dl class="profile-contact-grid mt-4">
            <div class="profile-contact-item">
                <dt>Adresse</dt>
                <dd>382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo</dd>
            </div>
            <div class="profile-contact-item">
                <dt>Téléphone</dt>
                <dd>
                    <a href="tel:+243999086990">+243 999 086 990</a>
                    <span class="profile-contact-sep" aria-hidden="true">·</span>
                    <a href="tel:+243975220079">975 220 079</a>
                </dd>
            </div>
            <div class="profile-contact-item">
                <dt>Email</dt>
                <dd><a href="mailto:support@salanggroup.com">support@salanggroup.com</a></dd>
            </div>
            <div class="profile-contact-item">
                <dt>Site web</dt>
                <dd>
                    <a href="https://www.salanggroup.com" target="_blank" rel="noopener noreferrer">www.salanggroup.com</a>
                </dd>
            </div>
        </dl>
    </div>

    <div class="profile-contact-actions">
        <a href="{{ route('home') }}" class="btn btn-primary profile-contact-cta w-full text-center">
            Voir l’accueil public
        </a>
        @if (Route::has('legal.mentions'))
            <a href="{{ route('legal.mentions') }}" class="profile-contact-secondary w-full text-center">
                Mentions légales &amp; éditeur
            </a>
        @endif
    </div>
</div>
