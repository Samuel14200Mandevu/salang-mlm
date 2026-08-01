@extends('cashier.layouts.app')

@push('styles')
<style>
    .commission-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .commission-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    .commission-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }
    .commission-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-500);
    }
    .commission-stat-card .label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .commission-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .icon-orange { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .icon-gold { background: rgba(217, 119, 6, 0.12); color: #d97706; }
    
    .commission-table {
        width: 100%;
        font-size: 0.875rem;
    }
    .commission-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
    }
    .commission-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        vertical-align: middle;
    }
    .commission-table tr:hover td {
        background: var(--bg-hover);
    }
    .commission-table .badge {
        display: inline-block;
        padding: 0.125rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .filter-section select,
    .filter-section input {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }
    .filter-section select:focus,
    .filter-section input:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .filter-section .btn-filter {
        padding: 0.375rem 1.5rem;
        background: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-600);
        transform: translateY(-1px);
    }
    .filter-section .btn-reset {
        padding: 0.375rem 1.5rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-hover);
    }
    
    .from-user-info {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .from-user-info svg {
        width: 0.75rem;
        height: 0.75rem;
    }
    
    .commission-percentage {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.8rem;
    }
    
    .badge-cash {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .badge-source-pos { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-source-mlm { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
    .badge-status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .badge-status-paid { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    
    @media (max-width: 640px) {
        .commission-stats {
            grid-template-columns: 1fr 1fr;
        }
        .commission-table {
            font-size: 0.7rem;
        }
        .commission-table th,
        .commission-table td {
            padding: 0.3rem 0.4rem;
        }
        .filter-section {
            flex-direction: column;
        }
        .filter-section select,
        .filter-section input,
        .filter-section button {
            width: 100%;
            min-width: unset;
        }
        .payment-badge {
            font-size: 0.45rem;
        }
        .card {
            padding: 0.875rem;
        }
    }
</style>
@endpush

@section('title', 'Toutes les commissions')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Toutes les commissions
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Commissions POS (CASH) et MLM
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="commission-stats animate-fadeInUp delay-1">
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total</div>
        </div>
        
        <div class="commission-stat-card animate-fadeInUp delay-2">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['pos_total'] ?? 0, 2) }}</div>
            <div class="label">POS CASH</div>
        </div>
        
        <div class="commission-stat-card animate-fadeInUp delay-3">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['mlm_total'] ?? 0, 2) }}</div>
            <div class="label">MLM</div>
        </div>
        
        <div class="commission-stat-card animate-fadeInUp delay-4">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">{{ $stats['total_members'] ?? 0 }}</div>
            <div class="label">Membres</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card animate-fadeInUp delay-2">
        <form method="GET" action="{{ route('cashier.commissions') }}" class="filter-section">
            <select name="type" class="flex-1">
                <option value="">Tous les types</option>
                @foreach($types ?? [] as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>
            
            <select name="status" class="flex-1">
                <option value="">Tous les statuts</option>
                @foreach($statuses ?? [] as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            
            <select name="source" class="flex-1">
                <option value="">Toutes les sources</option>
                <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS</option>
                <option value="mlm" {{ request('source') == 'mlm' ? 'selected' : '' }}>MLM</option>
            </select>
            
            <select name="user_id" class="flex-1">
                <option value=""> Tous les membres</option>
                @foreach($members ?? [] as $member)
                    <option value="{{ $member->id }}" {{ request('user_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }} ({{ $member->sponsor_id ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début" class="flex-1">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin" class="flex-1">
            
            <button type="submit" class="btn-filter">🔍 Filtrer</button>
            <a href="{{ route('cashier.commissions') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    <!-- Tableau des commissions -->
    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Membre (bénéficiaire)</th>
                        <th>Source</th>
                        <th>Type</th>
                        <th>%</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Période</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th class="hidden lg:table-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        <tr>
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $commission->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $commission->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->user?->sponsor_id ?? 'N/A' }}</div>
                                @if($commission->fromUser)
                                    <div class="from-user-info">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        De: {{ $commission->fromUser->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($commission->source == 'pos')
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.12); color: #22c55e; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.55rem; font-weight: 600;">POS</span>
                                @else
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.55rem; font-weight: 600;">MLM</span>
                                @endif
                            </td>
                            <td>
                                @if($commission->type == 'cash_pos')
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.12); color: #22c55e;">CASH POS</span>
                                @elseif($commission->type == 'direct')
                                    <span class="badge" style="background: rgba(99,102,241,0.15); color: #6366f1;">Direct</span>
                                @elseif($commission->type == 'indirect')
                                    <span class="badge" style="background: rgba(59,130,246,0.15); color: #3b82f6;">Indirect</span>
                                @elseif($commission->type == 'leadership')
                                    <span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b;">Leadership</span>
                                @elseif($commission->type == 'sponsor')
                                    <span class="badge" style="background: rgba(34,197,94,0.15); color: #22c55e;">Sponsor</span>
                                @else
                                    <span class="badge" style="background: var(--bg-secondary); color: var(--text-secondary);">{{ ucfirst($commission->type) }}</span>
                                @endif
                            </td>
                            <td class="commission-percentage">
                                {{ $commission->percentage ?? 0 }}%
                            </td>
                            <td>
                                <span class="font-bold {{ $commission->amount > 0 ? 'text-green-500' : 'text-red-500' }} text-sm">
                                    {{ $commission->amount > 0 ? '+' : '' }}${{ number_format($commission->amount, 2) }}
                                </span>
                                @if($commission->source == 'pos')
                                    <span class="badge-cash ml-1">CASH</span>
                                @endif
                            </td>
                            <td>
                                @if($commission->status == 'pending')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">En attente</span>
                                @elseif($commission->status == 'approved')
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">Approuvée</span>
                                @elseif($commission->status == 'paid')
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">Payée</span>
                                @elseif($commission->status == 'rejected')
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">Rejetée</span>
                                @else
                                    <span class="badge" style="background: var(--bg-secondary); color: var(--text-secondary);">{{ ucfirst($commission->status) }}</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->period ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <a href="{{ route('cashier.commissions.show', $commission->id) }}" 
                                   class="btn btn-primary btn-sm" title="Détails">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-lg font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucune commission trouvée</p>
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

    <!-- Info sur les commissions -->
    <div class="card animate-fadeInUp delay-4" style="background: rgba(34, 197, 94, 0.05); border-color: rgba(34, 197, 94, 0.15);">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-semibold text-[var(--text-primary)] text-sm"> Légende des commissions</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2 text-xs sm:text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #22c55e;"></span>
                        <span class="text-[var(--text-secondary)]">POS CASH</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #6366f1;"></span>
                        <span class="text-[var(--text-secondary)]">Direct Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #3b82f6;"></span>
                        <span class="text-[var(--text-secondary)]">Indirect Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #f59e0b;"></span>
                        <span class="text-[var(--text-secondary)]">Leadership Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #22c55e;"></span>
                        <span class="text-[var(--text-secondary)]">Sponsor Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #8b5cf6;"></span>
                        <span class="text-[var(--text-secondary)]">Autres</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection