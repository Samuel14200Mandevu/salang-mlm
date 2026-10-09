@php
    $current = $statusFilter ?? null;
@endphp
<div class="shop-filter-bar questions-status-filter-bar" role="tablist" aria-label="Filtrer par statut">
    <a href="{{ route('questions.index') }}"
       class="shop-chip {{ ! filled($current) ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ ! filled($current) ? 'true' : 'false' }}">
        Tous
    </a>
    <a href="{{ route('questions.index', ['status' => 'open']) }}"
       class="shop-chip {{ $current === 'open' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $current === 'open' ? 'true' : 'false' }}">
        Ouvertes
    </a>
    <a href="{{ route('questions.index', ['status' => 'resolved']) }}"
       class="shop-chip {{ $current === 'resolved' ? 'is-active' : '' }}"
       role="tab"
       aria-selected="{{ $current === 'resolved' ? 'true' : 'false' }}">
        Résolues
    </a>
</div>
