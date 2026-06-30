@extends('layouts.app')

@push('styles')
<style>
    .transaction-item:hover { transform: translateX(4px); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">👛 Mon Portefeuille</h1>
        <p class="text-[var(--text-secondary)] mt-1">Gérez vos fonds et vos transactions</p>
    </div>

    <!-- Solde -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Solde disponible</p>
            <p class="text-3xl font-bold text-primary-500">${{ number_format($balance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-3xl font-bold text-yellow-500">${{ number_format($pendingBalance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-blue-500">
            <p class="text-sm text-[var(--text-secondary)]">Total retiré</p>
            <p class="text-3xl font-bold text-blue-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Historique -->
    <div class="card animate-fadeInUp delay-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📊 Historique des transactions</h3>
            <span class="badge badge-neutral text-xs">{{ $transactions->count() ?? 0 }} transactions</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-right">Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="transition-colors">
                            <td class="text-[var(--text-secondary)] text-sm">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $transaction->type == 'commission' ? 'badge-success' : 'badge-info' }}">
                                    {{ $transaction->type_label ?? $transaction->type }}
                                </span>
                            </td>
                            <td>{{ $transaction->description ?? '-' }}</td>
                            <td class="text-right font-bold {{ $transaction->amount > 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $transaction->status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $transaction->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-5">
        <a href="{{ route('withdrawal.index') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Demander un retrait
        </a>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline">
            📦 Acheter un abonnement
        </a>
    </div>
</div>
@endsection