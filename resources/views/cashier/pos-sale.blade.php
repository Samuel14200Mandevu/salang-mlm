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
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .product-preview .product-image {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-md);
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
        color: var(--primary-500);
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
        color: #ef4444;
    }
    .sale-form .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .sale-form .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .sale-form .input-sm {
        padding: 0.375rem 0.75rem;
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
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
        transform: translateY(-2px);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3);
    }
    .btn-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .btn-block {
        width: 100%;
        justify-content: center;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .sponsor-info {
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .sponsor-info.valid {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .sponsor-info.invalid {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .sponsor-info.hidden {
        display: none;
    }
    .sponsor-info svg {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
    }
    .sponsor-info .rank-badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 600;
        background: rgba(90, 182, 56, 0.15);
        color: var(--primary-500);
    }
    .sponsor-info .commission-info {
        font-size: 0.7rem;
        color: #f59e0b;
        background: rgba(245, 158, 11, 0.1);
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        border: 1px solid rgba(245, 158, 11, 0.2);
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
        .card {
            padding: 0.875rem;
        }
        .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
    }
</style>
@endpush

@section('title', 'Vendre un produit')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Vendre un produit
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Renseignez les informations du client et du parrain
            </p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux produits
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    <!-- Aperçu du produit -->
    <div class="product-preview animate-fadeInUp delay-1">
        <div class="product-image">
            @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}">
            @else
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            @endif
        </div>
        <div class="product-info">
            <h3>{{ $product->name }}</h3>
            <p>{{ Str::limit($product->description ?? '', 60) }}</p>
            <div class="flex items-center gap-3 mt-1">
                <span class="price">${{ number_format($product->price, 2) }}</span>
                @if($product->pv_value)
                    <span class="pv-badge">{{ $product->pv_value }} PV</span>
                @endif
                @if($product->bv_value)
                    <span class="pv-badge" style="background:rgba(139,92,246,0.12);color:#8b5cf6;">{{ $product->bv_value }} BV</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Formulaire de vente -->
    <div class="card animate-fadeInUp delay-2">
        <form action="{{ route('cashier.pos.order') }}" method="POST" class="sale-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            
            <div class="form-row">
                <!-- Client -->
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
                    <input type="number" name="commission_amount" class="input" placeholder="5.00" step="0.50" min="0" max="15">
                    <p class="text-[10px] text-[var(--text-tertiary)] mt-1">Entre 5$ et 15$</p>
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
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
        if (!response.ok) {
            throw new Error('Erreur serveur: ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        info.classList.remove('hidden');
        if (data.valid) {
            const rankName = data.sponsor.rank || 'Membre';
            
            info.className = 'sponsor-info valid';
            info.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                ${data.message || 'Code invalide'}
            `;
        }
    })
    .catch(function(error) {
        console.error('Erreur:', error);
        info.className = 'sponsor-info invalid';
        info.innerHTML = `
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Erreur lors de la verification: ${error.message}
        `;
    });
}

// ================================================================
//  TOUCHE ENTREE POUR VERIFIER LE PARRAIN
// ================================================================
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
</script>
@endpush
@endsection