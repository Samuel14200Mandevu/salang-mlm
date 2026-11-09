@extends('admin.layouts.app')

@section('title', 'Historique stock')

@push('styles')
<style>
[x-cloak] { display: none !important; }

.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 12.5rem;
    max-height: min(70vh, 28rem);
    overflow-y: auto;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    padding: 0.35rem;
    z-index: 40;
}
.users-filter-menu a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    border-radius: 0.45rem;
    font-size: 0.8125rem;
    color: var(--text-primary);
    text-decoration: none;
}
.users-filter-menu a:hover { background: var(--bg-secondary); }
.users-filter-menu a.is-active {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 600;
}

.admin-app .header-actions .products-header-tools {
    flex: 0 0 auto;
    order: -1;
}
.admin-app .header-actions .products-header-search.header-search {
    width: 11.5rem;
    max-width: 11.5rem;
    flex: 0 0 11.5rem;
}
.admin-app .header-actions .products-header-search .search-wrapper,
.admin-app .header-actions .products-header-search .search-input {
    width: 100%;
    max-width: 100%;
}
@media (min-width: 1024px) {
    .admin-app .header-actions .products-header-search.header-search {
        width: 14rem;
        max-width: 14rem;
        flex-basis: 14rem;
    }
}
</style>
@endpush

@section('content')
@php
    $typeFilter = request('type');
    $filterActive = filled($typeFilter);
    $filterButtonLabel = $typeFilter
        ? ($typeLabels[$typeFilter] ?? 'Filtrer')
        : 'Filtrer';

    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));

    $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
        return route('admin.products.stock-history', array_filter(
            array_merge($filterQueryBase, $overrides),
            fn ($v) => $v !== null && $v !== ''
        ));
    };
@endphp

<div class="space-y-4 sm:space-y-6">

    @include('admin.layouts.partials.desktop-page-header', [
        'title' => 'Historique stock',
        'subtitle' => $movements->total().' mouvement(s)',
        'actions' => view('admin.products.partials.stock-history-header-actions', [
            'typeLabels' => $typeLabels,
            'typeFilter' => $typeFilter,
            'filterActive' => $filterActive,
            'filterButtonLabel' => $filterButtonLabel,
            'filterUrl' => $filterUrl,
        ])->render(),
    ])

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Historique</h1>
                <p class="page-subtitle">{{ $movements->total() }} mouvement(s)</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('admin.products.stock-overview') }}" class="btn btn-outline btn-sm btn-round-icon" title="Stock" aria-label="Stock">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Δ</th>
                        <th>Avant</th>
                        <th>Après</th>
                        <th class="hidden sm:table-cell">Motif</th>
                        <th class="hidden md:table-cell">Par</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="text-xs text-[var(--text-tertiary)] whitespace-nowrap">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($movement->product)
                                    <a href="{{ route('admin.products.stock', $movement->product_id) }}"
                                       class="text-sm font-medium text-[var(--text-primary)] hover:text-[var(--primary)]">
                                        {{ $movement->product->name }}
                                    </a>
                                    @if($movement->product->sku)
                                        <div class="text-[11px] font-mono text-[var(--text-tertiary)]">{{ $movement->product->sku }}</div>
                                    @endif
                                @else
                                    <span class="text-sm text-[var(--text-tertiary)]">Produit #{{ $movement->product_id }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $movement->quantity_delta >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $movement->type_label }}
                                </span>
                            </td>
                            <td class="font-semibold {{ $movement->quantity_delta >= 0 ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }}">
                                {{ $movement->quantity_delta > 0 ? '+'.$movement->quantity_delta : $movement->quantity_delta }}
                            </td>
                            <td>{{ $movement->quantity_before }}</td>
                            <td>{{ $movement->quantity_after }}</td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)] max-w-[14rem] truncate" title="{{ $movement->reason }}">
                                {{ $movement->reason ?: '—' }}
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-tertiary)]">
                                {{ $movement->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-[var(--text-secondary)]">
                                Aucun mouvement pour ce filtre.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($movements->hasPages())
        <div class="flex justify-center">
            <x-salang-pagination :paginator="$movements" />
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('historySearchDesktop');
    if (!input) return;
    let timeout;
    input.addEventListener('input', function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const url = new URL(window.location.href);
            const q = this.value.trim();
            if (q) url.searchParams.set('search', q);
            else url.searchParams.delete('search');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 400);
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(timeout);
            const url = new URL(window.location.href);
            const q = this.value.trim();
            if (q) url.searchParams.set('search', q);
            else url.searchParams.delete('search');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    });
});
</script>
@endpush
