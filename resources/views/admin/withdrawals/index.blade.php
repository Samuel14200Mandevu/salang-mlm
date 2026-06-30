@extends('admin.layouts.app')

@push('styles')
<style>
    .withdrawal-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🏦 Gestion des Retraits</h1>
            <p class="text-[var(--text-secondary)] mt-1">Suivez et gérez toutes les demandes de retrait</p>
        </div>
        <a href="{{ route('admin.withdrawals.export') }}" class="btn btn-outline btn-sm">
            📊 Exporter
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-yellow-500">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">En traitement</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['processing'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Total retiré</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Frais totaux</p>
            <p class="text-2xl font-bold text-purple-500">${{ number_format($stats['total_fees'] ?? 0, 2) }}</p>
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
        <select id="statusFilter" class="input w-auto min-w-[140px]">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="processing">En traitement</option>
            <option value="completed">Terminé</option>
            <option value="failed">Échoué</option>
        </select>
        <select id="methodFilter" class="input w-auto min-w-[140px]">
            <option value="">Toutes les méthodes</option>
            @foreach($methods ?? [] as $method)
                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
            @endforeach
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[130px]">
        <input type="date" id="dateTo" class="input w-auto min-w-[130px]">
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th>Montant</th>
                        <th class="hidden md:table-cell">Méthode</th>
                        <th>Statut</th>
                        <th class="hidden lg:table-cell">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTable">
                    @forelse($withdrawals ?? [] as $withdrawal)
                        <tr class="withdrawal-row" 
                            data-status="{{ $withdrawal->status }}"
                            data-method="{{ $withdrawal->method }}"
                            data-date="{{ $withdrawal->created_at->format('Y-m-d') }}">
                            <td class="font-mono text-sm">#{{ $withdrawal->id }}</td>
                            <td class="font-medium">{{ $withdrawal->user?->name ?? 'N/A' }}</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)]">{{ $withdrawal->user?->email ?? 'N/A' }}</td>
                            <td class="font-bold">${{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info">{{ ucfirst($withdrawal->method) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-sm text-[var(--text-secondary)]">
                                {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
                                        <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" 
                                                    onclick="return confirm('Approuver ce retrait ?')" title="Approuver">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button onclick="showRejectModal('{{ $withdrawal->id }}')" 
                                                class="btn btn-danger btn-sm btn-icon" title="Rejeter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aucune demande de retrait
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($withdrawals) && $withdrawals->hasPages())
            <div class="mt-4">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de rejet -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-[var(--bg-card)] rounded-xl shadow-2xl max-w-md w-full p-6 border border-[var(--border-color)]">
        <div class="text-center">
            <div class="text-5xl mb-4">❌</div>
            <h3 class="text-xl font-bold text-[var(--text-primary)]">Rejeter le retrait</h3>
            <p class="text-sm text-[var(--text-secondary)] mt-2">
                Veuillez indiquer la raison du rejet.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mt-4">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input" placeholder="Motif du rejet..." required></textarea>
            </div>
            <div class="mt-4 flex gap-3">
                <button type="submit" class="btn btn-danger flex-1">
                    ✅ Rejeter
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline flex-1">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.withdrawals.reject", ["id" => ":id"]) }}'.replace(':id', withdrawalId);
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRejectModal();
});

// Filtres
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const methodFilter = document.getElementById('methodFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const rows = document.querySelectorAll('#withdrawalsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        const method = methodFilter.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            const rowMethod = row.dataset.method || '';
            const rowDate = row.dataset.date || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (method && rowMethod !== method) show = false;
            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
    methodFilter.addEventListener('change', filterRows);
    dateFrom.addEventListener('change', filterRows);
    dateTo.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection