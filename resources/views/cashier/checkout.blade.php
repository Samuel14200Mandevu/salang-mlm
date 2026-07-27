{{-- resources/views/cashier/checkout.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .product-list-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    .product-list-item:last-child {
        border-bottom: none;
    }
    .product-list-item .item-image {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-secondary);
    }
    .product-list-item .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-list-item .item-info {
        flex: 1;
    }
    .product-list-item .item-info h4 {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .product-list-item .item-info .price {
        color: var(--primary-500);
        font-weight: 600;
        font-size: 0.813rem;
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

    .input {
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
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .input-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
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
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3);
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

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }

    .commission-info {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
    }
    .commission-info .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    .commission-info .value {
        font-size: 0.875rem;
        font-weight: 700;
        color: #d97706;
    }
    .commission-info .hint {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
    }

    @media (max-width: 640px) {
        .product-list-item {
            flex-wrap: wrap;
        }
        .product-list-item .item-image {
            width: 40px;
            height: 40px;
        }
        .card {
            padding: 0.875rem;
        }
    }
</style>
@endpush

@section('title', 'Finaliser la commande')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Finaliser la commande
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $products->count() }} produit(s) sélectionné(s)
            </p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 animate-fadeInUp delay-1">
        <!-- Formulaire -->
        <div class="lg:col-span-2 card">
            <form action="{{ route('cashier.checkout.order') }}" method="POST">
                @csrf
                
                <input type="hidden" name="products" value="{{ $products->pluck('id')->implode(',') }}">
                
                <div class="space-y-4">
                    <h3 class="font-semibold text-[var(--text-primary)]">Informations client</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)]">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="input mt-1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)]">Téléphone <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" class="input mt-1" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]">Email</label>
                        <input type="email" name="email" class="input mt-1">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]">Code parrain <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="sponsorCode" name="sponsor_code" class="input flex-1" placeholder="SALXXXXXX" required>
                            <button type="button" onclick="checkSponsor()" class="btn btn-outline btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Vérifier
                            </button>
                        </div>
                        <div id="sponsorInfo" class="sponsor-info hidden"></div>
                    </div>
                    
                    <!-- Commission CASH -->
                    <div class="commission-info">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold text-sm">Commission CASH pour le parrain</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-2">
                            <div>
                                <label class="label">Montant (5$ - 15$)</label>
                                <input type="number" name="commission_amount" class="input input-sm" 
                                       placeholder="5.00" step="0.50" min="5" max="15" value="5">
                            </div>
                            <div class="flex items-end">
                                <span class="text-xs text-[var(--text-secondary)]"> Bonus en espèces</span>
                            </div>
                        </div>
                        <div class="hint"> Une commission de 5$ à 15$ peut être attribuée au parrain pour cette vente</div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success w-full mt-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Confirmer la commande
                </button>
            </form>
        </div>
        
        <!-- Résumé -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4"> Résumé</h3>
            
            @foreach($products as $product)
                <div class="product-list-item">
                    <div class="item-image">
                        @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                            <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <svg class="w-full h-full p-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="item-info">
                        <h4>{{ $product->name }}</h4>
                        <span class="price">${{ number_format($product->price, 2) }}</span>
                        @if($product->pv_value)
                            <span class="pv-badge ml-2">{{ $product->pv_value }} PV</span>
                        @endif
                    </div>
                </div>
            @endforeach
            
            <div class="border-t border-[var(--border-color)] pt-4 mt-4">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span class="text-primary-500">${{ number_format($total, 2) }}</span>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-[var(--border-color)]">
                <div class="flex items-center gap-2 text-xs text-[var(--text-secondary)]">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Paiement en <strong class="text-green-500">espèces</strong> au guichet</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkSponsor() {
    const code = document.getElementById('sponsorCode').value.trim();
    const info = document.getElementById('sponsorInfo');
    
    if (!code) {
        info.className = 'sponsor-info hidden';
        info.innerHTML = '';
        return;
    }

    info.className = 'sponsor-info';
    info.innerHTML = '<span>Vérification...</span>';

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
            Erreur lors de la vérification: ${error.message}
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
</script>
@endpush
@endsection