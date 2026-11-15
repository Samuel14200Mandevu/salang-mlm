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
            'featured' => true,
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
            'label' => 'Vérification d\'identité',
            'desc' => 'KYC · statut et documents',
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
            'href' => route('activate.index'),
            'label' => 'Activer mon compte',
            'desc' => 'Package et activation',
            'iconTone' => 'accent',
            'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
        ],
    ];
    $featured = collect($helpLinks)->firstWhere('featured', true);
    $topicLinks = collect($helpLinks)->reject(fn ($l) => ! empty($l['featured']))->values();
@endphp
<div class="profile-page profile-page--account profile-subpage profile-help-page">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Centre d'aide</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Support, KYC, retraits et questions fréquentes</p>
    </div>
    @include('profile.partials.account-subpage-header', [
        'title' => 'Centre d\'aide',
        'sub' => 'Support · KYC · retraits',
    ])

    <div class="profile-subpage-body profile-help-layout">
        <div class="profile-help-main">
            <section class="profile-help-hero" aria-labelledby="helpHeroTitle">
                <div class="profile-help-hero__glow" aria-hidden="true"></div>
                <p class="profile-help-hero__eyebrow">Assistance Salang</p>
                <h2 id="helpHeroTitle" class="profile-help-hero__title">Comment pouvons-nous vous aider&nbsp;?</h2>
                <p class="profile-help-hero__sub text-pretty">
                    Parcourez les sujets ci-dessous ou posez votre question à l’assistant.
                </p>

                @if($featured)
                    <div class="profile-help-hero__cta">
                        <a href="{{ $featured['href'] }}" class="btn btn-primary profile-help-hero__btn">
                            <span class="profile-help-hero__btn-icon" aria-hidden="true">{!! $featured['icon'] !!}</span>
                            {{ $featured['label'] }}
                        </a>
                        @if(! empty($featured['fabSwitch']))
                            <div class="profile-help-hero__fab">
                                <span class="profile-help-hero__fab-label">Bouton flottant</span>
                                @include('profile.partials.assistant-fab-toggle', [
                                    'toggleId' => $featured['fabSwitchId'] ?? 'assistantFabToggleHelp',
                                ])
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <section class="profile-help-topics" aria-label="Sujets d’aide">
                <h3 class="profile-help-section-title">Sujets rapides</h3>
                <div class="profile-help-tiles">
                    @foreach ($topicLinks as $link)
                        <a href="{{ $link['href'] }}" class="profile-help-tile">
                            <span class="profile-account-row__icon profile-account-row__icon--tone-{{ $link['iconTone'] }}">
                                {!! $link['icon'] !!}
                            </span>
                            <span class="profile-help-tile__text">
                                <span class="profile-help-tile__label">{{ $link['label'] }}</span>
                                <span class="profile-help-tile__desc">{{ $link['desc'] }}</span>
                            </span>
                            <svg class="profile-help-tile__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="profile-help-faq" aria-labelledby="help-faq-heading">
            <div class="profile-help-faq__head">
                <div>
                    <h2 id="help-faq-heading" class="profile-help-section-title profile-help-section-title--flush">Questions fréquentes</h2>
                    <p class="profile-help-faq__sub">Réponses officielles Salang</p>
                </div>
                @if (count($memberFaqs))
                    <span class="profile-help-faq__count" id="helpFaqCount">{{ count($memberFaqs) }}</span>
                @endif
            </div>

            @if (count($memberFaqs))
                <label class="profile-help-search" for="helpFaqSearch">
                    <svg class="profile-help-search__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                    </svg>
                    <input
                        id="helpFaqSearch"
                        type="search"
                        class="profile-help-search__input"
                        placeholder="Rechercher une question…"
                        autocomplete="off"
                        enterkeyhint="search"
                    >
                </label>

                <div class="profile-help-faq__list" id="helpFaqList">
                    @foreach ($memberFaqs as $faq)
                        <details
                            class="profile-help-faq__item"
                            data-faq-q="{{ mb_strtolower($faq['question'].' '.$faq['answer']) }}"
                        >
                            <summary class="profile-help-faq__summary">
                                <span class="profile-help-faq__q">{{ $faq['question'] }}</span>
                                <svg class="profile-help-faq__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="profile-help-faq__a text-pretty">{{ $faq['answer'] }}</div>
                        </details>
                    @endforeach
                </div>
                <p class="profile-help-faq__empty" id="helpFaqEmpty" hidden>Aucune question ne correspond à votre recherche.</p>
            @else
                <div class="profile-help-faq__blank">
                    <p>Les questions fréquentes seront bientôt disponibles.</p>
                    <a href="{{ route('contact') }}" class="btn btn-outline btn-sm mt-3">Contacter le support</a>
                </div>
            @endif
        </section>
    </div>
</div>

@if (count($memberFaqs))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('helpFaqSearch');
    var list = document.getElementById('helpFaqList');
    var empty = document.getElementById('helpFaqEmpty');
    var countEl = document.getElementById('helpFaqCount');
    if (!input || !list) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('[data-faq-q]'));
    var total = items.length;

    input.addEventListener('input', function () {
        var q = (input.value || '').trim().toLowerCase();
        var visible = 0;
        items.forEach(function (el) {
            var hay = el.getAttribute('data-faq-q') || '';
            var show = !q || hay.indexOf(q) !== -1;
            el.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.hidden = visible > 0;
        if (countEl) countEl.textContent = String(q ? visible : total);
    });
});
</script>
@endpush
@endif
@endsection
