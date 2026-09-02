{{-- resources/views/cashier/consultations/index.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-card .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-total { background: rgba(15, 43, 79, 0.10); color: var(--primary); }
    .stat-icon-pending { background: rgba(245, 158, 11, 0.10); color: #f59e0b; }
    .stat-icon-processing { background: rgba(59, 130, 246, 0.10); color: #3b82f6; }
    .stat-icon-completed { background: rgba(34, 197, 94, 0.10); color: #22c55e; }

    .consultation-row {
        transition: background 0.2s ease;
    }
    .consultation-row:hover {
        background: var(--bg-secondary);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.813rem;
    }

    .table thead th {
        padding: 0.6rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        color: var(--text-secondary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--bg-secondary);
    }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    /* ===== BOUTONS AMÉLIORÉS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease, transform 0.15s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #091E3B);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-success {
        background: #22c55e;
        color: #FFFFFF;
    }
    .btn-success:hover {
        background: #16a34a;
    }

    .btn-danger {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-danger:hover {
        background: #8f2121;
    }

    .btn-sm {
        padding: 0.3rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }

    .btn-block {
        display: flex;
        width: 100%;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.20);
        color: #16a34a;
    }

    .alert-warning {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.20);
        color: #d97706;
    }

    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem 1rem; }
        .stat-card .stat-value { font-size: 1.25rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1.25rem; height: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .card { padding: 0.875rem; }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-xs { padding: 0.1rem 0.375rem; font-size: 0.6rem; }
    }
</style>
@endpush

@section('title', 'Mes Consultations')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Mes Consultations
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gérez vos fiches de consultation
            </p>
        </div>
        <a href="{{ route('cashier.consultations.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle Consultation
        </a>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    {{-- STATISTIQUES --}}
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total</p>
                    <p class="stat-value text-[var(--primary)]">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-total">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">En attente</p>
                    <p class="stat-value text-[#d97706]">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-pending">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">En traitement</p>
                    <p class="stat-value text-[#2563eb]">{{ $stats['processing'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-processing">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Terminées</p>
                    <p class="stat-value text-[#16a34a]">{{ $stats['completed'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-completed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTE --}}
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                        <tr class="consultation-row">
                            <td class="font-mono text-[var(--primary)] text-xs sm:text-sm">{{ $consultation->id }}</td>
                            <td class="text-xs sm:text-sm font-mono">{{ $consultation->code_id ?? 'N/A' }}</td>
                            <td class="text-sm">{{ $consultation->nom_complet }}</td>
                            <td class="text-[var(--text-tertiary)] text-xs sm:text-sm">
                                {{ $consultation->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
                                        'processing' => ['label' => 'En traitement', 'class' => 'badge-info'],
                                        'completed' => ['label' => 'Terminé', 'class' => 'badge-success'],
                                        'cancelled' => ['label' => 'Annulé', 'class' => 'badge-danger'],
                                    ];
                                    $status = $statusMap[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                    <a href="{{ route('cashier.consultations.show', $consultation) }}" class="btn btn-primary btn-xs sm:btn-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Voir
                                    </a>
                                    @if($consultation->status == 'completed')
                                        <a href="{{ route('cashier.consultations.print', $consultation) }}" target="_blank" class="btn btn-outline btn-xs sm:btn-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                            Imprimer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-tertiary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base font-medium">Aucune consultation</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Commencez par créer une nouvelle fiche</p>
                                <a href="{{ route('cashier.consultations.create') }}" class="btn btn-primary mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Nouvelle Consultation
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $consultations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection