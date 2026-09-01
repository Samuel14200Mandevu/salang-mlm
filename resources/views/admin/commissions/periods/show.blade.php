{{-- resources/views/admin/commissions/periods/show.blade.php --}}
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

.stat-detail {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.stat-detail:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-detail .value {
    font-size: 1.25rem;
    font-weight: 700;
}
.stat-detail .label {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}

.commission-type-item {
    display: flex;
    justify-content: space-between;
    padding: 0.375rem 0;
    border-bottom: 1px solid var(--border-light);
}
.commission-type-item:last-child {
    border-bottom: none;
}
.commission-type-item .type-label {
    font-size: 0.813rem;
    color: var(--text-secondary);
}
.commission-type-item .type-amount {
    font-weight: 600;
    color: var(--text-primary);
}

.top-earner-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-light);
}
.top-earner-item:last-child {
    border-bottom: none;
}
.top-earner-item .rank-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    flex-shrink: 0;
}
.top-earner-item .rank-number.top1 {
    background: #B54708;
    color: white;
}
.top-earner-item .rank-number.top2 {
    background: #6B7280;
    color: white;
}
.top-earner-item .rank-number.top3 {
    background: #B54708;
    color: white;
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

.btn-warning {
    background: #B54708;
    color: white;
    border-color: #B54708;
}
.btn-warning:hover {
    background: #92400E;
    border-color: #92400E;
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

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

.status-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.status-badge-pending { background: rgba(107, 114, 128, 0.12); color: #6b7280; border-color: rgba(107, 114, 128, 0.15); }
.status-badge-calculating { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.status-badge-calculated { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.status-badge-paying { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.status-badge-paid { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.status-badge-closed { background: rgba(107, 114, 128, 0.12); color: #6b7280; border-color: rgba(107, 114, 128, 0.15); }

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

@media (max-width: 640px) {
    .stat-detail { padding: 0.625rem; }
    .stat-detail .value { font-size: 1rem; }
    .card { padding: 0.875rem; }
    .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
    .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    .status-badge { font-size: 0.65rem; }
    .stats-grid { grid-template-columns: 1fr 1fr !important; }
    .action-buttons { flex-direction: column; }
    .action-buttons .btn { width: 100%; }
    .top-earner-item { padding: 0.375rem 0; }
    .top-earner-item .rank-number { width: 20px; height: 20px; font-size: 0.65rem; }
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
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Période {{ $period->period }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Détails de la période de commissions
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.commissions.periods') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.commissions.periods.export', $period->id) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Status & Progress -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="stat-detail">
            <p class="label">Statut</p>
            <p class="value">
                <span class="status-badge status-badge-{{ $period->status }}">
                    {{ $period->status_label }}
                </span>
            </p>
        </div>
        <div class="stat-detail">
            <p class="label">Progression</p>
            <p class="value">{{ number_format($period->progress, 1) }}%</p>
            <div class="progress-bar mt-1">
                <div class="fill" style="width: {{ $period->progress }}%"></div>
            </div>
        </div>
        <div class="stat-detail">
            <p class="label">Période</p>
            <p class="value">{{ $period->period }}</p>
            <p class="text-xs text-[var(--text-secondary)]">
                {{ $period->start_date->format('d/m/Y') }} → {{ $period->end_date->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="stat-detail border-l-4 border-[var(--primary-blue)]">
            <p class="label">Total Commissions</p>
            <p class="value text-[var(--primary-blue)]">${{ number_format($period->total_commissions, 2) }}</p>
        </div>
        <div class="stat-detail border-l-4 border-[#1C7E4A]">
            <p class="label">Payé</p>
            <p class="value text-[#1C7E4A]">${{ number_format($period->total_paid, 2) }}</p>
        </div>
        <div class="stat-detail border-l-4 border-[#B54708]">
            <p class="label">En attente</p>
            <p class="value text-[#B54708]">${{ number_format($stats['total_pending'] ?? 0, 2) }}</p>
        </div>
        <div class="stat-detail border-l-4 border-[#065F9C]">
            <p class="label">Utilisateurs</p>
            <p class="value text-[#065F9C]">{{ $stats['users_with_commissions'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Commissions by Type & Top Earners -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">

        <!-- By Type -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Commissions par type
            </h3>
            @php
                $totalByType = collect($stats['by_type'] ?? [])->sum('total') ?: 1;
            @endphp
            @forelse($stats['by_type'] ?? [] as $type => $data)
                <div class="commission-type-item">
                    <span class="type-label">{{ ucfirst($type) }}</span>
                    <div class="flex items-center gap-3">
                        <span class="type-amount">${{ number_format($data['total'], 2) }}</span>
                        <span class="text-xs text-[var(--text-tertiary)]">{{ $data['count'] }}</span>
                        <span class="text-xs text-[var(--text-tertiary)]">
                            {{ number_format(($data['total'] / $totalByType) * 100, 1) }}%
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">Aucune donnée</p>
            @endforelse
        </div>

        <!-- Top Earners -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Top 10 des gagnants
            </h3>
            @forelse($stats['top_earners'] ?? [] as $index => $earner)
                <div class="top-earner-item">
                    <span class="rank-number {{ $index < 3 ? 'top' . ($index + 1) : '' }}">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-[var(--text-primary)] text-sm truncate">
                            {{ $earner['user_name'] }}
                        </p>
                        <p class="text-xs text-[var(--text-secondary)] truncate">
                            {{ $earner['user_email'] }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-[#1C7E4A] text-sm">${{ number_format($earner['net'], 2) }}</p>
                        <p class="text-xs text-[var(--text-tertiary)]">Net</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">Aucun gagnant</p>
            @endforelse
        </div>
    </div>

    <!-- Actions -->
    @if($period->status != 'closed' && $period->status != 'paid')
    <div class="card animate-fadeInUp delay-4">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            Actions
        </h3>
        <div class="action-buttons flex flex-wrap gap-2 sm:gap-3">

            @if($period->status == 'pending')
                <form action="{{ route('admin.commissions.periods.process', $period->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="action" value="calculate_pv">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Calculer PV/BV
                    </button>
                </form>
            @endif

            @if($period->status == 'pending' || $period->status == 'calculated')
                <form action="{{ route('admin.commissions.periods.process', $period->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="action" value="calculate_ranks">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Calculer les grades
                    </button>
                </form>
            @endif

            @if($period->status == 'pending' || $period->status == 'calculated')
                <form action="{{ route('admin.commissions.periods.process', $period->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="action" value="calculate_commissions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Calculer les commissions
                    </button>
                </form>
            @endif

            @if($period->status == 'calculated' || $period->status == 'paying')
                <form action="{{ route('admin.commissions.periods.process', $period->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="action" value="generate_payments">
                    <button type="submit" class="btn btn-success btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Générer les paiements
                    </button>
                </form>
            @endif

            @if($period->status == 'paid')
                <form action="{{ route('admin.commissions.periods.close', $period->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Clôturer cette période ? Cette action est irréversible.')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Clôturer
                    </button>
                </form>
            @endif

            @if($period->status == 'pending' && $period->status != 'closed')
                <button onclick="openDeleteModal('{{ $period->id }}', '{{ $period->period }}')"
                        class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Recent Commissions -->
    <div class="card animate-fadeInUp delay-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                Dernières commissions
            </h3>
            <span class="text-xs text-[var(--text-secondary)]">{{ $period->commissions->count() }} total</span>
        </div>
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Montant</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Statut</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($period->commissions->take(10) ?? [] as $commission)
                        <tr>
                            <td class="font-medium text-sm">{{ $commission->user?->name ?? 'N/A' }}</td>
                            <td class="hidden sm:table-cell text-sm">
                                <span class="badge badge-info">{{ ucfirst($commission->type) }}</span>
                            </td>
                            <td class="font-bold text-[#1C7E4A] text-sm">${{ number_format($commission->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst($commission->status) }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-[var(--text-secondary)] text-sm">
                                Aucune commission
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($period->commissions->count() > 10)
            <div class="mt-2 text-right">
                <a href="{{ route('admin.commissions', ['period' => $period->period]) }}"
                   class="text-xs text-[var(--primary-blue)] hover:underline font-medium">
                    Voir toutes les commissions →
                </a>
            </div>
        @endif
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