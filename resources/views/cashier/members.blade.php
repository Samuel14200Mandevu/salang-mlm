@extends('cashier.layouts.app')

@push('styles')
<style>
    .member-row {
        transition: all 0.3s ease;
    }
    .member-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    .search-box {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .search-box .input {
        flex: 1;
        min-width: 150px;
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
    .avatar-sm {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .rank-badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 600;
        background: rgba(90, 182, 56, 0.15);
        color: var(--primary-500);
        border: 1px solid rgba(90, 182, 56, 0.2);
    }
    .commission-badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 600;
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .badge-cashier {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-cashier-principal {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
        font-weight: 700;
    }
    .badge-purple {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    .btn-icon {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
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
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    .btn-info {
        background: var(--gradient-info);
        color: white;
    }
    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
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
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    
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
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
    
    @media (max-width: 640px) {
        .filter-section {
            flex-direction: column;
        }
        .filter-section select,
        .filter-section input,
        .filter-section button {
            width: 100%;
            min-width: unset;
        }
        .search-box {
            flex-direction: column;
        }
        .search-box .input {
            width: 100%;
        }
        .card { padding: 0.875rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-icon { padding: 0.125rem 0.375rem; }
    }
</style>
@endpush

@section('title', 'Membres')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Membres
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gestion des membres du réseau
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau client
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total membres</p>
            <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec commissions</p>
            <p class="text-xl sm:text-2xl font-bold text-purple-500">{{ $stats['with_commissions'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commandes</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ $stats['orders'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card animate-fadeInUp delay-2">
        <form method="GET" action="{{ route('cashier.members') }}" class="filter-section">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Rechercher (nom, email, code)" class="flex-1">
            <select name="status">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
            <select name="role">
                <option value="">Tous les rôles</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Caissier</option>
                <option value="caissier_principal" {{ request('role') == 'caissier_principal' ? 'selected' : '' }}>Caissier Principal</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
            </select>
            <button type="submit" class="btn-filter">🔍 Filtrer</button>
            <a href="{{ route('cashier.members') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Membre</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Code</th>
                        <th class="text-xs sm:text-sm">Rôle</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Total CASH</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members ?? [] as $member)
                        <tr class="member-row">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar-sm">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm sm:text-base">{{ $member->name }}</div>
                                        <div class="text-xs text-[var(--text-secondary)]">{{ $member->email }}</div>
                                        @if($member->phone)
                                            <div class="text-xs text-[var(--text-secondary)]">{{ $member->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell font-mono text-xs text-primary-500">
                                {{ $member->sponsor_id ?? 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $roleName = $member->getRoleNames()->first() ?? 'user';
                                    $badgeClass = 'badge-neutral';
                                    if($roleName === 'admin') {
                                        $badgeClass = 'badge-purple';
                                    } elseif($roleName === 'cashier') {
                                        $badgeClass = 'badge-cashier';
                                    } elseif($roleName === 'caissier_principal') {
                                        $badgeClass = 'badge-cashier-principal';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $roleName == 'user' ? 'Utilisateur' : ucfirst(str_replace('_', ' ', $roleName)) }}
                                </span>
                            </td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell">
                                @php
                                    // ✅ UNIQUEMENT LES COMMISSIONS POS PAYÉES
                                    $totalCommissions = \App\Models\Commission::where('user_id', $member->id)
                                        ->where('source', 'pos')
                                        ->where('status', 'paid')
                                        ->sum('amount');
                                @endphp
                                @if($totalCommissions > 0)
                                    <span class="commission-badge">${{ number_format($totalCommissions, 2) }}</span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">Aucune</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('cashier.members.show', $member->id) }}" 
                                       class="btn btn-primary btn-sm btn-icon" title="Voir">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('cashier.members.commissions', $member->id) }}" 
                                       class="btn btn-success btn-sm btn-icon" title="Commissions POS">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('cashier.members.orders', $member->id) }}" 
                                       class="btn btn-info btn-sm btn-icon" title="Commandes">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun membre</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Aucun membre trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($members) && $members->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection