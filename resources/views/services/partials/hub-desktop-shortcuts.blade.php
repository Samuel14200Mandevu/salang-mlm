@php
    $shortcuts = [
        [
            'href' => route('network.index'),
            'label' => 'Mon réseau',
            'hint' => 'Filleuls & généalogie',
            'tone' => 'primary',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        ],
        [
            'href' => route('commissions.index'),
            'label' => 'Commissions',
            'hint' => 'Gains & historique',
            'tone' => 'success',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        [
            'href' => route('publications.index'),
            'label' => 'Événements',
            'hint' => 'Promos & actualités',
            'tone' => 'warning',
            'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'href' => auth()->user()->memberQuestionsEntryUrl(),
            'label' => 'Assistance',
            'hint' => 'Support & questions',
            'tone' => 'info',
            'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];
@endphp

<nav class="member-services-shortcuts" aria-label="Accès rapides services">
    @foreach($shortcuts as $item)
        <a href="{{ $item['href'] }}" class="member-services-shortcut member-services-shortcut--{{ $item['tone'] }}">
            <span class="member-services-shortcut__icon stat-icon stat-icon-{{ $item['tone'] === 'primary' ? 'primary' : ($item['tone'] === 'success' ? 'success' : ($item['tone'] === 'warning' ? 'warning' : 'info')) }}" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
            </span>
            <span class="member-services-shortcut__body">
                <span class="member-services-shortcut__label">{{ $item['label'] }}</span>
                <span class="member-services-shortcut__hint">{{ $item['hint'] }}</span>
            </span>
            <span class="member-services-shortcut__arrow" aria-hidden="true">→</span>
        </a>
    @endforeach
</nav>
