{{-- resources/views/admin/consultations/show.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.card:hover {
    border-color: var(--primary-blue);
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
    font-size: 0.938rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-top: 0.125rem;
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

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}
.btn-danger {
    background: #B91C1C;
    color: #fff;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
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
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

.form-control {
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
.form-control:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}

.form-control-sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.813rem;
}

.product-select {
    flex: 1 1 180px;
    min-width: 140px;
}
.product-price-display {
    min-width: 7.5rem;
    width: auto;
    max-width: 10rem;
    padding: 0.375rem 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.75rem;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    text-transform: uppercase;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-light);
}

.total-box {
    background: var(--primary-blue-bg);
    border: 1px solid var(--primary-blue-border);
    border-radius: 8px;
    padding: 1.25rem;
}
.total-box .label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--primary-blue);
    font-weight: 600;
}
.total-box .value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-blue);
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
    .grid-cols-3 {
        grid-template-columns: 1fr !important;
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

/* ===== MODALE SUPPRESSION ===== */
.consultation-delete-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.consultation-delete-overlay.active {
    opacity: 1;
    visibility: visible;
}
.consultation-delete-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem 1.75rem;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
    transform: scale(0.96) translateY(6px);
    transition: transform 0.2s ease;
}
.consultation-delete-overlay.active .consultation-delete-box {
    transform: scale(1) translateY(0);
}
.consultation-delete-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
}
.consultation-delete-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}
.consultation-delete-title {
    text-align: center;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}
.consultation-delete-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.55;
    margin-bottom: 0.75rem;
}
.consultation-delete-text strong {
    color: var(--text-primary);
}
.consultation-delete-warning {
    text-align: center;
    font-size: 0.75rem;
    color: #B91C1C;
    background: rgba(185, 28, 28, 0.06);
    border: 1px solid rgba(185, 28, 28, 0.15);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    margin-bottom: 1.25rem;
}
.consultation-delete-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}
.consultation-delete-actions .btn {
    min-width: 110px;
}
</style>
@endpush

@section('title', 'Fiche de Consultation ' . $consultation->id)

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Fiche {{ $consultation->id }}
            </h1>
            <div class="flex items-center gap-2 mt-0.5">
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
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.consultations.print', $consultation) }}" target="_blank" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </a>
            <button type="button" id="openConsultationDeleteModal" class="btn btn-sm" style="background:#B91C1C;color:#fff;border-color:#B91C1C;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
            <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <form action="{{ route('admin.consultations.update', $consultation) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Informations Patient -->
                <div class="card animate-fadeInUp delay-1">
                    <h3 class="text-base font-semibold text-[var(--primary-blue)] mb-3">Informations du Patient</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Code ID <span class="text-[var(--ui-stat-danger)]">*</span></div>
                            <input type="text" name="code_id" id="code_id" required class="form-control form-control-sm mt-1"
                                   value="{{ old('code_id', $consultation->code_id) }}"
                                   placeholder="Code membre"
                                   autocomplete="off">
                            <div id="codeIdInfo" class="text-xs mt-1 @if($consultation->client) text-[var(--ui-stat-success)] @endif">
                                @if($consultation->client)
                                    Membre : <strong>{{ $consultation->client->name }}</strong>
                                @endif
                            </div>
                            @error('code_id') <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="info-item">
                            <div class="label">Numéro</div>
                            <input type="text" name="numero" class="form-control form-control-sm mt-1"
                                   value="{{ old('numero', $consultation->numero) }}">
                        </div>
                        <div class="info-item">
                            <div class="label">Nom <span class="text-[var(--ui-stat-danger)]">*</span></div>
                            <input type="text" name="nom_complet" required class="form-control form-control-sm mt-1"
                                   value="{{ old('nom_complet', $consultation->nom_complet) }}">
                            @error('nom_complet') <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="info-item">
                            <div class="label">Genre</div>
                            <select name="genre" class="form-control form-control-sm mt-1">
                                <option value="">Sélectionner</option>
                                <option value="masculin" @selected(old('genre', $consultation->genre) === 'masculin')>Masculin</option>
                                <option value="feminin" @selected(old('genre', $consultation->genre) === 'feminin')>Féminin</option>
                            </select>
                        </div>
                        <div class="info-item">
                            <div class="label">Âge (ans)</div>
                            <input type="number" name="age" min="0" max="150" class="form-control form-control-sm mt-1"
                                   value="{{ old('age', $consultation->age) }}">
                        </div>
                        <div class="info-item">
                            <div class="label">Poids (kg)</div>
                            <input type="number" name="poids" step="0.1" min="0" class="form-control form-control-sm mt-1"
                                   value="{{ old('poids', $consultation->poids) }}">
                        </div>
                        <div class="info-item">
                            <div class="label">Taille (cm)</div>
                            <input type="number" name="taille" step="0.1" min="0" class="form-control form-control-sm mt-1"
                                   value="{{ old('taille', $consultation->taille) }}">
                        </div>
                        <div class="info-item">
                            <div class="label">Date examen</div>
                            <input type="date" name="date_examen" class="form-control form-control-sm mt-1"
                                   value="{{ old('date_examen', optional($consultation->date_examen)->format('Y-m-d')) }}">
                        </div>
                        <div class="info-item">
                            <div class="label">Caissier</div>
                            <div class="value">{{ $consultation->cashier?->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <br>

                <!-- Consultation -->
                <div class="card animate-fadeInUp delay-2">
                    <h3 class="text-base font-semibold text-[var(--primary-blue)] mb-3">Consultation</h3>
                    <div class="space-y-3">
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Motif</div>
                            <textarea name="reason" rows="2"
                                      class="form-control mt-1">{{ old('reason', $consultation->reason) }}</textarea>
                        </div>
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Symptômes</div>
                            <textarea name="symptoms" rows="2"
                                      class="form-control mt-1">{{ old('symptoms', $consultation->symptoms) }}</textarea>
                        </div>
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Observations</div>
                            <textarea name="observations" rows="2"
                                      class="form-control mt-1">{{ old('observations', $consultation->observations) }}</textarea>
                        </div>
                    </div>
                </div><br>

                <!-- Produits -->
                <div class="card animate-fadeInUp delay-3">
                    <h3 class="text-base font-semibold text-[var(--primary-blue)] mb-3">Produits Recommandés</h3>

                    <div id="productsContainer">
                        @php $productIndex = 0; @endphp
                        @if($consultation->recommended_products && count($consultation->recommended_products) > 0)
                            @foreach($consultation->recommended_products as $product)
                                @php
                                    $selectedProduct = $products->firstWhere('id', $product['product_id'] ?? null);
                                    $linePrice = $selectedProduct
                                        ? (float) ($selectedProduct->full_price ?? $selectedProduct->price)
                                        : (float) ($product['full_price'] ?? $product['prix'] ?? 0);
                                    $priceLabel = $selectedProduct
                                        ? \App\Services\ConsultationSaleService::priceLabel([
                                            'has_half' => $selectedProduct->hasHalfOption(),
                                            'half_price' => $selectedProduct->half_price,
                                            'full_price' => $selectedProduct->full_price ?? $selectedProduct->price,
                                            'prix' => $linePrice,
                                        ])
                                        : \App\Services\ConsultationSaleService::priceLabel($product);
                                @endphp
                                <div class="product-row flex flex-wrap items-center gap-2 mb-2">
                                    <select name="recommended_products[{{ $productIndex }}][product_id]"
                                            class="form-control form-control-sm product-select"
                                            onchange="updateProductPrice({{ $productIndex }})">
                                        <option value="">Sélectionner</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                data-full-price="{{ $p->full_price ?? $p->price }}"
                                                data-half-price="{{ $p->half_price ?? '' }}"
                                                data-has-half="{{ $p->hasHalfOption() ? '1' : '0' }}"
                                                @selected(($product['product_id'] ?? '') == $p->id)>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="recommended_products[{{ $productIndex }}][posologie]"
                                           value="{{ $product['posologie'] ?? '' }}" placeholder="Posologie"
                                           class="w-28 form-control form-control-sm">
                                    <div id="product_price_display_{{ $productIndex }}" class="product-price-display" title="Prix catalogue">{{ $priceLabel }}</div>
                                    <input type="hidden" name="recommended_products[{{ $productIndex }}][prix]"
                                           id="product_price_{{ $productIndex }}"
                                           value="{{ number_format($linePrice, 2, '.', '') }}"
                                           class="product-price">
                                    <input type="text" name="recommended_products[{{ $productIndex }}][observation]"
                                           value="{{ $product['observation'] ?? '' }}" placeholder="Observation"
                                           class="flex-1 min-w-[100px] form-control form-control-sm">
                                    <button type="button" class="text-[var(--ui-stat-danger)] hover:text-[#991B1B] p-1 remove-product" onclick="removeProductRow(this)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                @php $productIndex++; @endphp
                            @endforeach
                        @else
                            <div class="product-row flex flex-wrap items-center gap-2 mb-2">
                                <select name="recommended_products[0][product_id]"
                                        class="form-control form-control-sm product-select"
                                        onchange="updateProductPrice(0)">
                                    <option value="">Sélectionner</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}"
                                            data-full-price="{{ $p->full_price ?? $p->price }}"
                                            data-half-price="{{ $p->half_price ?? '' }}"
                                            data-has-half="{{ $p->hasHalfOption() ? '1' : '0' }}">
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="recommended_products[0][posologie]" placeholder="Posologie"
                                       class="w-28 form-control form-control-sm">
                                <div id="product_price_display_0" class="product-price-display" title="Prix catalogue">—</div>
                                <input type="hidden" name="recommended_products[0][prix]" id="product_price_0"
                                       class="product-price" value="0.00">
                                <input type="text" name="recommended_products[0][observation]" placeholder="Observation"
                                       class="flex-1 min-w-[100px] form-control form-control-sm">
                                <button type="button" class="text-[var(--ui-stat-danger)] hover:text-[#991B1B] p-1 remove-product hidden" onclick="removeProductRow(this)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            @php $productIndex++; @endphp
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap justify-between items-center gap-2">
                        <button type="button" class="text-sm text-[var(--primary-blue)] hover:text-[var(--primary-blue-dark)]" onclick="addProductRow()">+ Ajouter un produit</button>
                        <span class="font-bold">Total: <span id="productTotalDisplay">${{ number_format($consultation->total_produits, 2) }}</span></span>
                        <input type="hidden" name="total_produits" id="productTotalInput" value="{{ $consultation->total_produits }}">
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Services -->
                <div class="card animate-fadeInUp delay-4">
                    <h3 class="text-base font-semibold text-[var(--primary-blue)] mb-3">Services Supplémentaires</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium">Ceragem</label>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <input type="number" name="seances_ceragem" id="seances_ceragem"
                                       value="{{ $consultation->seances_ceragem }}" min="0"
                                       class="w-20 form-control form-control-sm"
                                       onchange="calculateServiceTotal()">
                                <span class="text-sm text-[var(--text-secondary)]">×</span>
                                <input type="number" name="prix_ceragem" id="prix_ceragem"
                                       value="{{ $consultation->prix_ceragem }}" step="0.50" min="0"
                                       class="w-28 form-control form-control-sm"
                                       onchange="calculateServiceTotal()">
                            </div>
                            <div class="text-right text-sm font-semibold mt-1">Total: <span id="total_ceragem_display">${{ number_format($consultation->seances_ceragem * $consultation->prix_ceragem, 2) }}</span></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Detox</label>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <input type="number" name="seances_detox" id="seances_detox"
                                       value="{{ $consultation->seances_detox }}" min="0"
                                       class="w-20 form-control form-control-sm"
                                       onchange="calculateServiceTotal()">
                                <span class="text-sm text-[var(--text-secondary)]">×</span>
                                <input type="number" name="prix_detox" id="prix_detox"
                                       value="{{ $consultation->prix_detox }}" step="0.50" min="0"
                                       class="w-28 form-control form-control-sm"
                                       onchange="calculateServiceTotal()">
                            </div>
                            <div class="text-right text-sm font-semibold mt-1">Total: <span id="total_detox_display">${{ number_format($consultation->seances_detox * $consultation->prix_detox, 2) }}</span></div>
                        </div>
                        <input type="hidden" name="total_services" id="total_services_input" value="{{ $consultation->total_services }}">
                    </div>
                </div>
                <br>

                <!-- Notes et Statut -->
                <div class="card animate-fadeInUp delay-5">
                    <h3 class="text-base font-semibold text-[var(--primary-blue)] mb-3">Traitement</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Notes</label>
                            <textarea name="admin_notes" rows="3"
                                      class="form-control">{{ old('admin_notes', $consultation->admin_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Statut</label>
                            <select name="status" class="form-control">
                                <option value="pending" @selected($consultation->status == 'pending')>En attente</option>
                                <option value="processing" @selected($consultation->status == 'processing')>En traitement</option>
                                <option value="completed" @selected($consultation->status == 'completed')>Terminé</option>
                                <option value="cancelled" @selected($consultation->status == 'cancelled')>Annulé</option>
                            </select>
                        </div>

                        <div class="total-box">
                            <div class="flex justify-between text-sm">
                                <span>Total Produits</span>
                                <span id="total_produits_display">${{ number_format($consultation->total_produits, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Total Services</span>
                                <span id="total_services_display">${{ number_format($consultation->total_services, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-[var(--primary-blue-border)] pt-2 mt-2">
                                <span>Total Général</span>
                                <span id="total_general_display">${{ number_format($consultation->total_general, 2) }}</span>
                            </div>
                            <input type="hidden" name="total_general" id="total_general_input" value="{{ $consultation->total_general }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Modale confirmation suppression --}}
<div id="consultationDeleteModal" class="consultation-delete-overlay" aria-hidden="true">
    <div class="consultation-delete-box" role="dialog" aria-modal="true" aria-labelledby="consultationDeleteTitle">
        <div class="consultation-delete-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 id="consultationDeleteTitle" class="consultation-delete-title">Supprimer la consultation</h3>
        <p class="consultation-delete-text">
            Voulez-vous supprimer la fiche <strong>#{{ $consultation->id }}</strong>
            de <strong>{{ $consultation->nom_complet }}</strong> ?
        </p>
        <div class="consultation-delete-warning">Cette action est définitive et ne peut pas être annulée.</div>
        <div class="consultation-delete-actions">
            <button type="button" class="btn btn-outline btn-sm" id="consultationDeleteCancel">Annuler</button>
            <form action="{{ route('admin.consultations.destroy', $consultation) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<script>
let productIndex = {{ $productIndex ?? 0 }};

(function () {
    const modal = document.getElementById('consultationDeleteModal');
    const openBtn = document.getElementById('openConsultationDeleteModal');
    const cancelBtn = document.getElementById('consultationDeleteCancel');
    if (!modal || !openBtn) return;

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });
})();

function formatMoney(value) {
    return '$' + (parseFloat(value) || 0).toFixed(2);
}

function updateProductPrice(index) {
    const row = document.querySelector(`#product_price_${index}`)?.closest('.product-row');
    if (!row) return;

    const select = row.querySelector('.product-select');
    const opt = select?.options[select.selectedIndex];
    const display = document.getElementById('product_price_display_' + index);
    const hidden = document.getElementById('product_price_' + index);

    if (!opt || !opt.value) {
        if (display) display.textContent = '—';
        if (hidden) hidden.value = '0.00';
        calculateProductTotal();
        return;
    }

    const fullPrice = parseFloat(opt.dataset.fullPrice || 0) || 0;
    const halfPrice = parseFloat(opt.dataset.halfPrice || 0) || 0;
    const hasHalf = opt.dataset.hasHalf === '1';

    if (display) {
        display.textContent = hasHalf
            ? (formatMoney(halfPrice) + ' - ' + formatMoney(fullPrice))
            : formatMoney(fullPrice);
    }
    if (hidden) hidden.value = fullPrice.toFixed(2);
    calculateProductTotal();
}

function addProductRow() {
    const container = document.getElementById('productsContainer');
    const row = document.createElement('div');
    row.className = 'product-row flex flex-wrap items-center gap-2 mb-2';

    let optionsHTML = '<option value="">Sélectionner</option>';
    @foreach($products as $p)
        optionsHTML += `<option value="{{ $p->id }}" data-full-price="{{ $p->full_price ?? $p->price }}" data-half-price="{{ $p->half_price ?? '' }}" data-has-half="{{ $p->hasHalfOption() ? '1' : '0' }}">{{ $p->name }}</option>`;
    @endforeach

    const idx = productIndex;
    row.innerHTML = `
        <select name="recommended_products[${idx}][product_id]"
                class="form-control form-control-sm product-select"
                onchange="updateProductPrice(${idx})">
            ${optionsHTML}
        </select>
        <input type="text" name="recommended_products[${idx}][posologie]" placeholder="Posologie"
               class="w-28 form-control form-control-sm">
        <div id="product_price_display_${idx}" class="product-price-display" title="Prix catalogue">—</div>
        <input type="hidden" name="recommended_products[${idx}][prix]" id="product_price_${idx}"
               class="product-price" value="0.00">
        <input type="text" name="recommended_products[${idx}][observation]" placeholder="Observation"
               class="flex-1 min-w-[100px] form-control form-control-sm">
        <button type="button" class="text-[var(--ui-stat-danger)] hover:text-[#991B1B] p-1 remove-product" onclick="removeProductRow(this)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(row);
    productIndex++;
    updateRemoveButtons();
    calculateProductTotal();
}

function removeProductRow(btn) {
    const row = btn.closest('.product-row');
    const container = document.getElementById('productsContainer');
    if (container.querySelectorAll('.product-row').length > 1) {
        row.remove();
        updateRemoveButtons();
        calculateProductTotal();
    }
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.product-row');
    rows.forEach((row, i) => {
        const btn = row.querySelector('.remove-product');
        if (rows.length > 1) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });
}

function calculateProductTotal() {
    const priceInputs = document.querySelectorAll('.product-price');
    let total = 0;
    priceInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('productTotalDisplay').textContent = '$' + total.toFixed(2);
    document.getElementById('productTotalInput').value = total;
    calculateGrandTotal();
}

function calculateServiceTotal() {
    const sc = parseInt(document.getElementById('seances_ceragem').value) || 0;
    const pc = parseFloat(document.getElementById('prix_ceragem').value) || 0;
    const totalCeragem = sc * pc;
    document.getElementById('total_ceragem_display').textContent = '$' + totalCeragem.toFixed(2);

    const sd = parseInt(document.getElementById('seances_detox').value) || 0;
    const pd = parseFloat(document.getElementById('prix_detox').value) || 0;
    const totalDetox = sd * pd;
    document.getElementById('total_detox_display').textContent = '$' + totalDetox.toFixed(2);

    const totalServices = totalCeragem + totalDetox;
    document.getElementById('total_services_display').textContent = '$' + totalServices.toFixed(2);
    document.getElementById('total_services_input').value = totalServices;
    calculateGrandTotal();
}

function calculateGrandTotal() {
    const totalProduits = parseFloat(document.getElementById('productTotalInput').value) || 0;
    const totalServices = parseFloat(document.getElementById('total_services_input').value) || 0;
    const totalGeneral = totalProduits + totalServices;

    document.getElementById('total_produits_display').textContent = '$' + totalProduits.toFixed(2);
    document.getElementById('total_general_display').textContent = '$' + totalGeneral.toFixed(2);
    document.getElementById('total_general_input').value = totalGeneral;
}

document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
    calculateProductTotal();
    calculateServiceTotal();

    const codeInput = document.getElementById('code_id');
    const codeInfo = document.getElementById('codeIdInfo');
    let codeTimer;
    function checkMemberCode() {
        const code = (codeInput?.value || '').trim();
        if (!codeInfo) return;
        if (!code) {
            codeInfo.textContent = '';
            return;
        }
        fetch(`{{ route('check.sponsor') }}?sponsor_id=${encodeURIComponent(code)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.exists && data.name) {
                codeInfo.innerHTML = `Membre : <strong>${data.name}</strong>`;
                codeInfo.className = 'text-xs mt-1 text-[var(--ui-stat-success)]';
            } else {
                codeInfo.textContent = data.message || 'Code membre invalide';
                codeInfo.className = 'text-xs mt-1 text-[var(--ui-stat-danger)]';
            }
        })
        .catch(() => {
            codeInfo.textContent = 'Erreur de vérification';
            codeInfo.className = 'text-xs mt-1 text-[var(--ui-stat-danger)]';
        });
    }
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            clearTimeout(codeTimer);
            codeTimer = setTimeout(checkMemberCode, 400);
        });
    }
});
</script>
@endsection