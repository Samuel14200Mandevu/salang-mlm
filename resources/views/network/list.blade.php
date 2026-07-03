@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .card { padding: 0.75rem; }
        .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mes Filleuls</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Liste complete de votre reseau</p>
        </div>
        <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour a l'arbre
        </a>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[180px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="levelFilter" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
            <option value="">Tous les niveaux</option>
            <option value="1">Niveau 1</option>
            <option value="2">Niveau 2</option>
            <option value="3">Niveau 3</option>
            <option value="4">Niveau 4+</option>
        </select>
        <select id="statusFilter" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="1">Actif</option>
            <option value="0">Inactif</option>
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">#</th>
                        <th class="text-xs sm:text-sm">Nom</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Niveau</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Package</th>
                        <th class="text-xs sm:text-sm">PV</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Inscrit</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody id="downlinesTable">
                    @forelse($downlines ?? [] as $member)
                        <tr data-name="{{ strtolower($member->name) }}" 
                            data-email="{{ strtolower($member->email) }}"
                            data-level="{{ $member->genealogy?->level ?? 1 }}"
                            data-status="{{ $member->is_active ? 1 : 0 }}">
                            <td class="font-mono text-xs sm:text-sm">#{{ $member->id }}</td>
                            <td class="font-medium text-sm sm:text-base">{{ $member->name }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $member->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">Niv. {{ $member->genealogy?->level ?? 1 }}</span>
                            </td>
                            <td class="hidden lg:table-cell text-sm sm:text-base">{{ $member->package?->name ?? 'Starter' }}</td>
                            <td class="text-sm sm:text-base">{{ number_format($member->pv_balance ?? 0) }}</td>
                            <td class="hidden xl:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $member->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Aucun membre dans votre reseau
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($downlines) && method_exists($downlines, 'links'))
            <div class="mt-3 sm:mt-4">
                {{ $downlines->links() }}
            </div>
        @endif
    </div>

    <!-- Export -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-3">
        <button onclick="exportCSV()" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter CSV
        </button>
        <button onclick="window.print()" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var levelFilter = document.getElementById('levelFilter');
    var statusFilter = document.getElementById('statusFilter');
    var rows = document.querySelectorAll('#downlinesTable tr');

    function filterRows() {
        var search = searchInput.value.trim().toLowerCase();
        var level = levelFilter.value;
        var status = statusFilter.value;

        rows.forEach(function(row) {
            var name = row.dataset.name || '';
            var email = row.dataset.email || '';
            var rowLevel = row.dataset.level || '1';
            var rowStatus = row.dataset.status || '1';

            var show = true;

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
    var rows = document.querySelectorAll('#downlinesTable tr');
    var csv = 'Nom,Email,Niveau,Package,PV,Statut,Inscrit le\n';

    rows.forEach(function(row) {
        if (row.style.display === 'none') return;
        var cells = row.querySelectorAll('td');
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

    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'mes_filleuls_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection