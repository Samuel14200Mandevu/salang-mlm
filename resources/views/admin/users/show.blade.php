@extends('admin.layouts.app')

@push('styles')
<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-icon-success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    .modal-icon-info {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    .modal-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .modal-text strong {
        color: var(--text-primary);
    }
    .modal-text .text-danger {
        color: #ef4444;
    }
    .modal-text .text-warning {
        color: #f59e0b;
    }
    .modal-text .text-success {
        color: #22c55e;
    }
    .modal-text .text-info {
        color: #3b82f6;
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
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        text-align: center;
        font-family: monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-500);
        letter-spacing: 2px;
        border: 2px dashed var(--border-color);
        margin: 1rem 0;
        word-break: break-all;
    }
    
    .info-row {
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-light);
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-row .label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
    }
    .info-row .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 0.125rem;
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
    .badge-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
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
    .btn-md { padding: 0.625rem 1.5rem; font-size: 0.875rem; }
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    .btn-warning { background: var(--gradient-warning); color: white; }
    .btn-success { background: var(--gradient-success); color: white; }
    .btn-danger { background: var(--gradient-danger); color: white; }
    .btn-info { background: var(--gradient-info); color: white; }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; }
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
    
    .sponsor-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    .sponsor-card:hover {
        background: var(--bg-hover);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    
    @media (max-width: 640px) {
        .modal-box { padding: 1.5rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
        .info-row .value { font-size: 0.85rem; }
        .info-grid { grid-template-columns: 1fr !important; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .card { padding: 0.875rem; }
        .sponsor-card { padding: 0.5rem 0.75rem; }
        .code-display { font-size: 1rem; padding: 0.5rem 0.75rem; }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .info-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Détails de l'utilisateur
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                ID: #{{ $user->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline">Retour</span>
            </a>
            
            @if(!$user->is_active)
            <button type="button" 
                    onclick="openGenerateCodeModal()" 
                    class="btn btn-info btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Générer code d'activation
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3 sm:p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg text-yellow-600 text-sm sm:text-base animate-fadeIn">
            {{ session('warning') }}
        </div>
    @endif

    <!-- User Information -->
    <div class="card p-3 sm:p-4 md:p-6 animate-fadeInUp delay-1">
        <div class="info-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-[var(--border-light)]">
            
            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row">
                    <span class="label">Nom complet</span>
                    <span class="value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value text-sm">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Téléphone</span>
                    <span class="value">{{ $user->phone ?? 'Non fourni' }}</span>
                </div>
            </div>

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        @if(!$user->is_active && $user->activation_code)
                            <span class="text-xs text-[var(--text-tertiary)] block mt-1">
                                Code: <span class="font-mono text-primary-500">{{ $user->activation_code }}</span>
                            </span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Rôle</span>
                    <span class="value">
                        <span class="badge {{ $user->hasRole('admin') ? 'badge-purple' : 'badge-neutral' }}">
                            {{ $user->hasRole('admin') ? 'Administrateur' : 'Utilisateur' }}
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
                <div class="info-row">
                    <span class="label">Package</span>
                    <span class="value">{{ $user->package?->name ?? 'Aucun' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Code de parrainage</span>
                    <span class="value font-mono text-primary-500 font-bold">{{ $user->sponsor_id ?? 'Aucun' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Parrain</span>
                    <span class="value">
                        @php
                            $parrain = App\Models\User::find($user->parrain_id);
                        @endphp
                        @if($parrain)
                            <a href="{{ route('admin.users.show', $parrain->id) }}" class="text-primary-500 hover:underline font-semibold">
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
                <div class="info-row">
                    <span class="label">Filleuls</span>
                    <span class="value">
                        @php
                            $filleuls = App\Models\User::where('parrain_id', $user->id)->get();
                        @endphp
                        @if($filleuls->count() > 0)
                            <span class="font-bold text-primary-500">{{ $filleuls->count() }}</span>
                            <span class="text-xs text-[var(--text-tertiary)] block">
                                @foreach($filleuls->take(5) as $filleul)
                                    <a href="{{ route('admin.users.show', $filleul->id) }}" class="text-primary-500 hover:underline">
                                        {{ $filleul->name }}
                                    </a>@if(!$loop->last), @endif
                                @endforeach
                                @if($filleuls->count() > 5)
                                    <span class="text-[var(--text-tertiary)]">et {{ $filleuls->count() - 5 }} autre(s)</span>
                                @endif
                            </span>
                        @else
                            <span class="text-[var(--text-tertiary)]">Aucun filleul</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Inscrit le</span>
                    <span class="value text-sm">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>

                <!-- ✅ PACKAGE D'ACTIVATION -->
                <div class="info-row">
                    <span class="label">Package d'activation</span>
                    <span class="value">
                        @if($user->activation_package_id)
                            @php
                                $activationPackage = App\Models\Package::find($user->activation_package_id);
                            @endphp
                            @if($activationPackage)
                                <span class="badge badge-info">
                                    {{ $activationPackage->name }}
                                </span>
                                <span class="text-xs text-[var(--text-tertiary)] block">
                                    PV: {{ $activationPackage->pv_value }} | BV: {{ $activationPackage->bv_value }}
                                </span>
                            @else
                                <span class="text-[var(--text-tertiary)]">Package non trouvé</span>
                            @endif
                        @else
                            <span class="text-[var(--text-tertiary)]">Aucun package associé</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="mt-4 pt-4 border-t border-[var(--border-color)] grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-primary-500">{{ $filleuls->count() ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Filleuls</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-500">{{ $commissionsCount ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Commissions</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Total Commissions</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-500">{{ $user->pv_balance ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">PV</p>
            </div>
        </div>

        <!-- Downlines List -->
        @if(isset($filleuls) && $filleuls->count() > 0)
        <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
            <h4 class="text-sm font-semibold text-[var(--text-primary)] mb-3">
                Liste des filleuls ({{ $filleuls->count() }})
            </h4>
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-xs">ID</th>
                            <th class="text-xs">Nom</th>
                            <th class="text-xs">Email</th>
                            <th class="text-xs">Code</th>
                            <th class="text-xs">Statut</th>
                            <th class="text-xs">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filleuls as $filleul)
                        <tr>
                            <td class="text-xs">#{{ $filleul->id }}</td>
                            <td class="text-sm">{{ $filleul->name }}</td>
                            <td class="text-xs text-[var(--text-secondary)]">{{ $filleul->email }}</td>
                            <td class="text-xs font-mono text-primary-500">{{ $filleul->sponsor_id }}</td>
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

        <!-- Actions -->
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)] flex flex-wrap gap-2 sm:gap-3">
            
            @if($user->is_active)
                <button type="button" 
                        onclick="openDeactivateModal()" 
                        class="btn btn-warning btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Désactiver
                </button>
            @else
                <button type="button" 
                        onclick="openActivateModal()" 
                        class="btn btn-success btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Activer
                </button>
                
                <button type="button" 
                        onclick="openGenerateCodeModal()" 
                        class="btn btn-info btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Générer code
                </button>
            @endif

            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>

            <button type="button" 
                    onclick="openDeleteModal()" 
                    class="btn btn-danger btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL GÉNÉRER CODE D'ACTIVATION AVEC CHOIX DU PACKAGE -->
<!-- ============================================================ -->
<div id="generateCodeModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-info">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
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
                <select name="package_id" class="input w-full" required>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Générer et envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Deactivate Modal -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
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

<!-- Activate Modal -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer l'activation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-success">activer</strong> le compte de <strong>{{ $user->name }}</strong> ?
            <br>
            L'utilisateur pourra se connecter à nouveau.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeActivateModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-success btn-sm">
                Activer
            </a>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
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
// MODAL ACTIVER
// ============================================================
function openActivateModal() {
    document.getElementById('activateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeActivateModal() {
    document.getElementById('activateModal').classList.remove('active');
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