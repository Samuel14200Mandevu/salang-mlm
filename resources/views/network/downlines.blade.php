@extends('layouts.app')

@section('title', 'Filiales')



@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <div class="downline-header member-list-header network-page-intro-desktop animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Mes filleuls</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Tous les membres de votre généalogie</p>
        </div>
        <div class="member-header-actions">
            <div class="header-search member-desktop-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher…"
                           autocomplete="off"
                           aria-label="Rechercher un filleul">
                </div>
            </div>

            <div class="supervision-filter-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm"
                        id="downlinesFilterBtn"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Filtrer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span id="downlinesFilterLabel">Filtrer</span>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition
                     @click.outside="open = false"
                     class="users-filter-menu users-filter-panel">
                    <div class="space-y-2">
                        <div>
                            <label for="levelFilter">Niveau</label>
                            <select id="levelFilter" class="form-control">
                                <option value="">Tous les niveaux</option>
                                <option value="1">Niveau 1</option>
                                <option value="2">Niveau 2</option>
                                <option value="3">Niveau 3</option>
                                <option value="4">Niveau 4+</option>
                            </select>
                        </div>
                        <div>
                            <label for="statusFilter">Statut</label>
                            <select id="statusFilter" class="form-control">
                                <option value="">Tous les statuts</option>
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm network-page-back-desktop">← Arbre</a>
        </div>
    </div>

    <div class="network-mobile-banner animate-fadeInUp">
        <div class="shop-catalog-banner network-catalog-banner">
            <div class="shop-catalog-banner__text">
                <p class="shop-catalog-banner__eyebrow">Réseau</p>
                <p class="shop-catalog-banner__title">Mes filleuls</p>
                <p class="shop-catalog-banner__sub">Tous les membres de votre généalogie</p>
            </div>
            <div class="shop-catalog-banner__tools">
                <a href="{{ route('network.index') }}" class="shop-banner-icon-btn" aria-label="Retour à l'arbre">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="animate-fadeInUp delay-1 p-3 bg-primary-500/5 border border-primary-500/20 rounded-lg">
        <p class="text-sm text-[var(--text-secondary)]">
            <span class="font-semibold text-primary-500">Votre code de parrain :</span>
            <span class="font-mono text-primary-500 font-bold">{{ Auth::user()?->sponsor_id }}</span>
            <span class="text-[var(--text-tertiary)] ml-2">(Partagez ce code pour parrainer de nouveaux membres)</span>
        </p>
    </div>

    {{-- Mobile : recherche + filtres (desktop = header) --}}
    <div class="downlines-filters-mobile filters-wrapper animate-fadeInUp delay-2 md:hidden">
        <div class="relative flex-1 min-w-0">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search" id="searchInputMobile" placeholder="Rechercher…" class="input pl-9 text-sm" autocomplete="off">
        </div>
        <select id="levelFilterMobile" class="input text-sm">
            <option value="">Tous les niveaux</option>
            <option value="1">Niveau 1</option>
            <option value="2">Niveau 2</option>
            <option value="3">Niveau 3</option>
            <option value="4">Niveau 4+</option>
        </select>
        <select id="statusFilterMobile" class="input text-sm">
            <option value="">Tous les statuts</option>
            <option value="1">Actif</option>
            <option value="0">Inactif</option>
        </select>
    </div>

    <!-- Table -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
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
                        <tr class="downline-row" 
                            data-name="{{ strtolower($member->name) }}" 
                            data-email="{{ strtolower($member->email) }}"
                            data-level="{{ $member->level ?? $member->genealogy?->level ?? 1 }}"
                            data-status="{{ $member->is_active ? 1 : 0 }}">
                            <td class="font-mono text-xs sm:text-sm">#{{ $member->id }}</td>
                            <td class="font-medium text-sm sm:text-base">
                                {{ $member->name }}
                                <!-- ✅ Affichage du code de parrain du membre -->
                                <span class="text-[8px] sm:text-[10px] text-[var(--text-tertiary)] block font-mono">
                                    Code: {{ $member->sponsor_id }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $member->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">Niv. {{ $member->level ?? $member->genealogy?->level ?? 1 }}</span>
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
                                <p class="text-base sm:text-lg font-medium">Aucun membre dans votre réseau</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Partagez votre lien de parrainage pour développer votre équipe</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($downlines) && method_exists($downlines, 'links'))
            <div class="mt-3 sm:mt-4">
                <x-salang-pagination :paginator="$downlines" />
            </div>
        @endif
    </div>

    <!-- Export -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-4">
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
    var searchInputMobile = document.getElementById('searchInputMobile');
    var levelFilterMobile = document.getElementById('levelFilterMobile');
    var statusFilterMobile = document.getElementById('statusFilterMobile');
    var rows = document.querySelectorAll('#downlinesTable tr.downline-row');

    function currentFilters() {
        var isMobile = window.matchMedia('(max-width: 768px)').matches;
        if (isMobile) {
            return {
                search: (searchInputMobile && searchInputMobile.value || '').trim().toLowerCase(),
                level: levelFilterMobile ? levelFilterMobile.value : '',
                status: statusFilterMobile ? statusFilterMobile.value : '',
            };
        }
        return {
            search: (searchInput && searchInput.value || '').trim().toLowerCase(),
            level: levelFilter ? levelFilter.value : '',
            status: statusFilter ? statusFilter.value : '',
        };
    }

    function filterRows() {
        var filters = currentFilters();

        rows.forEach(function(row) {
            var name = row.dataset.name || '';
            var email = row.dataset.email || '';
            var rowLevel = row.dataset.level || '1';
            var rowStatus = row.dataset.status || '1';
            var show = true;

            if (filters.search && !name.includes(filters.search) && !email.includes(filters.search)) {
                show = false;
            }

            if (filters.level === '4') {
                if (parseInt(rowLevel, 10) < 4) show = false;
            } else if (filters.level && rowLevel != filters.level) {
                show = false;
            }

            if (filters.status && rowStatus != filters.status) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    function syncFilterLabel() {
        var label = document.getElementById('downlinesFilterLabel');
        var btn = document.getElementById('downlinesFilterBtn');
        if (!label || !btn || !levelFilter || !statusFilter) return;
        var level = levelFilter.value;
        var status = statusFilter.value;
        var active = !!(level || status);
        var text = 'Filtrer';
        if (level === '1') text = 'Niveau 1';
        else if (level === '2') text = 'Niveau 2';
        else if (level === '3') text = 'Niveau 3';
        else if (level === '4') text = 'Niveau 4+';
        else if (status === '1') text = 'Actifs';
        else if (status === '0') text = 'Inactifs';
        label.textContent = text;
        btn.classList.toggle('border-[var(--primary)]', active);
        btn.classList.toggle('text-[var(--primary)]', active);
    }

    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (levelFilter) levelFilter.addEventListener('change', function () { filterRows(); syncFilterLabel(); });
    if (statusFilter) statusFilter.addEventListener('change', function () { filterRows(); syncFilterLabel(); });
    if (searchInputMobile) searchInputMobile.addEventListener('input', filterRows);
    if (levelFilterMobile) levelFilterMobile.addEventListener('change', filterRows);
    if (statusFilterMobile) statusFilterMobile.addEventListener('change', filterRows);
});

function exportCSV() {
    var rows = document.querySelectorAll('#downlinesTable tr');
    var csv = 'ID,Nom,Email,Niveau,Package,PV,Statut,Inscrit le,Code Parrain\n';

    rows.forEach(function(row) {
        if (row.style.display === 'none') return;
        var cells = row.querySelectorAll('td');
        if (cells.length < 7) return;

        // Récupérer le code de parrain depuis le nom
        var nameCell = cells[1];
        var code = '';
        var name = nameCell?.textContent?.trim() || '';
        var codeSpan = nameCell?.querySelector('.font-mono');
        if (codeSpan) {
            code = codeSpan.textContent.trim().replace('Code:', '').trim();
        }

        csv += [
            cells[0]?.textContent?.trim() || '',
            name,
            cells[2]?.textContent?.trim() || '',
            cells[3]?.textContent?.trim() || '',
            cells[4]?.textContent?.trim() || '',
            cells[5]?.textContent?.trim() || '',
            cells[7]?.textContent?.trim() || '',
            cells[6]?.textContent?.trim() || '',
            code
        ].join(',') + '\n';
    });

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'mes_filleuls_' + new Date().toISOString().slice(0,10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection