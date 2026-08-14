<?php
// resources/views/admin/pv/index.blade.php
?>

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
        flex-wrap: wrap;
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
    
    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.5rem;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
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
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-warning {
        background: var(--gradient-warning);
        color: white;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .table-pv-history {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .table-pv-history th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        background: var(--bg-secondary);
        font-weight: 600;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table-pv-history td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-light);
    }
    .table-pv-history tr:hover td {
        background: var(--bg-hover);
    }
    
    /* Toast notifications */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 400px;
        width: 100%;
    }
    .toast {
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        animation: slideInRight 0.4s ease;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .toast-success { background: #22c55e; }
    .toast-error { background: #ef4444; }
    .toast-warning { background: #f59e0b; }
    .toast-info { background: #3b82f6; }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    
    /* Custom Confirm Dialog */
    .confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .confirm-overlay.active {
        display: flex;
    }
    .confirm-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 480px;
        width: 95%;
        animation: modalIn 0.3s ease;
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .confirm-box .icon {
        font-size: 3rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .confirm-box h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .confirm-box p {
        color: var(--text-secondary);
        font-size: 0.938rem;
        text-align: center;
        margin-bottom: 0.25rem;
    }
    .confirm-box .warning-text {
        color: #ef4444;
        font-weight: 600;
        font-size: 0.875rem;
        margin: 0.75rem 0 1.5rem 0;
        padding: 0.75rem;
        background: rgba(239, 68, 68, 0.08);
        border-radius: var(--radius-sm);
        border-left: 3px solid #ef4444;
    }
    .confirm-box .btn-group {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .confirm-box .btn-group .btn {
        min-width: 120px;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    .badge-sm {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
    .flex-1 { flex: 1; }
    .flex-wrap { flex-wrap: wrap; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 0.75rem; }
    .gap-4 { gap: 1rem; }
    .items-center { align-items: center; }
    .items-end { align-items: flex-end; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .text-center { text-align: center; }
    .text-primary-500 { color: var(--primary-500); }
    .text-green-500 { color: #22c55e; }
    .text-blue-500 { color: #3b82f6; }
    .text-red-500 { color: #ef4444; }
    .text-secondary { color: var(--text-secondary); }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .text-sm { font-size: 0.875rem; }
    .text-lg { font-size: 1.125rem; }
    .text-xl { font-size: 1.25rem; }
    .text-2xl { font-size: 1.5rem; }
    .mt-1 { margin-top: 0.25rem; }
    .mt-2 { margin-top: 0.5rem; }
    .mt-4 { margin-top: 1rem; }
    .mt-6 { margin-top: 1.5rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-4 { margin-bottom: 1rem; }
    .p-4 { padding: 1rem; }
    .p-6 { padding: 1.5rem; }
    .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
    .border-l-4 { border-left-width: 4px; }
    .border-primary-500 { border-left-color: var(--primary-500); }
    .border-green-500 { border-left-color: #22c55e; }
    .border-blue-500 { border-left-color: #3b82f6; }
    .border-red-500 { border-left-color: #ef4444; }
    .rounded-lg { border-radius: var(--radius-lg); }
    .overflow-x-auto { overflow-x: auto; }
    .hidden { display: none; }
    .inline-block { display: inline-block; }
    .w-4 { width: 1rem; }
    .w-5 { width: 1.25rem; }
    .w-6 { width: 1.5rem; }
    .w-8 { width: 2rem; }
    .h-4 { height: 1rem; }
    .h-5 { height: 1.25rem; }
    .h-6 { height: 1.5rem; }
    .h-8 { height: 2rem; }
    .mr-2 { margin-right: 0.5rem; }
    .-mt-1 { margin-top: -0.25rem; }
    .flex-shrink-0 { flex-shrink: 0; }
    
    .bg-green-500\/10 { background: rgba(34, 197, 94, 0.1); }
    .bg-red-500\/10 { background: rgba(239, 68, 68, 0.1); }
    .border-green-500\/20 { border-color: rgba(34, 197, 94, 0.2); }
    .border-red-500\/20 { border-color: rgba(239, 68, 68, 0.2); }
    .animate-fadeIn { animation: fadeIn 0.4s ease; }
    .animate-fadeInUp { animation: fadeInUp 0.4s ease; }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Team PV Origin Styles */
    .team-pv-origin {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-top: 1rem;
        border: 1px solid var(--border-color);
    }
    .team-pv-origin .title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .team-pv-origin .title svg {
        width: 1rem;
        height: 1rem;
        color: var(--primary-500);
    }
    .team-pv-origin .origin-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
        border-bottom: 1px solid var(--border-light);
        font-size: 0.875rem;
    }
    .team-pv-origin .origin-item:last-child {
        border-bottom: none;
    }
    .team-pv-origin .origin-item .level {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.7rem;
        min-width: 45px;
    }
    .team-pv-origin .origin-item .name {
        color: var(--text-primary);
        flex: 1;
        margin-left: 0.75rem;
    }
    .team-pv-origin .origin-item .pv {
        font-weight: 700;
        color: var(--primary-500);
    }
    .team-pv-origin .total {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        font-size: 0.938rem;
    }
    .team-pv-origin .total .label {
        color: var(--text-secondary);
    }
    .team-pv-origin .total .value {
        color: var(--primary-500);
    }
    .team-pv-origin .empty {
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-align: center;
        padding: 0.5rem 0;
    }
    
    /* Level badge colors */
    .level-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 700;
    }
    .level-1 { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .level-2 { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .level-3 { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .level-4 { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .level-5 { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .level-6 { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
    .level-7 { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
    .level-8 { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
    .level-9 { background: rgba(234, 179, 8, 0.15); color: #eab308; }
    .level-10 { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    
    @media (max-width: 768px) {
        .user-profile-header {
            flex-direction: column;
            text-align: center;
        }
        .grid-3 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .grid-2 {
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
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .toast-container {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
        .confirm-box .btn-group {
            flex-direction: column;
        }
        .confirm-box .btn-group .btn {
            width: 100%;
        }
        .user-profile-info .text-right {
            text-align: center;
        }
        .team-pv-origin .origin-item {
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        .team-pv-origin .origin-item .name {
            flex: 1 1 100%;
            margin-left: 0;
        }
    }
    
    @media (max-width: 480px) {
        .flex-wrap-xs {
            flex-wrap: wrap;
        }
        .text-2xl {
            font-size: 1.25rem;
        }
        .table-pv-history {
            font-size: 0.75rem;
        }
        .table-pv-history th,
        .table-pv-history td {
            padding: 0.4rem 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <div id="toastContainer" class="toast-container"></div>

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Gestion des PV
            </h1>
            <p class="text-sm sm:text-base text-secondary mt-0.5 sm:mt-1">
                Gestion des Points de Volume pour <strong>{{ $user->name }}</strong>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ url('/admin/pv/import?user_id=' . $user->id) }}" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span class="hidden xs:inline">Importer des PV</span>
            </a>
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline">Retour</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
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

    <!-- User Profile -->
    <div class="user-profile-header animate-fadeInUp">
        <div class="user-profile-avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="user-profile-info flex-1">
            <h2 id="userNameDisplay">{{ $user->name }}</h2>
            <div class="subtitle">
                {{ $user->email }} 
                @if($user->phone && $user->phone !== 'N/A')
                    • {{ $user->phone }}
                @endif
            </div>
            <div>
                <span class="badge badge-purple">
                    {{ $user->rank ?? 'Distributeur' }} (Niv. {{ $user->rank_level ?? 1 }})
                </span>
                <span class="badge badge-info">
                    Code: {{ $user->sponsor_id ?? 'N/A' }}
                </span>
                @if($user->is_active)
                    <span class="badge badge-success">Actif</span>
                @else
                    <span class="badge badge-danger">Inactif</span>
                @endif
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs text-secondary">Inscrit le</div>
            <div class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</div>
            @if($user->parrain_id)
                <div class="text-xs text-secondary mt-1">Parrain</div>
                <div class="font-medium text-primary-500">
                    @php
                        $parrain = App\Models\User::find($user->parrain_id);
                    @endphp
                    {{ $parrain?->name ?? 'Inconnu' }}
                </div>
            @endif
        </div>
    </div>

    <!-- PV Statistics -->
    <div class="grid-3 animate-fadeInUp delay-1">
        <div class="pv-display-card border-l-4 border-primary-500">
            <div class="pv-label">PV Total</div>
            <div class="pv-value" id="pv_balance_display">{{ number_format($user->pv_balance ?? 0, 1, ',', ' ') }}</div>
            <div class="text-xs text-secondary mt-1">Points de Volume totaux</div>
        </div>
        <div class="pv-display-card border-l-4 border-green-500">
            <div class="pv-label">PV Mensuel</div>
            <div class="pv-value text-green-500" id="monthly_pv_display">{{ number_format($user->monthly_pv ?? 0, 1, ',', ' ') }}</div>
            <div class="text-xs text-secondary mt-1">Points de Volume du mois</div>
        </div>
        <div class="pv-display-card border-l-4 border-blue-500">
            <div class="pv-label">PV Equipe</div>
            <div class="pv-value text-blue-500" id="team_pv_display">{{ number_format($user->team_pv ?? 0, 1, ',', ' ') }}</div>
            <div class="text-xs text-secondary mt-1">PV genere par les filleuls</div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="grid-2 animate-fadeInUp delay-2">
        <div class="card">
            <h3 class="text-lg font-semibold text-[var(--text-primary)] mb-4">
                Modifier les PV
            </h3>
            
            <form method="POST" action="{{ route('admin.pv.update', $user->id) }}" id="pvForm">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label for="pv_balance">PV Total <span class="required">*</span></label>
                        <input type="text" id="pv_balance" name="pv_balance" 
                               value="{{ number_format($user->pv_balance ?? 0, 1, '.', '') }}" 
                               step="0.1" required
                               oninput="this.value = this.value.replace(',', '.')">
                        <small class="help-text">Points de Volume totaux de l'utilisateur. (Ex: 87.5)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_pv">PV Mensuel <span class="required">*</span></label>
                        <input type="text" id="monthly_pv" name="monthly_pv" 
                               value="{{ number_format($user->monthly_pv ?? 0, 1, '.', '') }}" 
                               step="0.1" required
                               oninput="this.value = this.value.replace(',', '.')">
                        <small class="help-text">Points de Volume du mois en cours. (Ex: 87.5)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="package_id">Package</label>
                    <select id="package_id" name="package_id">
                        <option value="">Aucun package</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ $user->package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} ({{ number_format($package->pv_value, 1, ',', ' ') }} PV • {{ number_format($package->bv_value, 1, ',', ' ') }} BV)
                            </option>
                        @endforeach
                    </select>
                    <small class="help-text">Le package affecte le PV et le grade de l'utilisateur.</small>
                </div>
                
                <div class="flex flex-wrap gap-3 mt-6">
                    <button type="submit" class="btn btn-primary flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mettre a jour
                    </button>
                    <button type="button" onclick="openDeletePVModal()" class="btn btn-danger">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Reinitialiser
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================= -->
        <!-- SECTION: PROVENANCE DU PV EQUIPE (TOUS NIVEAUX) -->
        <!-- ============================================= -->
        <div class="card">
            <h3 class="text-lg font-semibold text-[var(--text-primary)] mb-4">
                <svg class="inline-block w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Provenance du PV Equipe
            </h3>
            <p class="text-sm text-secondary mb-3">
                Liste des membres de l'equipe (tous niveaux) qui contribuent au PV d'equipe de <strong>{{ $user->name }}</strong>
            </p>
            
            @php
                $totalTeamPV = 0;
                $teamMembers = [];
                
                // Fonction récursive pour récupérer TOUS les descendants avec PV > 0
                // La récursion continue MEME si un membre intermédiaire a 0 PV
                function getAllDescendantsWithPV($userId, $level = 1, &$members = []) {
                    $children = App\Models\User::where('parrain_id', $userId)
                        ->where('is_active', true)
                        ->get();
                    
                    foreach ($children as $child) {
                        // On continue la récursion pour explorer TOUS les niveaux
                        // MEME si l'enfant a 0 PV (car ses descendants peuvent avoir du PV)
                        $childPV = $child->pv_balance ?? 0;
                        
                        // On ajoute l'enfant UNIQUEMENT s'il a du PV > 0
                        if ($childPV > 0) {
                            $members[] = [
                                'id' => $child->id,
                                'name' => $child->name,
                                'pv' => $childPV,
                                'level' => $level,
                            ];
                        }
                        
                        // 🔥 IMPORTANT: On explore TOUS les descendants, même si l'enfant a 0 PV
                        getAllDescendantsWithPV($child->id, $level + 1, $members);
                    }
                    return $members;
                }
                
                $teamMembers = getAllDescendantsWithPV($user->id, 1);
                $totalTeamPV = array_sum(array_column($teamMembers, 'pv'));
                
                // Compter le nombre de niveaux différents
                $maxLevel = !empty($teamMembers) ? max(array_column($teamMembers, 'level')) : 0;
            @endphp
            
            <div class="team-pv-origin">
                <div class="title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Origine des PV 
                    <span class="text-xs font-normal text-secondary ml-1">
                        ({{ count($teamMembers) }} membres actifs sur {{ $maxLevel }} niveaux)
                    </span>
                </div>
                
                @if(count($teamMembers) > 0)
                    @foreach($teamMembers as $member)
                        @php
                            $levelClass = 'level-' . min($member['level'], 10);
                        @endphp
                        <div class="origin-item">
                            <span class="level-badge {{ $levelClass }}">Niv {{ $member['level'] }}</span>
                            <span class="name">
                                <a href="{{ route('admin.users.show', $member['id']) }}" class="text-primary-500 hover:underline">
                                    {{ $member['name'] }}
                                </a>
                            </span>
                            <span class="pv">{{ number_format($member['pv'], 1, ',', ' ') }} PV</span>
                        </div>
                    @endforeach
                    
                    <div class="total">
                        <span class="label">Total PV Equipe</span>
                        <span class="value">{{ number_format($totalTeamPV, 1, ',', ' ') }} PV</span>
                    </div>
                @else
                    <div class="empty">
                        Aucun membre dans l'equipe de {{ $user->name }} avec des PV
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="card animate-fadeInUp delay-3">
        <div class="flex flex-wrap items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-[var(--text-primary)]">
                <svg class="inline-block w-5 h-5 text-info-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historique des PV
            </h3>
            <span class="text-sm text-secondary" id="history_count">
                Total: {{ isset($pvHistory) ? count($pvHistory) : 0 }} entrees
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="table-pv-history">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    @if(isset($pvHistory) && count($pvHistory) > 0)
                        @foreach($pvHistory as $entry)
                            <tr id="history-row-{{ $entry->id }}">
                                <td>{{ $entry->period ?? '-' }}</td>
                                <td>
                                    <span class="badge-sm badge-info">{{ ucfirst($entry->type) }}</span>
                                </td>
                                <td class="font-bold text-primary-500">{{ number_format($entry->amount, 1, ',', ' ') }}</td>
                                <td>{{ $entry->notes ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteHistory({{ $entry->id }})">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr id="noHistoryRow">
                            <td colspan="6" class="text-center text-secondary py-4">
                                Aucun historique de PV trouve
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Reset Confirmation -->
<div id="deletePVModal" class="confirm-overlay">
    <div class="confirm-box">
        <div class="icon">⚠️</div>
        <h3>Reinitialisation Complete</h3>
        <p>
            Etes-vous sur de vouloir reinitialiser <strong class="text-red-500">{{ $user->name }}</strong> ?
        </p>
        <div class="warning-text">
            Cette action est IRREVERSIBLE !<br>
            Tous les PV seront remis a 0 et le rang sera "Distributeur".
        </div>
        <div class="btn-group">
            <button class="btn btn-secondary" onclick="closeModal('deletePVModal')">Annuler</button>
            <button class="btn btn-danger" onclick="showResetConfirm()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Confirmer
            </button>
        </div>
    </div>
</div>

<script>
// ============================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    
    let icon = '';
    if (type === 'success') icon = '✓';
    else if (type === 'error') icon = '✗';
    else if (type === 'warning') icon = '⚠';
    else icon = 'ℹ';
    
    toast.innerHTML = '<span>' + icon + '</span> ' + message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.4s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

// ============================================================
// MODAL MANAGEMENT
// ============================================================
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function openDeletePVModal() {
    openModal('deletePVModal');
}

// Close modal on outside click
document.querySelectorAll('.confirm-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.confirm-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// ============================================================
// RESET CONFIRMATION
// ============================================================
function showResetConfirm() {
    closeModal('deletePVModal');
    
    showToast(
        'Reinitialisation en cours... Veuillez patienter.',
        'warning'
    );
    
    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.pv.reset', $user->id) }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';
    form.appendChild(methodInput);
    
    const confirmInput = document.createElement('input');
    confirmInput.type = 'hidden';
    confirmInput.name = 'confirmed';
    confirmInput.value = '1';
    form.appendChild(confirmInput);
    
    document.body.appendChild(form);
    form.submit();
}

// ============================================================
// DELETE HISTORY (AJAX)
// ============================================================
function deleteHistory(historyId) {
    showToast(
        'Suppression de l\'entree...',
        'warning'
    );
    
    const url = '{{ url('admin/pv/history') }}/' + historyId;
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('history-row-' + historyId);
            if (row) {
                row.remove();
            }
            
            const counter = document.getElementById('history_count');
            const currentCount = parseInt(counter.textContent.match(/\d+/)?.[0] || 0);
            counter.textContent = 'Total: ' + (currentCount - 1) + ' entrees';
            
            if (data.user) {
                document.getElementById('pv_balance_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.pv_balance);
                document.getElementById('monthly_pv_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.monthly_pv);
                document.getElementById('team_pv_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.team_pv);
                    
                document.getElementById('pv_balance').value = data.user.pv_balance;
                document.getElementById('monthly_pv').value = data.user.monthly_pv;
            }
            
            showToast('Historique supprime avec succes', 'success');
        } else {
            showToast('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la suppression', 'error');
    });
}

// ============================================================
// FORM HANDLING
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const pvForm = document.getElementById('pvForm');
    if (pvForm) {
        pvForm.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[name="pv_balance"], input[name="monthly_pv"]');
            inputs.forEach(input => {
                if (input.value) {
                    input.value = input.value.replace(',', '.');
                }
            });
        });
    }
});
</script>
@endsection