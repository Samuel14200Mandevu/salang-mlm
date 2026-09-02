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

    /* ===== CARTES ===== */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        transition: border-color 0.15s ease;
    }

    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.875rem 1rem;
        transition: border-color 0.15s ease;
    }

    /* ===== TABLE ===== */
    .member-row {
        transition: background 0.1s ease;
        cursor: pointer;
    }
    .member-row:hover {
        background: var(--bg-hover);
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

    /* ===== BADGES ===== */
    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.625rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .badge-success {
        background: #E6F4EC;
        color: #1F7B4D;
        border-color: #B8DFCC;
    }
    .badge-danger {
        background: #FDE8E8;
        color: #B32A2A;
        border-color: #F5C8C8;
    }
    .badge-warning {
        background: #FEF1E6;
        color: #A65A0E;
        border-color: #FADCB8;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
        border-color: var(--border-color);
    }
    .badge-admin {
        background: #E8EDF5;
        color: var(--primary-navy);
        border-color: #C8D4E3;
    }
    .badge-cashier {
        background: #E8EDF5;
        color: var(--primary-navy);
        border-color: #C8D4E3;
    }
    .badge-cashier-principal {
        background: #D4DFED;
        color: var(--primary-navy);
        border-color: #B0C5DB;
    }
    .badge-user {
        background: var(--bg-secondary);
        color: var(--text-secondary);
        border-color: var(--border-color);
    }
    .badge-sponsor {
        background: #E8EDF5;
        color: var(--primary-navy);
        border-color: #C8D4E3;
        font-family: monospace;
        font-size: 0.7rem;
    }
    .badge-commission {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.625rem;
        font-weight: 600;
        border: 1px solid transparent;
        background: #E6F4EC;
        color: #1F7B4D;
        border-color: #B8DFCC;
    }

    .avatar-sm {
        width: 2rem;
        height: 2rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        background: var(--primary-navy);
        color: white;
        flex-shrink: 0;
    }

    /* ===== BOUTONS ===== */
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
    .btn-md { padding: 0.5rem 1.25rem; font-size: 0.813rem; }
    .btn-xs { padding: 0.15rem 0.5rem; font-size: 0.65rem; }

    .btn-primary {
        background: var(--primary-navy);
        color: white;
        border-color: var(--primary-navy);
    }
    .btn-primary:hover {
        background: var(--primary-navy-dark);
        border-color: var(--primary-navy-dark);
    }

    .btn-success {
        background: #1F7B4D;
        color: white;
        border-color: #1F7B4D;
    }
    .btn-success:hover {
        background: #16633D;
        border-color: #16633D;
    }

    .btn-danger {
        background: #B32A2A;
        color: white;
        border-color: #B32A2A;
    }
    .btn-danger:hover {
        background: #8F2121;
        border-color: #8F2121;
    }

    .btn-warning {
        background: #A65A0E;
        color: white;
        border-color: #A65A0E;
    }
    .btn-warning:hover {
        background: #87480B;
        border-color: #87480B;
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

    .btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .btn-secondary:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }

    .btn-icon {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
        border-radius: 6px;
    }

    /* ===== RECHERCHE ===== */
    .header-search .search-wrapper {
        position: relative;
    }

    .header-search .search-wrapper .search-input {
        padding: 0.375rem 0.75rem 0.375rem 2.25rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        width: 220px;
        transition: border-color 0.15s ease, width 0.2s ease;
        outline: none;
    }

    .header-search .search-wrapper .search-input:focus {
        border-color: var(--primary-navy);
        width: 280px;
    }

    .header-search .search-wrapper .search-input::placeholder {
        color: var(--text-tertiary);
    }

    /* ===== FILTRES ===== */
    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-section select,
    .filter-section input[type="text"]:not(.search-input),
    .filter-section input[type="date"] {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
        transition: border-color 0.15s ease;
        outline: none;
    }

    .filter-section select:focus,
    .filter-section input[type="text"]:not(.search-input):focus,
    .filter-section input[type="date"]:focus {
        border-color: var(--primary-navy);
    }

    .filter-section .btn-filter {
        padding: 0.375rem 1.5rem;
        background: var(--primary-navy);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-navy-dark);
    }

    .filter-section .btn-reset {
        padding: 0.375rem 1.5rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-hover);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 640px) {
        .header-search .search-wrapper .search-input {
            width: 100%;
        }
        .header-search .search-wrapper .search-input:focus {
            width: 100%;
        }
        .filter-section {
            flex-direction: column;
        }
        .filter-section select,
        .filter-section input[type="text"]:not(.search-input),
        .filter-section input[type="date"],
        .filter-section button {
            width: 100%;
            min-width: unset;
        }
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-xs {
            padding: 0.15rem 0.375rem;
            font-size: 0.6rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .card-stats {
            padding: 0.625rem;
        }
        .avatar-sm {
            width: 1.5rem;
            height: 1.5rem;
            font-size: 0.6rem;
        }
        .card {
            padding: 0.875rem;
        }
        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
        .btn-icon {
            padding: 0.15rem 0.375rem;
        }
    }

    @media (max-width: 480px) {
        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('title', 'Membres')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Gestion des membres
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $members->total() }} membres enregistrés
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <a href="{{ route('cashier.members.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden xs:inline">Nouveau membre</span>
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    @php
        $totalMembers = $members->total() ?? 0;
        $activeMembers = isset($stats['active']) ? $stats['active'] : 0;
        $inactiveMembers = isset($stats['inactive']) ? $stats['inactive'] : 0;
        $withCommissions = isset($stats['with_commissions']) ? $stats['with_commissions'] : 0;
        $totalOrders = isset($stats['orders']) ? $stats['orders'] : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">{{ $totalMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#1F7B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#1F7B4D]">{{ $activeMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#B32A2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#B32A2A]">{{ $inactiveMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#A65A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commissions</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#A65A0E]">{{ $withCommissions }}</p>
        </div>
    </div>

    {{-- LISTE --}}
    <div class="card p-3 sm:p-4">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="membersTable">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th class="hidden sm:table-cell">Code sponsor</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="hidden md:table-cell">Total CASH</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($members ?? [] as $member)
                        <tr class="member-row">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar-sm">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ $member->name }}</div>
                                        <div class="text-xs text-[var(--text-secondary)]">{{ $member->email }}</div>
                                        @if($member->phone)
                                            <div class="text-xs text-[var(--text-secondary)]">{{ $member->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell font-mono text-xs text-[var(--primary-navy)]">
                                {{ $member->sponsor_id ?? 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $roleName = $member->getRoleNames()->first() ?? 'user';
                                    $badgeClass = 'badge-neutral';
                                    if($roleName === 'admin') {
                                        $badgeClass = 'badge-admin';
                                    } elseif($roleName === 'cashier') {
                                        $badgeClass = 'badge-cashier';
                                    } elseif($roleName === 'caissier_principal') {
                                        $badgeClass = 'badge-cashier-principal';
                                    } elseif($roleName === 'user') {
                                        $badgeClass = 'badge-user';
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
                                    $totalCommissions = \App\Models\Commission::where('user_id', $member->id)
                                        ->where('source', 'pos')
                                        ->where('status', 'paid')
                                        ->sum('amount');
                                @endphp
                                @if($totalCommissions > 0)
                                    <span class="badge badge-commission">${{ number_format($totalCommissions, 2) }}</span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">Aucune</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('cashier.members.show', $member->id) }}"
                                       class="btn btn-primary btn-xs btn-icon" title="Voir">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('cashier.members.commissions', $member->id) }}"
                                       class="btn btn-success btn-xs btn-icon" title="Commissions POS">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('cashier.members.orders', $member->id) }}"
                                       class="btn btn-warning btn-xs btn-icon" title="Commandes">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun membre</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Aucun membre trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $members->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            searchTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                if (query) {
                    url.searchParams.set('search', query);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }, 500);
        });
    }
});
</script>
@endpush
@endsection