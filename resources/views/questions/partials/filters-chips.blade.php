@php
    $current = $statusFilter ?? null;
    $chipQueryBase = array_filter([
        'q' => filled($q ?? null) ? $q : null,
    ], fn ($v) => filled($v));
@endphp
<div class="shop-filter-bar questions-status-filter-bar" role="tablist" aria-label="Filtrer par statut">
    <a href="{{ route('questions.index', $chipQueryBase) }}"
       class="shop-chip {{ ! filled($current) ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ ! filled($current) ? 'true' : 'false' }}">
        Tous
    </a>
    <a href="{{ route('questions.index', array_merge($chipQueryBase, ['status' => 'open'])) }}"
       class="shop-chip {{ $current === 'open' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $current === 'open' ? 'true' : 'false' }}">
        Ouvertes
    </a>
    <a href="{{ route('questions.index', array_merge($chipQueryBase, ['status' => 'resolved'])) }}"
       class="shop-chip {{ $current === 'resolved' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $current === 'resolved' ? 'true' : 'false' }}">
        Résolues
    </a>
</div>
