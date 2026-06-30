@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">💰 Commissions</h1>
        <p class="text-[var(--text-secondary)] mt-1">Suivez toutes les commissions générées</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-green-500">
            <p class="text-sm text-[var(--text-secondary)]">Total payé</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">${{ number_format($pendingCommissions ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>De</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                        <tr>
                            <td class="font-medium">{{ $commission->user?->name ?? 'N/A' }}</td>
                            <td class="text-[var(--text-secondary)]">{{ $commission->fromUser?->name ?? 'Système' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $commission->type_label ?? $commission->type }}</span>
                            </td>
                            <td class="font-bold text-green-500">+${{ number_format($commission->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $commission->status }}
                                </span>
                            </td>
                            <td class="text-sm text-[var(--text-secondary)]">{{ $commission->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aucune commission
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div class="mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection