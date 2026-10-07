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

{{-- Mobile : bandeau selon le grade --}}
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

{{-- Desktop / tablette --}}
<div class="dashboard-welcome-card animate-fadeInUp delay-1 hidden md:block">
    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
        <span class="{{ $currentRankBadgeClass ?? \App\Support\MlmRank::badgeClass($dashboardLevelNumber) }}">
            {{ $currentRankName }}
        </span>
        <span class="dashboard-level-badge">Niv. {{ $dashboardLevelNumber }}</span>
    </div>
    <h3 class="font-bold text-[var(--text-primary)] text-base sm:text-lg">{{ $dashboardLevel['welcome_title'] }}</h3>
    <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $dashboardLevel['welcome_message'] }}</p>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($ctaKeys as $key)
            @php $cta = $ctaDefinitions[$key] ?? null; @endphp
            @if($cta)
                <a href="{{ route($cta['route']) }}" class="btn {{ ($cta['primary'] ?? false) ? 'btn-primary' : 'btn-outline' }} btn-sm">
                    {{ $cta['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>

{{-- Mobile : actions sous le bandeau --}}
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
