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
    .form-group .input-sm {
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .commission-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        background: var(--bg-card);
    }
    .commission-card .card-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .commission-card .card-title .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: rgba(90, 182, 56, 0.12);
        color: var(--primary-500);
        margin-left: 0.5rem;
    }
    
    @media (max-width: 640px) {
        .commission-card {
            padding: 0.875rem;
        }
        .commission-card .card-title {
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
        .grid-commission {
            grid-template-columns: 1fr !important;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .grid-commission {
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Commission Settings</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configure commission rates and thresholds</p>
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

    <div class="commission-card animate-fadeInUp delay-1">
        <div class="card-title">
            Commission Rates
            <span class="badge">Unilevel</span>
        </div>
        
        <form action="{{ route('admin.settings.update-commission') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                
                <!-- Direct Commission -->
                <div class="form-group">
                    <label>Direct Commission (%) <span class="required">*</span></label>
                    <input type="number" name="direct_rate" step="0.01" min="0" max="100"
                           value="{{ old('direct_rate', $commissionSettings['rates']['direct'] ?? 30) }}"
                           class="input @error('direct_rate') input-error @enderror" required>
                    <p class="help-text">Commission paid to the direct sponsor</p>
                    @error('direct_rate')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Indirect Commission -->
                <div class="form-group">
                    <label>Indirect Commission (%) <span class="required">*</span></label>
                    <input type="number" name="indirect_rate" step="0.01" min="0" max="100"
                           value="{{ old('indirect_rate', $commissionSettings['rates']['indirect'] ?? 15) }}"
                           class="input @error('indirect_rate') input-error @enderror" required>
                    <p class="help-text">Commission paid to the sponsor's sponsor (level 2)</p>
                    @error('indirect_rate')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Leadership Commission -->
                <div class="form-group">
                    <label>Leadership Commission (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_rate" step="0.01" min="0" max="100"
                           value="{{ old('leadership_rate', $commissionSettings['rates']['leadership'] ?? 10) }}"
                           class="input @error('leadership_rate') input-error @enderror" required>
                    <p class="help-text">Commission paid to leaders (levels 3+)</p>
                    @error('leadership_rate')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Retail Profit -->
                <div class="form-group">
                    <label>Retail Profit (%) <span class="required">*</span></label>
                    <input type="number" name="retail_rate" step="0.01" min="0" max="100"
                           value="{{ old('retail_rate', $commissionSettings['rates']['retail'] ?? 25) }}"
                           class="input @error('retail_rate') input-error @enderror" required>
                    <p class="help-text">Profit on retail product sales</p>
                    @error('retail_rate')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 sm:mt-6">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Commission Rates
                </button>
            </div>
        </form>
    </div>

    <!-- Leadership & Withdrawal Settings -->
    <div class="commission-card animate-fadeInUp delay-2">
        <div class="card-title">
            Leadership & Withdrawal Settings
            <span class="badge">Thresholds</span>
        </div>
        
        <form action="{{ route('admin.settings.update-commission') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                
                <!-- Leadership Min PV -->
                <div class="form-group">
                    <label>Leadership Min PV <span class="required">*</span></label>
                    <input type="number" name="leadership_min_pv" min="0"
                           value="{{ old('leadership_min_pv', $commissionSettings['leadership']['min_pv'] ?? 1000) }}"
                           class="input @error('leadership_min_pv') input-error @enderror" required>
                    <p class="help-text">Minimum PV required to be a leader</p>
                    @error('leadership_min_pv')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Leadership Max Levels -->
                <div class="form-group">
                    <label>Leadership Max Levels <span class="required">*</span></label>
                    <input type="number" name="leadership_max_levels" min="1" max="10"
                           value="{{ old('leadership_max_levels', $commissionSettings['leadership']['max_levels'] ?? 5) }}"
                           class="input @error('leadership_max_levels') input-error @enderror" required>
                    <p class="help-text">Maximum levels for leadership commission</p>
                    @error('leadership_max_levels')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Withdrawal Fee -->
                <div class="form-group">
                    <label>Withdrawal Fee (%) <span class="required">*</span></label>
                    <input type="number" name="withdrawal_fee" step="0.01" min="0" max="100"
                           value="{{ old('withdrawal_fee', $commissionSettings['withdrawal_fee'] ?? 2.5) }}"
                           class="input @error('withdrawal_fee') input-error @enderror" required>
                    <p class="help-text">Fee charged on each withdrawal</p>
                    @error('withdrawal_fee')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Min Withdrawal -->
                <div class="form-group">
                    <label>Min Withdrawal ($) <span class="required">*</span></label>
                    <input type="number" name="min_withdrawal" step="0.01" min="0"
                           value="{{ old('min_withdrawal', $commissionSettings['min_withdrawal'] ?? 10) }}"
                           class="input @error('min_withdrawal') input-error @enderror" required>
                    <p class="help-text">Minimum amount allowed for withdrawal</p>
                    @error('min_withdrawal')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Values Summary -->
            <div class="mt-4 p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Current Configuration Summary</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs sm:text-sm">
                    <div>
                        <span class="text-[var(--text-tertiary)]">Direct:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['rates']['direct'] ?? 30 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Indirect:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['rates']['indirect'] ?? 15 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Leadership:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['rates']['leadership'] ?? 10 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Retail:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['rates']['retail'] ?? 25 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Fee:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['withdrawal_fee'] ?? 2.5 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Min Withdrawal:</span>
                        <span class="font-semibold text-[var(--text-primary)]">${{ $commissionSettings['min_withdrawal'] ?? 10 }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Leadership PV:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['leadership']['min_pv'] ?? 1000 }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Max Levels:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['leadership']['max_levels'] ?? 5 }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 sm:mt-6">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save All Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Unilevel Levels -->
    <div class="commission-card animate-fadeInUp delay-3">
        <div class="card-title">
            Unilevel Levels
            <span class="badge">Commission Distribution</span>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
            @php
                $levels = $commissionSettings['levels'] ?? [1 => 30, 2 => 15, 3 => 10, 4 => 5, 5 => 5];
            @endphp
            @foreach($levels as $level => $percentage)
                <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Level {{ $level }}</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $percentage }}%</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        @if($level == 1) Direct
                        @elseif($level == 2) Indirect
                        @elseif($level >= 3) Leadership
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
        
        <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                <span class="font-medium">Info:</span> These levels define the commission distribution across the unilevel network.
                Level 1 = Direct sponsor, Level 2 = Indirect, Levels 3+ = Leadership.
            </p>
        </div>
    </div>
</div>
@endsection