<nav class="member-wallet-action-cards member-fintech-animate member-fintech-animate--d2" aria-label="Actions portefeuille">
    <a href="{{ route('wallet.deposit') }}" class="member-wallet-action-card">
        <div class="member-wallet-action-card__content">
            <span class="member-wallet-action-card__icon is-deposit" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            <span class="member-wallet-action-card__title">Dépôt</span>
            <span class="member-wallet-action-card__subtitle">Ajouter des fonds</span>
        </div>
    </a>
    <a href="{{ route('withdrawal.index') }}" class="member-wallet-action-card">
        <div class="member-wallet-action-card__content">
            <span class="member-wallet-action-card__icon is-withdraw" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.65">
                    <rect x="2.5" y="10" width="11" height="8" rx="1.5"/>
                    <rect x="8" y="5.5" width="11" height="8" rx="1.5"/>
                    <path stroke-linecap="round" d="M11.5 8.5l2 2m0-2l-2 2"/>
                    <path stroke-linecap="round" d="M10 13.5h4"/>
                </svg>
            </span>
            <span class="member-wallet-action-card__title">Retrait</span>
            <span class="member-wallet-action-card__subtitle">Retirer vos gains</span>
        </div>
    </a>
    <a href="{{ route('commissions.index') }}" class="member-wallet-action-card">
        <div class="member-wallet-action-card__content">
            <span class="member-wallet-action-card__icon is-gains" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 18V12M12 18V8M16 18v-6"/>
                </svg>
            </span>
            <span class="member-wallet-action-card__title">Gains</span>
            <span class="member-wallet-action-card__subtitle">Voir vos revenus</span>
        </div>
    </a>
    <a href="{{ route('products.index') }}" class="member-wallet-action-card">
        <div class="member-wallet-action-card__content">
            <span class="member-wallet-action-card__icon is-shop" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </span>
            <span class="member-wallet-action-card__title">Boutique</span>
            <span class="member-wallet-action-card__subtitle">Nos produits</span>
        </div>
    </a>
</nav>
