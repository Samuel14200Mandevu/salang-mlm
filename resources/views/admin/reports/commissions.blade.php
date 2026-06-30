@extends('admin.layouts.app')

@push('styles')
<style>
    .commission-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">💰 Rapport des commissions</h1>
            <p class="text-[var(--text-secondary)] mt-1">Analyse détaillée des commissions</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
            ← Retour
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total commissions</p>
            <p class="text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Moyenne</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($stats['average'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">${{ number_format($stats['total_pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Payées</p>
            <p class="text-2xl font-bold text-blue-500">${{ number_format($stats['total_paid'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-5">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>De</th>
                        <th>Type</th>
                        <th class="text-right">Montant</th>
                        <th>%</th>
                        <th>Statut</th>
                        <th class="text-right">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-row">
                            <td class="font-medium">{{ $commission->user?->name ?? 'N/A' }}</td>
                            <td class="text-[var(--text-secondary)]">{{ $commission->fromUser?->name ?? 'Système' }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($commission->type) }}</span>
                            </td>
                            <td class="text-right font-bold text-green-500">+${{ number_format($commission->amount, 2) }}</td>
                            <td>{{ $commission->percentage }}%</td>
                            <td>
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst($commission->status) }}
                                </span>
                            </td>
                            <td class="text-right text-sm text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                Aucune commission
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($commissions) && $commissions->hasPages())
            <div class="mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection