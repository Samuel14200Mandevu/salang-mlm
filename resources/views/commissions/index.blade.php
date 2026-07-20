{{-- resources/views/commissions/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .commission-row {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.65rem;
        font-weight: 600;
    }
    .type-badge-sponsor { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-direct { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-indirect { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-leadership { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-retail { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
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
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    
    .table-wrap { overflow-x: auto; }
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
    .table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }
    
    .filters-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    /* ===== MODAL ===== */
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
        padding: 1.5rem;
        max-width: 700px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9) translateY(20px);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
    }
    .modal-overlay.active .modal-box {
        transform: scale(1) translateY(0);
    }
    .modal-box .modal-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: var(--bg-secondary);
        border: none;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text-secondary);
    }
    .modal-box .modal-close:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        transform: rotate(90deg);
    }
    .modal-box .modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .modal-box .modal-subtitle {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }
    .modal-box .modal-body {
        color: var(--text-secondary);
        font-size: 0.85rem;
        line-height: 1.6;
    }
    .modal-box .modal-body .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }
    .modal-box .modal-body .detail-item {
        background: var(--bg-secondary);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
    }
    .modal-box .modal-body .detail-item .label {
        font-size: 0.55rem;
        text-transform: uppercase;
        color: var(--text-tertiary);
        letter-spacing: 0.05em;
    }
    .modal-box .modal-body .detail-item .value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.85rem;
    }
    .modal-box .modal-body .detail-item .value.primary { color: var(--primary-500); }
    .modal-box .modal-body .detail-item .value.success { color: #22c55e; }
    .modal-box .modal-body .detail-item .value.warning { color: #f59e0b; }
    .modal-box .modal-body .calculation-box {
        background: rgba(59,130,246,0.06);
        padding: 0.75rem;
        border-radius: var(--radius-md);
        border-left: 3px solid var(--primary-500);
        margin: 0.5rem 0;
    }
    .modal-box .modal-body .calculation-box .calc-label {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .modal-box .modal-body .calculation-box .calc-formula {
        font-family: monospace;
        font-size: 0.75rem;
        color: var(--text-primary);
        margin-top: 0.15rem;
        padding: 0.3rem 0.5rem;
        background: var(--bg-card);
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
    }
    .modal-box .modal-body .calculation-box .calc-result {
        font-weight: 700;
        color: var(--primary-500);
        font-size: 0.9rem;
        margin-top: 0.15rem;
    }
    .modal-box .modal-footer {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .clickable-row {
        cursor: pointer;
    }
    .clickable-row:hover .view-detail-link {
        color: var(--primary-500);
    }
    .view-detail-link {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        transition: color 0.2s ease;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    
    .item-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .item-badge-package {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }
    .item-badge-product {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
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
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    .delay-7 { animation-delay: 0.35s; }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.65rem;
        }
        .type-badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .card { padding: 0.875rem; }
        .filters-wrapper { flex-direction: column; align-items: stretch; }
        .filters-wrapper .input { width: 100% !important; }
        .stats-grid { grid-template-columns: 1fr 1fr !important; }
        .modal-box .modal-body .detail-grid { grid-template-columns: 1fr; }
        .modal-box { padding: 1rem; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
<div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
    <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mes Commissions</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Suivez vos gains en detail</p>
    </div>
    <div class="flex flex-wrap gap-1.5 sm:gap-2">
        <!--  Bouton Export PDF -->
        <a href="{{ route('commissions.export-pdf', request()->all()) }}" 
           class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter PDF
        </a>
        <a href="{{ route('commissions.dashboard') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Tableau de bord
        </a>
        <a href="{{ route('commissions.levels') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Par niveau
        </a>
    </div>
</div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total gagne</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Paye</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format(($stats['total'] ?? 0) - ($stats['pending'] ?? 0), 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['total_count'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
<div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
    <!-- Recherche - Gauche (prend tout l'espace sur mobile) -->
    <div class="relative flex-1 min-w-[120px] sm:min-w-[180px] w-full sm:w-auto">
        <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </span>
        <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-7 sm:pl-9 text-sm sm:text-base w-full">
    </div>

    <!-- Filtres - Droite (se replie sur mobile) -->
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 ml-auto w-full sm:w-auto">
        <select id="typeFilter" class="input flex-1 sm:flex-none min-w-[80px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les types</option>
            <option value="sponsor">Sponsor</option>
            <option value="direct">Direct</option>
            <option value="indirect">Indirect</option>
            <option value="leadership">Leadership</option>
            <option value="retail">Retail</option>
        </select>
        <select id="statusFilter" class="input flex-1 sm:flex-none min-w-[80px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="paid">Paye</option>
            <option value="pending">En attente</option>
        </select>
        <input type="date" id="dateFrom" class="input flex-1 sm:flex-none min-w-[80px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Du">
        <input type="date" id="dateTo" class="input flex-1 sm:flex-none min-w-[80px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Au">
    </div>
</div>
    </div>

    <br>

    <!-- Commission List -->
    <div class="card animate-fadeInUp delay-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des commissions</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $commissions->total() ?? 0 }} commissions</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Item</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Description</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-row clickable-row" 
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}"
                            data-date="{{ $commission->created_at->format('Y-m-d') }}"
                            onclick="openCommissionDetails({{ $commission->id }})">
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    {{ ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                @if($commission->package_id)
                                    <span class="item-badge item-badge-package">Package</span>
                                    <span class="text-xs">{{ $commission->package->name ?? 'N/A' }}</span>
                                @elseif($commission->product_id)
                                    <span class="item-badge item-badge-product">Produit</span>
                                    <span class="text-xs">{{ $commission->product->name ?? 'N/A' }}</span>
                                @else
                                    <span class="text-[var(--text-tertiary)] text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden lg:table-cell">
                                {{ Str::limit($commission->description ?? '-', 30) }}
                            </td>
                            <td class="text-right font-bold text-green-500 text-sm sm:text-base">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ $commission->status == 'paid' ? 'Paye' : 'En attente' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commissions apparaîtront lorsque vous developperez votre reseau</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>

    <!-- Distribution by Type -->
    @if(!empty($stats['by_type']))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-7">
        @foreach($stats['by_type'] as $type => $data)
            <div class="card-stats border-l-4 border-{{ $data['color'] ?? 'primary' }}-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $data['label'] }}</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-{{ $data['color'] ?? 'primary' }}-500">
                    ${{ number_format($data['total'], 2) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $data['count'] }} commission(s)</p>
            </div>
        @endforeach
    </div>
    @endif


    <br>
    <!-- Quick Navigation -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-7">
        <a href="{{ route('commissions.dashboard') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Tableau de bord
        </a>
        <a href="{{ route('commissions.levels') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Commissions par niveau
        </a>
    </div>
</div>

<!-- ===== MODAL DETAILS ===== -->
<div id="commissionModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeCommissionModal()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div id="modalContent">
            <!-- Contenu injecte par JavaScript -->
        </div>
    </div>
</div>

@push('scripts')
<script>
// Donnees des types de commission avec leurs informations de calcul
const commissionTypes = {
    sponsor: {
        label: 'Sponsor Bonus',
        description: 'Commission de parrainage directe',
        formula: 'Prix du package ou produit × 30%',
        icon: 'S'
    },
    direct: {
        label: 'Commission Directe',
        description: 'Commission sur les achats des filleuls directs',
        formula: 'PV de l\'item × Taux du sponsor',
        icon: 'D'
    },
    indirect: {
        label: 'Commission Indirecte',
        description: 'Commission sur les niveaux inferieurs',
        formula: 'PV total × (Taux du sponsor - Taux du filleul)',
        icon: 'I'
    },
    leadership: {
        label: 'Leadership Bonus',
        description: 'Bonus de leadership sur le reseau',
        formula: 'PV total × Taux de leadership',
        icon: 'L'
    },
    retail: {
        label: 'Retail Bonus',
        description: 'Bonus sur les ventes au detail',
        formula: 'Montant de la vente × 25%',
        icon: 'R'
    }
};

function openCommissionDetails(commissionId) {
    fetch('/commissions/' + commissionId + '/json')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Erreur lors du chargement');
            }
            return response.json();
        })
        .then(function(data) {
            displayCommissionDetails(data);
        })
        .catch(function(error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des details', 'error');
        });
}

function displayCommissionDetails(commission) {
    const modal = document.getElementById('commissionModal');
    const content = document.getElementById('modalContent');
    
    const typeInfo = commissionTypes[commission.type] || commissionTypes.direct;
    
    const statusClass = commission.status === 'paid' ? 'success' : 'warning';
    const statusLabel = commission.status === 'paid' ? 'Paye' : 'En attente';
    
    // Récupérer les informations de l'item (package ou produit)
    const itemType = commission.item_type || 'unknown';
    const itemName = commission.item_name || commission.package_name || commission.product_name || 'N/A';
    const itemPV = commission.pv || 0;
    const itemPrice = commission.price || 0;
    const quantity = commission.quantity || 1;
    
    // Déterminer le badge de l'item
    let itemBadgeClass = '';
    let itemIcon = '';
    if (commission.package_id) {
        itemBadgeClass = 'item-badge-package';
        itemIcon = 'Package';
    } else if (commission.product_id) {
        itemBadgeClass = 'item-badge-product';
        itemIcon = 'Produit';
    }
    
    // Afficher le bon type d'item dans le modal
    let itemDisplay = '';
    if (commission.package_id || commission.product_id) {
        itemDisplay = `
            <div class="detail-item" style="grid-column: span 2;">
                <div class="label">Item</div>
                <div class="value">
                    <span class="item-badge ${itemBadgeClass}">${itemIcon}</span>
                    <span style="font-weight:600;">${itemName}</span>
                    ${quantity > 1 ? `× ${quantity}` : ''}
                </div>
            </div>
        `;
    }
    
    // Afficher le prix et le PV seulement s'ils sont > 0
    let priceDisplay = '';
    if (itemPrice > 0) {
        priceDisplay = `
            <div class="detail-item">
                <div class="label">Prix de l'item</div>
                <div class="value">${itemPrice}$</div>
            </div>
        `;
    }
    
    let pvDisplay = '';
    if (itemPV > 0) {
        pvDisplay = `
            <div class="detail-item">
                <div class="label">PV de l'item</div>
                <div class="value primary">${itemPV} PV</div>
            </div>
        `;
    }
    
    // Construire la formule de calcul avec les vraies valeurs
    let calculationHtml = '';
    if (commission.type === 'sponsor') {
        const priceValue = itemPrice > 0 ? itemPrice : 'Prix';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul du Sponsor Bonus</div>
                <div class="calc-formula">${priceValue}$ × 30%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPrice > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le prix de l'item</div>` : ''}
            </div>
        `;
    } else if (commission.type === 'direct') {
        const pvValue = itemPV > 0 ? itemPV : 'PV';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul de la Commission Directe</div>
                <div class="calc-formula">${pvValue} PV × ${pct}%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPV > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le PV de l'item</div>` : ''}
            </div>
        `;
    } else if (commission.type === 'indirect') {
        const pvValue = itemPV > 0 ? itemPV : 'PV';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul de la Commission Indirecte</div>
                <div class="calc-formula">${pvValue} PV × ${pct}%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPV > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le PV de l'item</div>` : ''}
            </div>
        `;
    } else if (commission.type === 'leadership') {
        const pvValue = itemPV > 0 ? itemPV : 'PV';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul du Leadership Bonus</div>
                <div class="calc-formula">${pvValue} PV × ${pct}%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPV > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le PV de l'item</div>` : ''}
            </div>
        `;
    } else {
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul</div>
                <div class="calc-formula">${commission.formula || 'Montant : ' + commission.amount + '$'}</div>
                <div class="calc-result">= $${commission.amount}</div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <div class="flex items-center gap-2 mb-1">
            <span class="type-badge type-badge-${commission.type}" style="font-size:0.8rem;padding:0.25rem 0.75rem;">
                ${typeInfo.icon} ${typeInfo.label}
            </span>
            <span class="badge badge-${statusClass}" style="font-size:0.7rem;">
                ${statusLabel}
            </span>
        </div>
        <h2 class="modal-title">${typeInfo.description}</h2>
        <p class="modal-subtitle">Commission #${commission.id} • ${commission.created_at}</p>
        
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">Montant</div>
                    <div class="value success">+ $${commission.amount}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Pourcentage</div>
                    <div class="value primary">${commission.percentage || 'N/A'}%</div>
                </div>
                <div class="detail-item">
                    <div class="label">De</div>
                    <div class="value">${commission.from_user || 'Systeme'}</div>
                </div>
                ${pvDisplay}
                ${itemDisplay}
                ${priceDisplay}
                <div class="detail-item">
                    <div class="label">Periode</div>
                    <div class="value">${commission.period || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Statut</div>
                    <div class="value ${statusClass}">${statusLabel}</div>
                </div>
            </div>
            
            ${calculationHtml}
            
            <div style="margin-top:0.5rem;padding:0.5rem 0.75rem;background:var(--bg-secondary);border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                <p style="font-size:0.65rem;color:var(--text-tertiary);">Description</p>
                <p style="font-size:0.8rem;color:var(--text-secondary);">${commission.description || 'Aucune description'}</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button onclick="closeCommissionModal()" class="btn btn-primary btn-sm">Fermer</button>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCommissionModal() {
    const modal = document.getElementById('commissionModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Fermer le modal en cliquant sur l'overlay
document.getElementById('commissionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCommissionModal();
    }
});

// Fermer avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCommissionModal();
    }
});

// Filtres
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var typeFilter = document.getElementById('typeFilter');
    var statusFilter = document.getElementById('statusFilter');
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');
    var rows = document.querySelectorAll('#commissionsTable tr');

    function filterRows() {
        var search = searchInput.value.trim().toLowerCase();
        var type = typeFilter.value;
        var status = statusFilter.value;
        var from = dateFrom.value;
        var to = dateTo.value;

        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            var rowType = row.dataset.type || '';
            var rowStatus = row.dataset.status || '';
            var rowDate = row.dataset.date || '';

            var show = true;

            if (search && !text.includes(search)) show = false;
            if (type && rowType !== type) show = false;
            if (status && rowStatus !== status) show = false;
            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    typeFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);
    dateFrom.addEventListener('change', filterRows);
    dateTo.addEventListener('change', filterRows);
});

function showToast(message, type) {
    type = type || 'success';
    document.querySelectorAll('.custom-toast').forEach(function(el) { el.remove(); });
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50';
    toast.style.animation = 'slideUp 0.3s ease forwards';
    toast.style.fontSize = '0.875rem';
    
    if (type === 'success') {
        toast.style.background = '#22c55e';
    } else if (type === 'error') {
        toast.style.background = '#ef4444';
    } else if (type === 'warning') {
        toast.style.background = '#f59e0b';
    } else {
        toast.style.background = '#6366f1';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}
</script>
@endpush
@endsection