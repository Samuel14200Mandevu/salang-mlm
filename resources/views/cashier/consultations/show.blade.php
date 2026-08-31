{{-- resources/views/cashier/consultations/show.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .card:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 24px rgba(37, 99, 235, 0.08);
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
    .info-item .label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
        font-weight: 600;
    }
    .info-item .value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-top: 0.125rem;
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
    
    .total-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #93c5fd;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }
    .total-box .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #1d4ed8;
        font-weight: 600;
    }
    .total-box .value {
        font-size: 2rem;
        font-weight: 800;
        color: #2563eb;
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.5rem 0.75rem;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-light);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    
    @media (max-width: 640px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .info-item .value {
            font-size: 0.85rem;
        }
        .total-box .value {
            font-size: 1.5rem;
        }
        .card {
            padding: 0.875rem;
        }
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
    }
    @media (max-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('title', 'Consultation #' . $consultation->id)

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-blue-600 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Fiche #{{ $consultation->id }}
            </h1>
            <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                <span class="text-sm text-[var(--text-secondary)]">Créée le {{ $consultation->created_at->format('d/m/Y H:i') }}</span>
                <span class="badge 
                    @if($consultation->status == 'pending') badge-warning
                    @elseif($consultation->status == 'processing') badge-info
                    @elseif($consultation->status == 'completed') badge-success
                    @else badge-danger @endif">
                    {{ $consultation->status_label }}
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.consultations.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            @if($consultation->status == 'completed')
                <a href="{{ route('cashier.consultations.print', $consultation) }}" target="_blank" class="btn btn-primary btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </a>
            @endif
        </div>
    </div>

    <!-- Infos Patient -->
    <div class="card animate-fadeInUp delay-1">
        <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Informations du Patient</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Code ID</div>
                <div class="value">{{ $consultation->code_id ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Numero de dossier</div>
                <div class="value">{{ $consultation->numero ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Nom complet</div>
                <div class="value">{{ $consultation->nom_complet }}</div>
            </div>
            <div class="info-item">
                <div class="label">Genre</div>
                <div class="value">{{ $consultation->genre_label }}</div>
            </div>
            <div class="info-item">
                <div class="label">Age</div>
                <div class="value">{{ $consultation->age ?? 'N/A' }} ans</div>
            </div>
            <div class="info-item">
                <div class="label">Poids</div>
                <div class="value">{{ $consultation->poids ?? 'N/A' }} kg</div>
            </div>
            <div class="info-item">
                <div class="label">Taille</div>
                <div class="value">{{ $consultation->taille ?? 'N/A' }} cm</div>
            </div>
            <div class="info-item">
                <div class="label">Date de l'examen</div>
                <div class="value">{{ $consultation->date_examen ? $consultation->date_examen->format('d/m/Y') : 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Caissier</div>
                <div class="value">{{ $consultation->cashier?->name ?? 'N/A' }}</div>
            </div>
            @if($consultation->admin)
            <div class="info-item">
                <div class="label">Traite par</div>
                <div class="value">{{ $consultation->admin?->name ?? 'N/A' }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Consultation -->
    @if($consultation->reason || $consultation->symptoms || $consultation->observations)
    <div class="card animate-fadeInUp delay-2">
        <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Consultation</h3>
        <div class="space-y-3">
            @if($consultation->reason)
                <div>
                    <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Motif</div>
                    <p class="text-sm sm:text-base text-[var(--text-primary)]">{{ $consultation->reason }}</p>
                </div>
            @endif
            @if($consultation->symptoms)
                <div>
                    <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Symptomes</div>
                    <p class="text-sm sm:text-base text-[var(--text-primary)]">{{ $consultation->symptoms }}</p>
                </div>
            @endif
            @if($consultation->observations)
                <div>
                    <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Observations</div>
                    <p class="text-sm sm:text-base text-[var(--text-primary)]">{{ $consultation->observations }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Produits -->
    @if($consultation->recommended_products && count($consultation->recommended_products) > 0)
    <div class="card animate-fadeInUp delay-3">
        <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Produits Recommandes</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Posologie</th>
                        <th style="text-align:right">Prix ($)</th>
                        <th>Observation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultation->recommended_products as $product)
                    <tr>
                        <td>{{ $product['produit'] ?? '' }}</td>
                        <td>{{ $product['posologie'] ?? '' }}</td>
                        <td style="text-align:right;font-weight:600;">${{ number_format($product['prix'] ?? 0, 2) }}</td>
                        <td>{{ $product['observation'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-right font-bold mt-3 text-sm sm:text-base">
            Total Produits: <span class="text-blue-600">${{ number_format($consultation->total_produits, 2) }}</span>
        </div>
    </div>
    @endif

    <!-- Services -->
    @if($consultation->seances_ceragem > 0 || $consultation->seances_detox > 0)
    <div class="card animate-fadeInUp delay-4">
        <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Services Supplementaires</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @if($consultation->seances_ceragem > 0)
            <div class="bg-amber-50 dark:bg-amber-900/20 p-3 sm:p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                <div class="font-semibold text-amber-600">Ceragem</div>
                <p class="text-sm sm:text-base">
                    {{ $consultation->seances_ceragem }} seances × ${{ number_format($consultation->prix_ceragem, 2) }}
                    <br>
                    <span class="font-bold">= ${{ number_format($consultation->seances_ceragem * $consultation->prix_ceragem, 2) }}</span>
                </p>
            </div>
            @endif
            @if($consultation->seances_detox > 0)
            <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 sm:p-4 rounded-lg border border-emerald-200 dark:border-emerald-800">
                <div class="font-semibold text-emerald-600">Detox</div>
                <p class="text-sm sm:text-base">
                    {{ $consultation->seances_detox }} seances × ${{ number_format($consultation->prix_detox, 2) }}
                    <br>
                    <span class="font-bold">= ${{ number_format($consultation->seances_detox * $consultation->prix_detox, 2) }}</span>
                </p>
            </div>
            @endif
        </div>
        <div class="text-right font-bold mt-3 text-sm sm:text-base">
            Total Services: <span class="text-blue-600">${{ number_format($consultation->total_services, 2) }}</span>
        </div>
    </div>
    @endif

    <!-- Total General -->
    <div class="total-box animate-fadeInUp delay-5">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <div class="label">Total General</div>
                <div class="text-sm text-[var(--text-secondary)]">
                    Produits + Services
                </div>
            </div>
            <div class="value">${{ number_format($consultation->total_general, 2) }}</div>
        </div>
    </div>

    <!-- Admin Notes -->
    @if($consultation->admin_notes)
    <div class="card animate-fadeInUp delay-6">
        <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-2">Notes de l'administrateur</h3>
        <p class="text-sm sm:text-base text-[var(--text-primary)]">{{ $consultation->admin_notes }}</p>
    </div>
    @endif
</div>
@endsection