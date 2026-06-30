@extends('layouts.app')

@push('styles')
<style>
    .method-card { cursor: pointer; transition: all 0.3s ease; }
    .method-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    .method-card.selected { border-color: var(--primary-500); background: rgba(99,102,241,0.05); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">💰 Demande de retrait</h1>
        <p class="text-[var(--text-secondary)] mt-1">Retirez vos gains en toute simplicité</p>
    </div>

    <!-- Info solde -->
    <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
        <p class="text-sm text-[var(--text-secondary)]">Solde disponible</p>
        <p class="text-3xl font-bold text-primary-500">${{ number_format($balance ?? 0, 2) }}</p>
        <p class="text-xs text-[var(--text-secondary)] mt-1">Frais de retrait: 2.5%</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Formulaire -->
        <div class="card animate-fadeInLeft delay-2">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">📝 Formulaire de retrait</h3>
            
            <form action="{{ route('withdrawal.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Montant ($)</label>
                    <input type="number" 
                           name="amount" 
                           step="0.01" 
                           min="10" 
                           max="{{ $balance ?? 0 }}"
                           class="input @error('amount') input-error @enderror"
                           placeholder="0.00"
                           required>
                    @error('amount')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Montant minimum: $10.00</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Méthode de retrait</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="method-card p-3 rounded-lg border border-[var(--border-color)] text-center" data-method="crypto">
                            <div class="text-2xl mb-1">🪙</div>
                            <p class="text-xs font-medium">Crypto</p>
                        </div>
                        <div class="method-card p-3 rounded-lg border border-[var(--border-color)] text-center" data-method="mobile_money">
                            <div class="text-2xl mb-1">📱</div>
                            <p class="text-xs font-medium">Mobile Money</p>
                        </div>
                        <div class="method-card p-3 rounded-lg border border-[var(--border-color)] text-center" data-method="bank">
                            <div class="text-2xl mb-1">🏦</div>
                            <p class="text-xs font-medium">Banque</p>
                        </div>
                    </div>
                    <select name="method" id="methodSelect" class="hidden" required>
                        <option value="crypto">Cryptomonnaie</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank">Virement bancaire</option>
                    </select>
                </div>

                <!-- Champs dynamiques -->
                <div id="crypto_fields" class="mb-4">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse de portefeuille</label>
                    <input type="text" name="payment_address" class="input" placeholder="0x... ou adresse TRC20">
                    <p class="text-xs text-[var(--text-secondary)] mt-1">USDT (TRC20), BTC, ETH</p>
                </div>

                <div id="mobile_fields" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Numéro de téléphone</label>
                    <input type="tel" name="phone_number" class="input" placeholder="+225 07 00 00 00 00">
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Orange Money, Airtel Money, M-Pesa</p>
                </div>

                <div id="bank_fields" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Coordonnées bancaires</label>
                    <textarea name="bank_details" rows="3" class="input" placeholder="Banque, IBAN, BIC, titulaire du compte..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Demander le retrait
                </button>
            </form>
        </div>

        <!-- Historique des retraits -->
        <div class="card animate-fadeInRight delay-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-[var(--text-primary)]">📋 Historique</h3>
                <span class="badge badge-neutral text-xs">{{ $withdrawals->count() ?? 0 }} demandes</span>
            </div>

            <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar">
                @forelse($withdrawals ?? [] as $withdrawal)
                    <div class="flex items-center justify-between p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition-colors">
                        <div>
                            <p class="font-semibold text-[var(--text-primary)]">${{ number_format($withdrawal->amount, 2) }}</p>
                            <p class="text-xs text-[var(--text-secondary)]">
                                {{ $withdrawal->method }} • {{ $withdrawal->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <span class="badge {{ $withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $withdrawal->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-8">Aucune demande de retrait</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodCards = document.querySelectorAll('.method-card');
    const methodSelect = document.getElementById('methodSelect');
    const cryptoFields = document.getElementById('crypto_fields');
    const mobileFields = document.getElementById('mobile_fields');
    const bankFields = document.getElementById('bank_fields');

    methodCards.forEach(card => {
        card.addEventListener('click', function() {
            // Retirer la sélection de tous
            methodCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const method = this.dataset.method;
            methodSelect.value = method;

            // Afficher les bons champs
            cryptoFields.classList.add('hidden');
            mobileFields.classList.add('hidden');
            bankFields.classList.add('hidden');

            if (method === 'crypto') cryptoFields.classList.remove('hidden');
            else if (method === 'mobile_money') mobileFields.classList.remove('hidden');
            else if (method === 'bank') bankFields.classList.remove('hidden');
        });
    });

    // Sélectionner la première méthode par défaut
    if (methodCards.length > 0) {
        methodCards[0].click();
    }
});
</script>
@endpush
@endsection