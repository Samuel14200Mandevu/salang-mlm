{{-- resources/views/admin/consultations/show.blade.php --}}
@extends('admin.layouts.app')

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

@section('title', 'Fiche de Consultation ' . $consultation->id)

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-blue-600 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Fiche {{ $consultation->id }}
            </h1>
            <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                <span class="text-sm text-[var(--text-secondary)]">Creee le {{ $consultation->created_at->format('d/m/Y H:i') }}</span>
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
            <a href="{{ route('admin.consultations.print', $consultation) }}" target="_blank" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </a>
            <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
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
                    <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Informations du Patient</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Code</div>
                            <div class="value">{{ $consultation->code_id ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Numero</div>
                            <div class="value">{{ $consultation->numero ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Nom</div>
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
                            <div class="label">Poids / Taille</div>
                            <div class="value">{{ $consultation->poids ?? 'N/A' }} kg / {{ $consultation->taille ?? 'N/A' }} cm</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Date examen</div>
                            <div class="value">{{ $consultation->date_examen ? $consultation->date_examen->format('d/m/Y') : 'N/A' }}</div>
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
                    <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Consultation</h3>
                    <div class="space-y-3">
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Motif</div>
                            <textarea name="reason" rows="2" 
                                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm mt-1">{{ old('reason', $consultation->reason) }}</textarea>
                        </div>
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Symptomes</div>
                            <textarea name="symptoms" rows="2" 
                                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm mt-1">{{ old('symptoms', $consultation->symptoms) }}</textarea>
                        </div>
                        <div>
                            <div class="label text-xs uppercase text-[var(--text-tertiary)] font-semibold">Observations</div>
                            <textarea name="observations" rows="2" 
                                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm mt-1">{{ old('observations', $consultation->observations) }}</textarea>
                        </div>
                    </div>
                </div><br>

                <!-- Produits -->
                <div class="card animate-fadeInUp delay-3">
                    <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Produits Recommandes</h3>
                    
                    <div id="productsContainer">
                        @php $productIndex = 0; @endphp
                        @if($consultation->recommended_products && count($consultation->recommended_products) > 0)
                            @foreach($consultation->recommended_products as $product)
                                <div class="product-row flex items-center gap-2 mb-2">
                                    <select name="recommended_products[{{ $productIndex }}][product_id]" 
                                            class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm"
                                            onchange="updateProductPrice(this, {{ $productIndex }})">
                                        <option value="">Selectionner</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" 
                                                @selected(($product['product_id'] ?? '') == $p->id)>
                                                {{ $p->name }} - ${{ number_format($p->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="recommended_products[{{ $productIndex }}][posologie]" 
                                           value="{{ $product['posologie'] ?? '' }}" placeholder="Posologie"
                                           class="w-32 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
                                    <input type="text" name="recommended_products[{{ $productIndex }}][prix]" 
                                           id="product_price_{{ $productIndex }}"
                                           value="{{ number_format($product['prix'] ?? 0, 2) }}" 
                                           class="w-24 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm text-right product-price" readonly>
                                    <input type="text" name="recommended_products[{{ $productIndex }}][observation]" 
                                           value="{{ $product['observation'] ?? '' }}" placeholder="Observation"
                                           class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
                                    <button type="button" class="text-red-500 hover:text-red-700 p-1 remove-product" onclick="removeProductRow(this)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                @php $productIndex++; @endphp
                            @endforeach
                        @else
                            <div class="product-row flex items-center gap-2 mb-2">
                                <select name="recommended_products[0][product_id]" 
                                        class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm"
                                        onchange="updateProductPrice(this, 0)">
                                    <option value="">Selectionner</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}">
                                            {{ $p->name }} - ${{ number_format($p->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="recommended_products[0][posologie]" placeholder="Posologie"
                                       class="w-32 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
                                <input type="text" name="recommended_products[0][prix]" id="product_price_0"
                                       class="w-24 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm text-right product-price" readonly value="0.00">
                                <input type="text" name="recommended_products[0][observation]" placeholder="Observation"
                                       class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
                                <button type="button" class="text-red-500 hover:text-red-700 p-1 remove-product hidden" onclick="removeProductRow(this)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            @php $productIndex++; @endphp
                        @endif
                    </div>
                    <div class="mt-3 flex justify-between items-center">
                        <button type="button" class="text-sm text-blue-500 hover:text-blue-600" onclick="addProductRow()">+ Ajouter un produit</button>
                        <span class="font-bold">Total: <span id="productTotalDisplay">${{ number_format($consultation->total_produits, 2) }}</span></span>
                        <input type="hidden" name="total_produits" id="productTotalInput" value="{{ $consultation->total_produits }}">
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Services -->
                <div class="card animate-fadeInUp delay-4">
                    <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Services Supplementaires</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium">Ceragem</label>
                            <div class="flex gap-2 mt-1">
                                <input type="number" name="seances_ceragem" id="seances_ceragem" 
                                       value="{{ $consultation->seances_ceragem }}" min="0"
                                       class="w-20 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-1 text-sm"
                                       onchange="calculateServiceTotal()">
                                <span class="text-sm text-[var(--text-secondary)]">×</span>
                                <input type="number" name="prix_ceragem" id="prix_ceragem" 
                                       value="{{ $consultation->prix_ceragem }}" step="0.50" min="0"
                                       class="w-28 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-1 text-sm"
                                       onchange="calculateServiceTotal()">
                            </div>
                            <div class="text-right text-sm font-semibold mt-1">Total: <span id="total_ceragem_display">${{ number_format($consultation->seances_ceragem * $consultation->prix_ceragem, 2) }}</span></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Detox</label>
                            <div class="flex gap-2 mt-1">
                                <input type="number" name="seances_detox" id="seances_detox" 
                                       value="{{ $consultation->seances_detox }}" min="0"
                                       class="w-20 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-1 text-sm"
                                       onchange="calculateServiceTotal()">
                                <span class="text-sm text-[var(--text-secondary)]">×</span>
                                <input type="number" name="prix_detox" id="prix_detox" 
                                       value="{{ $consultation->prix_detox }}" step="0.50" min="0"
                                       class="w-28 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-1 text-sm"
                                       onchange="calculateServiceTotal()">
                            </div>
                            <div class="text-right text-sm font-semibold mt-1">Total: <span id="total_detox_display">${{ number_format($consultation->seances_detox * $consultation->prix_detox, 2) }}</span></div>
                        </div>
                        <input type="hidden" name="total_services" id="total_services_input" value="{{ $consultation->total_services }}">
                    </div>
                </div><br>

                <!-- Notes et Statut -->
                <div class="card animate-fadeInUp delay-5">
                    <h3 class="text-base sm:text-lg font-semibold text-blue-600 mb-3 sm:mb-4">Traitement</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Notes</label>
                            <textarea name="admin_notes" rows="3" 
                                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm">{{ old('admin_notes', $consultation->admin_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Statut</label>
                            <select name="status" class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm">
                                <option value="pending" @selected($consultation->status == 'pending')>En attente</option>
                                <option value="processing" @selected($consultation->status == 'processing')>En traitement</option>
                                <option value="completed" @selected($consultation->status == 'completed')>Termine</option>
                                <option value="cancelled" @selected($consultation->status == 'cancelled')>Annule</option>
                            </select>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span>Total Produits</span>
                                <span id="total_produits_display">${{ number_format($consultation->total_produits, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Total Services</span>
                                <span id="total_services_display">${{ number_format($consultation->total_services, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-blue-600 border-t border-[var(--border-color)] pt-2 mt-2">
                                <span>Total General</span>
                                <span id="total_general_display">${{ number_format($consultation->total_general, 2) }}</span>
                            </div>
                            <input type="hidden" name="total_general" id="total_general_input" value="{{ $consultation->total_general }}">
                        </div>
                    </div>
                </div><br>

                <button type="submit" class="btn btn-success w-full">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre a jour
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let productIndex = {{ $productIndex ?? 0 }};

function updateProductPrice(select, index) {
    const price = select.options[select.selectedIndex]?.dataset?.price || 0;
    document.getElementById('product_price_' + index).value = parseFloat(price).toFixed(2);
    calculateProductTotal();
}

function addProductRow() {
    const container = document.getElementById('productsContainer');
    const row = document.createElement('div');
    row.className = 'product-row flex items-center gap-2 mb-2';
    
    let optionsHTML = '<option value="">Selectionner</option>';
    @foreach($products as $p)
        optionsHTML += `<option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} - ${{ number_format($p->price, 2) }}</option>`;
    @endforeach
    
    row.innerHTML = `
        <select name="recommended_products[${productIndex}][product_id]" 
                class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-3 py-2 text-sm"
                onchange="updateProductPrice(this, ${productIndex})">
            ${optionsHTML}
        </select>
        <input type="text" name="recommended_products[${productIndex}][posologie]" placeholder="Posologie"
               class="w-32 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
        <input type="text" name="recommended_products[${productIndex}][prix]" id="product_price_${productIndex}"
               class="w-24 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm text-right product-price" readonly value="0.00">
        <input type="text" name="recommended_products[${productIndex}][observation]" placeholder="Observation"
               class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-primary)] px-2 py-2 text-sm">
        <button type="button" class="text-red-500 hover:text-red-700 p-1 remove-product" onclick="removeProductRow(this)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
});
</script>
@endsection