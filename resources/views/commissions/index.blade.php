{{-- resources/views/commissions/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .commission-row {
        transition: all 0.2s ease;
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
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-retail { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-bonus { background: rgba(236,72,153,0.15); color: #ec4899; }
    
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
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Suivez vos gains en détail</p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
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
            <a href="{{ route('commissions.export') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
            <a href="{{ route('commissions.export') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total gagné</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payé</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format(($stats['total'] ?? 0) - ($stats['pending'] ?? 0), 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['total_count'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-wrapper animate-fadeInUp delay-5">
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
            <option value="paid">Payé</option>
            <option value="pending">En attente</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Du">
        <input type="date" id="dateTo" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Au">
    </div>

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
                        <th class="text-xs sm:text-sm hidden md:table-cell">De</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Description</th>
                        <th class="text-xs sm:text-sm text-right">Montant</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-row" 
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
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden lg:table-cell">
                                {{ Str::limit($commission->description ?? '-', 30) }}
                            </td>
                            <td class="text-right font-bold text-green-500 text-sm sm:text-base">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ $commission->status == 'paid' ? 'Payé' : 'En attente' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commissions apparaîtront lorsque vous développerez votre réseau</p>
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

@push('scripts')
<script>
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
</script>
@endpush
@endsection