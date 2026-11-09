{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')
<div class="orders-page space-y-4 sm:space-y-6">

    <div class="orders-page-header-desktop member-list-header animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mes commandes</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Suivez toutes vos commandes</p>
        </div>
        <div class="member-header-actions">
            <div class="header-search member-desktop-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           id="searchInputDesktop"
                           class="search-input"
                           placeholder="Rechercher…"
                           autocomplete="off"
                           aria-label="Rechercher une commande">
                </div>
            </div>

            <div class="orders-header-tools">
                <div class="supervision-filter-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="btn btn-outline btn-sm btn-icon orders-header-icon-btn"
                            id="ordersFilterBtn"
                            title="Filtrer"
                            :aria-expanded="open"
                            aria-haspopup="true"
                            aria-label="Filtrer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span id="ordersFilterLabel" class="sr-only">Filtrer</span>
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition
                         @click.outside="open = false"
                         class="users-filter-menu users-filter-panel">
                        <div class="space-y-2">
                            <div>
                                <label for="statusFilterDesktop">Statut</label>
                                <select id="statusFilterDesktop" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending">En attente</option>
                                    <option value="processing">En traitement</option>
                                    <option value="completed">Terminée</option>
                                    <option value="cancelled">Annulée</option>
                                </select>
                            </div>
                            <div>
                                <label for="dateFromDesktop">Du</label>
                                <input type="date" id="dateFromDesktop" class="form-control">
                            </div>
                            <div>
                                <label for="dateToDesktop">Au</label>
                                <input type="date" id="dateToDesktop" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('products.index') }}"
                   class="btn btn-primary btn-sm btn-icon orders-header-icon-btn"
                   title="Nouvelle commande"
                   aria-label="Nouvelle commande">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @include('orders.partials.catalog-head')

    <div class="orders-stats stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total commandes</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $orders->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total dépensé</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($totalSpent ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="orders-table-card card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Commande #</th>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Articles</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-row"
                            data-status="{{ $order->status }}"
                            data-date="{{ $order->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->items->count() }} article(s)
                            </td>
                            <td class="text-right font-bold text-primary-500 text-sm sm:text-base">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td>
                                @include('orders.partials.status-label', ['status' => $order->status])
                            </td>
                            <td class="text-right">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Commencez à magasiner pour passer votre première commande</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="orders-mobile-feed animate-fadeInUp delay-6">
        @include('orders.partials.mobile-list')
    </div>

    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
        <div class="orders-pagination mt-3 sm:mt-4">
            <x-salang-pagination :paginator="$orders" />
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchToggle = document.getElementById('ordersSearchToggle');
    var searchPanel = document.getElementById('ordersSearchPanel');

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function() {
            var open = searchPanel.classList.toggle('is-open');
            searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            searchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (open) {
                var input = searchPanel.querySelector('#searchInput');
                if (input) input.focus();
            }
        });
    }

    var searchInput = document.getElementById('searchInput');
    var statusFilter = document.getElementById('statusFilter');
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');

    var searchInputDesktop = document.getElementById('searchInputDesktop');
    var statusFilterDesktop = document.getElementById('statusFilterDesktop');
    var dateFromDesktop = document.getElementById('dateFromDesktop');
    var dateToDesktop = document.getElementById('dateToDesktop');

    var tableRows = document.querySelectorAll('#ordersTable tr.order-row');
    var mobileRows = document.querySelectorAll('.orders-mobile-row');

    function readFilters(source) {
        if (source === 'mobile') {
            return {
                search: (searchInput && searchInput.value || '').trim().toLowerCase(),
                status: statusFilter ? statusFilter.value : '',
                from: dateFrom ? dateFrom.value : '',
                to: dateTo ? dateTo.value : '',
            };
        }
        return {
            search: (searchInputDesktop && searchInputDesktop.value || '').trim().toLowerCase(),
            status: statusFilterDesktop ? statusFilterDesktop.value : '',
            from: dateFromDesktop ? dateFromDesktop.value : '',
            to: dateToDesktop ? dateToDesktop.value : '',
        };
    }

    function applyFilterToRow(row, filters) {
        var text = row.textContent.toLowerCase();
        var rowStatus = row.dataset.status || '';
        var rowDate = row.dataset.date || '';
        var show = true;
        if (filters.search && !text.includes(filters.search)) show = false;
        if (filters.status && rowStatus !== filters.status) show = false;
        if (filters.from && rowDate < filters.from) show = false;
        if (filters.to && rowDate > filters.to) show = false;
        row.style.display = show ? '' : 'none';
    }

    function filterRows(source) {
        var filters = readFilters(source);
        tableRows.forEach(function(row) { applyFilterToRow(row, filters); });
        mobileRows.forEach(function(row) {
            applyFilterToRow(row, filters);
            var item = row.closest('li');
            if (item) {
                item.style.display = row.style.display === 'none' ? 'none' : '';
            }
        });
    }

    function bindFilter(el, source) {
        if (!el) return;
        var eventName = el.tagName === 'SELECT' || el.type === 'date' ? 'change' : 'input';
        el.addEventListener(eventName, function() { filterRows(source); });
    }

    [searchInput, statusFilter, dateFrom, dateTo].forEach(function(el) { bindFilter(el, 'mobile'); });
    [searchInputDesktop, statusFilterDesktop, dateFromDesktop, dateToDesktop].forEach(function(el) { bindFilter(el, 'desktop'); });

    function syncOrdersFilterLabel() {
        var label = document.getElementById('ordersFilterLabel');
        var btn = document.getElementById('ordersFilterBtn');
        if (!label || !btn) return;
        var status = statusFilterDesktop ? statusFilterDesktop.value : '';
        var from = dateFromDesktop ? dateFromDesktop.value : '';
        var to = dateToDesktop ? dateToDesktop.value : '';
        var active = !!(status || from || to);
        var text = 'Filtrer';
        if (status === 'pending') text = 'En attente';
        else if (status === 'processing') text = 'En traitement';
        else if (status === 'completed') text = 'Terminée';
        else if (status === 'cancelled') text = 'Annulée';
        else if (from || to) text = 'Période';
        label.textContent = text;
        btn.classList.toggle('border-[var(--primary)]', active);
        btn.classList.toggle('text-[var(--primary)]', active);
    }
    [statusFilterDesktop, dateFromDesktop, dateToDesktop].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', syncOrdersFilterLabel);
    });
});
</script>
@endpush
@endsection
