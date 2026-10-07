<nav class="member-fintech-actions member-fintech-animate member-fintech-animate--d2" aria-label="Actions portefeuille">
    <a href="{{ route('wallet.deposit') }}" class="member-fintech-actions__item is-deposit">
        <span class="member-fintech-actions__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </span>
        <span class="member-fintech-actions__label">Dépôt</span>
    </a>
    <a href="{{ route('withdrawal.index') }}" class="member-fintech-actions__item is-withdraw">
        <span class="member-fintech-actions__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </span>
        <span class="member-fintech-actions__label">Retrait</span>
    </a>
    <a href="{{ route('commissions.index') }}" class="member-fintech-actions__item">
        <span class="member-fintech-actions__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <span class="member-fintech-actions__label">Gains</span>
    </a>
    <a href="{{ route('products.index') }}" class="member-fintech-actions__item">
        <span class="member-fintech-actions__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </span>
        <span class="member-fintech-actions__label">Boutique</span>
    </a>
</nav>
