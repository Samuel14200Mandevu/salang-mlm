@extends('layouts.app')

@section('title', 'Centre d\'aide')

@section('content')
<div class="profile-page profile-page--account profile-subpage">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Centre d'aide</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Support, KYC, retraits et rapports</p>
    </div>
    @include('profile.partials.account-subpage-header', [
        'title' => 'Centre d\'aide',
        'sub' => 'Support · KYC · retraits',
    ])

    <nav class="profile-account-menu profile-subpage-body" aria-label="Aide">
        @include('profile.partials.account-menu-row', [
            'href' => route('profile.assistant'),
            'label' => 'Assistant Salang',
            'fabSwitch' => true,
            'fabSwitchId' => 'assistantFabToggleHelp',
            'iconTone' => 'primary',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('contact'),
            'label' => 'Nous contacter',
            'iconTone' => 'sky',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('kyc.index'),
            'label' => 'Vérification d\'identité (KYC)',
            'iconTone' => 'success',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('withdrawal.index'),
            'label' => 'Retraits & paiements',
            'iconTone' => 'orange',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('report.index'),
            'label' => 'Rapports & statistiques',
            'iconTone' => 'violet',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ])
        @include('profile.partials.account-menu-row', [
            'href' => route('activate.index'),
            'label' => 'Activer mon compte',
            'iconTone' => 'accent',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
        ])
    </nav>
</div>
@endsection
