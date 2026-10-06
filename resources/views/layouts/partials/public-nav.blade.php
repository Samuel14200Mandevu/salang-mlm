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

        <a href="{{ url('/') }}" class="public-nav-brand flex items-center gap-2 min-w-0 flex-1">
            <x-ui.image
                src="images/salang_logo.png"
                alt="Salang Group"
                class="h-8 sm:h-10 w-auto shrink-0"
                width="160"
                height="44"
                :priority="true"
                :lazy="false"
            />
            <span class="hidden min-[380px]:flex sm:flex flex-col min-w-0">
                <span class="text-sm font-bold text-[var(--text-primary)] leading-none truncate">Salang Group</span>
                <span class="text-[0.625rem] uppercase tracking-[0.12em] text-[var(--text-muted)] mt-0.5 truncate hidden sm:block">Health Care International</span>
            </span>
        </a>

        <nav class="public-nav-links" aria-label="Sections">
            <a href="{{ url('/') }}#about">Entreprise</a>
            <a href="{{ url('/') }}#how-it-works">Parcours</a>
            <a href="{{ url('/') }}#remuneration">Rémunération</a>
        </nav>

        <div class="public-nav-actions flex items-center gap-1.5 sm:gap-3 shrink-0">
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
                <a href="{{ route('dashboard') }}" class="public-btn-primary text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-2.5 whitespace-nowrap">
                    Espace membre
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden md:inline text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="public-nav-header-cta public-btn-primary text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-2.5 whitespace-nowrap">
                    Adhérer
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
        <a href="{{ url('/') }}#about" @click="navOpen = false">Entreprise</a>
        <a href="{{ url('/') }}#how-it-works" @click="navOpen = false">Parcours membre</a>
        <a href="{{ url('/') }}#remuneration" @click="navOpen = false">Rémunération</a>
        <div class="public-nav-mobile-divider" aria-hidden="true"></div>
        @auth
            <a href="{{ route('dashboard') }}" class="public-nav-mobile-cta" @click="navOpen = false">Mon espace membre</a>
        @else
            <a href="{{ route('login') }}" class="public-nav-mobile-link-muted" @click="navOpen = false">Connexion</a>
            <a href="{{ route('register') }}" class="public-nav-mobile-cta" @click="navOpen = false">Adhérer — 30&nbsp;USD</a>
        @endauth
    </nav>
</header>
