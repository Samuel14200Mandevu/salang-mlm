{{-- resources/views/admin/pos-reports/sales.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .stat-card .number {
        font-size: 1.25rem;
        font-weight: 700;
    }
    .stat-card .label {
        font-size: 0.65rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .stat-card .icon {
        width: 2rem;
        height: 2rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .icon-green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .icon-orange { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    
    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .filter-section select,
    .filter-section input {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }
    .filter-section select:focus,
    .filter-section input:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .filter-section .btn-filter {
        padding: 0.375rem 1.5rem;
        background: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-600);
        transform: translateY(-1px);
    }
    .filter-section .btn-reset {
        padding: 0.375rem 1.5rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-hover);
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.813rem;
    }
    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
    
    .badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.375rem 1rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(90, 182, 56, 0.3);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    @media (max-width: 640px) {
        .stat-card .number { font-size: 1rem; }
        .filter-section { flex-direction: column; }
        .filter-section select, .filter-section input, .filter-section button { width: 100%; }
        .table thead th, .table tbody td { padding: 0.3rem 0.4rem; font-size: 0.7rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-primary-500"></span> Ventes POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Liste détaillée des ventes au guichet
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pos-reports.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.pos-reports.export') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commandes</p>
                    <p class="number text-primary-500">{{ number_format($salesStats['total_orders'] ?? 0) }}</p>
                </div>
                <div class="icon icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Total ventes</p>
                    <p class="number text-green-500">${{ number_format($salesStats['total_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">PV distribués</p>
                    <p class="number text-orange-500">{{ number_format($salesStats['total_pv'] ?? 0) }}</p>
                </div>
                <div class="icon icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Moyenne/commande</p>
                    <p class="number text-purple-500">${{ number_format($salesStats['average_order'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card animate-fadeInUp delay-2">
        <form method="GET" action="{{ route('admin.pos-reports.sales') }}" class="filter-section">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Rechercher..." class="flex-1">
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
            <button type="submit" class="btn-filter">🔍 Filtrer</button>
            <a href="{{ route('admin.pos-reports.sales') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    <!-- Liste des ventes -->
    <div class="card animate-fadeInUp delay-3">
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
                            <td class="font-mono text-xs text-primary-500">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <div class="font-medium text-sm">{{ $order->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $order->user?->phone ?? '' }}</div>
                            </td>
                            <td class="text-sm">{{ $order->cashier?->name ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                            <td class="text-right">${{ number_format($order->tax, 2) }}</td>
                            <td class="text-right font-bold">${{ number_format($order->total, 2) }}</td>
                            <td class="text-right text-green-500 font-medium">{{ number_format($totalPV) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
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
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection