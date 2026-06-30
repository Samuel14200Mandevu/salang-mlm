@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📜 Historique des promotions</h1>
            <p class="text-[var(--text-secondary)] mt-1">Suivez toutes les promotions de grades</p>
        </div>
        <a href="{{ route('admin.ranks') }}" class="btn btn-outline btn-sm">
            ← Retour aux rangs
        </a>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-3 animate-fadeInUp delay-1">
        <div class="relative flex-1 min-w-[180px] max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher un utilisateur..." class="input pl-9">
        </div>
        <select id="userFilter" class="input w-auto min-w-[200px]">
            <option value="">Tous les utilisateurs</option>
            @foreach($users ?? [] as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-2">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Ancien grade</th>
                        <th>Nouveau grade</th>
                        <th>PV</th>
                        <th>BV</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    @forelse($history ?? [] as $item)
                        <tr data-user="{{ $item->user_id }}">
                            <td class="text-sm text-[var(--text-secondary)]">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-medium">{{ $item->user?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-neutral">{{ $item->old_rank_name ?? 'Début' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $item->new_rank_name }}</span>
                            </td>
                            <td>{{ number_format($item->pv_at_time) }}</td>
                            <td>{{ number_format($item->bv_at_time) }}</td>
                            <td class="text-sm text-[var(--text-secondary)]">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Aucun historique de promotion
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($history) && $history->hasPages())
            <div class="mt-4">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const userFilter = document.getElementById('userFilter');
    const rows = document.querySelectorAll('#historyTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const userId = userFilter.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowUser = row.dataset.user || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (userId && rowUser !== userId) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    userFilter.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection