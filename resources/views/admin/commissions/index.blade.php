@extends('admin.layouts.app')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Commissions</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Suivez toutes les commissions generees</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-green-500 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total paye</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">${{ number_format($pendingCommissions ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">De</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Montant</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Statut</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                        <tr>
                            <td class="font-medium text-sm sm:text-base">{{ $commission->user?->name ?? 'N/A' }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">{{ $commission->fromUser?->name ?? 'Systeme' }}</td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">{{ $commission->type_label ?? $commission->type }}</span>
                            </td>
                            <td class="font-bold text-green-500 text-sm sm:text-base">+${{ number_format($commission->amount, 2) }}</td>
                            <td class="hidden lg:table-cell">
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ $commission->status }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">{{ $commission->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection