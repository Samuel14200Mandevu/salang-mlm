@extends('admin.layouts.app')

@push('styles')
<style>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.modal-box {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 1.75rem;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    transform: scale(0.95);
    transition: transform 0.25s ease;
    border: 1px solid var(--border-color);
}
.modal-overlay.active .modal-box {
    transform: scale(1);
}
.modal-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}
.modal-icon-info {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
}
.modal-icon-danger {
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
}
.modal-icon-warning {
    background: rgba(181, 71, 8, 0.1);
    color: #B54708;
}
.modal-icon-success {
    background: rgba(28, 126, 74, 0.1);
    color: #1C7E4A;
}
.modal-title {
    text-align: center;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.modal-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}
.modal-text strong {
    color: var(--text-primary);
}
.modal-text .text-danger {
    color: #B91C1C;
}
.modal-text .text-warning {
    color: #B54708;
}
.modal-text .text-success {
    color: #1C7E4A;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 100px;
    justify-content: center;
}

.code-display {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 0.625rem 1rem;
    text-align: center;
    font-family: monospace;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary-blue);
    letter-spacing: 1.5px;
    border: 2px dashed var(--border-color);
    margin: 0.75rem 0;
    word-break: break-all;
}

.info-row {
    display: flex;
    flex-direction: column;
    padding: 0.625rem 0;
    border-bottom: 1px solid var(--border-light);
}
.info-row:last-child {
    border-bottom: none;
}
.info-row .label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
}
.info-row .value {
    font-size: 0.938rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-top: 0.125rem;
}
.info-row .value .badge-level,
.table td .badge-level {
    vertical-align: middle;
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
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-info { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }
.badge-purple { background: rgba(10, 42, 108, 0.12); color: var(--primary-blue); border-color: rgba(10, 42, 108, 0.15); }
.badge-cashier { background: rgba(10, 42, 108, 0.08); color: var(--primary-blue); border-color: rgba(10, 42, 108, 0.12); }
.badge-cashier-principal { background: rgba(10, 42, 108, 0.15); color: var(--primary-blue); border-color: rgba(10, 42, 108, 0.2); font-weight: 700; }

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

.sponsor-card {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 0.625rem 0.875rem;
}
.sponsor-card:hover {
    background: var(--bg-hover);
}

.package-preview {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 0.875rem;
    border: 2px solid var(--border-color);
    margin-top: 0.5rem;
}
.package-preview:hover {
    border-color: var(--primary-blue);
}
.package-preview .package-name {
    font-weight: 600;
    color: var(--text-primary);
}
.package-preview .package-price {
    font-weight: 600;
    color: var(--primary-blue);
}
.package-preview .package-detail {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

/* PV Link */
.pv-link {
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
}
.pv-link:hover .pv-value {
    color: var(--primary-blue);
}
.pv-link .pv-value {
    transition: color 0.2s ease;
    display: inline-block;
}
.pv-link .pv-icon {
    transition: transform 0.2s ease;
}
.pv-link:hover .pv-icon {
    transform: translateX(3px);
}

@media (max-width: 640px) {
    .modal-box { padding: 1.25rem; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn { width: 100%; }
    .info-row .value { font-size: 0.85rem; }
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
    .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
    .card { padding: 0.875rem; }
    .sponsor-card { padding: 0.5rem 0.75rem; }
    .code-display { font-size: 1rem; padding: 0.5rem 0.75rem; }
    .package-preview { padding: 0.75rem; }
}
</style>
@endpush

@section('content')
@php
    $rankLevelShow = (int) ($user->rank_level ?? 0);
    $rankLabelShow = \App\Support\MlmRank::label($rankLevelShow);
    $rankBadgeShow = \App\Support\MlmRank::badgeClass($rankLevelShow);
    $userShowSubtitle = $user->name . ' · ID ' . $user->id;
@endphp

@include('admin.layouts.partials.desktop-page-header', [
    'title' => "Détails de l'utilisateur",
    'subtitle' => $userShowSubtitle,
    'actions' => view('admin.users.partials.show-header-actions', compact('user'))->render(),
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp">
    <div class="admin-mobile-page-head is-mobile-banner">
        <div class="admin-title-banner__text">
            <h1 class="page-title">Détails de l'utilisateur</h1>
            <p class="page-subtitle">{{ $userShowSubtitle }}</p>
        </div>
        @include('admin.users.partials.show-mobile-banner-actions', ['user' => $user])
    </div>
</div>

<div class="admin-mobile-page">
    <div class="admin-profile-hero md:hidden">
        <div class="admin-profile-hero__avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="admin-profile-hero__body">
            <p class="admin-profile-hero__name">{{ $user->name }}</p>
            <p class="admin-profile-hero__meta">{{ $user->email }}</p>
            <div class="admin-profile-hero__chips">
                <span class="{{ $rankBadgeShow }}">{{ $rankLabelShow }}</span>
                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            @if($user->sponsor_id)
                <span class="admin-profile-hero__code">#{{ $user->sponsor_id }}</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3 sm:p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg text-yellow-600 text-sm animate-fadeIn">
            {{ session('warning') }}
        </div>
    @endif

    <div class="admin-member-kpis md:hidden">
        <div class="admin-member-kpi">
            <strong>{{ $filleuls->count() ?? 0 }}</strong>
            <span>Filleuls</span>
        </div>
        <div class="admin-member-kpi">
            <strong>{{ $commissionsCount ?? 0 }}</strong>
            <span>Commissions</span>
        </div>
        <div class="admin-member-kpi">
            <strong>${{ number_format($totalCommissions ?? 0, 0) }}</strong>
            <span>Total $</span>
        </div>
        <a href="{{ route('admin.pv.show', $user->id) }}" class="admin-member-kpi pv-link">
            <strong>{{ $user->pv_balance ?? 0 }}</strong>
            <span>PV →</span>
        </a>
    </div>

    <!-- User Information -->
    <div class="admin-section animate-fadeInUp delay-1">
        <h2 class="admin-section__title md:hidden">Informations</h2>
        <div class="card p-3 sm:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-[var(--border-light)]">

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row hidden md:flex">
                    <span class="label">Nom complet</span>
                    <span class="value">{{ $user->name }}</span>
                </div>
                <div class="info-row hidden md:flex">
                    <span class="label">Email</span>
                    <span class="value text-sm">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Téléphone</span>
                    <span class="value">{{ $user->phone ?? 'Non fourni' }}</span>
                </div>
                <div class="info-row md:hidden">
                    <span class="label">Code parrain</span>
                    <span class="value font-mono text-primary-blue font-bold">{{ $user->sponsor_id ?? 'Aucun' }}</span>
                </div>
            </div>

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row md:hidden">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </span>
                </div>
                <div class="info-row hidden md:flex">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        @if(!$user->is_active && $user->activation_code)
                            <span class="text-xs text-[var(--text-tertiary)] block mt-1">
                                Code: <span class="font-mono text-primary-blue">{{ $user->activation_code }}</span>
                            </span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Rôle</span>
                    <span class="value">
                        @php
                            $roleName = $user->getRoleNames()->first() ?? 'user';
                            $roleDisplay = 'Utilisateur';
                            $badgeClass = 'badge-neutral';

                            if($roleName === 'admin') {
                                $roleDisplay = 'Administrateur';
                                $badgeClass = 'badge-purple';
                            } elseif($roleName === 'cashier') {
                                $roleDisplay = 'Caissier';
                                $badgeClass = 'badge-cashier';
                            } elseif($roleName === 'caissier_principal') {
                                $roleDisplay = 'Caissier Principal';
                                $badgeClass = 'badge-cashier-principal';
                            }

                            if($user->hasRole('caissier_principal') && $roleName !== 'caissier_principal') {
                                $roleDisplay = 'Caissier Principal';
                                $badgeClass = 'badge-cashier-principal';
                            } elseif($user->hasRole('cashier') && $roleName !== 'cashier' && $roleName !== 'caissier_principal') {
                                $roleDisplay = 'Caissier';
                                $badgeClass = 'badge-cashier';
                            } elseif($user->hasRole('admin') && $roleName !== 'admin') {
                                $roleDisplay = 'Administrateur';
                                $badgeClass = 'badge-purple';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ $roleDisplay }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">KYC</span>
                    <span class="value">
                        <span class="badge {{ $user->kyc_status === 'verified' ? 'badge-success' : ($user->kyc_status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $user->kyc_status_label ?? 'Non vérifié' }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row hidden md:flex">
                    <span class="label">Code de parrainage</span>
                    <span class="value font-mono text-primary-blue font-bold">{{ $user->sponsor_id ?? 'Aucun' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Parrain</span>
                    <span class="value">
                        @php
                            $parrain = App\Models\User::find($user->parrain_id);
                        @endphp
                        @if($parrain)
                            <a href="{{ route('admin.users.show', $parrain->id) }}" class="text-primary-blue hover:underline font-semibold">
                                {{ $parrain->name }}
                            </a>
                            <span class="text-xs text-[var(--text-tertiary)] block">
                                Email: {{ $parrain->email }}
                            </span>
                        @elseif($user->parrain_id)
                            <span class="text-red-500">Inconnu (ID: {{ $user->parrain_id }})</span>
                        @else
                            <span class="text-[var(--text-tertiary)]">Aucun parrain</span>
                        @endif
                    </span>
                </div>

                <!-- GRADE avec couleurs -->
                <div class="info-row">
                    <span class="label">Grade</span>
                    <span class="value">
                        @php
                            $rankLevel = (int) ($user->rank_level ?? 0);
                            $rankLabel = \App\Support\MlmRank::label($rankLevel);
                            $rankBadgeClass = \App\Support\MlmRank::badgeClass($rankLevel);
                        @endphp
                        <span class="{{ $rankBadgeClass }}">{{ $rankLabel }}</span>
                        <span class="text-xs text-[var(--text-tertiary)] font-normal ml-1">(Niv. {{ $rankLevel }})</span>
                        @if($user->activation_package_id)
                            @php
                                $activationPackage = App\Models\Package::find($user->activation_package_id);
                            @endphp
                            @if($activationPackage)
                                <span class="text-xs text-[var(--text-tertiary)] block">
                                    Package: {{ $activationPackage->name }}
                                </span>
                            @endif
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics desktop -->
        <div class="hidden md:grid mt-4 pt-4 border-t border-[var(--border-color)] grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-primary-blue">{{ $filleuls->count() ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Filleuls</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-[var(--ui-stat-info)]">{{ $commissionsCount ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Commissions</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-[var(--ui-stat-success)]">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Total Commissions</p>
            </div>
            <!-- PV Link -->
            <div class="text-center">
                <a href="{{ route('admin.pv.show', $user->id) }}"
                   class="pv-link block group"
                   title="Voir les PV dans la gestion des PV">
                    <p class="text-2xl font-bold text-primary-blue pv-value group-hover:text-[#061B4A] transition-colors">
                        {{ $user->pv_balance ?? 0 }}
                    </p>
                    <p class="text-xs text-[var(--text-secondary)] flex items-center justify-center gap-1">
                        PV
                        <svg class="w-3 h-3 text-[var(--text-tertiary)] pv-icon group-hover:text-primary-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </p>
                </a>
            </div>
        </div>

        <!-- Downlines List with Grade Column -->
        @if(isset($filleuls) && $filleuls->count() > 0)
        <div class="admin-section md:mt-4 md:pt-4 md:border-t md:border-[var(--border-color)]">
            <h4 class="admin-section__title">
                Filleuls ({{ $filleuls->count() }})
            </h4>

            <div class="admin-entity-list md:hidden">
                @foreach($filleuls as $filleul)
                    @php $flRank = (int) ($filleul->rank_level ?? 0); @endphp
                    <a href="{{ route('admin.users.show', $filleul->id) }}" class="admin-entity-card">
                        <div class="admin-entity-card__top">
                            <div class="min-w-0">
                                <div class="admin-entity-card__name">{{ $filleul->name }}</div>
                                <div class="admin-entity-card__sub">{{ $filleul->email }}</div>
                            </div>
                            <svg class="admin-entity-card__chevron w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                        <div class="admin-entity-card__meta">
                            <span class="{{ \App\Support\MlmRank::badgeClass($flRank) }}">{{ \App\Support\MlmRank::label($flRank) }}</span>
                            <span class="badge {{ $filleul->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $filleul->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="text-xs font-mono text-primary-blue">#{{ $filleul->sponsor_id }}</span>
                            <span class="text-xs text-[var(--text-secondary)]">{{ number_format((float) ($filleul->pv_balance ?? 0)) }} PV</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="table-wrap hidden md:block">
                <table class="table table-striped admin-table-desktop-only">
                    <thead>
                        <tr>
                            <th class="text-xs">ID</th>
                            <th class="text-xs">Nom</th>
                            <th class="text-xs">Email</th>
                            <th class="text-xs">Code</th>
                            <th class="text-xs">Grade</th>
                            <th class="text-xs hidden sm:table-cell">PV</th>
                            <th class="text-xs">Statut</th>
                            <th class="text-xs">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filleuls as $filleul)
                        <tr>
                            <td class="text-xs">{{ $filleul->id }}</td>
                            <td class="text-sm">{{ $filleul->name }}</td>
                            <td class="text-xs text-[var(--text-secondary)]">{{ $filleul->email }}</td>
                            <td class="text-xs font-mono text-primary-blue">{{ $filleul->sponsor_id }}</td>
                            <td>
                                @php
                                    $rankLevel = (int) ($filleul->rank_level ?? 0);
                                @endphp
                                <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">
                                    {{ \App\Support\MlmRank::label($rankLevel) }}
                                </span>
                                <span class="text-xs text-[var(--text-tertiary)]">(Niv. {{ $rankLevel }})</span>
                            </td>
                            <td class="hidden sm:table-cell text-sm font-medium font-mono text-[var(--ui-stat-success)]">
                                {{ number_format((float) ($filleul->pv_balance ?? 0)) }}
                            </td>
                            <td>
                                <span class="badge {{ $filleul->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $filleul->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $filleul->id) }}" class="btn btn-sm btn-primary">
                                    Voir
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL GÉNÉRER CODE D'ACTIVATION -->
<!-- ============================================================ -->
<div id="generateCodeModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-info">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h3 class="modal-title">Générer un code d'activation</h3>
        <p class="modal-text">
            Choisissez le package à associer au code d'activation pour
            <strong>{{ $user->name }}</strong>.
            <br>
            L'utilisateur recevra ce package lors de l'activation.
        </p>

        <form action="{{ route('admin.activations.generate-code', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Package</label>
                <select name="package_id" class="form-control w-full" required>
                    <option value="">Sélectionner un package</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}">
                            {{ $package->name }} - ${{ number_format($package->price, 2) }} ({{ $package->pv_value }} PV)
                        </option>
                    @endforeach
                </select>
            </div>

            @if($user->activation_code)
            <div class="code-display">
                {{ $user->activation_code }}
            </div>
            <p class="text-xs text-center text-[var(--text-tertiary)] -mt-2 mb-3">
                Code actuel (valable jusqu'au {{ \Carbon\Carbon::parse($user->activation_code_expires_at)->format('d/m/Y') }})
            </p>
            @endif

            <div class="modal-actions">
                <button type="button" onclick="closeGenerateCodeModal()" class="btn btn-outline btn-sm">
                    Annuler
                </button>
                <button type="submit" class="btn btn-info btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Générer et envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL ACTIVATION AVEC PACKAGE -->
<!-- ============================================================ -->
<div id="activateWithPackageModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Activer avec un package</h3>
        <p class="modal-text">
            Choisissez le package à attribuer à <strong>{{ $user->name }}</strong>.
            <br>
            L'utilisateur recevra les PV/BV correspondants lors de l'activation.
        </p>

        <form action="{{ route('admin.activations.activate-with-package', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Package d'activation</label>
                <select name="package_id" id="activationPackageSelect" class="form-control w-full" required>
                    <option value="">Sélectionner un package</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}"
                                data-name="{{ $package->name }}"
                                data-price="{{ $package->price }}"
                                data-pv="{{ $package->pv_value }}"
                                data-bv="{{ $package->bv_value }}"
                                data-commission="{{ $package->commission_rate ?? 30 }}">
                            {{ $package->name }} - ${{ number_format($package->price, 2) }} ({{ $package->pv_value }} PV)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Aperçu du package -->
            <div id="packagePreview" class="package-preview" style="display: none;">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="package-name" id="previewPackageName">Package</span>
                        <div class="package-detail">
                            <span id="previewPackagePV">0</span> PV |
                            <span id="previewPackageBV">0</span> BV |
                            Commission: <span id="previewPackageCommission">0</span>%
                        </div>
                    </div>
                    <span class="package-price" id="previewPackagePrice">$0.00</span>
                </div>
            </div>

            <!-- Infos PV/BV -->
            <div class="text-sm text-[var(--text-secondary)] bg-[var(--bg-secondary)] p-3 rounded-lg">
                <div class="flex justify-between">
                    <span>PV actuel:</span>
                    <span class="font-bold text-primary-blue">{{ $currentPV ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>BV actuel:</span>
                    <span class="font-bold text-primary-blue">{{ $currentBV ?? 0 }}</span>
                </div>
                <div class="flex justify-between border-t border-[var(--border-light)] pt-2 mt-2">
                    <span>PV après activation:</span>
                    <span class="font-bold text-[var(--ui-stat-success)]" id="newPVTotal">{{ $currentPV ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>BV après activation:</span>
                    <span class="font-bold text-[var(--ui-stat-success)]" id="newBVTotal">{{ $currentBV ?? 0 }}</span>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="closeActivateWithPackageModal()" class="btn btn-outline btn-sm">
                    Annuler
                </button>
                <button type="submit" class="btn btn-success btn-sm" id="activatePackageBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Activer le compte
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Deactivate Modal -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la désactivation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">désactiver</strong> le compte de <strong>{{ $user->name }}</strong> ?
            <br>
            L'utilisateur ne pourra pas se connecter jusqu'à sa réactivation.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeactivateModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-warning btn-sm">
                Désactiver
            </a>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la suppression</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">supprimer définitivement</strong> <strong>{{ $user->name }}</strong> ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong> et toutes les données seront perdues.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// MODAL GÉNÉRER CODE
// ============================================================
function openGenerateCodeModal() {
    document.getElementById('generateCodeModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeGenerateCodeModal() {
    document.getElementById('generateCodeModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL ACTIVATION AVEC PACKAGE
// ============================================================
function openActivateWithPackageModal() {
    document.getElementById('activateWithPackageModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    updatePackagePreview();
}

function closeActivateWithPackageModal() {
    document.getElementById('activateWithPackageModal').classList.remove('active');
    document.body.style.overflow = '';
}

function updatePackagePreview() {
    const select = document.getElementById('activationPackageSelect');
    const preview = document.getElementById('packagePreview');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        const name = selectedOption.dataset.name || 'Package';
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const pv = parseInt(selectedOption.dataset.pv) || 0;
        const bv = parseInt(selectedOption.dataset.bv) || 0;
        const commission = parseInt(selectedOption.dataset.commission) || 0;

        document.getElementById('previewPackageName').textContent = name;
        document.getElementById('previewPackagePrice').textContent = '$' + price.toFixed(2);
        document.getElementById('previewPackagePV').textContent = pv;
        document.getElementById('previewPackageBV').textContent = bv;
        document.getElementById('previewPackageCommission').textContent = commission;

        const currentPV = {{ $currentPV ?? 0 }};
        const currentBV = {{ $currentBV ?? 0 }};
        document.getElementById('newPVTotal').textContent = currentPV + pv;
        document.getElementById('newBVTotal').textContent = currentBV + bv;

        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('activationPackageSelect');
    if (select) {
        select.addEventListener('change', updatePackagePreview);
    }
});

// ============================================================
// MODAL DÉSACTIVER
// ============================================================
function openDeactivateModal() {
    document.getElementById('deactivateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL SUPPRIMER
// ============================================================
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// FERMER LES MODALS EN CLIQUANT À L'EXTÉRIEUR
// ============================================================
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ============================================================
// FERMER LES MODALS AVEC LA TOUCHE ESCAPE
// ============================================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endsection