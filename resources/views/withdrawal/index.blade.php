@extends('layouts.app')

@push('styles')
<style>
    .method-card {
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        text-align: center;
        background: var(--bg-secondary);
    }
    .method-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .method-card.selected {
        border-color: var(--primary-500);
        background: rgba(99,102,241,0.05);
    }
    .method-card .method-icon { font-size: 2rem; display: block; margin-bottom: 0.25rem; }
    .method-card .method-label { font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); }
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-3xl { font-size: 1.5rem; }
        .card { padding: 0.75rem; }
        .method-card { padding: 0.5rem; }
        .method-card .method-icon { font-size: 1.5rem; }
        .method-card .method-label { font-size: 0.6rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .text-2xl { font-size: 1.25rem; }
        .input { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Demande de retrait</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Retirez vos gains en toute simplicite</p>
    </div>

    <!-- Info solde -->
    <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500 p-3 sm:p-4">
        <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Solde disponible</p>
        <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format($balance ?? 0, 2) }}</p>
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Frais de retrait: 2.5%</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Formulaire -->
        <div class="card animate-fadeInLeft delay-2 p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Formulaire de retrait</h3>
            
            <form action="{{ route('withdrawal.store') }}" method="POST">
                @csrf

                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Montant ($)</label>
                    <input type="number" 
                           name="amount" 
                           step="0.01" 
                           min="10" 
                           max="{{ $balance ?? 0 }}"
                           class="input text-sm sm:text-base @error('amount') input-error @enderror"
                           placeholder="0.00"
                           required>
                    @error('amount')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Montant minimum: $10.00</p>
                </div>

                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-2">Methode de retrait</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="method-card" data-method="crypto" onclick="selectMethod(this)">
                            <span class="method-icon">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span class="method-label">Crypto</span>
                        </div>
                        <div class="method-card" data-method="mobile_money" onclick="selectMethod(this)">
                            <span class="method-icon">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <span class="method-label">Mobile Money</span>
                        </div>
                        <div class="method-card" data-method="bank" onclick="selectMethod(this)">
                            <span class="method-icon">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span class="method-label">Banque</span>
                        </div>
                    </div>
                    <select name="method" id="methodSelect" class="hidden" required>
                        <option value="crypto">Cryptomonnaie</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank">Virement bancaire</option>
                    </select>
                </div>

                <!-- Champs dynamiques -->
                <div id="crypto_fields" class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse de portefeuille</label>
                    <input type="text" name="payment_address" class="input text-sm sm:text-base" placeholder="0x... ou adresse TRC20">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">USDT (TRC20), BTC, ETH</p>
                </div>

                <div id="mobile_fields" class="mb-3 sm:mb-4 hidden">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Numero de telephone</label>
                    <input type="tel" name="phone_number" class="input text-sm sm:text-base" placeholder="+225 07 00 00 00 00">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Orange Money, Airtel Money, M-Pesa</p>
                </div>

                <div id="bank_fields" class="mb-3 sm:mb-4 hidden">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Coordonnees bancaires</label>
                    <textarea name="bank_details" rows="3" class="input text-sm sm:text-base" placeholder="Banque, IBAN, BIC, titulaire du compte..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Demander le retrait
                </button>
            </form>
        </div>

        <!-- Historique des retraits -->
        <div class="card animate-fadeInRight delay-3 p-3 sm:p-4 md:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $withdrawals->count() ?? 0 }} demandes</span>
            </div>

            <div class="space-y-2 sm:space-y-3 max-h-[300px] sm:max-h-[400px] overflow-y-auto custom-scrollbar">
                @forelse($withdrawals ?? [] as $withdrawal)
                    <div class="flex flex-wrap items-center justify-between p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition-colors gap-1 sm:gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">${{ number_format($withdrawal->amount, 2) }}</p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                {{ ucfirst($withdrawal->method) }} • {{ $withdrawal->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <span class="badge {{ $withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-6 sm:py-8 text-sm">Aucune demande de retrait</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectMethod(element) {
    var cards = document.querySelectorAll('.method-card');
    cards.forEach(function(card) {
        card.classList.remove('selected');
    });
    element.classList.add('selected');

    var method = element.dataset.method;
    document.getElementById('methodSelect').value = method;

    var cryptoFields = document.getElementById('crypto_fields');
    var mobileFields = document.getElementById('mobile_fields');
    var bankFields = document.getElementById('bank_fields');

    cryptoFields.classList.add('hidden');
    mobileFields.classList.add('hidden');
    bankFields.classList.add('hidden');

    if (method === 'crypto') {
        cryptoFields.classList.remove('hidden');
    } else if (method === 'mobile_money') {
        mobileFields.classList.remove('hidden');
    } else if (method === 'bank') {
        bankFields.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var firstCard = document.querySelector('.method-card');
    if (firstCard) {
        firstCard.click();
    }
});
</script>
@endpush
@endsection