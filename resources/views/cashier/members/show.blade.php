@extends('cashier.layouts.app')

@push('styles')
<style>
    :root {
        --primary-navy: #0F2B4F;
        --primary-navy-dark: #091E3B;
        --primary-navy-light: #1A3F6A;
        --bg-base: #F5F6F8;
        --bg-card: #FCFCFD;
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

    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
    }

    .detail-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
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
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        background: var(--primary-navy);
        color: white;
        flex-shrink: 0;
    }

    .rank-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 600;
        background: rgba(15, 43, 79, 0.08);
        color: var(--primary-navy);
        border: 1px solid rgba(15, 43, 79, 0.10);
    }

    .commission-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }
    .commission-summary .item {
        text-align: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 6px;
    }
    .commission-summary .item .number {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-navy);
    }
    .commission-summary .item .label {
        font-size: 0.65rem;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .badge-status {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: 1px solid transparent;
    }
    .badge-status-pending { background: rgba(245, 158, 11, 0.12); color: #A65A0E; border-color: #FADCB8; }
    .badge-status-approved { background: rgba(59, 130, 246, 0.12); color: #3b82f6; border-color: #C8D4E3; }
    .badge-status-paid { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }
    .badge-status-rejected { background: rgba(179, 42, 42, 0.12); color: #B32A2A; border-color: #F5C8C8; }
    .badge-status-cancelled { background: rgba(107, 114, 128, 0.12); color: #6b7280; border-color: #DCDEE3; }

    .type-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .type-badge-pos { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }
    .type-badge-direct { background: rgba(99, 102, 241, 0.12); color: #4F46E5; border-color: #C8D4E3; }
    .type-badge-indirect { background: rgba(59, 130, 246, 0.12); color: #2563EB; border-color: #C8D4E3; }
    .type-badge-leadership { background: rgba(245, 158, 11, 0.12); color: #A65A0E; border-color: #FADCB8; }
    .type-badge-purchase { background: rgba(139, 92, 246, 0.12); color: #7C3AED; border-color: #C8D4E3; }
    .type-badge-new_client { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }
    .type-badge-pos_transaction { background: rgba(59, 130, 246, 0.12); color: #2563EB; border-color: #C8D4E3; }
    .type-badge-sponsor { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; border-color: #B8DFCC; }

    .commission-row {
        transition: background 0.1s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }

    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-size: 0.625rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-success { background: #E6F4EC; color: #1F7B4D; border-color: #B8DFCC; }
    .badge-danger { background: #FDE8E8; color: #B32A2A; border-color: #F5C8C8; }
    .badge-info { background: #E8EDF5; color: var(--primary-navy); border-color: #C8D4E3; }
    .badge-warning { background: #FEF1E6; color: #A65A0E; border-color: #FADCB8; }
    .badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }
    .badge-primary { background: #E8EDF5; color: var(--primary-navy); border-color: #C8D4E3; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
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
    .btn-success {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }
    .btn-success:hover {
        background: #166B3E;
        border-color: #166B3E;
    }
    .btn-success:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
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
        position: sticky;
        top: 0;
        z-index: 10;
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

    .filleul-row {
        transition: background 0.1s ease;
    }
    .filleul-row:hover {
        background: var(--bg-hover);
    }

    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        align-items: center;
    }
    .filter-section select,
    .filter-section input {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
        transition: border-color 0.15s ease;
        outline: none;
    }
    .filter-section select:focus,
    .filter-section input:focus {
        border-color: var(--primary-navy);
    }
    .filter-section .btn-filter {
        padding: 0.375rem 1.5rem;
        background: var(--primary-navy);
        color: white;
        border: none;
        border-radius: 6px;
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
        border-radius: 6px;
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

    .badge-level {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-level-1 { background: rgba(16, 185, 129, 0.12); color: #10b981; border-color: #B8DFCC; }
    .badge-level-2 { background: rgba(59, 130, 246, 0.12); color: #2563EB; border-color: #C8D4E3; }
    .badge-level-3 { background: rgba(139, 92, 246, 0.12); color: #7C3AED; border-color: #C8D4E3; }
    .badge-level-4 { background: rgba(245, 158, 11, 0.12); color: #A65A0E; border-color: #FADCB8; }
    .badge-level-5 { background: rgba(179, 42, 42, 0.12); color: #B32A2A; border-color: #F5C8C8; }

    .alert-credentials {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .alert-credentials .alert-title {
        color: #166B3E;
        font-weight: 700;
        font-size: 1rem;
    }
    .alert-credentials .credentials-box {
        background: #FCFCFD;
        border-radius: 6px;
        padding: 1rem;
        margin-top: 0.75rem;
        border: 1px solid #bbf7d0;
    }
    .alert-credentials .credentials-box .cred-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .alert-credentials .credentials-box .cred-value {
        font-weight: 700;
        font-size: 1rem;
    }
    .alert-credentials .credentials-box .cred-value.text-danger { color: #dc2626; }
    .alert-credentials .credentials-box .cred-value.text-primary { color: var(--primary-navy); }
    .alert-credentials .credentials-box .cred-value.text-success { color: #1F7B4D; }
    .alert-credentials .alert-warning-text {
        margin-top: 0.75rem;
        color: #A65A0E;
        font-size: 0.875rem;
        background: #fef3c7;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        border-left: 3px solid #f59e0b;
    }
    .alert-credentials .alert-warning-text a {
        color: var(--primary-navy);
        font-weight: 600;
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .commission-summary { grid-template-columns: 1fr 1fr; }
        .commission-summary .item .number { font-size: 1rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .type-badge { font-size: 0.6rem; padding: 0.1rem 0.4rem; }
        .badge-status { font-size: 0.5rem; padding: 0.075rem 0.375rem; }
        .table-wrap { margin: 0 -0.875rem; padding: 0 0.875rem; }
        .filter-section { flex-direction: column; }
        .filter-section select, .filter-section input, .filter-section button { width: 100%; min-width: unset; }
        .card { padding: 0.875rem; }
        .alert-credentials { padding: 0.875rem; }
        .alert-credentials .credentials-box { padding: 0.75rem; }
        .alert-credentials .credentials-box .cred-value { font-size: 0.875rem; }
    }
</style>
@endpush

@section('title', 'Détails du membre')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Détails du membre
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: #{{ $member->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('cashier.members.orders', $member->id) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.members.pay-slip', $member->id) }}?period={{ date('Y-m') }}" 
               class="btn btn-success btn-sm" style="background: #B32A2A; border-color: #B32A2A;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Fiche de paie
            </a>
            {{-- BOUTON IMPRIMER L'ADHÉSION --}}
            <button type="button" 
                    id="btnPrintAdhesion" 
                    class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Adhésion PDF
            </button>
        </div>
    </div>

    {{-- ALERTE AVEC LES IDENTIFIANTS --}}
    @if(session('success') && session('password') && session('email'))
        <div class="alert-credentials">
            <div class="alert-title">{{ session('success') }}</div>
            <div class="credentials-box">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <div class="cred-label">Email</div>
                        <div class="cred-value text-primary">{{ session('email') }}</div>
                    </div>
                    <div>
                        <div class="cred-label">Mot de passe</div>
                        <div class="cred-value text-danger">{{ session('password') }}</div>
                    </div>
                    <div>
                        <div class="cred-label">Code membre</div>
                        <div class="cred-value text-success">{{ session('member_code') ?? $member->sponsor_id }}</div>
                    </div>
                </div>
                <div class="alert-warning-text">
                    <strong>Important :</strong> Transmettez ces identifiants au nouveau membre.
                    Il pourra se connecter sur <a href="{{ route('login') }}" target="_blank">{{ route('login') }}</a>
                </div>
            </div>
        </div>
    @elseif(session('success'))
        <div class="alert-credentials" style="border-color: var(--success); background: #f0fdf4;">
            <div class="alert-title" style="color: #166B3E;">{{ session('success') }}</div>
        </div>
    @endif

    {{-- INFORMATIONS DU MEMBRE --}}
    <div class="detail-card">
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
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-secondary)]">Code</p>
                            <p class="text-sm font-mono text-[var(--primary-navy)]">{{ $member->sponsor_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-secondary)]">Inscrit le</p>
                            <p class="text-sm">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-secondary)]">Grade</p>
                            <p class="text-sm font-bold text-[#4F46E5]">
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
                        <div>
                            <p class="text-xs text-[var(--text-secondary)]">Parrain</p>
                            <p class="text-sm font-medium text-[var(--primary-navy)]">
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

    {{-- RÉSUMÉ DES COMMISSIONS --}}
    <div class="commission-summary">
        <div class="item">
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total commissions</div>
        </div>
        <div class="item">
            <div class="number" style="color: #1F7B4D;">${{ number_format($stats['paid_commissions'] ?? 0, 2) }}</div>
            <div class="label">Payées</div>
        </div>
        <div class="item">
            <div class="number" style="color: #A65A0E;">${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="item">
            <div class="number" style="color: #6b7280;">{{ $stats['cancelled_commissions'] ?? 0 }}</div>
            <div class="label">Annulées</div>
        </div>
    </div>

    {{-- FILTRES COMMISSIONS --}}
    <div class="card">
        <form method="GET" action="{{ route('cashier.members.show', $member->id) }}" class="filter-section">
            <select name="type">
                <option value="">Tous les types</option>
                <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct Bonus</option>
                <option value="indirect" {{ request('type') == 'indirect' ? 'selected' : '' }}>Indirect Bonus</option>
                <option value="leadership" {{ request('type') == 'leadership' ? 'selected' : '' }}>Leadership</option>
                <option value="sponsor" {{ request('type') == 'sponsor' ? 'selected' : '' }}>Sponsor</option>
                <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Achat</option>
                <option value="pos_transaction" {{ request('type') == 'pos_transaction' ? 'selected' : '' }}>POS</option>
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
            <a href="{{ route('cashier.members.show', $member->id) }}" class="btn-reset">Réinitialiser</a>
        </form>
    </div>

    {{-- LISTE DES COMMISSIONS --}}
<div class="detail-card p-3 sm:p-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm">
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
                    <th>ID</th>
                    <th>De</th>
                    <th>Type</th>
                    <th>Source</th>
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
                            <div class="text-sm">{{ $commission->fromUser?->name ?? 'Système' }}</div>
                            @if($commission->fromUser)
                                <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->fromUser->sponsor_id ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $typeClass = 'type-badge-' . $commission->type;
                                if (!in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos'])) {
                                    $typeClass = 'type-badge-default';
                                }
                                $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                                if ($commission->type == 'cash_pos') { $typeLabel = 'CASH POS'; }
                            @endphp
                            <span class="type-badge {{ $typeClass }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td>
                            @if($commission->source == 'pos')
                                <span class="badge badge-success text-[8px]">POS</span>
                            @elseif($commission->source == 'mlm')
                                <span class="badge badge-info text-[8px]">MLM</span>
                            @elseif($commission->source == 'membership')
                                <span class="badge" style="background: rgba(139, 92, 246, 0.12); color: #7C3AED; border: 1px solid rgba(139, 92, 246, 0.15); font-size: 8px; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                    ADHÉSION
                                </span>
                            @elseif($commission->source == 'manual')
                                <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #A65A0E; border: 1px solid rgba(245, 158, 11, 0.15); font-size: 8px; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                    MANUEL
                                </span>
                            @else
                                <span class="badge badge-secondary text-[8px]">{{ strtoupper($commission->source ?? 'N/A') }}</span>
                            @endif
                        </td>
                        <td class="text-right font-bold text-sm
                            @if($commission->amount > 0 && in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos']))
                                text-[#1F7B4D]
                            @elseif($commission->amount > 0)
                                text-[#1F7B4D]
                            @else
                                text-[#B32A2A]
                            @endif
                        ">
                            @if($commission->amount > 0)
                                @if($commission->type == 'cash_pos')
                                    +${{ number_format($commission->amount, 2) }}
                                @elseif(in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership']))
                                    +${{ number_format($commission->amount, 2) }}
                                @else
                                    ${{ number_format($commission->amount, 2) }}
                                @endif
                            @else
                                ${{ number_format($commission->amount, 2) }}
                            @endif
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
                        <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                            <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

    {{-- LISTE DES FILLEULS --}}
    <div class="detail-card p-3 sm:p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                Filleuls
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $downlines->total() ?? 0 }})</span>
            </h3>
            <div class="flex flex-wrap gap-2 text-[10px] sm:text-xs">
                <span class="badge badge-success text-[8px]">Actif</span>
                <span class="badge badge-danger text-[8px]">Inactif</span>
            </div>
        </div>

        {{-- Filtres Filleuls --}}
        <div class="filter-section">
            <input type="text" id="downlineSearch" placeholder="Rechercher un filleul..." class="text-sm">
            <select id="downlineLevelFilter" class="text-sm">
                <option value="">Tous les niveaux</option>
                <option value="1">Niveau 1</option>
                <option value="2">Niveau 2</option>
                <option value="3">Niveau 3</option>
                <option value="4">Niveau 4</option>
                <option value="5">Niveau 5+</option>
            </select>
            <select id="downlineStatusFilter" class="text-sm">
                <option value="">Tous les statuts</option>
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
            </select>
            <button onclick="filterDownlines()" class="btn-filter">Filtrer</button>
            <button onclick="resetDownlineFilters()" class="btn-reset">Réinitialiser</button>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th>Code</th>
                        <th class="hidden md:table-cell">Niveau</th>
                        <th class="hidden lg:table-cell">PV</th>
                        <th class="hidden xl:table-cell">Inscrit</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="downlinesTable">
                    @forelse($downlines ?? [] as $downline)
                        <tr class="filleul-row"
                            data-name="{{ strtolower($downline->name) }}"
                            data-email="{{ strtolower($downline->email) }}"
                            data-level="{{ $downline->level ?? 1 }}"
                            data-status="{{ $downline->is_active ? 1 : 0 }}">
                            <td class="font-mono text-xs">#{{ $downline->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $downline->name }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">Tél: {{ $downline->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $downline->email }}
                            </td>
                            <td>
                                <span class="font-mono text-xs text-[var(--primary-navy)]">{{ $downline->sponsor_id ?? 'N/A' }}</span>
                            </td>
                            <td class="hidden md:table-cell">
                                @php
                                    $level = $downline->level ?? 1;
                                    $levelClass = 'badge-level';
                                    if ($level == 1) $levelClass .= ' badge-level-1';
                                    elseif ($level == 2) $levelClass .= ' badge-level-2';
                                    elseif ($level == 3) $levelClass .= ' badge-level-3';
                                    elseif ($level == 4) $levelClass .= ' badge-level-4';
                                    else $levelClass .= ' badge-level-5';
                                @endphp
                                <span class="{{ $levelClass }}">Niv. {{ $level }}</span>
                            </td>
                            <td class="hidden lg:table-cell text-sm font-medium text-[#1F7B4D]">
                                {{ number_format($downline->pv_balance ?? 0) }}
                            </td>
                            <td class="hidden xl:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $downline->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $downline->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">
                                    {{ $downline->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('cashier.members.show', $downline->id) }}"
                                   class="btn btn-primary btn-sm" title="Voir">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun filleul</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de filleuls</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($downlines) && $downlines->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $downlines->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function filterDownlines() {
    const search = document.getElementById('downlineSearch').value.trim().toLowerCase();
    const level = document.getElementById('downlineLevelFilter').value;
    const status = document.getElementById('downlineStatusFilter').value;
    const rows = document.querySelectorAll('#downlinesTable tr');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const rowLevel = row.dataset.level || '1';
        const rowStatus = row.dataset.status || '1';

        let show = true;

        if (search && !name.includes(search) && !email.includes(search)) {
            show = false;
        }

        if (level && rowLevel != level) {
            show = false;
        }

        if (status && rowStatus != status) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

function resetDownlineFilters() {
    document.getElementById('downlineSearch').value = '';
    document.getElementById('downlineLevelFilter').value = '';
    document.getElementById('downlineStatusFilter').value = '';
    filterDownlines();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('downlineSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterDownlines);
    }
    const levelFilter = document.getElementById('downlineLevelFilter');
    if (levelFilter) {
        levelFilter.addEventListener('change', filterDownlines);
    }
    const statusFilter = document.getElementById('downlineStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterDownlines);
    }
});

// Impression du formulaire d'adhésion
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnPrintAdhesion');
    
    if (btn) {
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            
            // Désactiver le bouton
            this.disabled = true;
            this.innerHTML = `
                <span class="spinner"></span>
                Téléchargement...
            `;

            const url = '{{ route("cashier.members.adhesion-pdf", $member->id) }}';
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.blob();
                })
                .then(blob => {
                    if (blob.size === 0) {
                        throw new Error('Le fichier PDF est vide');
                    }
                    
                    const pdfUrl = URL.createObjectURL(blob);
                    
                    // Télécharger le PDF
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = 'formulaire_adhesion_{{ $member->sponsor_id }}.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Ouvrir la boîte de dialogue d'impression
                    const printWindow = window.open(pdfUrl, '_blank');
                    if (printWindow) {
                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.print();
                            }, 300);
                        };
                    }
                    
                    setTimeout(() => URL.revokeObjectURL(pdfUrl), 5000);
                    
                    // Réinitialiser le bouton
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du téléchargement. Veuillez réessayer.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    }
});
</script>
@endpush
@endsection