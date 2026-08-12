@extends('admin.layouts.app')

@push('styles')
<style>
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .form-group .required {
        color: #ef4444;
    }
    .form-group .help-text {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 0.125rem;
    }
    .form-group .input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        background: var(--bg-input);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all 0.3s ease;
        outline: none;
    }
    .form-group .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .form-group .input-error {
        border-color: #ef4444;
    }
    .form-group .input-error:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }
    .form-group .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding-top: 0.25rem;
    }
    .form-group .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
    }
    .form-group .checkbox-group input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        accent-color: var(--primary-500);
        cursor: pointer;
    }
    
    .payment-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        background: var(--bg-card);
    }
    .payment-card .card-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .payment-card .card-title .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-left: 0.5rem;
    }
    .payment-card .card-title .badge-crypto {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .payment-card .card-title .badge-mobile {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    
    .gateway-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.125rem 0.5rem;
        border-radius: var(--radius-full);
    }
    .gateway-status .dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: var(--radius-full);
        display: inline-block;
    }
    .gateway-status .dot-enabled {
        background: #22c55e;
    }
    .gateway-status .dot-disabled {
        background: #ef4444;
    }
    .gateway-status-enabled {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .gateway-status-disabled {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    
    @media (max-width: 640px) {
        .payment-card {
            padding: 0.875rem;
        }
        .payment-card .card-title {
            font-size: 0.8125rem;
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
        .form-group .input {
            font-size: 0.8125rem;
            padding: 0.5rem 0.75rem;
        }
        .form-group .checkbox-group {
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group .checkbox-group label {
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Payment Settings</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configure payment gateways and fees</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="hidden xs:inline">Back</span>
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

    <!-- Crypto Payments -->
    <div class="payment-card animate-fadeInUp delay-1">
        <div class="card-title">
            Crypto Payments
            <span class="badge badge-crypto">BTC, ETH, USDT, USDC</span>
        </div>
        
        <form action="{{ route('admin.settings.update-payment') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-payment">
                
                <!-- Crypto Enabled -->
                <div class="form-group">
                    <label>Enable Crypto Payments</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="crypto_enabled" value="1" 
                                   {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'checked' : '' }}>
                            <span class="gateway-status {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                                <span class="dot {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                                {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </label>
                    </div>
                    <p class="help-text">Enable or disable cryptocurrency payments</p>
                </div>

                <!-- Crypto Networks -->
                <div class="form-group">
                    <label>Crypto Networks</label>
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
                    <p class="help-text">Select supported crypto networks</p>
                </div>

                <!-- Crypto Fee -->
                <div class="form-group">
                    <label>Crypto Fee (%) <span class="required">*</span></label>
                    <input type="number" name="crypto_fee" step="0.01" min="0" max="100"
                           value="{{ old('crypto_fee', $paymentSettings['fees']['crypto'] ?? 0.5) }}"
                           class="input @error('crypto_fee') input-error @enderror" required>
                    <p class="help-text">Fee charged on crypto transactions</p>
                    @error('crypto_fee')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Crypto Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Mobile Money Payments -->
    <div class="payment-card animate-fadeInUp delay-2">
        <div class="card-title">
            Mobile Money Payments
            <span class="badge badge-mobile">Airtel, Orange, M-Pesa</span>
        </div>
        
        <form action="{{ route('admin.settings.update-payment') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-payment">
                
                <!-- Mobile Money Enabled -->
                <div class="form-group">
                    <label>Enable Mobile Money</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="mobile_money_enabled" value="1" 
                                   {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'checked' : '' }}>
                            <span class="gateway-status {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                                <span class="dot {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                                {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </label>
                    </div>
                    <p class="help-text">Enable or disable mobile money payments</p>
                </div>

                <!-- Mobile Money Providers -->
                <div class="form-group">
                    <label>Mobile Money Providers</label>
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
                    <p class="help-text">Select supported mobile money providers</p>
                </div>

                <!-- Mobile Money Fee -->
                <div class="form-group">
                    <label>Mobile Money Fee (%) <span class="required">*</span></label>
                    <input type="number" name="mobile_money_fee" step="0.01" min="0" max="100"
                           value="{{ old('mobile_money_fee', $paymentSettings['fees']['mobile_money'] ?? 1.5) }}"
                           class="input @error('mobile_money_fee') input-error @enderror" required>
                    <p class="help-text">Fee charged on mobile money transactions</p>
                    @error('mobile_money_fee')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Mobile Money Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Current Configuration Summary -->
    <div class="payment-card animate-fadeInUp delay-3">
        <div class="card-title">
            Current Configuration Summary
            <span class="badge badge-crypto">Overview</span>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Crypto</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                        <span class="dot {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                        {{ ($paymentSettings['gateways']['crypto']['enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Fee: {{ $paymentSettings['fees']['crypto'] ?? 0.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Networks: {{ implode(', ', $paymentSettings['gateways']['crypto']['networks'] ?? ['TRC20', 'ERC20', 'BEP20']) }}
                </p>
            </div>
            
            <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Mobile Money</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'gateway-status-enabled' : 'gateway-status-disabled' }}">
                        <span class="dot {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'dot-enabled' : 'dot-disabled' }}"></span>
                        {{ ($paymentSettings['gateways']['mobile_money']['enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Fee: {{ $paymentSettings['fees']['mobile_money'] ?? 1.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Providers: {{ implode(', ', $paymentSettings['gateways']['mobile_money']['providers'] ?? ['Airtel Money', 'Orange Money', 'M-Pesa']) }}
                </p>
            </div>
            
            <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Bank Transfer</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="gateway-status gateway-status-enabled">
                        <span class="dot dot-enabled"></span>
                        Enabled
                    </span>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Fee: {{ $paymentSettings['fees']['bank_transfer'] ?? 0.5 }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                    Standard bank transfer option
                </p>
            </div>
        </div>
    </div>
</div>
@endsection