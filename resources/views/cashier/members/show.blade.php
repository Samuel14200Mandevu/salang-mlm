@extends('cashier.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    :root {
        --bg-base: #F4F5F7;
        --bg-card: #F8F9FA;
        --bg-secondary: #EEF0F3;
        --bg-hover: #E8EAEE;
        --text-primary: #111827;
        --text-secondary: #4B5563;
        --text-tertiary: #6B7280;
        --border-color: #D1D5DB;
        --border-light: #E5E7EB;
        --success: #0F7B3E;
        --danger: #991B1B;
        --warning: #9A5A0A;
        --navy: #1E293B;
        --navy-dark: #0F172A;
        --slate: #475569;
        --slate-light: #94A3B8;
    }

    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
        transition: background 0.15s ease;
    }

    .detail-card:hover {
        background: #F8F9FA;
    }

    .detail-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        font-weight: 600;
    }

    .detail-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 0.2rem;
    }

    .avatar-lg {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 700;
        background: var(--navy);
        color: white;
        flex-shrink: 0;
    }

    .commission-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin: 1rem 0;
    }
    .commission-summary .item {
        text-align: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 6px;
        border: 1px solid var(--border-light);
    }
    .commission-summary .item .number {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--navy);
        letter-spacing: -0.01em;
    }
    .commission-summary .item .label {
        font-size: 0.6rem;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 0.15rem;
        display: block;
    }

    .badge-status {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid transparent;
    }
    .badge-status-pending { background: #FEF3C7; color: #9A5A0A; border-color: #FDE68A; }
    .badge-status-approved { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .badge-status-paid { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
    .badge-status-rejected { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }
    .badge-status-cancelled { background: #F3F4F6; color: #6B7280; border-color: #D1D5DB; }

    .type-badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
    }
    .type-badge-pos { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
    .type-badge-direct { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .type-badge-indirect { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .type-badge-leadership { background: #FEF3C7; color: #9A5A0A; border-color: #FDE68A; }
    .type-badge-purchase { background: #EDE9FE; color: #5B21B6; border-color: #DDD6FE; }
    .type-badge-new_client { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
    .type-badge-pos_transaction { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .type-badge-sponsor { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }

    .commission-row {
        transition: background 0.1s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }

    .badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
    }
    .badge-success { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
    .badge-danger { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }
    .badge-info { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .badge-warning { background: #FEF3C7; color: #9A5A0A; border-color: #FDE68A; }
    .badge-secondary { background: #F3F4F6; color: #6B7280; border-color: #D1D5DB; }
    .badge-primary { background: #E2E8F0; color: #1E293B; border-color: #CBD5E1; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        transition: background 0.15s ease, border-color 0.15s ease;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
        letter-spacing: 0.01em;
    }
    .btn-sm { padding: 0.2rem 0.6rem; font-size: 0.7rem; }
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
        background: var(--navy);
        color: white;
        border-color: var(--navy);
    }
    .btn-primary:hover {
        background: var(--navy-dark);
        border-color: var(--navy-dark);
    }
    .btn-success {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }
    .btn-success:hover {
        background: #0A5C30;
        border-color: #0A5C30;
    }
    .btn-danger {
        background: var(--danger);
        color: white;
        border-color: var(--danger);
    }
    .btn-danger:hover {
        background: #7F1D1D;
        border-color: #7F1D1D;
    }
    .btn-success:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn .spinner {
        display: inline-block;
        width: 0.9rem;
        height: 0.9rem;
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
        font-size: 0.8rem;
    }
    .table thead th {
        padding: 0.4rem 0.6rem;
        text-align: left;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table tbody td {
        padding: 0.4rem 0.6rem;
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
        padding: 0.3rem 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.75rem;
        flex: 1;
        min-width: 120px;
        transition: border-color 0.15s ease;
        outline: none;
        font-family: 'Inter', sans-serif;
    }
    .filter-section select:focus,
    .filter-section input:focus {
        border-color: var(--navy);
        outline: 2px solid rgba(30, 41, 59, 0.1);
    }
    .filter-section .btn-filter {
        padding: 0.3rem 1.25rem;
        background: var(--navy);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        cursor: pointer;
        transition: background 0.15s ease;
        font-family: 'Inter', sans-serif;
    }
    .filter-section .btn-filter:hover {
        background: var(--navy-dark);
    }
    .filter-section .btn-reset {
        padding: 0.3rem 1.25rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        cursor: pointer;
        transition: background 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-family: 'Inter', sans-serif;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-hover);
    }

    .badge-level {
        display: inline-block;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 600;
        border: 1px solid transparent;
        letter-spacing: 0.03em;
    }
    .badge-level-1 { background: #D1FAE5; color: #065F46; border-color: #A7F3D0; }
    .badge-level-2 { background: #DBEAFE; color: #1E40AF; border-color: #BFDBFE; }
    .badge-level-3 { background: #EDE9FE; color: #5B21B6; border-color: #DDD6FE; }
    .badge-level-4 { background: #FEF3C7; color: #9A5A0A; border-color: #FDE68A; }
    .badge-level-5 { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }

    .alert-credentials {
        background: #F0FDF4;
        border: 2px solid #86EFAC;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .alert-credentials .alert-title {
        color: #065F46;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .alert-credentials .credentials-box {
        background: #F8F9FA;
        border-radius: 6px;
        padding: 1rem;
        margin-top: 0.75rem;
        border: 1px solid #BBF7D0;
    }
    .alert-credentials .credentials-box .cred-label {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .alert-credentials .credentials-box .cred-value {
        font-weight: 600;
        font-size: 0.95rem;
    }
    .alert-credentials .credentials-box .cred-value.text-danger { color: #DC2626; }
    .alert-credentials .credentials-box .cred-value.text-primary { color: var(--navy); }
    .alert-credentials .credentials-box .cred-value.text-success { color: #0F7B3E; }
    .alert-credentials .alert-warning-text {
        margin-top: 0.75rem;
        color: #9A5A0A;
        font-size: 0.8rem;
        background: #FEF3C7;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        border-left: 3px solid #F59E0B;
    }
    .alert-credentials .alert-warning-text a {
        color: var(--navy);
        font-weight: 600;
        text-decoration: underline;
    }

    .pv-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.75rem;
        margin: 0.75rem 0;
    }
    .pv-summary .pv-item {
        text-align: center;
        padding: 0.5rem;
        background: var(--bg-secondary);
        border-radius: 6px;
        border: 1px solid var(--border-light);
    }
    .pv-summary .pv-item .number {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--navy);
        letter-spacing: -0.01em;
    }
    .pv-summary .pv-item .label {
        font-size: 0.55rem;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
        margin-top: 0.1rem;
    }

    .pv-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .footer-links {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-light);
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        justify-content: center;
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }
    .footer-links a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .footer-links a:hover {
        color: var(--navy);
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .commission-summary { grid-template-columns: 1fr 1fr; }
        .commission-summary .item .number { font-size: 0.95rem; }
        .table thead th, .table tbody td { padding: 0.3rem 0.4rem; font-size: 0.6rem; }
        .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.6rem; }
        .type-badge { font-size: 0.55rem; padding: 0.1rem 0.35rem; }
        .badge-status { font-size: 0.45rem; padding: 0.05rem 0.3rem; }
        .table-wrap { margin: 0 -0.875rem; padding: 0 0.875rem; }
        .filter-section { flex-direction: column; }
        .filter-section select, .filter-section input, .filter-section button { width: 100%; min-width: unset; }
        .card { padding: 0.875rem; }
        .alert-credentials { padding: 0.875rem; }
        .alert-credentials .credentials-box { padding: 0.75rem; }
        .alert-credentials .credentials-box .cred-value { font-size: 0.85rem; }
        .pv-summary { grid-template-columns: 1fr 1fr; }
        .footer-links { flex-direction: column; align-items: center; gap: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Détails du membre')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] tracking-tight">
                Détails du membre
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: {{ $member->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('cashier.members.orders', $member->id) }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.members.pay-slip', $member->id) }}?period={{ date('Y-m') }}" class="btn btn-danger btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4v16h16V4zM8 12h8M12 8v8"/>
                </svg>
                Fiche de paie
            </a>
            <button type="button" id="btnPrintAdhesion" class="btn btn-success btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9V3h12v6M6 21h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    <path d="M18 9V3H6v6z"/>
                    <path d="M8 12v4h8v-4"/>
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
        <div class="alert-credentials" style="border-color: #86EFAC; background: #F0FDF4;">
            <div class="alert-title" style="color: #065F46;">{{ session('success') }}</div>
        </div>
    @endif

    {{-- INFORMATIONS DU MEMBRE --}}
    <div class="detail-card">
        <div class="flex flex-wrap items-start gap-4">
            <div class="avatar-lg">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-[var(--text-primary)] tracking-tight">{{ $member->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    @php
                        $roleName = $member->getRoleNames()->first() ?? 'user';
                    @endphp
                    @if($roleName != 'user')
                        <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Email</p>
                            <p class="text-sm text-[var(--text-primary)]">{{ $member->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Téléphone</p>
                            <p class="text-sm text-[var(--text-primary)]">{{ $member->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Code</p>
                            <p class="text-sm font-mono text-[var(--navy)]">{{ $member->sponsor_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Inscrit le</p>
                            <p class="text-sm text-[var(--text-primary)]">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Grade</p>
                            <p class="text-sm font-bold text-[var(--navy)]">
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
                                <span class="text-xs text-[var(--text-tertiary)] font-normal">(Niv. {{ $level }})</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Parrain</p>
                            <p class="text-sm font-medium text-[var(--navy)]">
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

    {{-- SECTION PV DU MEMBRE --}}
    @php
        $pvBalance = \App\Models\UserPvBalance::where('user_id', $member->id)->first();
        $totalPv = $pvBalance ? $pvBalance->total_pv : 0;
        $availablePv = $pvBalance ? $pvBalance->available_pv : 0;
        $allocatedPv = $pvBalance ? $pvBalance->allocated_pv : 0;
        $pendingPv = $pvBalance ? $pvBalance->pending_pv : 0;
    @endphp

    <div class="detail-card">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm">Points de Volume (PV)</h3>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">Solde et gestion des PV du membre</p>
            </div>
            <div class="pv-actions">
                @if($availablePv > 0)
                    <a href="{{ route('cashier.pv.distribute') }}?member_id={{ $member->id }}" class="btn btn-primary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Distribuer PV
                    </a>
                @endif
                <a href="{{ route('cashier.pv.dashboard') }}" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    Voir mes PV
                </a>
            </div>
        </div>

        <div class="pv-summary">
            <div class="pv-item">
                <div class="number">{{ number_format($totalPv) }}</div>
                <span class="label">PV Total</span>
            </div>
            <div class="pv-item" style="border-color: #86EFAC; background: #F0FDF4;">
                <div class="number" style="color: #0F7B3E;">{{ number_format($availablePv) }}</div>
                <span class="label">PV Disponibles</span>
            </div>
            <div class="pv-item" style="border-color: #93C5FD; background: #EFF6FF;">
                <div class="number" style="color: #1E40AF;">{{ number_format($allocatedPv) }}</div>
                <span class="label">PV Alloués</span>
            </div>
            <div class="pv-item" style="border-color: #FCD34D; background: #FFFBEB;">
                <div class="number" style="color: #9A5A0A;">{{ number_format($pendingPv) }}</div>
                <span class="label">PV en Attente</span>
            </div>
        </div>

    </div>

    {{-- RÉSUMÉ DES COMMISSIONS --}}
    <div class="commission-summary">
        <div class="item">
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <span class="label">Total commissions</span>
        </div>
        <div class="item">
            <div class="number" style="color: #0F7B3E;">${{ number_format($stats['paid_commissions'] ?? 0, 2) }}</div>
            <span class="label">Payées</span>
        </div>
        <div class="item">
            <div class="number" style="color: #9A5A0A;">${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</div>
            <span class="label">En attente</span>
        </div>
        <div class="item">
            <div class="number" style="color: #6B7280;">{{ $stats['cancelled_commissions'] ?? 0 }}</div>
            <span class="label">Annulées</span>
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
            <a href="{{ route('cashier.members.show', $member->id) }}" class="btn-reset">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
                Réinitialiser
            </a>
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
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $commission->id }}</td>
                            <td>
                                <div class="text-sm text-[var(--text-primary)]">{{ $commission->fromUser?->name ?? 'Système' }}</div>
                                @if($commission->fromUser)
                                    <div class="text-xs text-[var(--text-tertiary)]">Code: {{ $commission->fromUser->sponsor_id ?? 'N/A' }}</div>
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
                                    <span class="badge" style="background: #EDE9FE; color: #5B21B6; border-color: #DDD6FE; font-size: 8px; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                        ADHÉSION
                                    </span>
                                @elseif($commission->source == 'manual')
                                    <span class="badge" style="background: #FEF3C7; color: #9A5A0A; border-color: #FDE68A; font-size: 8px; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                        MANUEL
                                    </span>
                                @else
                                    <span class="badge badge-secondary text-[8px]">{{ strtoupper($commission->source ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="text-right font-bold text-sm
                                @if($commission->amount > 0 && in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos']))
                                    text-[#0F7B3E]
                                @elseif($commission->amount > 0)
                                    text-[#0F7B3E]
                                @else
                                    text-[#991B1B]
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-[var(--text-tertiary)]">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
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
                <option value="">Tous les grades</option>
                <option value="0">Membre</option>
                <option value="1">Distributeur</option>
                <option value="2">Qualification</option>
                <option value="3">Cumul Directeur</option>
                <option value="4">Directeur</option>
                <option value="5">Manager Senior</option>
                <option value="6">Directeur Envolée</option>
                <option value="7">Saphire Manager</option>
                <option value="8">Diamant Bleu</option>
                <option value="9">Perle Diamant</option>
            </select>
            <select id="downlineStatusFilter" class="text-sm">
                <option value="">Tous les statuts</option>
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
            </select>
            <button onclick="filterDownlines()" class="btn-filter">Filtrer</button>
            <button onclick="resetDownlineFilters()" class="btn-reset">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
                Réinitialiser
            </button>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th>Code</th>
                        <th class="hidden md:table-cell">Grade</th>
                        <th class="hidden lg:table-cell">PV</th>
                        <th class="hidden xl:table-cell">Inscrit</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="downlinesTable">
                    @forelse($downlines ?? [] as $downline)
                        @php
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
                            $level = $downline->rank_level ?? 0;
                            $gradeName = $levelNames[$level] ?? 'Niveau ' . $level;
                            
                            $levelClass = 'badge-level';
                            if ($level == 1) $levelClass .= ' badge-level-1';
                            elseif ($level == 2) $levelClass .= ' badge-level-2';
                            elseif ($level == 3) $levelClass .= ' badge-level-3';
                            elseif ($level == 4) $levelClass .= ' badge-level-4';
                            else $levelClass .= ' badge-level-5';
                        @endphp
                        <tr class="filleul-row"
                            data-name="{{ strtolower($downline->name) }}"
                            data-email="{{ strtolower($downline->email) }}"
                            data-level="{{ $level }}"
                            data-status="{{ $downline->is_active ? 1 : 0 }}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $downline->id }}</td>
                            <td>
                                <div class="font-medium text-sm text-[var(--text-primary)]">{{ $downline->name }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">Tél: {{ $downline->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $downline->email }}
                            </td>
                            <td>
                                <span class="font-mono text-xs text-[var(--navy)]">{{ $downline->sponsor_id ?? 'N/A' }}</span>
                            </td>
                            <td class="hidden md:table-cell">
                                <span class="{{ $levelClass }}">
                                    {{ $gradeName }}
                                    <span class="text-xs text-[var(--text-secondary)] font-normal">(Niv. {{ $level }})</span>
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-sm font-medium text-[#0F7B3E]">
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-[var(--text-tertiary)]">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 010 7.75"/>
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
        const rowLevel = row.dataset.level || '0';
        const rowStatus = row.dataset.status || '1';

        let show = true;

        if (search && !name.includes(search) && !email.includes(search)) {
            show = false;
        }

        if (level !== '' && parseInt(rowLevel) !== parseInt(level)) {
            show = false;
        }

        if (status !== '' && parseInt(rowStatus) !== parseInt(status)) {
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
                    
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = 'formulaire_adhesion_{{ $member->sponsor_id }}.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    const printWindow = window.open(pdfUrl, '_blank');
                    if (printWindow) {
                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.print();
                            }, 300);
                        };
                    }
                    
                    setTimeout(() => URL.revokeObjectURL(pdfUrl), 5000);
                    
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