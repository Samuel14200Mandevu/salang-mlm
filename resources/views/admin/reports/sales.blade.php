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

.sales-row {
    transition: background 0.15s ease;
}
.sales-row:hover {
    background: var(--bg-hover);
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
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }

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

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.input::placeholder {
    color: var(--text-muted);
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
.delay-6 { animation-delay: 0.3s; }
.delay-7 { animation-delay: 0.35s; }

@media (max-width: 640px) {
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    .card-stats { padding: 0.625rem; }
    .card-stats .text-2xl { font-size: 1.25rem; }
    .card { padding: 0.875rem; }
    .grid-cols-5 { grid-template-columns: 1fr 1fr !important; }
    .grid-cols-4 { grid-template-columns: 1fr 1fr !important; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Rapport des ventes</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Analyse détaillée des commandes</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.pdf', ['type' => 'sales']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
               class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'orders']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
               class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                CSV
            </a>
            <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-3 sm:p-4 animate-fadeInUp delay-1">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Statut commande</label>
                <select name="status" class="input w-full text-sm">
                    <option value="">Tous</option>
                    @foreach($statuses ?? [] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Statut paiement</label>
                <select name="payment_status" class="input w-full text-sm">
                    <option value="">Tous</option>
                    @foreach($paymentStatuses ?? [] as $status)
                        <option value="{{ $status }}" {{ request('payment_status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-full text-sm">
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-full text-sm">
            </div>
            <div class="flex items-end gap-2 col-span-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1">Filtrer</button>
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline btn-sm flex-1">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="card-stats border-l-4 border-[var(--primary-blue)] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total commandes</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $stats['total_orders'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A] animate-fadeInUp delay-3 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Chiffre d'affaires</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#065F9C] animate-fadeInUp delay-4 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Panier moyen</p>
            <p class="text-lg sm:text-xl font-bold text-[#065F9C]">${{ number_format($stats['avg_order_value'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B54708] animate-fadeInUp delay-5 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">TVA totale</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">${{ number_format($stats['total_tax'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-[var(--primary-blue)] animate-fadeInUp delay-6 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Livraison</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">${{ number_format($stats['total_shipping'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="card animate-fadeInUp delay-7 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Client</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Articles</th>
                        <th class="text-xs sm:text-sm text-right">Sous-total</th>
                        <th class="text-xs sm:text-sm text-right hidden lg:table-cell">TVA</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right hidden xl:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                        <tr class="sales-row">
                            <td class="font-mono text-xs sm:text-sm">#{{ $order->order_number }}</td>
                            <td class="hidden sm:table-cell text-sm">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="hidden md:table-cell text-sm">{{ $order->items->count() }}</td>
                            <td class="text-right text-sm">${{ number_format($order->subtotal, 2) }}</td>
                            <td class="text-right hidden lg:table-cell text-sm">${{ number_format($order->tax, 2) }}</td>
                            <td class="text-right font-bold text-[var(--primary-blue)] text-sm">${{ number_format($order->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $order->status == 'completed' ? 'badge-success' : ($order->status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-right text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Aucune commande trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($orders) && $orders->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection