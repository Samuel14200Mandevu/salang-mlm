{{-- resources/views/admin/orders/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.order-row {
    transition: background 0.15s ease;
}
.order-row:hover {
    background: var(--bg-hover);
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.order-status-pending {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.order-status-processing {
    background: rgba(6, 95, 156, 0.12);
    color: #065F9C;
    border-color: rgba(6, 95, 156, 0.15);
}
.order-status-completed {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.order-status-cancelled {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.card-stats:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

/* Search bar in header */
.header-search {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-search .search-wrapper {
    position: relative;
}

.header-search .search-wrapper .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.header-search .search-wrapper .search-input {
    padding: 0.375rem 0.75rem 0.375rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.813rem;
    width: 220px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, width 0.2s ease;
    outline: none;
}

.header-search .search-wrapper .search-input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
    width: 280px;
}

.header-search .search-wrapper .search-input::placeholder {
    color: var(--text-muted);
}

.header-search .search-wrapper .clear-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    display: none;
    transition: background 0.15s ease;
}
.header-search .search-wrapper .clear-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.header-search .search-wrapper .clear-btn.visible {
    display: block;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }

@media (max-width: 640px) {
    .header-search {
        width: 100%;
    }
    .header-search .search-wrapper {
        flex: 1;
    }
    .header-search .search-wrapper .search-input {
        width: 100%;
    }
    .header-search .search-wrapper .search-input:focus {
        width: 100%;
    }
    .table thead th, .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    .btn-sm svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    .card-stats {
        padding: 0.625rem;
    }
    .card-stats .text-2xl {
        font-size: 1.25rem;
    }
    .order-status {
        font-size: 0.55rem;
        padding: 0.1rem 0.4rem;
    }
    .card {
        padding: 0.875rem;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    .table thead th, .table tbody td {
        padding: 0.25rem 0.375rem;
        font-size: 0.6rem;
    }
    .btn-sm {
        padding: 0.125rem 0.375rem;
        font-size: 0.6rem;
    }
    .btn-sm svg {
        width: 0.75rem;
        height: 0.75rem;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Gestion des commandes</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Gérez toutes les commandes de la plateforme</p>
        </div>
        <div class="header-search">
            <div class="search-wrapper">
                <span class="search-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Rechercher..."
                       autocomplete="off">
                <button type="button" id="clearBtn" class="clear-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-[var(--primary-blue)]">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $totalOrders ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B54708] animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#065F9C] animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-lg sm:text-xl font-bold text-[#065F9C]">{{ $processingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A] animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B91C1C] animate-fadeInUp delay-5">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulées</p>
            <p class="text-lg sm:text-xl font-bold text-[#B91C1C]">{{ $cancelledCount ?? 0 }}</p>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card animate-fadeInUp delay-3">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commandes</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $orders->total() ?? 0 }} commandes</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm">Client</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Articles</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm">Paiement</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-row"
                            data-search="{{ strtolower($order->order_number . ' ' . ($order->user?->name ?? '') . ' ' . ($order->user?->email ?? '')) }}">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="font-medium text-sm">
                                {{ $order->user?->name ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->user?->email ?? 'N/A' }}
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->items->count() }} article(s)
                            </td>
                            <td class="text-right font-bold text-[var(--primary-blue)] text-sm">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td>
                                <span class="order-status order-status-{{ $order->status }}">
                                    @if($order->status == 'pending') En attente
                                    @elseif($order->status == 'processing') En traitement
                                    @elseif($order->status == 'completed') Terminée
                                    @elseif($order->status == 'cancelled') Annulée
                                    @else {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'En attente' : 'Échoué') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-primary btn-sm" title="Voir">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($order->status == 'pending' || $order->status == 'processing')
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" title="Marquer comme terminée">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->status == 'pending')
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Annuler" onclick="return confirm('Annuler cette commande ?')">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commandes apparaîtront ici</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var clearBtn = document.getElementById('clearBtn');
    var rows = document.querySelectorAll('#ordersTable tr');

    function filterRows() {
        var search = searchInput.value.trim().toLowerCase();

        rows.forEach(function(row) {
            var data = row.dataset.search || '';
            var text = row.textContent.toLowerCase();
            var show = data.includes(search) || text.includes(search);
            row.style.display = show ? '' : 'none';
        });

        clearBtn.classList.toggle('visible', search.length > 0);
    }

    // Recherche en temps réel
    searchInput.addEventListener('input', filterRows);

    // Raccourci ESC pour effacer
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            clearBtn.classList.remove('visible');
            filterRows();
            this.blur();
        }
    });

    // Bouton clear
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        filterRows();
        searchInput.focus();
    });

    // Raccourci Ctrl+F / Cmd+F pour focus sur la recherche
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            // Ne pas interférer avec le Ctrl+F du navigateur
        }
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            var active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
                return;
            }
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });

    // Focus automatique si le champ est vide
    if (!searchInput.value) {
        // Ne pas focus automatiquement pour ne pas gêner
    }
});
</script>
@endpush
@endsection