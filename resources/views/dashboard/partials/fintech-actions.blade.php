@php
    $actionDefinitions = [
        'products' => ['route' => 'products.index', 'label' => 'Boutique', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        'packages' => ['route' => 'subscriptions.index', 'label' => 'Package', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4'],
        'withdrawal' => ['route' => 'withdrawal.index', 'label' => 'Retrait', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'team' => ['route' => 'network.index', 'label' => 'Équipe', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        'network' => ['route' => 'network.index', 'label' => 'Réseau', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        'commissions' => ['route' => 'commissions.index', 'label' => 'Gains', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
        'wallet' => ['route' => 'wallet.index', 'label' => 'Wallet', 'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        'grades' => ['route' => 'rank.index', 'label' => 'Grade', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
    ];
    $actions = $dashboardLevel['quick_actions'] ?? ['wallet', 'commissions', 'team', 'products'];
    $actions = array_slice(array_unique($actions), 0, 5);
@endphp
<nav class="member-fintech-actions member-fintech-animate member-fintech-animate--d2" aria-label="Actions rapides">
    @foreach($actions as $index => $key)
        @php $def = $actionDefinitions[$key] ?? null; @endphp
        @if($def)
            <a href="{{ route($def['route']) }}" class="member-fintech-actions__item {{ $index === 0 ? 'is-primary' : '' }}">
                <span class="member-fintech-actions__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $def['icon'] }}"/>
                    </svg>
                </span>
                <span class="member-fintech-actions__label">{{ $def['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
