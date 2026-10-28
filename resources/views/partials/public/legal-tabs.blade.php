@php
    $tabs = [
        ['route' => 'legal.mentions', 'label' => 'Mentions légales'],
        ['route' => 'legal.terms', 'label' => 'Conditions générales'],
        ['route' => 'legal.privacy', 'label' => 'Confidentialité'],
    ];
@endphp
<nav class="public-legal-tabs" aria-label="Documents légaux">
    @foreach ($tabs as $tab)
        <a
            href="{{ route($tab['route']) }}"
            class="public-legal-tabs__link {{ request()->routeIs($tab['route']) ? 'is-active' : '' }}"
            @if (request()->routeIs($tab['route'])) aria-current="page" @endif
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
