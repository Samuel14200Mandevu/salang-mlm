{{-- resources/views/admin/wallets/show.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-primary { background: rgba(90, 182, 56, 0.12); color: var(--primary-500); }
    .stat-icon-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .stat-icon-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .stat-icon-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }
    
    .amount-positive { color: #22c55e; }
    .amount-negative { color: #ef4444; }
    
    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem; }
        .stat-card .text-2xl { font-size: 1.25rem; }
        .card { padding: 0.875rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Portefeuille #{{ $wallet->id }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $wallet->user->name ?? 'Utilisateur' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <a href="{{ route('admin.wallets') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.wallets.adjust', $wallet->id) }}" 
               class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajuster
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card border-l-4 border-primary-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Solde</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">
                        ${{ number_format($wallet->balance, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-green-500 animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total crédits</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">
                        ${{ number_format($stats['total_credited'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-red-500 animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total débits</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">
                        ${{ number_format($stats['total_debited'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-4 border-blue-500 animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">
                        {{ $stats['transaction_count'] ?? 0 }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du portefeuille -->
    <div class="card animate-fadeInUp delay-5">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
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
        <div class="flex items-center justify-between mb-3 sm:mb-4">
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
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
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