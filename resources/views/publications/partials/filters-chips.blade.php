@php
    $type = $typeFilter ?? null;
    $activeOnly = $activeFilter ?? false;
    $qValue = filled($q ?? null) ? $q : null;

    $withType = static function (?string $typeValue) use ($activeOnly, $qValue): array {
        $params = [];
        if (filled($qValue)) {
            $params['q'] = $qValue;
        }
        if (filled($typeValue)) {
            $params['type'] = $typeValue;
        }
        if ($activeOnly) {
            $params['active'] = '1';
        }

        return $params;
    };

    $toggleActiveParams = array_filter([
        'q' => $qValue,
        'type' => filled($type) ? $type : null,
    ], fn ($v) => filled($v));
    if (! $activeOnly) {
        $toggleActiveParams['active'] = '1';
    }
@endphp
<div class="shop-filter-bar publications-type-filter-bar" role="tablist" aria-label="Filtrer les publications">
    <a href="{{ route('publications.index', $withType(null)) }}"
       class="shop-chip {{ ! filled($type) ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ ! filled($type) ? 'true' : 'false' }}">
        Tous
    </a>
    <a href="{{ route('publications.index', $withType('event')) }}"
       class="shop-chip {{ $type === 'event' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $type === 'event' ? 'true' : 'false' }}">
        Événements
    </a>
    <a href="{{ route('publications.index', $withType('promotion')) }}"
       class="shop-chip {{ $type === 'promotion' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $type === 'promotion' ? 'true' : 'false' }}">
        Promotions
    </a>
    <a href="{{ route('publications.index', $toggleActiveParams) }}"
       class="shop-chip {{ $activeOnly ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $activeOnly ? 'true' : 'false' }}">
        Actifs seulement
    </a>
</div>
