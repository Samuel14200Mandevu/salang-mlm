@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.form-group {
    margin-bottom: 1.25rem;
}
.form-group label {
    display: block;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}
.form-group .required {
    color: #B91C1C;
}
.form-group .help-text {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    margin-top: 0.125rem;
}

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
.form-control-error {
    border-color: #B91C1C;
}
.form-control-error:focus {
    border-color: #B91C1C;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12);
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 0.25rem;
}
.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
}
.checkbox-group input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    accent-color: var(--primary-blue);
    cursor: pointer;
}

.payment-card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    background: var(--bg-card);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.payment-card .card-title {
    font-size: 0.938rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 0.5rem;
}
.payment-card .card-title .badge {
    font-size: 0.6rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-left: 0.5rem;
}
.payment-card .card-title .badge-crypto {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border: 1px solid rgba(181, 71, 8, 0.15);
}
.payment-card .card-title .badge-mobile {
    background: rgba(6, 95, 156, 0.12);
    color: #065F9C;
    border: 1px solid rgba(6, 95, 156, 0.15);
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

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.gateway-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    border: 1px solid transparent;
}
.gateway-status .dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    display: inline-block;
}
.gateway-status .dot-enabled {
    background: #1C7E4A;
}
.gateway-status .dot-disabled {
    background: #B91C1C;
}
.gateway-status-enabled {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.gateway-status-disabled {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }

@media (max-width: 640px) {
    .payment-card {
        padding: 0.875rem;
    }
    .payment-card .card-title {
        font-size: 0.813rem;
    }
    .form-group {
        margin-bottom: 0.875rem;
    }
    .form-group label {
        font-size: 0.75rem;
    }
    .form-group .help-text {
        font-size: 0.65rem;
    }
    .form-control {
        font-size: 0.813rem;
        padding: 0.5rem 0.75rem;
    }
    .checkbox-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    .checkbox-group label {
        font-size: 0.75rem;
    }
    .grid-payment {
        grid-template-columns: 1fr !important;
    }
    .gateway-status {
        font-size: 0.6rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .grid-payment {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Paramètres de paiement</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Configurer les passerelles de paiement et les frais</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Crypto Payments -->
    <div class="payment-card animate-fadeInUp delay-1">
        <div class="card-title">
            Paiements Crypto
            <span class="badge badge-crypto">BTC, ETH, USDT, USDC</span>
        </div>

        <form action="{{ route('admin.settings.update-payment') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-payment">

                <!-- Crypto Enabled -->
                <div class="form-group">
                    <label>Activer les paiements crypto</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="crypto_enabled" value="1"
                                   {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'checked' : '' }}>
                            <span class="gateway-status {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                                <span class="dot {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                                {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'Activé' : 'Désactivé' }}
                            </span>
                        </label>
                    </div>
                    <span class="help-text">Activer ou désactiver les paiements en cryptomonnaie</span>
                </div>

                <!-- Crypto Networks -->
                <div class="form-group">
                    <label>Réseaux crypto</label>
                    <div class="checkbox-group">
                        @php
                            $networks = $paymentSettings['gateways']['crypto']['networks'] ?? ['TRC20', 'ERC20', 'BEP20'];
                        @endphp
                        @foreach(['TRC20', 'ERC20', 'BEP20'] as $network)
                            <label>
                                <input type="checkbox" name="crypto_networks[]" value="{{ $network }}"
                                       {{ in_array($network, $networks) ? 'checked' : '' }}>
                                {{ $network }}
                            </label>
                        @endforeach
                    </div>
                    <span class="help-text">Sélectionner les réseaux crypto supportés</span>
                </div>

                <!-- Crypto Fee -->
                <div class="form-group">
                    <label>Frais crypto (%) <span class="required">*</span></label>
                    <input type="number" name="crypto_fee" step="0.01" min="0" max="100"
                           value="{{ old('crypto_fee', $paymentSettings['fees']['crypto'] ?? 0.5) }}"
                           class="form-control @error('crypto_fee') form-control-error @enderror" required>
                    <span class="help-text">Frais appliqués sur les transactions crypto</span>
                    @error('crypto_fee')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <!-- Mobile Money Payments -->
    <div class="payment-card animate-fadeInUp delay-2">
        <div class="card-title">
            Paiements Mobile Money
            <span class="badge badge-mobile">Airtel, Orange, M-Pesa</span>
        </div>

        <form action="{{ route('admin.settings.update-payment') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-payment">

                <!-- Mobile Money Enabled -->
                <div class="form-group">
                    <label>Activer Mobile Money</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="mobile_money_enabled" value="1"
                                   {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'checked' : '' }}>
                            <span class="gateway-status {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                                <span class="dot {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                                {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'Activé' : 'Désactivé' }}
                            </span>
                        </label>
                    </div>
                    <span class="help-text">Activer ou désactiver les paiements Mobile Money</span>
                </div>

                <!-- Mobile Money Providers -->
                <div class="form-group">
                    <label>Opérateurs Mobile Money</label>
                    <div class="checkbox-group">
                        @php
                            $providers = $paymentSettings['gateways']['mobile_money']['providers'] ?? ['Airtel Money', 'Orange Money', 'M-Pesa'];
                        @endphp
                        @foreach(['Airtel Money', 'Orange Money', 'M-Pesa'] as $provider)
                            <label>
                                <input type="checkbox" name="mobile_money_providers[]" value="{{ $provider }}"
                                       {{ in_array($provider, $providers) ? 'checked' : '' }}>
                                {{ $provider }}
                            </label>
                        @endforeach
                    </div>
                    <span class="help-text">Sélectionner les opérateurs Mobile Money supportés</span>
                </div>

                <!-- Mobile Money Fee -->
                <div class="form-group">
                    <label>Frais Mobile Money (%) <span class="required">*</span></label>
                    <input type="number" name="mobile_money_fee" step="0.01" min="0" max="100"
                           value="{{ old('mobile_money_fee', $paymentSettings['fees']['mobile_money'] ?? 1.5) }}"
                           class="form-control @error('mobile_money_fee') form-control-error @enderror" required>
                    <span class="help-text">Frais appliqués sur les transactions Mobile Money</span>
                    @error('mobile_money_fee')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <!-- Current Configuration Summary -->
    <div class="payment-card animate-fadeInUp delay-3">
        <div class="card-title">
            Résumé de la configuration actuelle
            <span class="badge badge-crypto">Aperçu</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Crypto</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                        <span class="dot {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                        {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'Activé' : 'Désactivé' }}
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Frais: {{ $paymentSettings['fees']['crypto'] ?? 0.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Réseaux: {{ implode(', ', $paymentSettings['gateways']['crypto']['networks'] ?? ['TRC20', 'ERC20', 'BEP20']) }}
                </p>
            </div>

            <div class="p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Mobile Money</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                        <span class="dot {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                        {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'Activé' : 'Désactivé' }}
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Frais: {{ $paymentSettings['fees']['mobile_money'] ?? 1.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Opérateurs: {{ implode(', ', $paymentSettings['gateways']['mobile_money']['providers'] ?? ['Airtel Money', 'Orange Money', 'M-Pesa']) }}
                </p>
            </div>

            <div class="p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Virement bancaire</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status gateway-status-enabled">
                        <span class="dot dot-enabled"></span>
                        Activé
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Frais: {{ $paymentSettings['fees']['bank_transfer'] ?? 0.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Option de virement bancaire standard
                </p>
            </div>
        </div>
    </div>

</div>
@endsection