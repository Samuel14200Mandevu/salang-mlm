@extends('layouts.app')

@push('styles')
<style>
    .commission-card {
        transition: all 0.3s ease;
    }
    .commission-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
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
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">💰 Mes Commissions</h1>
            <p class="text-[var(--text-secondary)] mt-1">Suivez tous vos gains en détail</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('commissions.export') }}" class="btn btn-outline btn-sm">
                📊 Exporter
            </a>
            <a href="{{ route('commissions.pdf') }}" class="btn btn-outline btn-sm">
                📄 PDF
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total gagné</p>
            <p class="text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Payées</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format(($stats['total'] ?? 0) - ($stats['pending'] ?? 0), 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Nombre total</p>
            <p class="text-2xl font-bold text-purple-500">{{ $stats['total_count'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[150px] max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-9">
        </div>
        <select id="typeFilter" class="input w-auto min-w-[140px]">
            <option value="">Tous les types</option>
            <option value="direct">Direct</option>
            <option value="indirect">Indirect</option>
            <option value="leadership">Leadership</option>
            <option value="retail">Retail</option>
        </select>
        <select id="statusFilter" class="input w-auto min-w-[140px]">
            <option value="">Tous les statuts</option>
            <option value="paid">Payé</option>
            <option value="pending">En attente</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[130px]">
        <input type="date" id="dateTo" class="input w-auto min-w-[130px]">
    </div>

    <!-- Liste des commissions -->
    <div class="card animate-fadeInUp delay-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📋 Historique des commissions</h3>
            <span class="badge badge-neutral text-xs">{{ $commissions->total() ?? 0 }} commissions</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>De</th>
                        <th>Description</th>
                        <th class="text-right">Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions ?? [] as $commission)
                        <tr class="commission-card" 
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}"
                            data-date="{{ $commission->created_at->format('Y-m-d') }}">
                            <td class="text-sm text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    @if($commission->type == 'direct') 👤 Direct
                                    @elseif($commission->type == 'indirect') 👥 Indirect
                                    @elseif($commission->type == 'leadership') 👑 Leadership
                                    @elseif($commission->type == 'retail') 🛍️ Retail
                                    @else 🎁 Bonus
                                    @endif
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)]">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-sm">
                                {{ Str::limit($commission->description ?? '-', 40) }}
                            </td>
                            <td class="text-right font-bold text-green-500">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $commission->status == 'paid' ? '✅ Payé' : '⏳ En attente' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aucune commission pour le moment.
                                <br>
                                <span class="text-xs">Commencez à parrainer des membres pour gagner des commissions !</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
    <div class="mt-4">
        {{ $commissions->links() }}
    </div>
@endif
    </div>

    <!-- Répartition par type -->
    @if(!empty($stats['by_type']))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-7">
        @foreach($stats['by_type'] as $type => $data)
            <div class="card-stats border-l-4 border-{{ $data['color'] ?? 'primary' }}-500">
                <p class="text-sm text-[var(--text-secondary)]">{{ $data['label'] }}</p>
                <p class="text-xl font-bold text-{{ $data['color'] ?? 'primary' }}-500">
                    ${{ number_format($data['total'], 2) }}
                </p>
                <p class="text-xs text-[var(--text-secondary)]">{{ $data['count'] }} commission(s)</p>
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

        rows.forEach(row => {
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