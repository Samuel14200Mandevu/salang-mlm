{{-- Menu dédié Responsable IT (pas de navigation MLM membre) --}}
<ul class="space-y-0.5">
    <li class="admin-sidebar-home-item">
        <div class="admin-sidebar-home-row">
            <a href="{{ route('it-manager.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('it-manager.dashboard') ? 'active' : '' }}"
               data-title="Accueil">
                <x-ui.sidebar-link-icon tone="blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </x-ui.sidebar-link-icon>
                <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Accueil</span>
            </a>
            <button type="button"
                    class="admin-rail-chevron"
                    x-show="!isMobile"
                    x-cloak
                    @click.stop="sidebarOpen = !sidebarOpen; if (typeof window.salangPersistSidebarOpen === 'function') window.salangPersistSidebarOpen(sidebarOpen)"
                    :aria-label="sidebarOpen ? 'Réduire le menu' : 'Agrandir le menu'"
                    :title="sidebarOpen ? 'Réduire le menu' : 'Agrandir le menu'">
                <span class="admin-rail-chevron__icon" aria-hidden="true" x-text="sidebarOpen ? '‹' : '›'"></span>
            </button>
        </div>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Activité</div>
    </li>
    {{-- Registre / Suivi : topbar desktop — visibles seulement dans le drawer mobile --}}
    <li x-show="isMobile" x-cloak>
        <a href="{{ route('it-manager.activity-register.show') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.activity-register.*') ? 'active' : '' }}"
           data-title="Registre">
            <x-ui.sidebar-link-icon tone="amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Registre</span>
        </a>
    </li>
    <li x-show="isMobile" x-cloak>
        <a href="{{ route('it-manager.follow-up.index') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.follow-up.*') ? 'active' : '' }}"
           data-title="Suivi">
            <x-ui.sidebar-link-icon tone="orange">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Suivi</span>
        </a>
    </li>
    <li>
        <a href="{{ route('it-manager.members.index') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.members.*') ? 'active' : '' }}"
           data-title="Membres">
            <x-ui.sidebar-link-icon tone="blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Membres</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Support</div>
    </li>
    <li>
        <a href="{{ route('questions.index', ['status' => 'open']) }}"
           class="sidebar-link {{ request()->routeIs('questions.*') ? 'active' : '' }}"
           data-title="Questions">
            <x-ui.sidebar-link-icon tone="green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Questions</span>
        </a>
    </li>
    {{-- Messages / Annonces : topbar desktop — visibles seulement dans le drawer mobile --}}
    <li x-show="isMobile" x-cloak>
        <a href="{{ route('site-management.contacts.index') }}"
           class="sidebar-link {{ request()->routeIs('site-management.contacts.*') ? 'active' : '' }}"
           data-title="Messages">
            <x-ui.sidebar-link-icon tone="sky">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Messages</span>
        </a>
    </li>
    <li x-show="isMobile" x-cloak>
        <a href="{{ route('it-manager.announcements.create') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.announcements.*') ? 'active' : '' }}"
           data-title="Annonces">
            <x-ui.sidebar-link-icon tone="purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Annonces</span>
        </a>
    </li>

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Contenu</div>
    </li>
    <li>
        <a href="{{ route('it-manager.site-pages.edit') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.site-pages.*') ? 'active' : '' }}"
           data-title="Pages site">
            <x-ui.sidebar-link-icon tone="slate">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Pages site</span>
        </a>
    </li>
    <li>
        <a href="{{ route('it-manager.faq.edit') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.faq.*') ? 'active' : '' }}"
           data-title="FAQ">
            <x-ui.sidebar-link-icon tone="green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Aide / FAQ</span>
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
    <li>
        <a href="{{ route('it-manager.media.index') }}"
           class="sidebar-link {{ request()->routeIs('it-manager.media.*') ? 'active' : '' }}"
           data-title="Médias">
            <x-ui.sidebar-link-icon tone="sky">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Médias</span>
        </a>
    </li>

    @if(auth()->user()?->hasRole('admin'))
        <li>
            <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Administration</div>
        </li>
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
               data-title="Panel Admin">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 01-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 01-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 01-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Panel Admin</span>
            </a>
        </li>
    @endif

    <li>
        <div class="sidebar-section transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Compte</div>
    </li>
    <li>
        <a href="{{ route('profile.settings') }}"
           class="sidebar-link {{ request()->routeIs('profile.settings') || request()->routeIs('profile.index') ? 'active' : '' }}"
           data-title="Paramètres">
            <x-ui.sidebar-link-icon tone="slate">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </x-ui.sidebar-link-icon>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Paramètres</span>
        </a>
    </li>

    <li class="pt-3 mt-3 border-t border-[var(--border-color)]">
        <a href="{{ url('/?site=1') }}"
           class="sidebar-link"
           data-title="Voir le site">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Voir le site</span>
        </a>
    </li>

    <li class="admin-sidebar-logout-item pt-4 mt-4 border-t border-[var(--border-color)]">
        <form method="POST" action="{{ route('logout') }}" class="admin-sidebar-logout logout-form sidebar-logout-form">
            @csrf
            <button type="submit"
                    class="sidebar-link sidebar-link--button sidebar-link--logout danger w-full"
                    data-title="Déconnexion">
                <x-ui.sidebar-link-icon tone="danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </x-ui.sidebar-link-icon>
                <span class="label transition-opacity duration-200" :class="!sidebarOpen ? 'hidden' : ''">Déconnexion</span>
            </button>
        </form>
    </li>
</ul>
