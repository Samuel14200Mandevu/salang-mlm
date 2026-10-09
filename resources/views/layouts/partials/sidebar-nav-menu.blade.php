<ul class="space-y-0.5">
    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Mon espace</div>
    </li>
    <li>
        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           data-title="Tableau de bord">
            <x-ui.sidebar-link-icon tone="blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Tableau de bord</span>
        </a>
    </li>
    <li>
        <a href="{{ route('rank.index') }}"
           class="sidebar-link {{ request()->routeIs('rank.*') ? 'active' : '' }}"
           data-title="Mon rang">
            <x-ui.sidebar-link-icon tone="teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Ma carrière</span>
        </a>
    </li>
    <li>
        <a href="{{ route('profile.index') }}"
           class="sidebar-link {{ request()->routeIs('profile.index') ? 'active' : '' }}"
           data-title="Mon profil">
            <x-ui.sidebar-link-icon tone="navy">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Mon profil</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Boutique</div>
    </li>
    <li>
        <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="sky">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Produits</span>
        </a>
    </li>
    <li>
        <a href="{{ route('subscriptions.index') }}" class="sidebar-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="primary-soft">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Packages</span>
        </a>
    </li>
    <li>
        <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="cyan">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Mes commandes</span>
        </a>
    </li>
    <li>
        <a href="{{ route('cart.index') }}" class="sidebar-link {{ request()->routeIs('cart.index') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="amber">
                <div class="relative">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <x-ui.cart-badge variant="sidebar" />
                </div>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Panier</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Réseau</div>
    </li>
    <li>
        <a href="{{ route('network.index') }}" class="sidebar-link {{ request()->routeIs('network.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Mon réseau</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Finances</div>
    </li>
    <li>
        <a href="{{ route('wallet.index') }}" class="sidebar-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="emerald">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Portefeuille</span>
        </a>
    </li>
    <li>
        <a href="{{ route('commissions.index') }}" class="sidebar-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="lime">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Mes commissions</span>
        </a>
    </li>
    <li>
        <a href="{{ route('my-pv.index') }}" class="sidebar-link {{ request()->routeIs('my-pv.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Historique PV</span>
        </a>
    </li>
    <li>
        <a href="{{ route('kyc.index') }}" class="sidebar-link {{ request()->routeIs('kyc.*') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="rose">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Vérification KYC</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Services</div>
    </li>
    <li>
        <a href="{{ route('services.index') }}"
           class="sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
           data-title="Services">
            <x-ui.sidebar-link-icon tone="primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Hub Services</span>
        </a>
    </li>
    <li>
        <a href="{{ auth()->user()->memberQuestionsEntryUrl() }}"
           class="sidebar-link {{ request()->routeIs('questions.*') ? 'active' : '' }}"
           data-title="Assistance">
            <x-ui.sidebar-link-icon tone="sky">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Assistance</span>
        </a>
    </li>
    @auth
        <li>
            <a href="{{ route('publications.index') }}"
               class="sidebar-link {{ request()->routeIs('publications.*') && ! auth()->user()->hasSupervisionAccess() ? 'active' : '' }}"
               data-title="Événements">
                <x-ui.sidebar-link-icon tone="amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </x-ui.sidebar-link-icon>
                <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Événements & promos</span>
            </a>
        </li>
        @if(auth()->user()->hasSupervisionAccess())
            <li>
                <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Supervision</div>
            </li>
            <li>
                <a href="{{ route('questions.index', ['status' => 'open']) }}"
                   class="sidebar-link {{ request()->routeIs('questions.*') ? 'active' : '' }}"
                   data-title="Questions reçues">
                    <x-ui.sidebar-link-icon tone="green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </x-ui.sidebar-link-icon>
                    <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Questions reçues</span>
                </a>
            </li>
            <li>
                <a href="{{ route('publications.index') }}"
                   class="sidebar-link {{ request()->routeIs('publications.*') ? 'active' : '' }}"
                   data-title="Publications">
                    <x-ui.sidebar-link-icon tone="purple">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </x-ui.sidebar-link-icon>
                    <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Publications</span>
                </a>
            </li>
        @endif
    @endauth

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Compte</div>
    </li>
    <li>
        <a href="{{ route('profile.settings') }}" class="sidebar-link">
            <x-ui.sidebar-link-icon tone="slate">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Préférences</span>
        </a>
    </li>
    <li>
        <a href="{{ route('profile.password') }}" class="sidebar-link {{ request()->routeIs('profile.password') ? 'active' : '' }}">
            <x-ui.sidebar-link-icon tone="pink">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Changer mot de passe</span>
        </a>
    </li>

    @auth
        @if(Auth::user()->hasRole('admin'))
            <li>
                <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Administration</div>
            </li>
            <li>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <x-ui.sidebar-link-icon tone="orange">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </x-ui.sidebar-link-icon>
                    <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Panneau d'administration</span>
                </a>
            </li>
        @endif

        <li>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-link sidebar-link--button sidebar-link--logout w-full">
                    <x-ui.sidebar-link-icon tone="danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </x-ui.sidebar-link-icon>
                    <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Déconnexion</span>
                </button>
            </form>
        </li>
    @endauth
</ul>
