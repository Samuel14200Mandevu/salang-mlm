{{-- resources/views/cashier/consultations/index.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .consultation-row {
        transition: all 0.3s ease;
    }
    .consultation-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-md {
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
    }
    
    .btn-primary {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
    }
    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.4);
    }
    
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
    }
    
    .btn-success {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
    }
    .btn-success:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.4);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .card { padding: 0.875rem; }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-sm {
            padding: 0.125rem 0.375rem;
            font-size: 0.6rem;
        }
    }
</style>
@endpush

@section('title', 'Mes Consultations')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-blue-600 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Mes Consultations
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gérez vos fiches de consultation
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.consultations.create') }}" class="btn btn-success btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle Consultation
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success p-3 sm:p-4 bg-green-500/10 text-green-600 rounded-lg border border-green-500/20 animate-fadeInUp">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning p-3 sm:p-4 bg-yellow-500/10 text-yellow-600 rounded-lg border border-yellow-500/20 animate-fadeInUp">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-blue-600">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-400 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-xl sm:text-2xl font-bold text-blue-400">{{ $stats['processing'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $stats['completed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">#</th>
                        <th class="text-xs sm:text-sm">Code</th>
                        <th class="text-xs sm:text-sm">Patient</th>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                        <tr class="consultation-row">
                            <td class="font-mono text-xs sm:text-sm text-blue-600">#{{ $consultation->id }}</td>
                            <td class="text-xs sm:text-sm font-mono">{{ $consultation->code_id ?? 'N/A' }}</td>
                            <td class="text-sm sm:text-base">{{ $consultation->nom_complet }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $consultation->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'processing' => 'En traitement',
                                        'completed' => 'Terminé',
                                        'cancelled' => 'Annulé',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$consultation->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$consultation->status] ?? ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cashier.consultations.show', $consultation) }}" class="btn btn-primary btn-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Voir
                                    </a>
                                    @if($consultation->status == 'completed')
                                        <a href="{{ route('cashier.consultations.print', $consultation) }}" target="_blank" class="btn btn-outline btn-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                            Imprimer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune consultation</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Commencez par créer une nouvelle fiche</p>
                                <a href="{{ route('cashier.consultations.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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