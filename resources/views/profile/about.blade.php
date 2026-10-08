@extends('layouts.app')

@section('title', 'À propos de Salang')

@section('content')
<div class="profile-page profile-page--account profile-subpage profile-about-page">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">À propos de Salang</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Entreprise, mission et informations légales</p>
    </div>

    @include('profile.partials.account-subpage-header', [
        'title' => 'À propos',
        'sub' => 'Salang Group · mission & légal',
    ])

    <div class="profile-subpage-body">
        @include('profile.partials.about-content')

        <p class="profile-about-section-label">Documents & contact</p>
        <nav class="profile-account-menu profile-about-legal-menu" aria-label="Informations légales">
            @include('profile.partials.account-menu-row', [
                'href' => route('contact'),
                'label' => 'Nous contacter',
                'iconTone' => 'sky',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
            ])
            @include('profile.partials.account-menu-row', [
                'href' => route('terms-of-service'),
                'label' => 'Conditions d\'utilisation',
                'iconTone' => 'primary',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            ])
            @include('profile.partials.account-menu-row', [
                'href' => route('privacy-policy'),
                'label' => 'Politique de confidentialité',
                'iconTone' => 'violet',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
            ])
            @include('profile.partials.account-menu-row', [
                'href' => route('cookie-policy'),
                'label' => 'Politique des cookies',
                'iconTone' => 'accent',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            ])
        </nav>
    </div>
</div>
@endsection
