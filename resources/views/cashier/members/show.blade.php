@extends('cashier.layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .detail-card:hover {
        border-color: var(--primary-500);
    }
    .detail-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .detail-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }
    .avatar-lg {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .rank-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
        background: rgba(90, 182, 56, 0.15);
        color: var(--primary-500);
        border: 1px solid rgba(90, 182, 56, 0.2);
    }
    .commission-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }
    .commission-summary .item {
        text-align: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
    }
    .commission-summary .item .number {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-500);
    }
    .commission-summary .item .label {
        font-size: 0.65rem;
        color: var(--text-secondary);
        text-transform: uppercase;
    }
    
    .badge-status {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .badge-status-paid { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-status-cancelled { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .type-badge-pos { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-purchase { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .type-badge-new_client { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-pos_transaction { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-sponsor { background: rgba(34,197,94,0.15); color: #22c55e; }
    
    .commission-row {
        transition: all 0.3s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
        transform: translateX(2px);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    
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
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
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
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
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
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    
    /* Filtres */
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
    
    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .commission-summary { grid-template-columns: 1fr 1fr; }
        .commission-summary .item .number { font-size: 1rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .type-badge { font-size: 0.6rem; padding: 0.1rem 0.4rem; }
        .badge-status { font-size: 0.5rem; padding: 0.075rem 0.375rem; }
        .table-wrap {
            margin: 0 -0.875rem;
            padding: 0 0.875rem;
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
    }
</style>
@endpush

@section('title', 'Détails du membre')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Détails du membre
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                ID: #{{ $member->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('cashier.members.orders', $member->id) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

   <!-- Informations du membre -->
<div class="detail-card animate-fadeInUp delay-1">
    <div class="flex flex-wrap items-start gap-4">
        <div class="avatar-lg">
            {{ strtoupper(substr($member->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-[var(--text-primary)]">{{ $member->name }}</h2>
            <div class="flex flex-wrap gap-2 mt-1">
                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                    {{ $member->is_active ? 'Actif' : 'Inactif' }}
                </span>
                @php
                    $roleName = $member->getRoleNames()->first() ?? 'user';
                @endphp
                @if($roleName != 'user')
                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-3">
                <!-- Colonne 1: Informations personnelles -->
                <div>
                    <div class="mb-2">
                        <p class="text-xs text-[var(--text-secondary)]">Email</p>
                        <p class="text-sm">{{ $member->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Téléphone</p>
                        <p class="text-sm">{{ $member->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Colonne 2: Informations du compte -->
                <div>
                    <div class="mb-2">
                        <p class="text-xs text-[var(--text-secondary)]">Code</p>
                        <p class="text-sm font-mono text-primary-500">{{ $member->sponsor_id ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Inscrit le</p>
                        <p class="text-sm">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                
                <!-- ✅ Colonne 3: Niveau, Pourcentage et Parrain -->
                <div>
                    <div class="mb-2">
                        <p class="text-xs text-[var(--text-secondary)]">Grade & Niveau</p>
                        <p class="text-sm font-bold text-purple-500">
                            @php
                                $level = $member->rank_level ?? 0;
                                $levelNames = [
                                    0 => 'Membre',
                                    1 => 'Distributeur',
                                    2 => 'Qualification',
                                    3 => 'Cumul Directeur',
                                    4 => 'Directeur',
                                    5 => 'Manager Senior',
                                    6 => 'Directeur Envolée',
                                    7 => 'Saphire Manager',
                                    8 => 'Diamant Bleu',
                                    9 => 'Perle Diamant',
                                ];
                                echo $levelNames[$level] ?? 'Niveau ' . $level;
                            @endphp
                            <span class="text-xs text-[var(--text-secondary)] font-normal">(Niv. {{ $level }})</span>
                        </p>
                    </div>
                    <div class="mb-2">
                        <p class="text-xs text-[var(--text-secondary)]">Taux de commission</p>
                        <p class="text-sm font-bold text-green-500">
                            @php
                                // Taux selon le niveau (d'après CommissionDistributor)
                                $commissionRates = [
                                    0 => 0,
                                    1 => 0,
                                    2 => 0,
                                    3 => 22,
                                    4 => 26,
                                    5 => 30,
                                    6 => 34,
                                    7 => 40,
                                    8 => 43,
                                    9 => 45,
                                ];
                                $rate = $commissionRates[$level] ?? 0;
                            @endphp
                            @if($rate > 0)
                                {{ $rate }}%
                            @else
                                <span class="text-[var(--text-tertiary)] font-normal">Aucun</span>
                            @endif
                            <span class="text-xs text-[var(--text-secondary)] font-normal">(sur les PV)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Parrain</p>
                        <p class="text-sm font-medium text-primary-500">
                            @if($member->parrain)
                                {{ $member->parrain->name }}
                                <span class="text-xs text-[var(--text-secondary)] font-normal">
                                    (Code: {{ $member->parrain->sponsor_id ?? 'N/A' }})
                                </span>
                            @else
                                <span class="text-[var(--text-tertiary)]">Aucun parrain</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Résumé des commissions -->
    <div class="commission-summary animate-fadeInUp delay-2">
        <div class="item">
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total commissions</div>
        </div>
        <div class="item">
            <div class="number" style="color: #22c55e;">${{ number_format($stats['paid_commissions'] ?? 0, 2) }}</div>
            <div class="label">Payées</div>
        </div>
        <div class="item">
            <div class="number" style="color: #f59e0b;">${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="item">
            <div class="number" style="color: #6b7280;">{{ $stats['cancelled_commissions'] ?? 0 }}</div>
            <div class="label">Annulées</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card animate-fadeInUp delay-2">
        <form method="GET" action="{{ route('cashier.members.show', $member->id) }}" class="filter-section">
            <select name="type">
                <option value="">Tous les types</option>
                <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct Bonus</option>
                <option value="indirect" {{ request('type') == 'indirect' ? 'selected' : '' }}>Indirect Bonus</option>
                <option value="leadership" {{ request('type') == 'leadership' ? 'selected' : '' }}>Leadership Bonus</option>
                <option value="sponsor" {{ request('type') == 'sponsor' ? 'selected' : '' }}>Sponsor Bonus</option>
                <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                <option value="pos_transaction" {{ request('type') == 'pos_transaction' ? 'selected' : '' }}>POS Transaction</option>
            </select>
            <select name="status">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvée</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetée</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>
            <select name="source">
                <option value="">Toutes les sources</option>
                <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS</option>
                <option value="mlm" {{ request('source') == 'mlm' ? 'selected' : '' }}>MLM</option>
            </select>
            <button type="submit" class="btn-filter">Filtrer</button>
            <a href="{{ route('cashier.members.show', $member->id) }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    <!-- Liste des commissions -->
    <div class="detail-card animate-fadeInUp delay-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                Commissions
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $commissions->total() ?? 0 }})</span>
            </h3>
            <div class="flex flex-wrap gap-2 text-[10px] sm:text-xs">
                <span class="badge-status badge-status-pending">En attente</span>
                <span class="badge-status badge-status-approved">Approuvée</span>
                <span class="badge-status badge-status-paid">Payée</span>
                <span class="badge-status badge-status-rejected">Rejetée</span>
                <span class="badge-status badge-status-cancelled">Annulée</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm">De</th>
                        <th class="text-xs sm:text-sm">Type</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-row">
                            <td class="font-mono text-xs">#{{ $commission->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $commission->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->user?->sponsor_id ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $commission->fromUser?->name ?? 'Système' }}</div>
                                @if($commission->fromUser)
                                    <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->fromUser->sponsor_id ?? 'N/A' }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    {{ $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type)) }}
                                </span>
                                @if($commission->source == 'pos')
                                    <span class="badge badge-success text-[8px]">POS</span>
                                @else
                                    <span class="badge badge-info text-[8px]">MLM</span>
                                @endif
                            </td>
                            <td class="text-right font-bold {{ $commission->amount > 0 ? 'text-green-500' : 'text-red-500' }} text-sm">
                                {{ $commission->amount > 0 ? '+' : '' }}${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>
                                @if($commission->status == 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($commission->status == 'approved')
                                    <span class="badge badge-info">Approuvée</span>
                                @elseif($commission->status == 'paid')
                                    <span class="badge badge-success">Payée</span>
                                @elseif($commission->status == 'rejected')
                                    <span class="badge badge-danger">Rejetée</span>
                                @elseif($commission->status == 'cancelled')
                                    <span class="badge badge-secondary">Annulée</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($commission->status) }}</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('cashier.commissions.show', $commission->id) }}" 
                                       class="btn btn-primary btn-sm" title="Détails">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de commissions</p>
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