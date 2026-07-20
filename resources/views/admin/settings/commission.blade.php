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
    
    .section-divider {
        border: none;
        border-top: 2px dashed var(--border-color);
        margin: 1rem 0;
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
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configure commission rates, levels and thresholds</p>
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

    <!-- ============================================================ -->
    <!-- COMMISSION RATES BY LEVEL                                      -->
    <!-- ============================================================ -->
    <div class="commission-card animate-fadeInUp delay-1">
        <div class="card-title">
            Commission Rates by Level
            <span class="badge">Unilevel</span>
        </div>
        
        <form action="{{ route('admin.settings.commission.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                
                <!-- Level 1 -->
                <div class="form-group">
                    <label>Level 1 (%) <span class="required">*</span></label>
                    <input type="number" name="level_1" step="0.01" min="0" max="100"
                           value="{{ old('level_1', $commissionSettings['levels'][1] ?? 0) }}"
                           class="input @error('level_1') input-error @enderror" required>
                    <p class="help-text">Distributeur - Starter (0%)</p>
                    @error('level_1')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 2 -->
                <div class="form-group">
                    <label>Level 2 (%) <span class="required">*</span></label>
                    <input type="number" name="level_2" step="0.01" min="0" max="100"
                           value="{{ old('level_2', $commissionSettings['levels'][2] ?? 0) }}"
                           class="input @error('level_2') input-error @enderror" required>
                    <p class="help-text">Qualification - Supervisor (0%)</p>
                    @error('level_2')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 3 -->
                <div class="form-group">
                    <label>Level 3 (%) <span class="required">*</span></label>
                    <input type="number" name="level_3" step="0.01" min="0" max="100"
                           value="{{ old('level_3', $commissionSettings['levels'][3] ?? 22) }}"
                           class="input @error('level_3') input-error @enderror" required>
                    <p class="help-text">Cumul Directeur - Direct Commission</p>
                    @error('level_3')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 4 -->
                <div class="form-group">
                    <label>Level 4 (%) <span class="required">*</span></label>
                    <input type="number" name="level_4" step="0.01" min="0" max="100"
                           value="{{ old('level_4', $commissionSettings['levels'][4] ?? 26) }}"
                           class="input @error('level_4') input-error @enderror" required>
                    <p class="help-text">Directeur - Indirect Commission</p>
                    @error('level_4')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 5 -->
                <div class="form-group">
                    <label>Level 5 (%) <span class="required">*</span></label>
                    <input type="number" name="level_5" step="0.01" min="0" max="100"
                           value="{{ old('level_5', $commissionSettings['levels'][5] ?? 30) }}"
                           class="input @error('level_5') input-error @enderror" required>
                    <p class="help-text">Manager Senior - Leadership</p>
                    @error('level_5')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 6 -->
                <div class="form-group">
                    <label>Level 6 (%) <span class="required">*</span></label>
                    <input type="number" name="level_6" step="0.01" min="0" max="100"
                           value="{{ old('level_6', $commissionSettings['levels'][6] ?? 34) }}"
                           class="input @error('level_6') input-error @enderror" required>
                    <p class="help-text">Directeur Envolée</p>
                    @error('level_6')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 7 -->
                <div class="form-group">
                    <label>Level 7 (%) <span class="required">*</span></label>
                    <input type="number" name="level_7" step="0.01" min="0" max="100"
                           value="{{ old('level_7', $commissionSettings['levels'][7] ?? 40) }}"
                           class="input @error('level_7') input-error @enderror" required>
                    <p class="help-text">Saphire Manager</p>
                    @error('level_7')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 8 -->
                <div class="form-group">
                    <label>Level 8 (%) <span class="required">*</span></label>
                    <input type="number" name="level_8" step="0.01" min="0" max="100"
                           value="{{ old('level_8', $commissionSettings['levels'][8] ?? 43) }}"
                           class="input @error('level_8') input-error @enderror" required>
                    <p class="help-text">Diamant Bleu</p>
                    @error('level_8')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 9 -->
                <div class="form-group">
                    <label>Level 9 (%) <span class="required">*</span></label>
                    <input type="number" name="level_9" step="0.01" min="0" max="100"
                           value="{{ old('level_9', $commissionSettings['levels'][9] ?? 45) }}"
                           class="input @error('level_9') input-error @enderror" required>
                    <p class="help-text">Perle Diamant</p>
                    @error('level_9')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <hr class="section-divider">

            <!-- Leadership Bonus Rates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                <div class="form-group">
                    <label>Leadership Level 5 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_5" step="0.01" min="0" max="100"
                           value="{{ old('leadership_5', $commissionSettings['rates']['leadership'][5] ?? 0.5) }}"
                           class="input @error('leadership_5') input-error @enderror" required>
                    <p class="help-text">Manager Senior</p>
                    @error('leadership_5')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Level 6 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_6" step="0.01" min="0" max="100"
                           value="{{ old('leadership_6', $commissionSettings['rates']['leadership'][6] ?? 1.1) }}"
                           class="input @error('leadership_6') input-error @enderror" required>
                    <p class="help-text">Directeur Envolée</p>
                    @error('leadership_6')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Level 7 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_7" step="0.01" min="0" max="100"
                           value="{{ old('leadership_7', $commissionSettings['rates']['leadership'][7] ?? 1.8) }}"
                           class="input @error('leadership_7') input-error @enderror" required>
                    <p class="help-text">Saphire Manager</p>
                    @error('leadership_7')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Level 8 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_8" step="0.01" min="0" max="100"
                           value="{{ old('leadership_8', $commissionSettings['rates']['leadership'][8] ?? 2.6) }}"
                           class="input @error('leadership_8') input-error @enderror" required>
                    <p class="help-text">Diamant Bleu</p>
                    @error('leadership_8')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Level 9 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_9" step="0.01" min="0" max="100"
                           value="{{ old('leadership_9', $commissionSettings['rates']['leadership'][9] ?? 3.5) }}"
                           class="input @error('leadership_9') input-error @enderror" required>
                    <p class="help-text">Perle Diamant</p>
                    @error('leadership_9')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="section-divider">

            <!-- Retail, Consumer, Global -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 grid-commission">
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

                <div class="form-group">
                    <label>Consumer Bonus (%) <span class="required">*</span></label>
                    <input type="number" name="consumer_bonus" step="0.01" min="0" max="100"
                           value="{{ old('consumer_bonus', $commissionSettings['rates']['consumer_bonus'] ?? 6) }}"
                           class="input @error('consumer_bonus') input-error @enderror" required>
                    <p class="help-text">Consumer bonus on personal purchases</p>
                    @error('consumer_bonus')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Global Bonus Pool (%) <span class="required">*</span></label>
                    <input type="number" name="global_bonus" step="0.01" min="0" max="100"
                           value="{{ old('global_bonus', $commissionSettings['rates']['global_bonus_pool'] ?? 6) }}"
                           class="input @error('global_bonus') input-error @enderror" required>
                    <p class="help-text">Global bonus pool distribution</p>
                    @error('global_bonus')
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

    <!-- ============================================================ -->
    <!-- LEADERSHIP & WITHDRAWAL SETTINGS                             -->
    <!-- ============================================================ -->
    <div class="commission-card animate-fadeInUp delay-2">
        <div class="card-title">
            Leadership & Withdrawal Settings
            <span class="badge">Thresholds</span>
        </div>
        
        <form action="{{ route('admin.settings.commission.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                
                <div class="form-group">
                    <label>Leadership Min PV <span class="required">*</span></label>
                    <input type="number" name="leadership_min_pv" min="0"
                           value="{{ old('leadership_min_pv', $commissionSettings['leadership']['min_pv'] ?? 30) }}"
                           class="input @error('leadership_min_pv') input-error @enderror" required>
                    <p class="help-text">Minimum PV required to be a leader</p>
                    @error('leadership_min_pv')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Max Levels <span class="required">*</span></label>
                    <input type="number" name="leadership_max_levels" min="1" max="10"
                           value="{{ old('leadership_max_levels', $commissionSettings['leadership']['max_levels'] ?? 9) }}"
                           class="input @error('leadership_max_levels') input-error @enderror" required>
                    <p class="help-text">Maximum levels for leadership commission</p>
                    @error('leadership_max_levels')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

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
                        <span class="text-[var(--text-tertiary)]">Level 3:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][3] ?? 22 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 4:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][4] ?? 26 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 5:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][5] ?? 30 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 6:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][6] ?? 34 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 7:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][7] ?? 40 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 8:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][8] ?? 43 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Level 9:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][9] ?? 45 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Leadership Min PV:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['leadership']['min_pv'] ?? 30 }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Withdrawal Fee:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['withdrawal_fee'] ?? 2.5 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Min Withdrawal:</span>
                        <span class="font-semibold text-[var(--text-primary)]">${{ $commissionSettings['min_withdrawal'] ?? 10 }}</span>
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

    <!-- ============================================================ -->
    <!-- UNILEVEL LEVELS DISPLAY                                       -->
    <!-- ============================================================ -->
    <div class="commission-card animate-fadeInUp delay-3">
        <div class="card-title">
            Unilevel Levels
            <span class="badge">Commission Distribution</span>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
            @php
                $levels = $commissionSettings['levels'] ?? [1 => 0, 2 => 0, 3 => 22, 4 => 26, 5 => 30, 6 => 34, 7 => 40, 8 => 43, 9 => 45];
            @endphp
            @foreach($levels as $level => $percentage)
                @if($level <= 9)
                <div class="p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Level {{ $level }}</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $percentage }}%</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        @if($level == 1) Starter
                        @elseif($level == 2) Qualified
                        @elseif($level == 3) Direct
                        @elseif($level == 4) Indirect
                        @elseif($level >= 5) Leadership
                        @endif
                    </p>
                </div>
                @endif
            @endforeach
        </div>
        
        <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                <span class="font-medium">Info:</span> These levels define the commission distribution across the unilevel network.
                Level 1-2 = No commission, Level 3 = Direct sponsor, Level 4 = Indirect, Levels 5+ = Leadership.
            </p>
        </div>
    </div>
</div>
@endsection