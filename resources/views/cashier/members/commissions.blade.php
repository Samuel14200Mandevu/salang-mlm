@extends('cashier.layouts.app')

@push('styles')
<style>
    :root {
        --primary-navy: #0F2B4F;
        --primary-navy-dark: #091E3B;
        --primary-navy-light: #1A3F6A;
        --bg-base: #F5F6F8;
        --bg-card: #FFFFFF;
        --bg-secondary: #EEF0F3;
        --bg-hover: #E8EAEE;
        --text-primary: #1A1A1E;
        --text-secondary: #4A4A52;
        --text-tertiary: #7A7A82;
        --border-color: #DCDEE3;
        --border-light: #E8EAEE;
        --success: #1F7B4D;
        --danger: #B32A2A;
        --warning: #A65A0E;
        --info: #0A2A6C;
    }

    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.875rem 1rem;
        transition: border-color 0.15s ease;
    }

    .type-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .type-badge-pos { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }
    .type-badge-direct { background: rgba(99, 102, 241, 0.12); color: #6366f1; border-color: #C8D4E3; }
    .type-badge-indirect { background: rgba(59, 130, 246, 0.12); color: #3b82f6; border-color: #C8D4E3; }
    .type-badge-leadership { background: rgba(245, 158, 11, 0.12); color: #A65A0E; border-color: #FADCB8; }
    .type-badge-purchase { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; border-color: #C8D4E3; }
    .type-badge-new_client { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }
    .type-badge-pos_transaction { background: rgba(59, 130, 246, 0.12); color: #3b82f6; border-color: #C8D4E3; }
    .type-badge-sponsor { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }

    .badge-status {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 6px;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: 1px solid transparent;
    }
    .badge-status-paid { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }

    .commission-row {
        transition: background 0.1s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        transition: border-color 0.15s ease;
    }

    .table-wrap { overflow-x: auto; }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
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
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }

    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.625rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-success { background: #E6F4EC; color: #1F7B4D; border-color: #B8DFCC; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.813rem;
        transition: background 0.15s ease, border-color 0.15s ease;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }
    .btn-primary {
        background: var(--primary-navy);
        color: white;
        border-color: var(--primary-navy);
    }
    .btn-primary:hover {
        background: var(--primary-navy-dark);
        border-color: var(--primary-navy-dark);
    }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .card-stats { padding: 0.625rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .type-badge { font-size: 0.6rem; padding: 0.1rem 0.4rem; }
        .badge-status { font-size: 0.5rem; padding: 0.075rem 0.375rem; }
        .card-stats .text-xl { font-size: 1.1rem; }
        .table-wrap { margin: 0 -0.875rem; padding: 0 0.875rem; }
    }
</style>
@endpush

@section('title', 'Commissions de ' . $member->name)

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Commissions de {{ $member->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: #{{ $member->id }} • Code: {{ $member->sponsor_id ?? 'N/A' }}
            </p>
        </div>
        <a href="{{ route('cashier.members.show', $member->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total CASH</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#1F7B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payé</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#1F7B4D]">${{ number_format($stats['paid'] ?? 0, 2) }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#8b5cf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#8b5cf6]">{{ $commissions->total() ?? 0 }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#A65A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#A65A0E]">{{ $member->pv_balance ?? 0 }}</p>
        </div>
    </div>

    {{-- LISTE DES COMMISSIONS --}}
    <div class="card p-3 sm:p-4">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                Liste des commissions
                <span class="text-xs text-[var(--text-secondary)] font-normal ml-1">
                    ({{ $commissions->total() ?? 0 }})
                </span>
            </h3>
            <span class="badge-status badge-status-paid">Payée</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th class="hidden sm:table-cell">Source</th>
                        <th class="text-right">Montant</th>
                        <th>Statut</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-row">
                            <td class="font-mono text-xs">#{{ $commission->id }}</td>
                            <td>
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    {{ $commission->type_label ?? ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="text-right font-bold text-[#1F7B4D] text-sm">
                                ${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-success">Payée</span>
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('cashier.commissions.show', $commission->id) }}"
                                   class="btn btn-primary btn-sm" title="Détails">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de commissions POS</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($commissions) && $commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection