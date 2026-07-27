{{-- resources/views/cashier/daily-sales.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Ventes du jour')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-purple-500"></span> Ventes du jour
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle vente
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commandes</p>
            <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $stats['total_orders'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Moyenne</p>
            <p class="text-xl sm:text-2xl font-bold text-purple-500">${{ number_format($stats['average_order'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Méthodes</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ count($stats['payment_methods'] ?? []) }}</p>
        </div>
    </div>

    <!-- Détail par méthode de paiement -->
    @if(isset($stats['payment_methods']) && count($stats['payment_methods']) > 0)
    <div class="card animate-fadeInUp delay-2">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Par méthode de paiement</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            @foreach($stats['payment_methods'] as $method => $data)
                <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-sm text-[var(--text-secondary)]">{{ ucfirst(str_replace('_', ' ', $method)) }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $data['count'] }} commandes</p>
                    <p class="text-sm text-[var(--text-secondary)]">${{ number_format($data['total'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Liste des ventes -->
    <div class="card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Détail des ventes</h3>
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm">Client</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Paiement</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Heure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales ?? [] as $sale)
                        <tr>
                            <td class="font-mono text-xs sm:text-sm text-primary-500">#{{ $sale->order_number }}</td>
                            <td class="text-sm sm:text-base">{{ $sale->user?->name ?? 'N/A' }}</td>
                            <td class="text-right font-bold text-sm sm:text-base">${{ number_format($sale->total, 2) }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')) }}</span>
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $sale->created_at->format('H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune vente aujourd'hui</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Commencez à vendre !</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection