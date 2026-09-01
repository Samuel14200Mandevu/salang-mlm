{{-- resources/views/admin/pos-reports/sales.blade.php --}}
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

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-card .number {
    font-size: 1.25rem;
    font-weight: 700;
}
.stat-card .label {
    font-size: 0.625rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.stat-card .icon {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.icon-blue { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-green { background: rgba(28, 126, 74, 0.08); color: #1C7E4A; }
.icon-purple { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-orange { background: rgba(181, 71, 8, 0.08); color: #B54708; }

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }

.filter-section {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.filter-section select,
.filter-section input {
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.813rem;
    flex: 1;
    min-width: 120px;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.filter-section select:focus,
.filter-section input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.filter-section .btn-filter {
    padding: 0.375rem 1.25rem;
    background: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    transition: background 0.15s ease;
}
.filter-section .btn-filter:hover {
    background: var(--primary-blue-dark);
}
.filter-section .btn-reset {
    padding: 0.375rem 1.25rem;
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.filter-section .btn-reset:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.813rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
    vertical-align: middle;
}
.table tbody tr:hover td {
    background: var(--bg-hover);
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

@media (max-width: 640px) {
    .stat-card .number { font-size: 1.125rem; }
    .filter-section { flex-direction: column; }
    .filter-section select, .filter-section input, .filter-section button { width: 100%; }
    .table thead th, .table tbody td { padding: 0.3rem 0.4rem; font-size: 0.7rem; }
    .card { padding: 0.875rem; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Ventes POS</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Liste détaillée des ventes au guichet
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.pos-reports.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.pos-reports.export') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commandes</p>
                    <p class="number text-[var(--primary-blue)]">{{ number_format($salesStats['total_orders'] ?? 0) }}</p>
                </div>
                <div class="icon icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Total ventes</p>
                    <p class="number text-[#1C7E4A]">${{ number_format($salesStats['total_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">PV distribués</p>
                    <p class="number text-[#B54708]">{{ number_format($salesStats['total_pv'] ?? 0) }}</p>
                </div>
                <div class="icon icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Moyenne/commande</p>
                    <p class="number text-[var(--primary-blue)]">${{ number_format($salesStats['average_order'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4">
        <form method="GET" action="{{ route('admin.pos-reports.sales') }}" class="filter-section">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="flex-1">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début" class="flex-1">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin" class="flex-1">
            <select name="cashier_id" class="flex-1">
                <option value="">Tous les caissiers</option>
                @foreach($cashiers ?? [] as $cashier)
                    <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                        {{ $cashier->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-filter">Filtrer</button>
            <a href="{{ route('admin.pos-reports.sales') }}" class="btn-reset">Réinitialiser</a>
        </form>
    </div>

    <!-- Sales List -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Caissier</th>
                        <th class="text-right">Sous-total</th>
                        <th class="text-right">TVA</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">PV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                        @php
                            $totalPV = $order->items->sum(function($item) {
                                return ($item->pv_value ?? 0) * $item->quantity;
                            });
                        @endphp
                        <tr>
                            <td class="text-xs text-[var(--text-secondary)] whitespace-nowrap">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-mono text-xs text-[var(--primary-blue)]">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <div class="font-medium text-sm">{{ $order->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $order->user?->phone ?? '' }}</div>
                            </td>
                            <td class="text-sm">{{ $order->cashier?->name ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                            <td class="text-right">${{ number_format($order->tax, 2) }}</td>
                            <td class="text-right font-bold text-[var(--primary-blue)]">${{ number_format($order->total, 2) }}</td>
                            <td class="text-right text-[#1C7E4A] font-medium">{{ number_format($totalPV) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-base font-medium">Aucune vente POS</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucune vente trouvée pour cette période</p>
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