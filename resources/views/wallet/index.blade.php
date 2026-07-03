@extends('layouts.app')

@push('styles')
<style>
    .transaction-item:hover { transform: translateX(4px); }
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-3xl { font-size: 1.5rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .card { padding: 0.75rem; }
        .text-2xl { font-size: 1.25rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .btn svg { width: 0.875rem; height: 0.875rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Portefeuille</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gerez vos fonds et vos transactions</p>
    </div>

    <!-- Solde -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Solde disponible</p>
            <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format($balance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl sm:text-3xl font-bold text-yellow-500">${{ number_format($pendingBalance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-blue-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Total retire</p>
            <p class="text-2xl sm:text-3xl font-bold text-blue-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Historique -->
    <div class="card animate-fadeInUp delay-4 p-3 sm:p-4 md:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des transactions</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $transactions->count() ?? 0 }} transactions</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Description</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="transition-colors">
                            <td class="text-[var(--text-secondary)] text-[10px] sm:text-sm">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge {{ $transaction->type == 'commission' ? 'badge-success' : 'badge-info' }} text-[10px] sm:text-xs">
                                    {{ $transaction->type_label ?? $transaction->type }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell text-xs sm:text-sm">{{ $transaction->description ?? '-' }}</td>
                            <td class="text-right font-bold text-xs sm:text-sm {{ $transaction->amount > 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $transaction->status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ $transaction->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Aucune transaction
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($transactions) && $transactions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <a href="{{ route('withdrawal.index') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Demander un retrait
        </a>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
            </svg>
            Acheter un abonnement
        </a>
    </div>
</div>
@endsection