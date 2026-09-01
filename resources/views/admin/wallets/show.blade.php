{{-- resources/views/admin/wallets/show.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.stat-card .stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-icon-primary { background: var(--primary-blue-bg); color: var(--primary-blue); }
.stat-icon-success { background: rgba(28, 126, 74, 0.08); color: #1C7E4A; }
.stat-icon-info { background: rgba(6, 95, 156, 0.08); color: #065F9C; }
.stat-icon-warning { background: rgba(181, 71, 8, 0.08); color: #B54708; }

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

.amount-positive { color: #1C7E4A; }
.amount-negative { color: #B91C1C; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }
.delay-6 { animation-delay: 0.3s; }

@media (max-width: 640px) {
    .stat-card { padding: 0.625rem; }
    .stat-card .text-2xl { font-size: 1.25rem; }
    .card { padding: 0.875rem; }
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
    .stats-grid { grid-template-columns: 1fr 1fr !important; }
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
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Portefeuille #{{ $wallet->id }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $wallet->user->name ?? 'Utilisateur' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <a href="{{ route('admin.wallets') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.wallets.adjust', $wallet->id) }}"
               class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajuster
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card border-l-4 border-[var(--primary-blue)]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Solde</p>
                    <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">
                        ${{ number_format($wallet->balance, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-[#1C7E4A] animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total crédits</p>
                    <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">
                        ${{ number_format($stats['total_credited'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-[#B91C1C] animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total débits</p>
                    <p class="text-lg sm:text-xl font-bold text-[#B91C1C]">
                        ${{ number_format($stats['total_debited'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-[#065F9C] animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</p>
                    <p class="text-lg sm:text-xl font-bold text-[#065F9C]">
                        {{ $stats['transaction_count'] ?? 0 }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Information -->
    <div class="card animate-fadeInUp delay-5">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            Informations du portefeuille
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Utilisateur</p>
                <p class="font-semibold text-[var(--text-primary)]">{{ $wallet->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Email</p>
                <p class="font-semibold text-[var(--text-primary)]">{{ $wallet->user->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Statut</p>
                <p>
                    <span class="badge {{ $wallet->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ ucfirst($wallet->status ?? 'Actif') }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Créé le</p>
                <p class="font-semibold text-[var(--text-primary)]">{{ $wallet->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Dernière mise à jour</p>
                <p class="font-semibold text-[var(--text-primary)]">{{ $wallet->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="card animate-fadeInUp delay-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                Historique des transactions
            </h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">
                {{ $transactions->count() ?? 0 }} transactions
            </span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm">Type</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr>
                            <td class="text-xs sm:text-sm text-[var(--text-secondary)]">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $transaction->type == 'credit' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($transaction->type ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-right font-bold {{ $transaction->type == 'credit' ? 'amount-positive' : 'amount-negative' }}">
                                {{ $transaction->type == 'credit' ? '+' : '-' }}
                                ${{ number_format(abs($transaction->amount ?? 0), 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $transaction->status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst($transaction->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-xs sm:text-sm text-[var(--text-secondary)] hidden lg:table-cell">
                                {{ Str::limit($transaction->description ?? '-', 30) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune transaction</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Ce portefeuille n'a pas encore de transactions</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
    
</div>
@endsection