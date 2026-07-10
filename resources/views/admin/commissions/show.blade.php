{{-- resources/views/admin/commissions/show.blade.php --}}

@extends('admin.layouts.app')

@push('styles')
<style>
    /* ============================================================
       STYLES PRINCIPAUX
       ============================================================ */
    
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
    .detail-value.amount {
        font-size: 1.5rem;
        font-weight: 800;
    }
    .detail-value.amount-positive { color: #22c55e; }
    .detail-value.amount-negative { color: #ef4444; }
    
    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-retail { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-bonus { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    /* Avatar */
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }
    .avatar-lg { width: 3rem; height: 3rem; font-size: 1rem; }
    .avatar-gradient {
        background: var(--gradient-primary);
        color: white;
    }
    
    /* Timeline */
    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .timeline-item:last-child { border-bottom: none; }
    .timeline-dot {
        width: 0.625rem;
        height: 0.625rem;
        border-radius: 50%;
        margin-top: 0.25rem;
        flex-shrink: 0;
    }
    .timeline-dot-success { background: #22c55e; }
    .timeline-dot-pending { background: #f59e0b; }
    .timeline-dot-info { background: #3b82f6; }
    .timeline-dot-danger { background: #ef4444; }
    
    /* Buttons */
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
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    .btn-success { background: #22c55e; color: white; }
    .btn-success:hover { background: #16a34a; transform: translateY(-2px); }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; transform: translateY(-2px); }
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    
    /* Card */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    /* Table */
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; }
    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
    .table tbody tr:hover {
        background: var(--bg-hover);
    }
    
    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    /* ============================================================
       RESPONSIVE
       ============================================================ */
    
    @media (max-width: 640px) {
        .detail-card {
            padding: 0.875rem;
        }
        .detail-value {
            font-size: 0.875rem;
        }
        .detail-value.amount {
            font-size: 1.25rem;
        }
        .avatar-lg {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 0.75rem;
        }
        .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .btn-sm {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }
        .card {
            padding: 0.875rem;
        }
        .table thead th,
        .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .type-badge {
            font-size: 0.6rem;
            padding: 0.1rem 0.4rem;
        }
        .header-actions {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .detail-grid {
            grid-template-columns: 1fr !important;
        }
        .timeline-item {
            padding: 0.375rem 0;
        }
    }
    
    @media (max-width: 480px) {
        .detail-card {
            padding: 0.75rem;
        }
        .detail-value.amount {
            font-size: 1.1rem;
        }
        .card {
            padding: 0.75rem;
        }
        .table thead th,
        .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
        .badge {
            font-size: 0.55rem;
            padding: 0.1rem 0.375rem;
        }
        .type-badge {
            font-size: 0.55rem;
            padding: 0.075rem 0.3rem;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .detail-card {
            padding: 1rem;
        }
        .detail-value.amount {
            font-size: 1.3rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- ============================================================
    EN-TÊTE
    ============================================================ -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commission #{{ $commission->id }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Details de la commission
            </p>
        </div>
        <div class="header-actions flex flex-wrap gap-2">
            {{-- ✅ Route corrigée : admin.commissions (sans .index) --}}
            <a href="{{ route('admin.commissions') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            @if($commission->status == 'pending')
                {{-- ✅ Route corrigée : admin.commissions.approve --}}
                <form action="{{ route('admin.commissions.approve', $commission->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approuver
                    </button>
                </form>
                {{-- ✅ Route corrigée : admin.commissions.reject --}}
                <form action="{{ route('admin.commissions.reject', $commission->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rejeter
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- ============================================================
    INFORMATIONS PRINCIPALES
    ============================================================ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        
        <!-- Montant -->
        <div class="detail-card text-center">
            <p class="detail-label">Montant</p>
            <p class="detail-value amount amount-positive">
                +${{ number_format($commission->amount, 2) }}
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                Taux: {{ $commission->percentage }}%
            </p>
        </div>
        
        <!-- Type -->
        <div class="detail-card text-center">
            <p class="detail-label">Type</p>
            <p class="detail-value">
                <span class="type-badge type-badge-{{ $commission->type }}">
                    {{ $commission->type_label ?? ucfirst($commission->type) }}
                </span>
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                Commission {{ $commission->type }}
            </p>
        </div>
        
        <!-- Statut -->
        <div class="detail-card text-center">
            <p class="detail-label">Statut</p>
            <p class="detail-value">
                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : ($commission->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ $commission->status == 'paid' ? 'Payee' : ($commission->status == 'pending' ? 'En attente' : 'Annulee') }}
                </span>
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($commission->paid_at)
                    Payee le {{ $commission->paid_at->format('d/m/Y H:i') }}
                @else
                    Non payee
                @endif
            </p>
        </div>
    </div>

    <!-- ============================================================
    DETAILS UTILISATEUR ET SOURCE
    ============================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        
        <!-- Utilisateur -->
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
                Utilisateur
            </h3>
            
            <div class="flex items-center gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="avatar avatar-lg avatar-gradient">
                    {{ $commission->user?->name ? substr($commission->user->name, 0, 2) : 'N/A' }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $commission->user?->name ?? 'N/A' }}
                    </p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)] truncate">
                        {{ $commission->user?->email ?? 'Email non disponible' }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        ID: #{{ $commission->user_id }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Source -->
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
                Source
            </h3>
            
            <div class="flex items-center gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="avatar avatar-lg avatar-gradient">
                    {{ $commission->fromUser?->name ? substr($commission->fromUser->name, 0, 2) : 'S' }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $commission->fromUser?->name ?? 'Systeme' }}
                    </p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)] truncate">
                        {{ $commission->fromUser?->email ?? 'Genere automatiquement' }}
                    </p>
                    @if($commission->fromUser)
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            Parrain: {{ $commission->fromUser->parrain?->name ?? 'Aucun' }}
                        </p>
                    @endif
                </div>
            </div>
            
            @if($commission->package)
            <div class="mt-2 sm:mt-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Package associe</p>
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    {{ $commission->package->name }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    ${{ number_format($commission->package->price, 2) }}
                </p>
            </div>
            @endif
        </div>
    </div>

    <!-- ============================================================
    DESCRIPTION
    ============================================================ -->
    @if($commission->description)
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">
            Description
        </h3>
        <p class="text-[var(--text-secondary)] text-sm">{{ $commission->description }}</p>
    </div>
    @endif

    <!-- ============================================================
    CHRONOLOGIE
    ============================================================ -->
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
            Chronologie
        </h3>

        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-info"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission creee</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->created_at->format('d/m/Y H:i:s') }}
                    <span class="text-[10px]">({{ $commission->created_at->diffForHumans() }})</span>
                </p>
            </div>
        </div>

        @if($commission->status == 'paid')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission payee</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->paid_at?->format('d/m/Y H:i:s') ?? 'N/A' }}
                </p>
            </div>
        </div>
        @elseif($commission->status == 'pending')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-pending"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">En attente de paiement</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    Cette commission sera payee automatiquement lors du prochain cycle
                </p>
            </div>
        </div>
        @elseif($commission->status == 'cancelled')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-danger"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission annulee</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->updated_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- ============================================================
    COMMISSIONS SIMILAIRES
    ============================================================ -->
    @if(isset($similarCommissions) && $similarCommissions->count() > 0)
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
            Commissions similaires
        </h3>
        
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Type</th>
                        <th>Montant</th>
                        <th class="hidden md:table-cell">Statut</th>
                        <th class="hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($similarCommissions as $similar)
                        <tr>
                            <td class="font-medium text-sm">
                                {{ $similar->user?->name ?? 'N/A' }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="type-badge type-badge-{{ $similar->type }}">
                                    {{ $similar->type_label ?? ucfirst($similar->type) }}
                                </span>
                            </td>
                            <td class="amount-positive">+${{ number_format($similar->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $similar->status == 'paid' ? 'badge-success' : ($similar->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $similar->status == 'paid' ? 'Paye' : ($similar->status == 'pending' ? 'En attente' : 'Annule') }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs">
                                {{ $similar->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection