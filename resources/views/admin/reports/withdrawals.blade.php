@extends('admin.layouts.app')

@push('styles')
<style>
    .withdrawal-row:hover { background: var(--bg-hover); }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .stats-grid { grid-template-columns: 1fr 1fr !important; }
        .filter-grid { grid-template-columns: 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Rapport des retraits</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Analyse detaillee des retraits</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.pdf', ['type' => 'withdrawals']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'withdrawals']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                CSV
            </a>
            <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4">
        <form method="GET" class="filter-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Statut</label>
                <select name="status" class="input w-full text-sm">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Payé</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Méthode</label>
                <select name="method" class="input w-full text-sm">
                    <option value="">Toutes</option>
                    <option value="crypto" {{ request('method') == 'crypto' ? 'selected' : '' }}>Crypto</option>
                    <option value="mobile_money" {{ request('method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="bank" {{ request('method') == 'bank' ? 'selected' : '' }}>Banque</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-full text-sm">
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-full text-sm">
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Montant min</label>
                <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="input w-full text-sm" placeholder="0">
            </div>
            <div>
                <label class="text-xs text-[var(--text-secondary)]">Montant max</label>
                <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="input w-full text-sm" placeholder="10000">
            </div>
            <div class="flex items-end gap-2 col-span-2">
                <button type="submit" class="btn btn-primary btn-sm w-full">Filtrer</button>
                <a href="{{ route('admin.reports.withdrawals') }}" class="btn btn-outline btn-sm w-full">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payés</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['completed'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Échoués</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">${{ number_format($stats['failed'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-5">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm">Montant</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Méthode</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals ?? [] as $withdrawal)
                        <tr class="withdrawal-row">
                            <td class="font-mono text-xs sm:text-sm">#{{ $withdrawal->id }}</td>
                            <td class="font-medium text-sm sm:text-base">{{ $withdrawal->user?->name ?? 'N/A' }}</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">{{ $withdrawal->user?->email ?? 'N/A' }}</td>
                            <td class="font-bold text-sm sm:text-base">${{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst($withdrawal->method) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aucun retrait
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($withdrawals) && $withdrawals->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection