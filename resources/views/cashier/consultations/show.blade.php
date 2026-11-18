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

    .checkout-card {
        padding: 0;
        overflow: hidden;
    }

    .checkout-card__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem 1rem;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, rgba(19, 65, 120, 0.06), rgba(19, 65, 120, 0.01));
        border-bottom: 1px solid var(--border-color);
    }

    .checkout-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary);
        letter-spacing: -0.01em;
    }

    .checkout-card__hint {
        margin: 0.25rem 0 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
        max-width: 36rem;
        line-height: 1.4;
    }

    .checkout-card__badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
    }

    .checkout-card__body {
        padding: 1rem 1.25rem 1.25rem;
    }

    .checkout-stats {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .checkout-stat {
        flex: 1 1 0;
        min-width: 7.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        background: var(--bg-secondary);
        padding: 0.65rem 0.85rem;
    }

    .checkout-stat .label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--text-tertiary);
        font-weight: 600;
        white-space: nowrap;
    }

    .checkout-stat .value {
        margin-top: 0.15rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
        white-space: nowrap;
    }

    .checkout-stat .value-muted {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .checkout-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 0 0.5rem;
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .checkout-section-title::before {
        content: '';
        width: 3px;
        height: 0.9rem;
        border-radius: 2px;
        background: var(--primary);
        flex-shrink: 0;
    }

    .checkout-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 1rem;
        padding: 0.875rem 1rem;
        border-radius: var(--radius-md, 8px);
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
    }

    .checkout-bar__meta {
        margin: 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }

    .checkout-bar__meta strong {
        color: var(--text-primary);
        font-weight: 700;
    }

    .checkout-bar__total {
        color: var(--primary);
        font-weight: 700;
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

    .table thead th.text-right,
    .table tbody td.text-right {
        text-align: right;
    }

    .table tbody td {
        padding: 0.5rem 0.75rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
    }

    .table thead th.col-price,
    .table tbody td.line-price-cell {
        text-align: right;
        width: 7.5rem;
        white-space: nowrap;
        padding-right: 1.5rem;
    }

    .table thead th.col-status,
    .table tbody td.col-status {
        padding-left: 1.25rem;
    }

    .table tbody td.line-price-cell > div {
        text-align: right;
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
        background: var(--primary-hover, #134178);
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

    .portion-toggle {
        display: inline-flex;
        flex-shrink: 0;
        align-items: stretch;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        overflow: hidden;
        background: var(--bg-secondary);
    }
    .portion-toggle label {
        margin: 0;
        cursor: pointer;
    }
    .portion-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .portion-toggle span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        padding: 0.2rem 0.45rem;
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--text-secondary);
        line-height: 1;
        user-select: none;
    }
    .portion-toggle label + label span {
        border-left: 1px solid var(--border-color);
    }
    .portion-toggle input:checked + span {
        background: var(--primary);
        color: #fff;
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

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-700 dark:text-green-400 text-sm animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

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
                <a href="{{ route('cashier.consultations.print', $consultation) }}" class="btn btn-primary btn-sm">
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
                <div class="value">
                    {{ $consultation->code_id ?? 'N/A' }}
                    @if($consultation->client)
                        <span class="text-xs text-[var(--text-secondary)] block">Membre PV : {{ $consultation->client->name }}</span>
                    @endif
                </div>
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

    @php
        $serviceLines = $serviceLines ?? \App\Services\ConsultationSaleService::serviceLines($consultation);
        $serviceStats = $serviceStats ?? \App\Services\ConsultationSaleService::serviceStats($consultation);
        $hasProductLines = ! empty($productLines);
        $hasServiceLines = collect($serviceLines)->contains(fn ($l) => ((int) ($l['seances_prescribed'] ?? 0)) > 0);
        $canCheckout = $canSell && ((($productStats['pending'] ?? 0) > 0) || (($serviceStats['pending'] ?? 0) > 0));
    @endphp

    {{-- PRODUITS + SERVICES : un seul encaissement --}}
    @if($hasProductLines || $hasServiceLines)
    <div class="card checkout-card">
        <div class="checkout-card__head">
            <div>
                <h3 class="checkout-card__title">Produits & services</h3>
                <p class="checkout-card__hint">
                    Sélectionnez produits et/ou séances, puis encaisser en une fois (paiement partiel possible).
                </p>
            </div>
            <div class="checkout-card__badges">
                @if($hasProductLines)
                    <span class="badge badge-info">{{ $productStats['pending'] ?? 0 }} produit(s) à payer</span>
                @endif
                @if($hasServiceLines)
                    <span class="badge badge-warning">{{ $serviceStats['pending'] ?? 0 }} service(s) ouvert(s)</span>
                @endif
            </div>
        </div>

        <div class="checkout-card__body">
        <div class="checkout-stats">
            @if($hasProductLines)
            <div class="checkout-stat">
                <div class="label">Produits prescrits</div>
                <div class="value">${{ number_format($productStats['recommended_amount'] ?? 0, 2) }}</div>
            </div>
            <div class="checkout-stat">
                <div class="label">Produits payés</div>
                <div class="value">${{ number_format($productStats['paid_amount'] ?? 0, 2) }}</div>
            </div>
            @endif
            @if($hasServiceLines)
            <div class="checkout-stat">
                <div class="label">Services prescrits</div>
                <div class="value">${{ number_format($serviceStats['recommended_amount'] ?? 0, 2) }}</div>
            </div>
            <div class="checkout-stat">
                <div class="label">Services payés / reste</div>
                <div class="value">
                    ${{ number_format($serviceStats['paid_amount'] ?? 0, 2) }}
                    <span class="value-muted">/ ${{ number_format($serviceStats['remaining_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
            @endif
        </div>

        @if($canCheckout)
        <form action="{{ route('cashier.consultations.checkout-selection', $consultation) }}" method="POST" id="consultationCheckoutForm">
            @csrf

            @if($hasProductLines)
            <h4 class="checkout-section-title">Produits recommandés</h4>
            <div class="table-wrap mb-5">
                <table class="table" id="consultationProductsTable">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" id="selectAllPending" class="rounded border-[var(--border-color)]" title="Tout sélectionner (à payer)">
                            </th>
                            <th>Produit</th>
                            <th>Portion</th>
                            <th>Posologie</th>
                            <th class="text-right col-price">Prix ($)</th>
                            <th class="col-status">Statut</th>
                            <th>Observation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productLines as $line)
                            @php
                                $lineStatus = $line['status'] ?? 'pending';
                                $isPending = $lineStatus === 'pending';
                                $enriched = \App\Services\ConsultationSaleService::enrichLinePricing($line);
                                $hasHalf = \App\Services\ConsultationSaleService::lineHasHalf($enriched);
                                $halfPrice = \App\Services\ConsultationSaleService::lineMinPrice($enriched);
                                $fullPrice = \App\Services\ConsultationSaleService::lineMaxPrice($enriched);
                                $defaultPrice = $fullPrice;
                            @endphp
                            <tr class="product-line-row"
                                data-half-price="{{ $halfPrice }}"
                                data-full-price="{{ $fullPrice }}"
                                data-has-half="{{ $hasHalf ? '1' : '0' }}">
                                <td>
                                    @if($isPending)
                                        <input type="checkbox"
                                               name="line_keys[]"
                                               value="{{ $line['line_key'] }}"
                                               class="line-select rounded border-[var(--border-color)]"
                                               data-price="{{ $defaultPrice }}">
                                    @endif
                                </td>
                                <td>{{ $line['produit'] ?? '' }}</td>
                                <td>
                                    @if($isPending && $hasHalf)
                                        <div class="portion-toggle" role="group" aria-label="Portion">
                                            <label>
                                                <input type="radio" class="line-portion" name="portions[{{ $line['line_key'] }}]" value="full" checked>
                                                <span>Flacon</span>
                                            </label>
                                            <label>
                                                <input type="radio" class="line-portion" name="portions[{{ $line['line_key'] }}]" value="half">
                                                <span>1/2</span>
                                            </label>
                                        </div>
                                    @elseif($isPending)
                                        <input type="hidden" name="portions[{{ $line['line_key'] }}]" value="full">
                                        <span class="text-xs text-[var(--text-tertiary)]">Flacon</span>
                                    @else
                                        <span class="text-xs text-[var(--text-secondary)]">
                                            {{ \App\Services\ConsultationSaleService::portionLabel($line['portion'] ?? null) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $line['posologie'] ?? '' }}</td>
                                <td class="text-right font-semibold line-price-cell">
                                    <span class="line-price-display">${{ number_format($defaultPrice, 2) }}</span>
                                    @if($hasHalf)
                                        <div class="text-[10px] font-normal text-[var(--text-tertiary)]">{{ \App\Services\ConsultationSaleService::priceLabel($enriched) }}</div>
                                    @endif
                                </td>
                                <td class="col-status">
                                    <span class="badge {{ \App\Services\ConsultationSaleService::statusBadgeClass($lineStatus) }}">
                                        {{ \App\Services\ConsultationSaleService::statusLabel($lineStatus) }}
                                    </span>
                                    @if(!empty($line['order_id']))
                                        <a href="{{ route('cashier.orders.show', $line['order_id']) }}" class="text-[10px] text-[var(--primary)] ml-1">Cmd #{{ $line['order_id'] }}</a>
                                    @endif
                                </td>
                                <td>{{ $line['observation'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($hasServiceLines)
            <h4 class="checkout-section-title mt-4">Services supplémentaires</h4>
            <div class="table-wrap mb-2">
                <table class="table" id="consultationServicesTable">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Prescrit</th>
                            <th>Payé</th>
                            <th>Reste</th>
                            <th class="text-right col-price">Prix ($)</th>
                            <th class="col-status">Statut</th>
                            <th>Séances maintenant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceLines as $svc)
                            @if(((int) ($svc['seances_prescribed'] ?? 0)) <= 0)
                                @continue
                            @endif
                            @php $remaining = (int) ($svc['seances_remaining'] ?? 0); @endphp
                            <tr>
                                <td>{{ $svc['label'] }}</td>
                                <td>x {{ (int) $svc['seances_prescribed'] }}</td>
                                <td>x {{ (int) $svc['seances_paid'] }}</td>
                                <td>x {{ $remaining }}</td>
                                <td class="text-right font-semibold line-price-cell">
                                    <div>${{ number_format($svc['prix'], 2) }}</div>
                                    <div class="text-[10px] font-normal text-[var(--text-tertiary)] leading-tight">
                                        payé ${{ number_format($svc['amount_paid'], 2) }}<br>
                                        reste ${{ number_format($svc['amount_remaining'], 2) }}
                                    </div>
                                </td>
                                <td class="col-status">
                                    <span class="badge {{ \App\Services\ConsultationSaleService::statusBadgeClass($svc['status']) }}">
                                        {{ \App\Services\ConsultationSaleService::statusLabel($svc['status']) }}
                                    </span>
                                    @if(!empty($svc['order_id']))
                                        <a href="{{ route('cashier.orders.show', $svc['order_id']) }}" class="text-[10px] text-[var(--primary)] ml-1">Cmd #{{ $svc['order_id'] }}</a>
                                    @endif
                                </td>
                                <td>
                                    @if($remaining > 0)
                                        <div class="inline-flex items-center gap-1">
                                            <input type="number"
                                                   id="service_qty_{{ $svc['key'] }}"
                                                   name="service_qty[{{ $svc['key'] }}]"
                                                   min="0"
                                                   max="{{ $remaining }}"
                                                   value="0"
                                                   class="service-qty w-16 form-control form-control-sm text-center"
                                                   data-unit-price="{{ $svc['prix'] }}"
                                                   data-max="{{ $remaining }}"
                                                   aria-label="Séances {{ $svc['label'] }}">
                                            <span class="text-xs text-[var(--text-tertiary)]">/ {{ $remaining }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-[var(--text-tertiary)]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="checkout-bar">
                <p class="checkout-bar__meta">
                    Sélection :
                    <strong id="selectionCount">0</strong> produit(s),
                    <strong id="serviceSelectionCount">0</strong> séance(s) —
                    <span class="checkout-bar__total" id="selectionTotal">$0.00</span>
                </p>
                <button type="submit" class="btn btn-primary btn-sm" id="checkoutSelectionBtn" disabled>
                    Encaisser
                </button>
            </div>
        </form>
        @else
            @if($hasProductLines)
            <h4 class="checkout-section-title">Produits recommandés</h4>
            <div class="table-wrap mb-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Posologie</th>
                            <th class="text-right col-price">Prix ($)</th>
                            <th class="col-status">Statut</th>
                            <th>Observation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productLines as $line)
                            @php $lineStatus = $line['status'] ?? 'pending'; @endphp
                            <tr>
                                <td>{{ \App\Services\ConsultationSaleService::productDisplayName($line) }}</td>
                                <td>{{ $line['posologie'] ?? '' }}</td>
                                <td class="text-right font-semibold">{{ \App\Services\ConsultationSaleService::priceLabel($line) }}</td>
                                <td>
                                    <span class="badge {{ \App\Services\ConsultationSaleService::statusBadgeClass($lineStatus) }}">
                                        {{ \App\Services\ConsultationSaleService::statusLabel($lineStatus) }}
                                    </span>
                                </td>
                                <td>{{ $line['observation'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($hasServiceLines)
            <h4 class="checkout-section-title mt-4">Services supplémentaires</h4>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Prescrit</th>
                            <th>Payé</th>
                            <th>Reste</th>
                            <th class="text-right col-price">Prix ($)</th>
                            <th class="col-status">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceLines as $svc)
                            @if(((int) ($svc['seances_prescribed'] ?? 0)) <= 0)
                                @continue
                            @endif
                            <tr>
                                <td>{{ $svc['label'] }}</td>
                                <td>x {{ (int) $svc['seances_prescribed'] }}</td>
                                <td>x {{ (int) $svc['seances_paid'] }}</td>
                                <td>x {{ (int) $svc['seances_remaining'] }}</td>
                                <td class="text-right font-semibold line-price-cell">${{ number_format($svc['prix'], 2) }}</td>
                                <td class="col-status">
                                    <span class="badge {{ \App\Services\ConsultationSaleService::statusBadgeClass($svc['status']) }}">
                                        {{ \App\Services\ConsultationSaleService::statusLabel($svc['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!$canSell)
                <p class="text-xs text-[var(--text-secondary)] mt-3">
                    L’encaissement est disponible lorsque la fiche est <strong>en traitement</strong> ou <strong>terminée</strong>.
                </p>
            @else
                <p class="text-xs text-[var(--text-secondary)] mt-3">Rien à encaisser pour le moment.</p>
            @endif
        @endif
        </div>
    </div>
    @elseif($consultation->recommended_products && count($consultation->recommended_products) > 0)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Produits recommandés</h3>
        <p class="text-sm text-[var(--text-secondary)] mb-3">
            Ces lignes ne sont pas liées au catalogue (sans identifiant produit) : encaissement partiel indisponible.
        </p>
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
                        <td>{{ \App\Services\ConsultationSaleService::productDisplayName($product) }}</td>
                        <td>{{ $product['posologie'] ?? '' }}</td>
                        <td class="text-right font-semibold">{{ \App\Services\ConsultationSaleService::priceLabel($product) }}</td>
                        <td>{{ $product['observation'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($consultation->orders && $consultation->orders->isNotEmpty())
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Commandes liées</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Date</th>
                        <th class="text-right">Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultation->orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('cashier.orders.show', $order->id) }}" class="text-[var(--primary)] font-medium">
                                {{ $order->order_number ?? '#' . $order->id }}
                            </a>
                        </td>
                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-right font-semibold">${{ number_format($order->total ?? 0, 2) }}</td>
                        <td>{{ ucfirst($order->status ?? '—') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- NOTES ADMIN --}}
    @if($consultation->admin_notes)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-2">Notes de l'administrateur</h3>
        <p class="text-sm text-[var(--text-primary)]">{{ $consultation->admin_notes }}</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    function formatMoney(n) {
        return '$' + n.toFixed(2);
    }

    const form = document.getElementById('consultationCheckoutForm');
    if (!form) return;

    const selectAll = document.getElementById('selectAllPending');
    const boxes = form.querySelectorAll('.line-select');
    const qtyInputs = form.querySelectorAll('.service-qty');
    const productCountEl = document.getElementById('selectionCount');
    const serviceCountEl = document.getElementById('serviceSelectionCount');
    const totalEl = document.getElementById('selectionTotal');
    const submitBtn = document.getElementById('checkoutSelectionBtn');

    function syncRowPrice(row) {
        if (!row) return 0;
        const hasHalf = row.dataset.hasHalf === '1';
        const fullPrice = parseFloat(row.dataset.fullPrice || '0') || 0;
        const halfPrice = parseFloat(row.dataset.halfPrice || '0') || 0;
        const portionInput = row.querySelector('.line-portion:checked')
            || row.querySelector('input[name^="portions["]');
        const portion = portionInput ? portionInput.value : 'full';
        const price = (portion === 'half' && hasHalf) ? halfPrice : fullPrice;
        const checkbox = row.querySelector('.line-select');
        const display = row.querySelector('.line-price-display');
        if (checkbox) checkbox.dataset.price = String(price);
        if (display) display.textContent = formatMoney(price);
        return price;
    }

    function refresh() {
        let productCount = 0;
        let productTotal = 0;
        boxes.forEach(function (cb) {
            const row = cb.closest('.product-line-row');
            syncRowPrice(row);
            if (cb.checked) {
                productCount++;
                productTotal += parseFloat(cb.dataset.price || '0') || 0;
            }
        });

        let serviceCount = 0;
        let serviceTotal = 0;
        qtyInputs.forEach(function (input) {
            const max = parseInt(input.dataset.max || '0', 10) || 0;
            let qty = parseInt(input.value || '0', 10) || 0;
            if (qty < 0) qty = 0;
            if (qty > max) qty = max;
            input.value = String(qty);
            const unit = parseFloat(input.dataset.unitPrice || '0') || 0;
            serviceCount += qty;
            serviceTotal += qty * unit;
        });

        if (productCountEl) productCountEl.textContent = String(productCount);
        if (serviceCountEl) serviceCountEl.textContent = String(serviceCount);
        if (totalEl) totalEl.textContent = formatMoney(productTotal + serviceTotal);
        if (submitBtn) submitBtn.disabled = (productCount + serviceCount) === 0;

        if (selectAll) {
            const pending = boxes.length;
            selectAll.checked = pending > 0 && productCount === pending;
            selectAll.indeterminate = productCount > 0 && productCount < pending;
        }
    }

    boxes.forEach(function (cb) {
        cb.addEventListener('change', refresh);
    });

    form.querySelectorAll('.line-portion').forEach(function (input) {
        input.addEventListener('change', function () {
            const row = input.closest('.product-line-row');
            const checkbox = row ? row.querySelector('.line-select') : null;
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
            }
            refresh();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            boxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
            refresh();
        });
    }

    qtyInputs.forEach(function (input) {
        input.addEventListener('input', refresh);
        input.addEventListener('change', refresh);
    });

    refresh();
})();
</script>
@endpush