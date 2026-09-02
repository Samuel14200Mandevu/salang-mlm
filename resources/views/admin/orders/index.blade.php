{{-- resources/views/admin/orders/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-navy: #0F2B4F;
    --primary-navy-dark: #091E3B;
    --primary-navy-light: #1A3F6A;
    --bg-base: #F5F6F8;
    --bg-card: #FFFFFF;
    --bg-secondary: #EEF0F3;
    --bg-hover: #E8EAEE;
    --text-primary: #1A1A1E;
    --text-secondary: #4A4A52;
    --text-tertiary: #7A7A82;
    --border-color: #DCDEE3;
    --border-light: #E8EAEE;
    --success: #1F7B4D;
    --danger: #B32A2A;
    --warning: #A65A0E;
    --info: #0A2A6C;
}

.order-row {
    transition: background 0.1s ease;
}
.order-row:hover {
    background: var(--bg-hover);
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.order-status-pending {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.order-status-processing {
    background: #E8F0F8;
    color: #065F9C;
    border-color: #C8DCE8;
}
.order-status-completed {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.order-status-cancelled {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    transition: border-color 0.15s ease;
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
    transition: background 0.15s ease, border-color 0.15s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-navy);
    color: white;
    border-color: var(--primary-navy);
}
.btn-primary:hover {
    background: var(--primary-navy-dark);
    border-color: var(--primary-navy-dark);
}

.btn-success {
    background: #1F7B4D;
    color: white;
    border-color: #1F7B4D;
}
.btn-success:hover {
    background: #16633D;
    border-color: #16633D;
}

.btn-danger {
    background: #B32A2A;
    color: white;
    border-color: #B32A2A;
}
.btn-danger:hover {
    background: #8F2121;
    border-color: #8F2121;
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
    border-radius: 10px;
    padding: 1.25rem;
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.badge-danger {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}
.badge-info {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}

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

.header-search .search-wrapper {
    position: relative;
}

.header-search .search-wrapper .search-input {
    padding: 0.375rem 0.75rem 0.375rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.813rem;
    width: 220px;
    transition: border-color 0.15s ease, width 0.2s ease;
    outline: none;
}

.header-search .search-wrapper .search-input:focus {
    border-color: var(--primary-navy);
    width: 280px;
}

.header-search .search-wrapper .search-input::placeholder {
    color: var(--text-tertiary);
}

.header-search .search-wrapper .clear-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-tertiary);
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
    .card-stats {
        padding: 0.625rem;
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
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Commandes</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $orders->total() ?? 0 }} commandes
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
            </p>
        </div>
        <div class="header-search">
            <div class="search-wrapper">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Rechercher une commande"
                       autocomplete="off"
                       value="{{ request('search') }}">
                <button type="button" id="clearBtn" class="clear-btn {{ request('search') ? 'visible' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">{{ $totalOrders ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#A65A0E]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-lg sm:text-xl font-bold text-[#065F9C]">{{ $processingCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl font-bold text-[#1F7B4D]">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-5">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulées</p>
            <p class="text-lg sm:text-xl font-bold text-[#B32A2A]">{{ $cancelledCount ?? 0 }}</p>
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
                        <th>N° commande</th>
                        <th>Client</th>
                        <th class="hidden md:table-cell">Email</th>
                        <th class="hidden sm:table-cell">Articles</th>
                        <th class="text-right">Total</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                        <th class="text-right">Actions</th>
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
                            <td class="text-right font-bold text-[var(--primary-navy)] text-sm">
                                {{ number_format($order->total, 2) }} €
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
                                <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'En attente' : 'Échoué') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-primary btn-sm" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
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
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    let searchTimeout;

    // Gestion de la recherche
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Afficher/masquer le bouton clear
            clearBtn.classList.toggle('visible', query.length > 0);
            
            searchTimeout = setTimeout(() => {
                fetchOrders(query);
            }, 300);
        });

        // Raccourci ESC pour effacer
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                clearBtn.classList.remove('visible');
                fetchOrders('');
                this.blur();
            }
        });

        // Focus avec la touche /
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const active = document.activeElement;
                if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
                    return;
                }
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }

    // Bouton clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            fetchOrders('');
            searchInput.focus();
        });
    }

    function fetchOrders(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('ordersTable');
        if (!tableBody) return;

        // Afficher un indicateur de chargement
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--primary-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Recherche en cours…</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Mettre à jour le tableau
            const newTableBody = doc.getElementById('ordersTable');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            // Mettre à jour la pagination
            const newPagination = doc.getElementById('paginationContainer');
            const paginationContainer = document.getElementById('paginationContainer');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }

            // Mettre à jour le titre et le compteur
            const title = document.querySelector('h1');
            const subtitle = document.querySelector('.text-sm.text-\\[var\\(--text-secondary\\)\\]');
            if (title && subtitle) {
                const totalMatch = html.match(/(\d+)\s+commandes?/);
                if (totalMatch) {
                    subtitle.textContent = totalMatch[0];
                }
                // Ajouter l'info de recherche
                if (query) {
                    subtitle.innerHTML = totalMatch[0] + ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Résultats pour "' + query + '"</span>';
                }
            }

            // Mettre à jour les statistiques
            updateStats(doc);
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[#B32A2A]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }

    function updateStats(doc) {
        const statValues = doc.querySelectorAll('.card-stats .text-lg, .card-stats .text-xl');
        const currentStats = document.querySelectorAll('.card-stats .text-lg, .card-stats .text-xl');
        
        if (statValues.length === currentStats.length && statValues.length > 0) {
            statValues.forEach((stat, index) => {
                if (currentStats[index]) {
                    currentStats[index].textContent = stat.textContent;
                }
            });
        }
    }

    // Recharger les données lors du retour sur la page
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && searchInput) {
            const currentQuery = searchInput.value.trim();
            // Ne recharger que si la recherche est vide
            if (!currentQuery) {
                fetchOrders('');
            }
        }
    });
});
</script>
@endpush
@endsection