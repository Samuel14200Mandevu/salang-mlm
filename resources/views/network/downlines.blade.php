@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📋 Mes Filleuls</h1>
            <p class="text-[var(--text-secondary)] mt-1">Liste complète de votre réseau</p>
        </div>
        <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">
            ← Retour à l'arbre
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
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-9">
        </div>
        <select id="levelFilter" class="input w-auto min-w-[130px]">
            <option value="">Tous les niveaux</option>
            <option value="1">Niveau 1</option>
            <option value="2">Niveau 2</option>
            <option value="3">Niveau 3</option>
            <option value="4">Niveau 4+</option>
        </select>
        <select id="statusFilter" class="input w-auto min-w-[130px]">
            <option value="">Tous les statuts</option>
            <option value="1">Actif</option>
            <option value="0">Inactif</option>
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-2">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th class="hidden sm:table-cell">Niveau</th>
                        <th class="hidden md:table-cell">Package</th>
                        <th>PV</th>
                        <th class="hidden lg:table-cell">Inscrit le</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="downlinesTable">
                    @forelse($downlines ?? [] as $member)
                        <tr data-name="{{ strtolower($member->name) }}" 
                            data-email="{{ strtolower($member->email) }}"
                            data-level="{{ $member->genealogy?->level ?? 1 }}"
                            data-status="{{ $member->is_active ? 1 : 0 }}">
                            <td class="font-mono text-sm">#{{ $member->id }}</td>
                            <td class="font-medium">{{ $member->name }}</td>
                            <td class="text-[var(--text-secondary)]">{{ $member->email }}</td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info">Niv. {{ $member->genealogy?->level ?? 1 }}</span>
                            </td>
                            <td class="hidden md:table-cell">{{ $member->package?->name ?? 'Starter' }}</td>
                            <td>{{ number_format($member->pv_balance ?? 0) }}</td>
                            <td class="hidden lg:table-cell text-sm text-[var(--text-secondary)]">
                                {{ $member->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $member->is_active ? '✅ Actif' : '❌ Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Aucun membre dans votre réseau
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($downlines) && method_exists($downlines, 'links'))
    <div class="mt-4">
        {{ $downlines->links() }}
    </div>
@endif
    </div>

    <!-- Export -->
    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-3">
        <button onclick="exportCSV()" class="btn btn-outline btn-sm">
            📊 Exporter CSV
        </button>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            🖨️ Imprimer
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const levelFilter = document.getElementById('levelFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#downlinesTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const level = levelFilter.value;
        const status = statusFilter.value;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const rowLevel = row.dataset.level || '1';
            const rowStatus = row.dataset.status || '1';

            let show = true;

            if (search && !name.includes(search) && !email.includes(search)) {
                show = false;
            }

            if (level && rowLevel != level) {
                show = false;
            }

            if (status && rowStatus != status) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    levelFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);
});

function exportCSV() {
    const rows = document.querySelectorAll('#downlinesTable tr');
    let csv = 'Nom,Email,Niveau,Package,PV,Statut,Inscrit le\n';

    rows.forEach(row => {
        if (row.style.display === 'none') return;
        const cells = row.querySelectorAll('td');
        if (cells.length < 7) return;

        csv += [
            cells[1]?.textContent?.trim() || '',
            cells[2]?.textContent?.trim() || '',
            cells[3]?.textContent?.trim() || '',
            cells[4]?.textContent?.trim() || '',
            cells[5]?.textContent?.trim() || '',
            cells[7]?.textContent?.trim() || '',
            cells[6]?.textContent?.trim() || ''
        ].join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'mes_filleuls_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection