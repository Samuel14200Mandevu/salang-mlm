@php
    $iconWallet = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>';
    $iconTx = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
    $iconWithdraw = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
    $iconCommission = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $iconPv = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>';
    $iconPackage = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/></svg>';
    $iconOrders = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>';
    $iconNetwork = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    $iconExport = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>';
@endphp

<section class="member-fintech-panel member-reports-links member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">Accès rapide</h2>
            <p class="member-fintech-panel__sub">Historiques et exports</p>
        </div>
    </div>

    <nav class="profile-account-menu member-reports-menu" aria-label="Rapports">
        @include('profile.partials.account-menu-row', [
            'href' => route('wallet.index'),
            'label' => 'Portefeuille',
            'icon' => $iconWallet,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('wallet.transactions'),
            'label' => 'Transactions',
            'icon' => $iconTx,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('withdrawal.index'),
            'label' => 'Retraits',
            'icon' => $iconWithdraw,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('commissions.index'),
            'label' => 'Commissions',
            'icon' => $iconCommission,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('my-pv.index'),
            'label' => 'Mes PV',
            'icon' => $iconPv,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('orders.index'),
            'label' => 'Mes commandes',
            'icon' => $iconOrders,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('subscriptions.index'),
            'label' => 'Packages',
            'icon' => $iconPackage,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('report.network'),
            'label' => 'Réseau & ventes filleuls',
            'icon' => $iconNetwork,
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('report.export'),
            'label' => 'Exporter le portefeuille',
            'icon' => $iconExport,
        ])
    </nav>
</section>
