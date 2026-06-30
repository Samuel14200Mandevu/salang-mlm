@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">👛 Portefeuilles</h1>
        <p class="text-[var(--text-secondary)] mt-1">Gérez les portefeuilles des utilisateurs</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total des portefeuilles</p>
            <p class="text-2xl font-bold text-primary-500">{{ $totalWallets ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-green-500">
            <p class="text-sm text-[var(--text-secondary)]">Solde total</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($totalBalance ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Solde</th>
                        <th>En attente</th>
                        <th>Retiré</th>
                        <th>Devise</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                        <tr>
                            <td class="font-medium">{{ $wallet->user?->name ?? 'N/A' }}</td>
                            <td class="font-bold text-green-500">${{ number_format($wallet->balance, 2) }}</td>
                            <td class="text-yellow-500">${{ number_format($wallet->pending_balance, 2) }}</td>
                            <td class="text-blue-500">${{ number_format($wallet->total_withdrawn, 2) }}</td>
                            <td>
                                <span class="badge badge-neutral">{{ $wallet->currency ?? 'USD' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mt-4">
                {{ $wallets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection