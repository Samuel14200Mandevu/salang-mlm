{{-- resources/views/cashier/consultations/show.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }

    .info-item .label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        font-weight: 600;
    }

    .info-item .value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-top: 0.125rem;
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

    .total-box {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem 1.5rem;
    }

    .total-box .label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        font-weight: 600;
    }

    .total-box .value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
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
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.5rem 0.75rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

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
        transition: background 0.2s ease;
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

    .btn-sm {
        padding: 0.3rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }

    .service-box {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        border: 1px solid var(--border-color);
    }

    .service-box-ceragem {
        background: rgba(245, 158, 11, 0.04);
        border-color: rgba(245, 158, 11, 0.15);
    }

    .service-box-detox {
        background: rgba(34, 197, 94, 0.04);
        border-color: rgba(34, 197, 94, 0.15);
    }

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
        .service-box { padding: 0.5rem 0.75rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
    }

    @media (max-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.65rem;
        }
        .total-box .value {
            font-size: 1.25rem;
        }
        .btn-xs { padding: 0.1rem 0.375rem; font-size: 0.6rem; }
    }
</style>
@endpush

@section('title', 'Consultation #' . $consultation->id)

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Fiche {{ $consultation->id }}
            </h1>
            <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                <span class="text-sm text-[var(--text-secondary)]">
                    Créée le {{ $consultation->created_at->format('d/m/Y H:i') }}
                </span>
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
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.consultations.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            @if($consultation->status == 'completed')
                <a href="{{ route('cashier.consultations.print', $consultation) }}" target="_blank" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </a>
            @endif
        </div>
    </div>

    {{-- INFOS PATIENT --}}
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Informations du Patient</h3>
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

    {{-- CONSULTATION --}}
    @if($consultation->reason || $consultation->symptoms || $consultation->observations)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Consultation</h3>
        <div class="space-y-3">
            @if($consultation->reason)
                <div>
                    <div class="text-xs uppercase text-[var(--text-tertiary)] font-semibold">Motif</div>
                    <p class="text-sm text-[var(--text-primary)]">{{ $consultation->reason }}</p>
                </div>
            @endif
            @if($consultation->symptoms)
                <div>
                    <div class="text-xs uppercase text-[var(--text-tertiary)] font-semibold">Symptomes</div>
                    <p class="text-sm text-[var(--text-primary)]">{{ $consultation->symptoms }}</p>
                </div>
            @endif
            @if($consultation->observations)
                <div>
                    <div class="text-xs uppercase text-[var(--text-tertiary)] font-semibold">Observations</div>
                    <p class="text-sm text-[var(--text-primary)]">{{ $consultation->observations }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- PRODUITS RECOMMANDÉS --}}
    @if($consultation->recommended_products && count($consultation->recommended_products) > 0)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Produits Recommandes</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Posologie</th>
                        <th class="text-right">Prix ($)</th>
                        <th>Observation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultation->recommended_products as $product)
                    <tr>
                        <td>{{ $product['produit'] ?? '' }}</td>
                        <td>{{ $product['posologie'] ?? '' }}</td>
                        <td class="text-right font-semibold">${{ number_format($product['prix'] ?? 0, 2) }}</td>
                        <td>{{ $product['observation'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-right font-semibold mt-3 text-sm">
            Total Produits: <span class="text-[var(--primary)]">${{ number_format($consultation->total_produits, 2) }}</span>
        </div>
    </div>
    @endif

    {{-- SERVICES SUPPLÉMENTAIRES --}}
    @if($consultation->seances_ceragem > 0 || $consultation->seances_detox > 0)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Services Supplementaires</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @if($consultation->seances_ceragem > 0)
            <div class="service-box service-box-ceragem">
                <div class="font-semibold text-[#d97706]">Ceragem</div>
                <p class="text-sm">
                    {{ $consultation->seances_ceragem }} seances × ${{ number_format($consultation->prix_ceragem, 2) }}
                    <br>
                    <span class="font-bold">= ${{ number_format($consultation->seances_ceragem * $consultation->prix_ceragem, 2) }}</span>
                </p>
            </div>
            @endif
            @if($consultation->seances_detox > 0)
            <div class="service-box service-box-detox">
                <div class="font-semibold text-[#16a34a]">Detox</div>
                <p class="text-sm">
                    {{ $consultation->seances_detox }} seances × ${{ number_format($consultation->prix_detox, 2) }}
                    <br>
                    <span class="font-bold">= ${{ number_format($consultation->seances_detox * $consultation->prix_detox, 2) }}</span>
                </p>
            </div>
            @endif
        </div>
        <div class="text-right font-semibold mt-3 text-sm">
            Total Services: <span class="text-[var(--primary)]">${{ number_format($consultation->total_services, 2) }}</span>
        </div>
    </div>
    @endif

    {{-- TOTAL GENERAL --}}
    <div class="total-box">
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

    {{-- NOTES ADMIN --}}
    @if($consultation->admin_notes)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-2">Notes de l'administrateur</h3>
        <p class="text-sm text-[var(--text-primary)]">{{ $consultation->admin_notes }}</p>
    </div>
    @endif
</div>
@endsection