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

    {{-- PRODUITS RECOMMANDÉS (vente partielle) --}}
    @if(!empty($productLines))
    <div class="card">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
            <div>
                <h3 class="text-base font-semibold text-[var(--primary)]">Produits recommandés</h3>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">
                    Le patient peut n’en acheter qu’une partie lors d’une ou plusieurs ventes.
                </p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="badge badge-info">{{ $productStats['total'] ?? 0 }} recommandé(s)</span>
                <span class="badge badge-success">{{ $productStats['purchased'] ?? 0 }} payé(s)</span>
                <span class="badge badge-warning">{{ $productStats['pending'] ?? 0 }} à payer</span>
                @if(($productStats['declined'] ?? 0) > 0)
                    <span class="badge badge-secondary">{{ $productStats['declined'] }} refusé(s)</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <div class="total-box py-3 px-4">
                <div class="label">Total recommandé (produits)</div>
                <div class="value text-xl">${{ number_format($productStats['recommended_amount'] ?? 0, 2) }}</div>
            </div>
            <div class="total-box py-3 px-4">
                <div class="label">Total payé (produits)</div>
                <div class="value text-xl">${{ number_format($productStats['paid_amount'] ?? 0, 2) }}</div>
            </div>
        </div>

        @if($canSell && ($productStats['pending'] ?? 0) > 0)
        <form action="{{ route('cashier.consultations.checkout-selection', $consultation) }}" method="POST" id="consultationCheckoutForm">
            @csrf
            <div class="table-wrap">
                <table class="table" id="consultationProductsTable">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" id="selectAllPending" class="rounded border-[var(--border-color)]" title="Tout sélectionner (à payer)">
                            </th>
                            <th>Produit</th>
                            <th>Posologie</th>
                            <th class="text-right">Prix ($)</th>
                            <th>Statut</th>
                            <th>Observation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productLines as $line)
                            @php
                                $lineStatus = $line['status'] ?? 'pending';
                                $isPending = $lineStatus === 'pending';
                            @endphp
                            <tr data-price="{{ (float) ($line['prix'] ?? 0) }}">
                                <td>
                                    @if($isPending)
                                        <input type="checkbox"
                                               name="line_keys[]"
                                               value="{{ $line['line_key'] }}"
                                               class="line-select rounded border-[var(--border-color)]"
                                               data-price="{{ (float) ($line['prix'] ?? 0) }}">
                                    @endif
                                </td>
                                <td>{{ $line['produit'] ?? '' }}</td>
                                <td>{{ $line['posologie'] ?? '' }}</td>
                                <td class="text-right font-semibold">${{ number_format($line['prix'] ?? 0, 2) }}</td>
                                <td>
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
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t border-[var(--border-color)]">
                <p class="text-sm text-[var(--text-secondary)]">
                    Sélection : <span class="font-semibold text-[var(--text-primary)]" id="selectionCount">0</span> produit(s) —
                    <span class="font-semibold text-[var(--primary)]" id="selectionTotal">$0.00</span>
                </p>
                <button type="submit" class="btn btn-primary btn-sm" id="checkoutSelectionBtn" disabled>
                    Encaisser la sélection
                </button>
            </div>
        </form>
        @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Posologie</th>
                        <th class="text-right">Prix ($)</th>
                        <th>Statut</th>
                        <th>Observation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productLines as $line)
                        @php $lineStatus = $line['status'] ?? 'pending'; @endphp
                        <tr>
                            <td>{{ $line['produit'] ?? '' }}</td>
                            <td>{{ $line['posologie'] ?? '' }}</td>
                            <td class="text-right font-semibold">${{ number_format($line['prix'] ?? 0, 2) }}</td>
                            <td>
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
        @if(!$canSell)
            <p class="text-xs text-[var(--text-secondary)] mt-3">
                L’encaissement des produits est disponible lorsque la fiche est <strong>en traitement</strong> ou <strong>terminée</strong>.
            </p>
        @elseif(($productStats['pending'] ?? 0) === 0)
            <p class="text-xs text-[var(--text-secondary)] mt-3">Tous les produits recommandés ont été traités (payés ou refusés).</p>
        @endif
        @endif
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
                        <td>{{ $product['produit'] ?? '' }}</td>
                        <td>{{ $product['posologie'] ?? '' }}</td>
                        <td class="text-right font-semibold">${{ number_format($product['prix'] ?? 0, 2) }}</td>
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

    {{-- SERVICES SUPPLÉMENTAIRES --}}
    @if($consultation->seances_ceragem > 0 || $consultation->seances_detox > 0)
    <div class="card">
        <h3 class="text-base font-semibold text-[var(--primary)] mb-3">Services Supplementaires</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @if($consultation->seances_ceragem > 0)
            <div class="service-box service-box-ceragem">
                <div class="font-semibold text-[var(--ui-stat-warning)]">Ceragem</div>
                <p class="text-sm">
                    {{ $consultation->seances_ceragem }} seances × ${{ number_format($consultation->prix_ceragem, 2) }}
                    <br>
                    <span class="font-bold">= ${{ number_format($consultation->seances_ceragem * $consultation->prix_ceragem, 2) }}</span>
                </p>
            </div>
            @endif
            @if($consultation->seances_detox > 0)
            <div class="service-box service-box-detox">
                <div class="font-semibold text-[var(--ui-stat-success)]">Detox</div>
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

    {{-- SYNTHÈSE FINANCIÈRE --}}
    <div class="total-box">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <div class="label">Estimation globale (fiche)</div>
                <div class="text-sm text-[var(--text-secondary)]">
                    Produits recommandés + services (Ceragem / Detox). Ce montant n’est pas le total encaissé.
                </div>
            </div>
            <div class="value">${{ number_format($consultation->total_general, 2) }}</div>
        </div>
        @if(!empty($productStats))
        <div class="mt-3 pt-3 border-t border-[var(--border-color)] text-sm text-[var(--text-secondary)] flex flex-wrap gap-x-6 gap-y-1">
            <span>Produits payés : <strong class="text-[var(--text-primary)]">${{ number_format($productStats['paid_amount'] ?? 0, 2) }}</strong></span>
            <span>Produits restants à payer : <strong class="text-[var(--text-primary)]">${{ number_format(max(0, ($productStats['recommended_amount'] ?? 0) - ($productStats['paid_amount'] ?? 0)), 2) }}</strong></span>
        </div>
        @endif
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

@push('scripts')
<script>
(function () {
    const form = document.getElementById('consultationCheckoutForm');
    if (!form) return;

    const selectAll = document.getElementById('selectAllPending');
    const boxes = form.querySelectorAll('.line-select');
    const countEl = document.getElementById('selectionCount');
    const totalEl = document.getElementById('selectionTotal');
    const submitBtn = document.getElementById('checkoutSelectionBtn');

    function formatMoney(n) {
        return '$' + n.toFixed(2);
    }

    function refresh() {
        let count = 0;
        let total = 0;
        boxes.forEach(function (cb) {
            if (cb.checked) {
                count++;
                total += parseFloat(cb.dataset.price || '0') || 0;
            }
        });
        if (countEl) countEl.textContent = String(count);
        if (totalEl) totalEl.textContent = formatMoney(total);
        if (submitBtn) submitBtn.disabled = count === 0;
        if (selectAll) {
            const pending = boxes.length;
            selectAll.checked = pending > 0 && count === pending;
            selectAll.indeterminate = count > 0 && count < pending;
        }
    }

    boxes.forEach(function (cb) {
        cb.addEventListener('change', refresh);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            boxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
            refresh();
        });
    }

    refresh();
})();
</script>
@endpush