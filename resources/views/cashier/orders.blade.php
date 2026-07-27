{{-- resources/views/cashier/orders.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Commandes POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-primary-500"></span> Commandes POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Liste des commandes effectuées au guichet
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.commissions') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Commissions
            </a>
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total POS</p>
            <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $stats['completed'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulées</p>
            <p class="text-xl sm:text-2xl font-bold text-red-500">{{ $stats['cancelled'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm">Client</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Source</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
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
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-success">POS</span>
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
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande POS</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commandes effectuées au guichet apparaîtront ici</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Faire une vente
                                </a>
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