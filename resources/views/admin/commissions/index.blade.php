@extends('admin.layouts.app')

@push('styles')
<style>
    .commission-row {
        transition: all 0.2s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }
    
    .amount-positive {
        color: #22c55e;
        font-weight: 700;
    }
    
    .filters-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
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
    .btn-md { padding: 0.625rem 1.5rem; font-size: 0.875rem; }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    .btn-icon { padding: 0.375rem 0.625rem; }
    
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
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .filters-wrapper { flex-direction: column; align-items: stretch; }
        .filters-wrapper .input { width: 100% !important; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .card { padding: 0.875rem; }
        .stats-grid { grid-template-columns: 1fr 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr !important; }
        .card { padding: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Commissions</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérer toutes les commissions générées</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-green-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total payé</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($pendingCommissions ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-red-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulé</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">${{ number_format($totalCancelled ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-4 border-l-4 border-blue-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $commissions->total() ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-wrapper animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[140px]">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher un utilisateur..."
                   class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        
        <select id="typeFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les types</option>
            @foreach($types ?? [] as $type)
                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        
        <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="paid">Payé</option>
            <option value="cancelled">Annulé</option>
        </select>
        
        <a href="{{ route('admin.commissions.export') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter
        </a>
    </div>

    <!-- Commission List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">De</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Montant</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Statut</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions as $commission)
                        <tr class="commission-row" 
                            data-user="{{ strtolower($commission->user?->name ?? '') }}"
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}">
                            <td class="font-medium text-sm sm:text-base">
                                {{ $commission->user?->name ?? 'N/A' }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">
                                    {{ $commission->type_label ?? $commission->type }}
                                </span>
                            </td>
                            <td class="amount-positive text-sm sm:text-base">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : ($commission->status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $commission->status == 'paid' ? 'Payé' : ($commission->status == 'pending' ? 'En attente' : 'Annulé') }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.commissions.show', $commission->id) }}" 
                                   class="btn btn-outline btn-sm btn-icon" title="Voir">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commissions apparaîtront ici une fois générées</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#commissionsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const type = typeFilter.value;
        const status = statusFilter.value;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (type && rowType !== type) show = false;
            if (status && rowStatus !== status) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    typeFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection