@php
    $panels = [
        [
            'title' => 'Actualités & support',
            'items' => [
                [
                    'href' => route('publications.index'),
                    'label' => 'Événements & promos',
                    'desc' => 'Galerie, annonces et bonus carrière',
                    'icon' => 'primary',
                    'path' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                ],
                [
                    'href' => auth()->user()->memberQuestionsEntryUrl(),
                    'label' => 'Assistance & questions',
                    'desc' => 'Échange avec le support Salang',
                    'icon' => 'info',
                    'path' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'href' => route('notifications.index'),
                    'label' => 'Notifications',
                    'desc' => 'Alertes et messages de l’équipe',
                    'icon' => 'warning',
                    'path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                ],
            ],
        ],
        [
            'title' => 'Compte & finances',
            'items' => [
                [
                    'href' => route('report.index'),
                    'label' => 'Rapports & statistiques',
                    'desc' => 'Synthèse activité et historiques',
                    'icon' => 'info',
                    'path' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
                [
                    'href' => route('kyc.index'),
                    'label' => 'Vérification KYC',
                    'desc' => 'Identité et conformité',
                    'icon' => 'success',
                    'path' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                ],
                [
                    'href' => route('withdrawal.index'),
                    'label' => 'Retraits',
                    'desc' => 'Transfert vers mobile money ou banque',
                    'icon' => 'warning',
                    'path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                ],
                [
                    'href' => route('profile.help'),
                    'label' => 'Centre d’aide',
                    'desc' => 'Guides et contacts utiles',
                    'icon' => 'primary',
                    'path' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                ],
            ],
        ],
    ];

    if (auth()->user()->hasRole('admin')) {
        $panels[] = [
            'title' => 'Administration',
            'items' => [
                [
                    'href' => route('admin.dashboard'),
                    'label' => 'Tableau de bord admin',
                    'desc' => 'Gestion plateforme MLM',
                    'icon' => 'primary',
                    'path' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                ],
            ],
        ];
    }

    if (auth()->user()->hasSupervisionAccess()) {
        $panels[] = [
            'title' => 'Supervision',
            'items' => [
                [
                    'href' => route('questions.index', ['status' => 'open']),
                    'label' => 'Questions reçues',
                    'desc' => 'Fil des membres à traiter',
                    'icon' => 'success',
                    'path' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                ],
                [
                    'href' => route('publications.index'),
                    'label' => 'Gérer les publications',
                    'desc' => 'Création et modération',
                    'icon' => 'purple',
                    'path' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                ],
            ],
        ];
    }

    $iconClass = fn (string $tone) => match ($tone) {
        'success' => 'stat-icon-success',
        'warning' => 'stat-icon-warning',
        'info' => 'stat-icon-info',
        'purple' => 'stat-icon-purple',
        default => 'stat-icon-primary',
    };
@endphp

<div class="member-services-hub-panels">
    @foreach($panels as $panel)
        <section class="member-services-hub-panel" aria-labelledby="hub-panel-{{ $loop->index }}">
            <h2 id="hub-panel-{{ $loop->index }}" class="member-services-hub-panel__title">{{ $panel['title'] }}</h2>
            <div class="member-services-hub-panel__grid report-grid">
                @foreach($panel['items'] as $item)
                    <a href="{{ $item['href'] }}" class="report-card">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="stat-icon {{ $iconClass($item['icon']) }} flex-shrink-0">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['path'] }}"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">{{ $item['label'] }}</h3>
                                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-0.5">{{ $item['desc'] }}</p>
                                <span class="inline-block mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Ouvrir →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
