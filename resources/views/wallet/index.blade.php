{{-- resources/views/wallet/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Portefeuille')

@section('content')
<div class="member-wallet member-wallet--fintech space-y-4 sm:space-y-6">
    {{-- Mobile : style Binance / fintech --}}
    <div class="member-fintech-shell">
        @include('wallet.partials.fintech-hero')
        @include('wallet.partials.fintech-actions')
        @include('wallet.partials.fintech-metrics')
        @include('wallet.partials.fintech-feed')
    </div>

    {{-- Desktop : vue classique --}}
    <div class="member-wallet-classic-shell space-y-4 sm:space-y-6">
        <div class="member-page-intro animate-fadeInUp">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon portefeuille</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérez vos fonds et vos transactions</p>
        </div>

        <div class="stats-grid grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
            <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Solde disponible</p>
                <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format($balance ?? 0, 2) }}</p>
            </div>
            <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">En attente</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-500">${{ number_format($pendingBalance ?? 0, 2) }}</p>
            </div>
            <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-blue-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Total retiré</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des transactions</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $transactions->total() }} transactions</span>
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
                        @forelse($transactions as $transaction)
                            <tr class="transition-colors">
                                <td class="text-[var(--text-secondary)] text-[10px] sm:text-sm">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="hidden sm:table-cell">
                                    <span class="badge {{ $transaction->type == 'commission' ? 'badge-success' : ($transaction->type == 'deposit' ? 'badge-info' : 'badge-warning') }} text-[10px] sm:text-xs">
                                        {{ $transaction->type_label }}
                                    </span>
                                </td>
                                <td class="hidden md:table-cell text-xs sm:text-sm">{{ $transaction->description ?? '-' }}</td>
                                <td class="text-right font-bold text-xs sm:text-sm {{ $transaction->amount > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                        {{ $transaction->status == 'completed' ? 'Terminé' : 'En attente' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                    Aucune transaction
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="mt-3 sm:mt-4">
                    <x-salang-pagination :paginator="$transactions" />
                </div>
            @endif
        </div>

        <div class="wallet-actions member-mobile-stack-actions flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-5">
            <a href="{{ route('wallet.deposit') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                Déposer
            </a>
            <a href="{{ route('withdrawal.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                Retirer
            </a>
            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                Acheter un abonnement
            </a>
        </div>
    </div>
</div>
@endsection
