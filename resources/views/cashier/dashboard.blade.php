{{-- resources/views/cashier/dashboard.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-300);
    }
    .stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .stat-icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .stat-icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .stat-icon-orange { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .stat-icon-red { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
    .quick-action {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: block;
    }
    .quick-action:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    .quick-action .icon {
        width: 1.5rem;
        height: 1.5rem;
        margin: 0 auto 0.25rem;
        display: block;
        color: var(--primary-500);
    }
    .quick-action .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .quick-action .sub {
        font-size: 0.65rem;
        color: var(--text-secondary);
        margin-top: 0.125rem;
    }
    .alert-warning {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius-lg);
        padding: 0.75rem 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }
    .alert-warning .alert-icon {
        color: #f59e0b;
        flex-shrink: 0;
    }
    .alert-warning .alert-title {
        font-weight: 600;
        color: #f59e0b;
    }
    .alert-warning .alert-text {
        color: rgba(245, 158, 11, 0.8);
        font-size: 0.875rem;
        flex: 1;
    }
    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem; }
        .stat-card .stat-value { font-size: 1.25rem; }
        .quick-action { padding: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Dashboard')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- Compte inactif --}}
    @if(isset($user) && !$user->is_active)
    <div class="alert-warning animate-fadeInUp delay-1">
        <svg class="alert-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span class="alert-title">Compte inactif</span>
        <span class="alert-text">Activez votre compte pour recevoir des commissions</span>
        <a href="{{ route('activate.index') }}" class="btn btn-primary btn-sm flex-shrink-0">
            Activer maintenant
        </a>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-7 h-7 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard Caissier
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Bienvenue, {{ Auth::user()->name ?? 'Caissier' }} ! Gérez vos ventes au guichet.
            </p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle vente
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commandes aujourd'hui</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $stats['total_orders_today'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Ventes aujourd'hui</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['total_sales_today'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Clients aujourd'hui</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-orange-500">{{ $stats['customers_today'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ $stats['pending_orders'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        <a href="{{ route('cashier.pos') }}" class="quick-action">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="label">Point de Vente</span>
            <p class="sub">Lancer une vente au guichet</p>
        </a>

        <a href="{{ route('cashier.orders') }}" class="quick-action">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="label">Commandes</span>
            <p class="sub">Voir toutes les commandes</p>
        </a>

        <a href="{{ route('cashier.daily-sales') }}" class="quick-action">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            <span class="label">Ventes du jour</span>
            <p class="sub">Rapport des ventes</p>
        </a>
    </div>

    {{-- Commandes récentes --}}
    <div class="card animate-fadeInUp delay-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commandes récentes</h3>
            <a href="{{ route('cashier.orders') }}" class="text-sm text-primary-500 hover:underline">Voir tout</a>
        </div>
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm">Client</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                        <tr>
                            <td class="font-mono text-xs sm:text-sm text-primary-500">#{{ $order->order_number }}</td>
                            <td class="text-sm sm:text-base">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="text-right font-bold text-sm sm:text-base">${{ number_format($order->total, 2) }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'completed' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        'processing' => 'badge-info',
                                    ];
                                    $statusLabels = [
                                        'completed' => 'Terminée',
                                        'pending' => 'En attente',
                                        'cancelled' => 'Annulée',
                                        'processing' => 'En traitement',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$order->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('cashier.orders.show', $order->id) }}" class="btn btn-primary btn-sm">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-[var(--text-secondary)] text-sm">
                                Aucune commande récente
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection