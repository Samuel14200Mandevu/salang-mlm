@extends('layouts.app')

@section('title', 'Centre d\'aide')

@section('content')
@php
    $memberFaqs = app(\App\Services\SiteContentService::class)->faqs();
    $helpLinks = [
        [
            'href' => route('profile.assistant'),
            'label' => 'Assistant Salang',
            'desc' => 'Posez une question à l’assistant',
            'fabSwitch' => true,
            'fabSwitchId' => 'assistantFabToggleHelp',
            'iconTone' => 'primary',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ],
        [
            'href' => route('contact'),
            'label' => 'Nous contacter',
            'desc' => 'Écrire au support Salang',
            'iconTone' => 'sky',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        ],
        [
            'href' => route('kyc.index'),
            'label' => 'Vérification d\'identité (KYC)',
            'desc' => 'Statut et documents',
            'iconTone' => 'success',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        ],
        [
            'href' => route('withdrawal.index'),
            'label' => 'Retraits & paiements',
            'desc' => 'Demander un retrait',
            'iconTone' => 'orange',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        ],
        [
            'href' => route('report.index'),
            'label' => 'Rapports & statistiques',
            'desc' => 'Suivi d’activité',
            'iconTone' => 'violet',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ],
        [
            'href' => route('activate.index'),
            'label' => 'Activer mon compte',
            'desc' => 'Package et activation',
            'iconTone' => 'accent',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
        ],
    ];
@endphp
<div class="profile-page profile-page--account profile-subpage profile-help-page">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Centre d'aide</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Support, KYC, retraits et rapports</p>
    </div>
    @include('profile.partials.account-subpage-header', [
        'title' => 'Centre d\'aide',
        'sub' => 'Support · KYC · retraits',
    ])

    <div class="profile-subpage-body profile-help-layout">
        <div class="profile-help-main space-y-4">
            <nav class="profile-account-menu md:hidden" aria-label="Aide">
                @foreach ($helpLinks as $link)
                    @include('profile.partials.account-menu-row', $link)
                @endforeach
            </nav>

            <div class="profile-help-tiles" aria-label="Aide">
                @foreach ($helpLinks as $link)
                    <a href="{{ $link['href'] }}" class="profile-help-tile">
                        <span class="profile-account-row__icon profile-account-row__icon--tone-{{ $link['iconTone'] }}">
                            {!! $link['icon'] !!}
                        </span>
                        <span class="min-w-0">
                            <span class="profile-help-tile__label">{{ $link['label'] }}</span>
                            <span class="profile-help-tile__desc">{{ $link['desc'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        @if (count($memberFaqs))
            <section class="profile-help-faq" aria-labelledby="help-faq-heading">
                <div class="flex items-end justify-between gap-2 mb-3">
                    <div>
                        <h2 id="help-faq-heading" class="text-sm font-semibold text-[var(--text-primary)]">Questions fréquentes</h2>
                        <p class="text-xs text-[var(--text-tertiary)] mt-0.5">Réponses officielles Salang</p>
                    </div>
                    <span class="text-[0.7rem] font-medium text-[var(--text-tertiary)] tabular-nums">{{ count($memberFaqs) }}</span>
                </div>
                <div class="space-y-2">
                    @foreach ($memberFaqs as $faq)
                        <details class="group rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] px-3.5 py-2.5 open:shadow-sm transition-shadow">
                            <summary class="cursor-pointer list-none flex items-start justify-between gap-3 text-sm font-medium text-[var(--text-primary)] [&::-webkit-details-marker]:hidden">
                                <span>{{ $faq['question'] }}</span>
                                <svg class="w-4 h-4 shrink-0 mt-0.5 text-[var(--text-tertiary)] transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="mt-2.5 pt-2.5 border-t border-[var(--border-color)] text-sm text-[var(--text-secondary)] leading-relaxed whitespace-pre-line">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
