@extends('layouts.app')

@push('styles')
<style>
    .commission-card { transition: all 0.3s ease; }
    .commission-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-hover); }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.65rem;
        font-weight: 600;
    }
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-retail { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-bonus { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .type-badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mes Commissions</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Suivez tous vos gains en detail</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('commissions.export') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
            <a href="{{ route('commissions.pdf') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total gagne</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Payees</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format(($stats['total'] ?? 0) - ($stats['pending'] ?? 0), 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Nombre total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['total_count'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[150px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="typeFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les types</option>
            <option value="direct">Direct</option>
            <option value="indirect">Indirect</option>
            <option value="leadership">Leadership</option>
            <option value="retail">Retail</option>
        </select>
        <select id="statusFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="paid">Paye</option>
            <option value="pending">En attente</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
        <input type="date" id="dateTo" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
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
                        <th class="text-xs sm:text-sm hidden md:table-cell">De</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Description</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-card" 
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}"
                            data-date="{{ $commission->created_at->format('Y-m-d') }}">
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    {{ ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                {{ $commission->fromUser?->name ?? 'Systeme' }}
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
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aucune commission pour le moment.
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

    <!-- Repartition par type -->
    @if(!empty($stats['by_type']))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-7">
        @foreach($stats['by_type'] as $type => $data)
            <div class="card-stats p-3 sm:p-4 border-l-4 border-{{ $data['color'] ?? 'primary' }}-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $data['label'] }}</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-{{ $data['color'] ?? 'primary' }}-500">
                    ${{ number_format($data['total'], 2) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $data['count'] }} commission(s)</p>
            </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const rows = document.querySelectorAll('#commissionsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const type = typeFilter.value;
        const status = statusFilter.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';
            const rowDate = row.dataset.date || '';

            let show = true;

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
</script>
@endpush
@endsection