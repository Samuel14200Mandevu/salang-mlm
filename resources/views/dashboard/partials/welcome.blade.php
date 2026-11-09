@php
    $ctaKeys = $dashboardLevel['welcome_ctas'] ?? array_slice($dashboardLevel['quick_actions'] ?? [], 0, 2);
    $ctaDefinitions = [
        'products' => ['route' => 'products.index', 'label' => 'Boutique', 'primary' => true],
        'packages' => ['route' => 'subscriptions.index', 'label' => 'Mon package', 'primary' => true],
        'team' => ['route' => 'network.index', 'label' => 'Mon équipe', 'primary' => false],
        'network' => ['route' => 'network.index', 'label' => 'Mon réseau', 'primary' => false],
        'commissions' => ['route' => 'commissions.index', 'label' => 'Commissions', 'primary' => false],
        'grades' => ['route' => 'rank.index', 'label' => 'Mon grade', 'primary' => false],
        'wallet' => ['route' => 'wallet.index', 'label' => 'Portefeuille', 'primary' => false],
    ];
@endphp

{{-- Mobile uniquement — le desktop suit la structure IT (titre + KPI + cartes) --}}
<div class="member-dashboard-greeting md:hidden">
    <div class="flex flex-wrap items-center gap-2 mb-1">
        <span class="{{ $currentRankBadgeClass ?? \App\Support\MlmRank::badgeClass($dashboardLevelNumber) }}">
            {{ $currentRankName }}
        </span>
        <span class="member-dashboard-greeting__level">Niv. {{ $dashboardLevelNumber }}</span>
    </div>
    <h1>{{ $dashboardLevel['welcome_title'] }}</h1>
    <p>{{ $dashboardLevel['welcome_message'] }}</p>
</div>

<div class="member-mobile-stack-actions md:hidden">
    @foreach($ctaKeys as $key)
        @php $cta = $ctaDefinitions[$key] ?? null; @endphp
        @if($cta)
            <a href="{{ route($cta['route']) }}" class="btn {{ ($cta['primary'] ?? false) ? 'btn-primary' : 'btn-outline' }} btn-sm">
                {{ $cta['label'] }}
            </a>
        @endif
    @endforeach
</div>
