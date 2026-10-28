@php
    $isHome = request()->routeIs('home');
@endphp
<header class="public-nav" x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false">
    <div class="public-nav-inner">
        <button
            type="button"
            class="public-nav-menu-btn md:hidden"
            :class="{ 'is-open': navOpen }"
            @click="navOpen = !navOpen"
            :aria-expanded="navOpen"
            aria-controls="publicNavMobile"
            :aria-label="navOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
        >
            <span class="public-nav-menu-icon" aria-hidden="true">
                <span class="public-nav-menu-bar"></span>
                <span class="public-nav-menu-bar"></span>
                <span class="public-nav-menu-bar"></span>
            </span>
        </button>

        <a href="{{ url('/?site=1') }}" class="public-nav-brand">
            <x-ui.image
                src="images/salang_logo.png"
                alt="Salang Group"
                class="public-nav-brand__logo"
                width="160"
                height="44"
                :priority="true"
                :lazy="false"
            />
            <span class="public-nav-brand__text">
                <span class="public-nav-brand__name">Salang Group</span>
                <span class="public-nav-brand__tag">Health Care International</span>
            </span>
        </a>

        <nav class="public-nav-links" aria-label="Navigation principale">
            <a href="{{ url('/?site=1') }}" class="{{ $isHome ? 'is-active' : '' }}">Accueil</a>
            <a href="{{ route('site.offer') }}" class="{{ request()->routeIs('site.offer') ? 'is-active' : '' }}">Offre</a>
            <a href="{{ route('site.remuneration') }}" class="{{ request()->routeIs('site.remuneration') ? 'is-active' : '' }}">Rémunération</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}">Contact</a>
            <a href="{{ route('site.about') }}" class="{{ request()->routeIs('site.about') ? 'is-active' : '' }}">À propos</a>
        </nav>

        <div class="public-nav-actions">
            <button
                type="button"
                id="theme-toggle"
                class="public-theme-toggle"
                aria-label="Activer le thème sombre"
                aria-pressed="false"
            >
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" id="theme-icon" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="public-nav-cta">
                    Espace membre
                    <span aria-hidden="true">→</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="public-nav-login">Connexion</a>
                <a href="{{ route('register') }}" class="public-nav-cta public-nav-header-cta">
                    Adhérer
                    <span aria-hidden="true">→</span>
                </a>
            @endauth
        </div>
    </div>

    <nav
        id="publicNavMobile"
        class="public-nav-mobile md:hidden"
        x-show="navOpen"
        x-cloak
        @click.outside="navOpen = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        aria-label="Menu mobile"
    >
        <a href="{{ url('/?site=1') }}" @click="navOpen = false" class="{{ $isHome ? 'is-active' : '' }}">Accueil</a>
        <a href="{{ route('site.offer') }}" @click="navOpen = false">Offre</a>
        <a href="{{ route('site.remuneration') }}" @click="navOpen = false">Rémunération</a>
        <a href="{{ route('contact') }}" @click="navOpen = false">Contact</a>
        <a href="{{ route('site.about') }}" @click="navOpen = false">À propos</a>
        <div class="public-nav-mobile-divider" aria-hidden="true"></div>
        @auth
            <a href="{{ route('dashboard') }}" class="public-nav-mobile-cta" @click="navOpen = false">Mon espace membre</a>
        @else
            <a href="{{ route('login') }}" class="public-nav-mobile-link-muted" @click="navOpen = false">Connexion</a>
            <a href="{{ route('register') }}" class="public-nav-mobile-cta" @click="navOpen = false">Adhérer — 30&nbsp;USD</a>
        @endauth
    </nav>
</header>
