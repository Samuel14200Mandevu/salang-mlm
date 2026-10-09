@php
    $icon = fn (string $path) => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';
@endphp

<div class="member-services-hub profile-account-mobile">
    @auth
        @if(auth()->user()->hasRole('admin'))
            <p class="profile-account-section-title">Administration</p>
            <nav class="profile-account-menu" aria-label="Administration">
                @include('profile.partials.account-menu-row', [
                    'href' => route('admin.dashboard'),
                    'label' => 'Tableau de bord admin',
                    'iconTone' => 'primary',
                    'icon' => $icon('M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'),
                ])
            </nav>
        @endif
    @endauth

    <p class="profile-account-section-title">Réseau & activité</p>
    <nav class="profile-account-menu" aria-label="Réseau et activité">
        @include('profile.partials.account-menu-row', [
            'href' => route('network.index'),
            'label' => 'Mon réseau',
            'iconTone' => 'primary',
            'icon' => $icon('M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('commissions.index'),
            'label' => 'Mes commissions',
            'iconTone' => 'accent',
            'icon' => $icon('M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('rank.index'),
            'label' => 'Mon rang',
            'iconTone' => 'accent',
            'icon' => $icon('M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('my-pv.index'),
            'label' => 'Historique PV',
            'iconTone' => 'sky',
            'icon' => $icon('M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'),
        ])
    </nav>

    <p class="profile-account-section-title">Commandes & boutique</p>
    <nav class="profile-account-menu" aria-label="Commandes et boutique">
        @include('profile.partials.account-menu-row', [
            'href' => route('orders.index'),
            'label' => 'Mes commandes',
            'iconTone' => 'primary',
            'icon' => $icon('M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('cart.index'),
            'label' => 'Panier',
            'iconTone' => 'rose',
            'icon' => $icon('M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('subscriptions.index'),
            'label' => 'Mon package / abonnement',
            'iconTone' => 'purple',
            'icon' => $icon('M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4'),
        ])
    </nav>

    <p class="profile-account-section-title">Compte & finances</p>
    <nav class="profile-account-menu" aria-label="Compte et finances">
        @include('profile.partials.account-menu-row', [
            'href' => route('notifications.index'),
            'label' => 'Notifications',
            'iconTone' => 'warning',
            'icon' => $icon('M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('kyc.index'),
            'label' => 'Vérification KYC',
            'iconTone' => 'success',
            'icon' => $icon('M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'),
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('withdrawal.index'),
            'label' => 'Retraits',
            'iconTone' => 'orange',
            'icon' => $icon('M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'),
        ])
    </nav>

    <p class="profile-account-section-title">Assistance & événements</p>
    <nav class="profile-account-menu" aria-label="Assistance et événements">
        @include('profile.partials.account-menu-row', [
            'href' => auth()->user()->memberQuestionsEntryUrl(),
            'label' => 'Assistance & questions',
            'iconTone' => 'sky',
            'icon' => $icon('M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
        ])
        @auth
            @if(auth()->user()->hasAnyRole(['user', 'cashier']) && ! auth()->user()->hasSupervisionAccess())
            @endif
            @include('profile.partials.account-menu-row', [
                'href' => route('publications.index'),
                'label' => 'Événements & promos',
                'iconTone' => 'orange',
                'icon' => $icon('M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'),
            ])
        @endauth
    </nav>

    @auth
        @if(auth()->user()->hasSupervisionAccess())
            <p class="profile-account-section-title">Supervision</p>
            <nav class="profile-account-menu" aria-label="Supervision">
                @include('profile.partials.account-menu-row', [
                    'href' => route('questions.index', ['status' => 'open']),
                    'label' => 'Questions reçues',
                    'iconTone' => 'success',
                    'icon' => $icon('M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'),
                ])
                @include('profile.partials.account-menu-row', [
                    'href' => route('publications.index'),
                    'label' => 'Publications',
                    'iconTone' => 'purple',
                    'icon' => $icon('M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'),
                ])
            </nav>
        @endif
    @endauth
</div>
