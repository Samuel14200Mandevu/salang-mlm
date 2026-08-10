@extends('admin.layouts.app')

@push('styles')
<style>
    .pv-stats-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .pv-stats-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .user-profile-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .user-profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .user-profile-info h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .user-profile-info .subtitle {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .user-profile-info .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .pv-display-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }
    .pv-display-card .pv-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-500);
    }
    .pv-display-card .pv-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .form-group label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .form-group input[readonly] {
        background: var(--bg-secondary);
        cursor: not-allowed;
    }
    .form-group .help-text {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .form-group .help-text-info {
        background: rgba(59, 130, 246, 0.08);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        border-left: 3px solid #3b82f6;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .form-group .help-text-info strong {
        color: var(--text-primary);
    }
    
    .team-pv-display {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .team-pv-display .label {
        font-size: 0.813rem;
        color: var(--text-secondary);
    }
    .team-pv-display .value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--primary-500);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.813rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
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
    .btn-warning {
        background: var(--gradient-warning);
        color: white;
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }
    .btn-secondary:hover {
        background: var(--bg-hover);
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
    .btn-info {
        background: var(--gradient-info);
        color: white;
    }
    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
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
    
    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.5rem;
    }
    
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        max-width: 500px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalIn 0.3s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    .modal-header h2 {
        font-size: 1.125rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .modal-close {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0 0.5rem;
        transition: color 0.2s ease;
    }
    .modal-close:hover {
        color: var(--text-primary);
    }
    
    .search-section {
        background: var(--bg-secondary);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .search-section .search-input {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        min-width: 200px;
    }
    .search-section .search-input:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    @media (max-width: 768px) {
        .user-profile-header {
            flex-direction: column;
            text-align: center;
        }
        .grid-3 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .user-profile-avatar {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        .pv-display-card .pv-value {
            font-size: 1.75rem;
        }
        .search-section {
            flex-direction: column;
        }
        .search-section .search-input {
            width: 100%;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Gestion des PV
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gestion des Points de Volume pour <strong>{{ $user->name }}</strong>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline">Retour</span>
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- ===== PROFIL DE L'UTILISATEUR ===== -->
    <div class="user-profile-header animate-fadeInUp">
        <div class="user-profile-avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="user-profile-info flex-1">
            <h2>{{ $user->name }}</h2>
            <div class="subtitle">
                {{ $user->email }} 
                @if($user->phone && $user->phone !== 'N/A')
                    • {{ $user->phone }}
                @endif
            </div>
            <div>
                
                <span class="badge badge-purple">
                    {{ $user->rank_name ?? 'Distributeur' }} (Niv. {{ $user->rank_level ?? 1 }})
                </span>
                <span class="badge badge-info">
                    Code: {{ $user->sponsor_id ?? 'N/A' }}
                </span>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs text-[var(--text-secondary)]">Inscrit le</div>
            <div class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</div>
            @if($user->parrain_id)
                <div class="text-xs text-[var(--text-secondary)] mt-1">Parrain</div>
                <div class="font-medium text-primary-500">
                    @php
                        $parrain = App\Models\User::find($user->parrain_id);
                    @endphp
                    {{ $parrain?->name ?? 'Inconnu' }}
                </div>
            @endif
        </div>
    </div>

    <!-- ===== STATISTIQUES PV ===== -->
    <div class="grid-3 animate-fadeInUp delay-1">
        <div class="pv-display-card border-l-4 border-primary-500">
            <div class="pv-label">PV Total</div>
            <div class="pv-value">{{ number_format($user->pv_balance ?? 0, 0, ',', ' ') }}</div>
            <div class="text-xs text-[var(--text-secondary)] mt-1">Points de Volume totaux</div>
        </div>
        <div class="pv-display-card border-l-4 border-green-500">
            <div class="pv-label">PV Mensuel</div>
            <div class="pv-value text-green-500">{{ number_format($user->monthly_pv ?? 0, 0, ',', ' ') }}</div>
            <div class="text-xs text-[var(--text-secondary)] mt-1">Points de Volume du mois</div>
        </div>
        <div class="pv-display-card border-l-4 border-blue-500">
            <div class="pv-label">PV Équipe</div>
            <div class="pv-value text-blue-500">{{ number_format($user->team_pv ?? 0, 0, ',', ' ') }}</div>
            <div class="text-xs text-[var(--text-secondary)] mt-1">PV généré par les filleuls</div>
        </div>
    </div>

    <!-- ===== FORMULAIRE DE MODIFICATION ===== -->
    <div class="card animate-fadeInUp delay-2">
        <h3 class="text-lg font-semibold text-[var(--text-primary)] mb-4">
            <svg class="inline-block w-5 h-5 text-warning-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier les PV
        </h3>
        
        <form method="POST" action="{{ route('admin.pv.update', $user->id) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="pv_balance">PV Total <span class="required">*</span></label>
                    <input type="number" id="pv_balance" name="pv_balance" min="0" step="1" value="{{ $user->pv_balance ?? 0 }}" required>
                    <small class="help-text">Points de Volume totaux de l'utilisateur. Affecte le grade et les commissions.</small>
                </div>
                
                <div class="form-group">
                    <label for="monthly_pv">PV Mensuel <span class="required">*</span></label>
                    <input type="number" id="monthly_pv" name="monthly_pv" min="0" step="1" value="{{ $user->monthly_pv ?? 0 }}" required>
                    <small class="help-text">Points de Volume du mois en cours. Réinitialisé le 7 de chaque mois.</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="package_id">Package</label>
                <select id="package_id" name="package_id">
                    <option value="">Aucun package</option>
                    @foreach($packages ?? [] as $package)
                        <option value="{{ $package->id }}" {{ $user->package_id == $package->id ? 'selected' : '' }}>
                            {{ $package->name }} ({{ $package->pv_value }} PV • {{ $package->bv_value }} BV)
                        </option>
                    @endforeach
                </select>
                <small class="help-text">Le package affecte le PV et le grade de l'utilisateur.</small>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-6">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <button type="button" onclick="openAddMonthlyPV()" class="btn btn-success">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    + PV Mensuel
                </button>
                <button type="button" onclick="openDeletePVModal()" class="btn btn-danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Réinitialiser PV
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: AJOUTER PV MENSUEL ===== -->
<div id="addMonthlyPvModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter PV Mensuel
            </h2>
            <button class="modal-close" onclick="closeModal('addMonthlyPvModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.pv.monthly', $user->id) }}">
            @csrf
            <div class="form-group">
                <label>Utilisateur</label>
                <input type="text" value="{{ $user->name }}" disabled class="bg-[var(--bg-secondary)]" style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border-color); border-radius:var(--radius-md);">
            </div>
            
            <div class="form-group">
                <label for="modal_monthly_pv_amount">PV à ajouter <span class="required">*</span></label>
                <input type="number" id="modal_monthly_pv_amount" name="amount" min="1" step="1" required placeholder="Ex: 100" style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border-color); border-radius:var(--radius-md);">
                <small class="help-text">Ce montant sera ajouté au PV mensuel et au PV total.</small>
            </div>
            
            <div class="form-group">
                <label for="modal_monthly_pv_notes">Notes (optionnel)</label>
                <input type="text" id="modal_monthly_pv_notes" name="notes" placeholder="Raison de l'ajout..." style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border-color); border-radius:var(--radius-md);">
            </div>
            
            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMonthlyPvModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: RÉINITIALISER PV ===== -->
<div id="deletePVModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <svg class="w-5 h-5 text-danger-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Réinitialiser les PV
            </h2>
            <button class="modal-close" onclick="closeModal('deletePVModal')">&times;</button>
        </div>
        <div class="text-center">
            <div class="modal-icon modal-icon-danger mx-auto mb-4" style="width:4rem;height:4rem;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(239,68,68,0.1);color:#ef4444;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-[var(--text-primary)] mb-2">Réinitialiser les PV</h3>
            <p class="text-[var(--text-secondary)] mb-4">
                Êtes-vous sûr de vouloir réinitialiser <strong>tous les PV</strong> de <strong>{{ $user->name }}</strong> ?
                <br>
                <span class="text-danger text-sm">Cette action est irréversible !</span>
            </p>
            <form method="POST" action="{{ route('admin.pv.update', $user->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="pv_balance" value="0">
                <input type="hidden" name="monthly_pv" value="0">
                <input type="hidden" name="team_pv" value="0">
                <input type="hidden" name="package_id" value="">
                <div class="flex gap-2 justify-center">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deletePVModal')">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================================
// GESTION DES MODALS
// ============================================================
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function openAddMonthlyPV() {
    openModal('addMonthlyPvModal');
}

function openRecalculateRank() {
    openModal('recalculateRankModal');
}

function openDeletePVModal() {
    openModal('deletePVModal');
}

// Fermer les modals en cliquant à l'extérieur
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Raccourci clavier ESC pour fermer les modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endsection