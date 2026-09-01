{{-- resources/views/admin/commissions/periods/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.period-row {
    transition: background 0.15s ease;
}
.period-row:hover {
    background: var(--bg-hover);
}

.status-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.status-badge-pending { background: rgba(107, 114, 128, 0.12); color: #6b7280; border-color: rgba(107, 114, 128, 0.15); }
.status-badge-calculating { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.status-badge-calculated { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.status-badge-paying { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.status-badge-paid { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.status-badge-closed { background: rgba(107, 114, 128, 0.12); color: #6b7280; border-color: rgba(107, 114, 128, 0.15); }

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.card-stats:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.input::placeholder {
    color: var(--text-muted);
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

.progress-bar {
    width: 100%;
    height: 0.375rem;
    background: var(--bg-secondary);
    border-radius: 9999px;
    overflow: hidden;
}
.progress-bar .fill {
    height: 100%;
    border-radius: 9999px;
    background: var(--primary-blue);
    transition: width 0.6s ease;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-box {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
.modal-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}
.modal-icon-success {
    background: rgba(28, 126, 74, 0.1);
    color: #1C7E4A;
}
.modal-icon-danger {
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
}
.modal-title {
    text-align: center;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}
.modal-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 90px;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }
.delay-6 { animation-delay: 0.3s; }

@media (max-width: 640px) {
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
    .btn-sm svg { width: 0.875rem; height: 0.875rem; }
    .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    .card-stats { padding: 0.625rem; }
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
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Périodes de commissions</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Gérer les périodes de calcul</p>
        </div>
        <button onclick="openCreateModal()" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle période
        </button>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-[var(--primary-blue)] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $stats['total_periods'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B54708] animate-fadeInUp delay-2 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A] animate-fadeInUp delay-3 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payées</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">{{ $stats['paid'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[var(--primary-blue)] animate-fadeInUp delay-4 p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Clôturées</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $stats['closed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="calculating">En calcul</option>
            <option value="calculated">Calculé</option>
            <option value="paying">En paiement</option>
            <option value="paid">Payé</option>
            <option value="closed">Clôturé</option>
        </select>
        <select id="yearFilter" class="input w-auto min-w-[100px] sm:min-w-[120px] text-sm" style="padding-left: 0.75rem;">
            <option value="">Toutes les années</option>
            @foreach($years ?? [] as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <!-- Periods List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4">
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
                            <td class="font-mono text-sm">{{ $period->period }}</td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $period->start_date->format('d/m/Y') }}
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $period->end_date->format('d/m/Y') }}
                            </td>
                            <td class="font-bold text-[var(--primary-blue)] text-sm">
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
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($period->status == 'pending' || $period->status == 'calculated')
                                        <a href="{{ route('admin.commissions.periods.process', $period->id) }}?action=process_all"
                                           class="btn btn-primary btn-sm btn-icon" title="Traiter">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($period->status == 'paid' && $period->status != 'closed')
                                        <form action="{{ route('admin.commissions.periods.close', $period->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon"
                                                    onclick="return confirm('Clôturer cette période ?')" title="Clôturer">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($period->status != 'closed' && $period->status != 'paid')
                                        <button onclick="openDeleteModal('{{ $period->id }}', '{{ $period->period }}')"
                                                class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

    <!-- Footer -->
    <div class="footer-links mt-4 pt-4 border-t border-[var(--border-color)] flex flex-wrap gap-4 text-xs text-[var(--text-muted)]">
        <a href="{{ route('legal.terms') }}" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:underline">Conditions générales d'utilisation</a>
        <a href="{{ route('legal.privacy') }}" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:underline">Politique de confidentialité</a>
        <span>© {{ date('Y') }} — Tous droits réservés</span>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <h3 class="modal-title">Nouvelle période</h3>
        <p class="modal-text">
            Créer une nouvelle période de calcul des commissions.
        </p>
        <form action="{{ route('admin.commissions.periods.create') }}" method="POST" class="modal-actions" style="flex-direction:column; gap:0.75rem;">
            @csrf
            <div class="grid grid-cols-2 gap-3 w-full">
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
            <div class="flex gap-2 w-full">
                <button type="submit" class="btn btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
                <button type="button" onclick="closeCreateModal()" class="btn btn-outline flex-1">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la suppression</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">supprimer</strong> la période <strong id="periodNameDisplay"></strong> ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong>.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form id="deleteForm" action="" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

function openCreateModal() {
    document.getElementById('createModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openDeleteModal(periodId, periodName) {
    document.getElementById('deleteModal').classList.add('active');
    document.getElementById('periodNameDisplay').textContent = periodName;
    document.getElementById('deleteForm').action = '/admin/commissions/periods/' + periodId;
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endsection