@php
    $itCenterActive = request()->routeIs('it-manager.follow-up.*');
    $newMessages = \App\Models\ContactMessage::query()
        ->where('status', \App\Models\ContactMessage::STATUS_NEW)
        ->count();
@endphp

{{-- Barre mobile IT = même structure que le membre (Accueil · … · Compte), sans feuille « Plus » --}}
<div class="member-mobile-nav-bar member-mobile-nav-bar--salang md:hidden">
    <nav class="mobile-bottom-nav mobile-bottom-nav--salang" id="mobileBottomNav" aria-label="Navigation IT mobile">
        <a href="{{ route('it-manager.dashboard') }}"
           class="nav-item {{ request()->routeIs('it-manager.dashboard') ? 'active' : '' }}">
            <svg class="nav-item__icon nav-item__icon--home" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10v10a1 1 0 001 1h5v-6a1 1 0 011-1h4a1 1 0 011 1v6h5a1 1 0 001-1V10a1 1 0 00-.293-.707l-7-7z"/>
            </svg>
            <span>Accueil</span>
        </a>

        <a href="{{ route('it-manager.activity-register.show') }}"
           class="nav-item {{ request()->routeIs('it-manager.activity-register.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>Registre</span>
        </a>

        <a href="{{ route('it-manager.follow-up.index') }}"
           class="nav-item nav-item--services-slot {{ $itCenterActive ? 'active' : '' }}"
           aria-hidden="true"
           tabindex="-1">
            <span class="nav-item__icon-gap"></span>
            <span>Suivi</span>
        </a>

        <a href="{{ route('site-management.contacts.index') }}"
           class="nav-item {{ request()->routeIs('site-management.contacts.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>Messages</span>
            @if ($newMessages > 0)
                <span class="badge-count">{{ $newMessages > 99 ? '99+' : $newMessages }}</span>
            @endif
        </a>

        <a href="{{ route('profile.settings') }}"
           class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Compte</span>
        </a>
    </nav>

    <a href="{{ route('it-manager.follow-up.index') }}"
       class="member-mobile-nav__services-logo {{ $itCenterActive ? 'is-active' : '' }}"
       aria-label="Suivi registre">
        <span class="member-mobile-nav__services-disc">
            <img class="member-mobile-nav__services-disc-logo"
                 src="{{ asset('images/salang_logo.webp') }}"
                 alt=""
                 decoding="async">
        </span>
    </a>
</div>
