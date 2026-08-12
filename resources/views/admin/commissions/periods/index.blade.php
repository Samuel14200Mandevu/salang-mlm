{{-- resources/views/admin/commissions/periods/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .period-row {
        transition: all 0.2s ease;
    }
    .period-row:hover {
        background: var(--bg-hover);
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .status-badge-pending { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    .status-badge-calculating { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .status-badge-calculated { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .status-badge-paying { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .status-badge-paid { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .status-badge-closed { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    
    .progress-bar {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
    }
    .progress-bar .fill {
        height: 100%;
        border-radius: 9999px;
        background: var(--gradient-primary);
        transition: width 0.8s ease;
    }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Périodes de commissions</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérer les périodes de calcul</p>
        </div>
        <button onclick="openCreateModal()" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden xs:inline">Nouvelle période</span>
            <span class="inline xs:hidden">+</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $stats['total_periods'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payées</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $stats['paid'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Clôturées</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['closed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="calculating">En calcul</option>
            <option value="calculated">Calculé</option>
            <option value="paying">En paiement</option>
            <option value="paid">Payé</option>
            <option value="closed">Clôturé</option>
        </select>
        <select id="yearFilter" class="input w-auto min-w-[100px] sm:min-w-[120px] text-sm sm:text-base">
            <option value="">Toutes les années</option>
            @foreach($years ?? [] as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <!-- Periods List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Période</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Début</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Fin</th>
                        <th class="text-xs sm:text-sm">Commissions</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Progrès</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="periodsTable">
                    @forelse($periods ?? [] as $period)
                        <tr class="period-row" 
                            data-status="{{ $period->status }}"
                            data-year="{{ substr($period->period, 0, 4) }}">
                            <td class="font-mono text-sm sm:text-base">{{ $period->period }}</td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $period->start_date->format('d/m/Y') }}
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $period->end_date->format('d/m/Y') }}
                            </td>
                            <td class="font-bold text-primary-500 text-sm sm:text-base">
                                ${{ number_format($period->total_commissions, 2) }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="progress-bar flex-1">
                                        <div class="fill" style="width: {{ $period->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-[var(--text-secondary)]">{{ number_format($period->progress, 1) }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-badge-{{ $period->status }}">
                                    {{ $period->status_label }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.commissions.periods.show', $period->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Voir">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($period->status == 'pending' || $period->status == 'calculated')
                                        <a href="{{ route('admin.commissions.periods.process', $period->id) }}?action=process_all" 
                                           class="btn btn-primary btn-sm btn-icon" title="Traiter">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    @if($period->status == 'paid' && $period->status != 'closed')
                                        <form action="{{ route('admin.commissions.periods.close', $period->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" 
                                                    onclick="return confirm('Clôturer cette période ?')" title="Clôturer">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($period->status != 'closed' && $period->status != 'paid')
                                        <button onclick="openDeleteModal('{{ $period->id }}', '{{ $period->period }}')" 
                                                class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune période</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Créez une nouvelle période pour commencer</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($periods) && $periods->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $periods->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ============================================================
CREATE MODAL
============================================================ -->
<div id="createModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-box" style="background:var(--bg-card); border-radius:var(--radius-lg); padding:2rem; max-width:450px; width:90%; box-shadow:var(--shadow-xl); border:1px solid var(--border-color);">
        <div class="modal-icon modal-icon-success" style="width:4rem; height:4rem; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:rgba(34,197,94,0.1); color:#22c55e;">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <h3 class="modal-title" style="text-align:center; font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">Nouvelle période</h3>
        <p class="modal-text" style="text-align:center; font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.6;">
            Créer une nouvelle période de calcul des commissions.
        </p>
        <form action="{{ route('admin.commissions.periods.create') }}" method="POST" class="modal-actions" style="display:flex; flex-direction:column; gap:0.75rem;">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-[var(--text-secondary)] font-medium mb-1">Année</label>
                    <select name="year" class="input w-full text-sm" required>
                        @for($i = date('Y'); $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-[var(--text-secondary)] font-medium mb-1">Mois</label>
                    <select name="month" class="input w-full text-sm" required>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
                <button type="button" onclick="closeCreateModal()" class="btn btn-outline w-full text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
DELETE MODAL
============================================================ -->
<div id="deleteModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-box" style="background:var(--bg-card); border-radius:var(--radius-lg); padding:2rem; max-width:450px; width:90%; box-shadow:var(--shadow-xl); border:1px solid var(--border-color);">
        <div class="modal-icon modal-icon-danger" style="width:4rem; height:4rem; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:rgba(239,68,68,0.1); color:#ef4444;">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title" style="text-align:center; font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">Confirmer la suppression</h3>
        <p class="modal-text" style="text-align:center; font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.6;">
            Êtes-vous sûr de vouloir <strong style="color:#ef4444;">supprimer</strong> la période <strong id="periodNameDisplay"></strong> ?
            <br>
            Cette action est <strong style="color:#ef4444;">irréversible</strong>.
        </p>
        <div class="modal-actions" style="display:flex; gap:0.75rem; justify-content:center;">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; border:2px solid var(--border-color); background:transparent; color:var(--text-primary); border-radius:var(--radius-md); cursor:pointer;">
                Annuler
            </button>
            <form id="deleteForm" action="" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; background:var(--gradient-danger); color:white; border:none; border-radius:var(--radius-md); cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const yearFilter = document.getElementById('yearFilter');
    const rows = document.querySelectorAll('#periodsTable tr');

    function filterRows() {
        const status = statusFilter.value;
        const year = yearFilter.value;

        rows.forEach(function(row) {
            const rowStatus = row.dataset.status || '';
            const rowYear = row.dataset.year || '';

            let show = true;

            if (status && rowStatus !== status) show = false;
            if (year && rowYear !== year) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', filterRows);
    yearFilter.addEventListener('change', filterRows);
});

// ============================================================
// CREATE MODAL
// ============================================================
function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
    document.body.style.overflow = '';
}

// ============================================================
// DELETE MODAL
// ============================================================
function openDeleteModal(periodId, periodName) {
    document.getElementById('deleteModal').style.display = 'flex';
    document.getElementById('periodNameDisplay').textContent = periodName;
    document.getElementById('deleteForm').action = '/admin/commissions/periods/' + periodId;
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(function(modal) {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
@endpush
@endsection