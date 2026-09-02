{{-- resources/views/cashier/pos-sale.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .product-preview {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md, 8px);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .product-preview .product-image {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-md, 6px);
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-preview .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-preview .product-image svg {
        width: 40px;
        height: 40px;
        color: var(--text-tertiary);
    }
    .product-preview .product-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .product-preview .product-info p {
        font-size: 0.813rem;
        color: var(--text-secondary);
    }
    .product-preview .product-info .price {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--primary);
    }
    .product-preview .product-info .source-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }
    .source-badge-pos {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .source-badge-mlm {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }

    .sale-form .form-group {
        margin-bottom: 1rem;
    }
    .sale-form label {
        display: block;
        font-size: 0.813rem;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .sale-form .required {
        color: #b32a2a;
    }
    .sale-form .input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: border-color 0.2s ease;
        outline: none;
    }
    .sale-form .input:focus {
        border-color: var(--primary);
    }
    .sale-form .input-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
    }
    .sale-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

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
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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

    .btn-gold {
        background: #d97706;
        color: #FFFFFF;
    }
    .btn-gold:hover {
        background: #b45309;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-print-thermal {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-print-thermal:hover {
        background: #8f2121;
    }

    .btn-print-a4 {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-print-a4:hover {
        background: var(--primary-hover, #091E3B);
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .sponsor-info {
        padding: 0.4rem 0.75rem;
        border-radius: var(--radius-md, 6px);
        font-size: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .sponsor-info.valid {
        background: rgba(34, 197, 94, 0.08);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }
    .sponsor-info.invalid {
        background: rgba(179, 42, 42, 0.08);
        color: #b32a2a;
        border: 1px solid rgba(179, 42, 42, 0.15);
    }
    .sponsor-info.hidden { display: none; }
    .sponsor-info svg {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
    }
    .sponsor-info .rank-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        background: rgba(15, 43, 79, 0.10);
        color: var(--primary);
    }
    .sponsor-info .commission-info {
        font-size: 0.65rem;
        color: #d97706;
        background: rgba(245, 158, 11, 0.08);
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        border: 1px solid rgba(245, 158, 11, 0.15);
    }

    .pv-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 600;
        background: rgba(59, 130, 246, 0.10);
        color: #2563eb;
    }

    .print-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .print-actions .btn {
        flex: 1;
        min-width: 120px;
    }

    .thermal-ticket {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        width: 80mm;
        max-width: 80mm;
        padding: 10px;
        background: white;
        color: black;
        display: none;
    }

    .alert-success {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        background: rgba(34, 197, 94, 0.06);
        border: 1px solid rgba(34, 197, 94, 0.15);
        color: #16a34a;
        font-size: 0.875rem;
    }

    .alert-error {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        background: rgba(179, 42, 42, 0.06);
        border: 1px solid rgba(179, 42, 42, 0.15);
        color: #b32a2a;
        font-size: 0.875rem;
    }

    @media (max-width: 640px) {
        .product-preview {
            flex-direction: column;
            text-align: center;
        }
        .product-preview .product-image {
            width: 60px;
            height: 60px;
        }
        .sale-form .form-row {
            grid-template-columns: 1fr;
        }
        .card { padding: 0.875rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .print-actions .btn { min-width: 80px; }
    }
</style>
@endpush

@section('title', 'Vendre un produit')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-[var(--primary)] mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Vendre un produit
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Renseignez les informations du client et du parrain
            </p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux produits
        </a>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert-success">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>

        <div class="print-actions">
            <button onclick="printThermalTicket()" class="btn btn-print-thermal btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Ticket thermique
            </button>
            <button onclick="window.print()" class="btn btn-print-a4 btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer A4
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- APERÇU DU PRODUIT --}}
    <div class="product-preview">
        <div class="product-image">
            @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}">
            @else
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            @endif
        </div>
        <div class="product-info">
            <h3>{{ $product->name }}</h3>
            <p>{{ Str::limit($product->description ?? '', 60) }}</p>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
                <span class="price">${{ number_format($product->price, 2) }}</span>
                @if($product->pv_value)
                    <span class="pv-badge">{{ $product->pv_value }} PV</span>
                @endif
                @if($product->bv_value)
                    <span class="pv-badge" style="background:rgba(139,92,246,0.10);color:#8b5cf6;">{{ $product->bv_value }} BV</span>
                @endif
                <span class="source-badge source-badge-pos">POS</span>
            </div>
        </div>
    </div>

    {{-- FORMULAIRE DE VENTE --}}
    <div class="card">
        <form action="{{ route('cashier.pos.order') }}" method="POST" class="sale-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="source" value="pos">

            <div class="form-row">
                <div class="form-group">
                    <label>Nom client <span class="required">*</span></label>
                    <input type="text" name="name" class="input" placeholder="Jean Dupont" required>
                </div>

                <div class="form-group">
                    <label>Telephone client <span class="required">*</span></label>
                    <input type="text" name="phone" class="input" placeholder="07000000" required>
                </div>

                <div class="form-group">
                    <label>Email client</label>
                    <input type="email" name="email" class="input" placeholder="client@email.com">
                </div>

                <div class="form-group">
                    <label>Code parrain <span class="required">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="sponsorCode" name="sponsor_code"
                               class="input flex-1" placeholder="SALXXXXXX" required>
                        <button type="button" onclick="checkSponsor()" class="btn btn-outline btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Verifier
                        </button>
                    </div>
                    <div id="sponsorInfo" class="sponsor-info hidden"></div>
                </div>

                <div class="form-group">
                    <label>Quantite</label>
                    <input type="number" name="quantity" class="input" value="1" min="1" max="10">
                </div>

                <div class="form-group">
                    <label>Commission CASH <span class="text-xs text-[var(--text-tertiary)]">(optionnelle)</span></label>
                    <input type="number" name="commission_amount" class="input" placeholder="5.00" step="0.50" min="0" max="15" value="5">
                    <p class="text-[10px] text-[var(--text-tertiary)] mt-1">Entre 5$ et 15$</p>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Valider la vente
                </button>
                <a href="{{ route('cashier.pos') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

{{-- TICKET THERMIQUE --}}
<div class="thermal-ticket" id="thermalTicket">
    <div style="text-align:center;border-bottom:1px dashed #ccc;padding-bottom:8px;margin-bottom:8px;">
        <h2 style="font-size:16px;font-weight:bold;margin:0;">SALANG GROUP SARL</h2>
        <p style="font-size:10px;margin:2px 0;color:#666;">E-COMMERCE &amp; MLM</p>
        <p style="font-size:10px;margin:2px 0;color:#666;">Rond Point CHIKUDU, Batiment KBS</p>
        <p style="font-size:10px;margin:2px 0;color:#666;">Tel: +243 975 220 079</p>
        <p style="font-size:10px;margin:2px 0;color:#666;">--------------------------------</p>
        <p style="font-size:11px;font-weight:bold;margin:4px 0;">Ticket de caisse</p>
        <p id="ticketDate" style="font-size:10px;margin:2px 0;color:#666;">{{ now()->format('d/m/Y H:i') }}</p>
        <p style="font-size:10px;margin:2px 0;color:#666;">--------------------------------</p>
    </div>

    <table style="width:100%;border-collapse:collapse;font-size:11px;margin:8px 0;">
        <thead>
            <tr>
                <th style="text-align:left;border-bottom:1px solid #ccc;padding:4px 0;font-size:10px;text-transform:uppercase;">Article</th>
                <th style="text-align:right;border-bottom:1px solid #ccc;padding:4px 0;font-size:10px;text-transform:uppercase;">Qté</th>
                <th style="text-align:right;border-bottom:1px solid #ccc;padding:4px 0;font-size:10px;text-transform:uppercase;">Prix</th>
                <th style="text-align:right;border-bottom:1px solid #ccc;padding:4px 0;font-size:10px;text-transform:uppercase;">Total</th>
            </tr>
        </thead>
        <tbody id="ticketItems">
            <tr>
                <td colspan="4" style="text-align:center;color:#999;padding:8px 0;">Aucun article</td>
            </tr>
        </tbody>
    </table>

    <div style="border-top:1px solid #ccc;padding-top:8px;margin-top:8px;text-align:right;font-size:14px;font-weight:bold;">
        <p>Total: <span id="ticketTotal">$0.00</span></p>
        <p style="font-size:12px;font-weight:normal;color:#666;">
            Commission CASH: <span id="ticketCommission">$0.00</span>
        </p>
    </div>

    <div style="text-align:center;font-size:10px;color:#666;border-top:1px dashed #ccc;padding-top:8px;margin-top:8px;">
        <p>--------------------------------</p>
        <p style="font-weight:bold;">Merci pour votre achat!</p>
        <div style="font-size:9px;color:#999;margin-top:4px;">
            N° ID.NAT: 22-7300-N634640<br>
            N° RCCM: CD/BKVIRCM/20-8-001165001
        </div>
    </div>
</div>

@push('scripts')
<script>
// ================================================================
//  VERIFICATION DU CODE PARRAIN
// ================================================================
function checkSponsor() {
    const code = document.getElementById('sponsorCode').value.trim();
    const info = document.getElementById('sponsorInfo');

    if (!code) {
        info.className = 'sponsor-info hidden';
        info.innerHTML = '';
        return;
    }

    info.className = 'sponsor-info';
    info.innerHTML = '<span>Verification...</span>';

    fetch(`/cashier/sponsor/check?code=${encodeURIComponent(code)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(response) {
        if (!response.ok) throw new Error('Erreur serveur: ' + response.status);
        return response.json();
    })
    .then(function(data) {
        info.classList.remove('hidden');
        if (data.valid) {
            const rankName = data.sponsor.rank || 'Membre';
            info.className = 'sponsor-info valid';
            info.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="flex flex-wrap items-center gap-1">
                    <strong>${data.sponsor.name}</strong>
                    <span class="text-xs text-[var(--text-tertiary)]">(ID: #${data.sponsor.id})</span>
                    <span class="rank-badge">${rankName}</span>
                </div>
            `;
        } else {
            info.className = 'sponsor-info invalid';
            info.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                ${data.message || 'Code invalide'}
            `;
        }
    })
    .catch(function(error) {
        console.error('Erreur:', error);
        info.className = 'sponsor-info invalid';
        info.innerHTML = `
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Erreur: ${error.message}
        `;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('sponsorCode');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkSponsor();
            }
        });
    }
});

// ================================================================
//  IMPRESSION TICKET THERMIQUE
// ================================================================
function printThermalTicket() {
    const orderData = getOrderData();
    if (!orderData) {
        alert('Aucune commande à imprimer');
        return;
    }

    updateThermalTicket(orderData);

    const ticket = document.getElementById('thermalTicket');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Ticket de caisse</title>
                <style>
                    body { margin: 0; padding: 0; display: flex; justify-content: center; background: #f0f0f0; }
                    .print-content { width: 80mm; padding: 10px; background: white; font-family: 'Courier New', monospace; font-size: 12px; }
                    .print-content .header { text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-bottom: 8px; }
                    .print-content .header h2 { font-size: 16px; font-weight: bold; margin: 0; }
                    .print-content .header p { font-size: 10px; margin: 2px 0; color: #666; }
                    .print-content .items { width: 100%; border-collapse: collapse; font-size: 11px; margin: 8px 0; }
                    .print-content .items th { text-align: left; border-bottom: 1px solid #ccc; padding: 4px 0; font-size: 10px; text-transform: uppercase; }
                    .print-content .items td { padding: 3px 0; border-bottom: 1px dashed #eee; }
                    .print-content .items .text-right { text-align: right; }
                    .print-content .total { border-top: 1px solid #ccc; padding-top: 8px; margin-top: 8px; text-align: right; font-size: 14px; font-weight: bold; }
                    .print-content .footer { text-align: center; font-size: 10px; color: #666; border-top: 1px dashed #ccc; padding-top: 8px; margin-top: 8px; }
                    .print-content .footer .signature { font-size: 9px; color: #999; margin-top: 4px; }
                    @media print { body { background: white; } }
                </style>
            </head>
            <body>
                <div class="print-content">
                    ${ticket.innerHTML}
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 1000);
                    };
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

function updateThermalTicket(data) {
    document.getElementById('ticketDate').textContent = data.date || new Date().toLocaleString();

    const tbody = document.getElementById('ticketItems');
    if (data.items && data.items.length > 0) {
        tbody.innerHTML = data.items.map(item => `
            <tr>
                <td>${item.name}</td>
                <td class="text-right">${item.quantity}</td>
                <td class="text-right">$${item.price.toFixed(2)}</td>
                <td class="text-right">$${(item.price * item.quantity).toFixed(2)}</td>
            </tr>
        `).join('');
    }

    document.getElementById('ticketTotal').textContent = `$${data.total.toFixed(2)}`;
    document.getElementById('ticketCommission').textContent = `$${data.commission.toFixed(2)}`;
}

function getOrderData() {
    try {
        const data = localStorage.getItem('last_order_data');
        if (data) return JSON.parse(data);
    } catch (e) {
        console.error('Erreur:', e);
    }
    return null;
}

function saveOrderData(orderData) {
    localStorage.setItem('last_order_data', JSON.stringify(orderData));
}

@if(session('success'))
    @php
        $lastOrder = \App\Models\Order::with('items')->latest()->first();
        if ($lastOrder) {
            $orderData = [
                'date' => now()->format('d/m/Y H:i'),
                'items' => $lastOrder->items->map(function($item) {
                    return [
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                })->toArray(),
                'total' => $lastOrder->total,
                'commission' => $lastOrder->metadata['commission_amount'] ?? 0,
            ];
            echo "localStorage.setItem('last_order_data', '" . json_encode($orderData) . "');";
        }
    @endphp
@endif
</script>
@endpush
@endsection