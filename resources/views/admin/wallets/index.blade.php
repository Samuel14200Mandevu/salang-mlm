@extends('admin.layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Portefeuilles</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gerez les portefeuilles des utilisateurs</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total des portefeuilles</p>
            <p class="text-lg sm:text-2xl font-bold text-primary-500">{{ $totalWallets ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-green-500 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Solde total</p>
            <p class="text-lg sm:text-2xl font-bold text-green-500">${{ number_format($totalBalance ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm">Solde</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">En attente</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Retire</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Devise</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                        <tr>
                            <td class="font-medium text-sm sm:text-base">{{ $wallet->user?->name ?? 'N/A' }}</td>
                            <td class="font-bold text-green-500 text-sm sm:text-base">${{ number_format($wallet->balance, 2) }}</td>
                            <td class="hidden sm:table-cell text-yellow-500 text-sm sm:text-base">${{ number_format($wallet->pending_balance, 2) }}</td>
                            <td class="hidden md:table-cell text-blue-500 text-sm sm:text-base">${{ number_format($wallet->total_withdrawn, 2) }}</td>
                            <td class="hidden lg:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $wallet->currency ?? 'USD' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Aucun portefeuille
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wallets->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $wallets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection