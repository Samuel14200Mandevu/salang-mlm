        @php
            $memberServicesNavActive = request()->routeIs('services.*');
        @endphp

        <div class="member-mobile-nav-bar member-mobile-nav-bar--salang md:hidden">
            <nav class="mobile-bottom-nav mobile-bottom-nav--salang" id="mobileBottomNav" aria-label="Navigation mobile">
                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-item__icon nav-item__icon--home" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10v10a1 1 0 001 1h5v-6a1 1 0 011-1h4a1 1 0 011 1v6h5a1 1 0 001-1V10a1 1 0 00-.293-.707l-7-7z"/>
                    </svg>
                    <span>Accueil</span>
                </a>

                <a href="{{ route('products.index') }}"
                   class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Boutique</span>
                    <x-ui.cart-badge variant="bottom-nav" />
                </a>

                <a href="{{ route('services.index') }}"
                   class="nav-item nav-item--services-slot {{ $memberServicesNavActive ? 'active' : '' }}"
                   aria-hidden="true"
                   tabindex="-1">
                    <span class="nav-item__icon-gap"></span>
                    <span>Services</span>
                </a>

                <a href="{{ route('wallet.index') }}"
                   class="nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Portefeuille</span>
                </a>

                <a href="{{ route('profile.index') }}"
                   class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Compte</span>
                </a>
            </nav>

            <a href="{{ route('services.index') }}"
               class="member-mobile-nav__services-logo {{ $memberServicesNavActive ? 'is-active' : '' }}"
               aria-label="Services">
                <span class="member-mobile-nav__services-disc">
                    <img class="member-mobile-nav__services-disc-logo"
                         src="{{ asset('images/salang_logo.webp') }}"
                         alt=""
                         decoding="async">
                </span>
            </a>
        </div>

    </div>
