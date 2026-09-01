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
.form-control-sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
}

.commission-card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    background: var(--bg-card);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.commission-card .card-title {
    font-size: 0.938rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 0.5rem;
}
.commission-card .card-title .badge {
    font-size: 0.6rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
    border: 1px solid var(--primary-blue-border);
    margin-left: 0.5rem;
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

.section-divider {
    border: none;
    border-top: 2px dashed var(--border-color);
    margin: 1rem 0;
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
    .commission-card {
        padding: 0.875rem;
    }
    .commission-card .card-title {
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
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Paramètres des commissions</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Configurer les taux, niveaux et seuils des commissions</p>
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

    <!-- Commission Rates -->
    <div class="commission-card animate-fadeInUp delay-1">
        <div class="card-title">
            Taux de commission par niveau
            <span class="badge">Unilevel</span>
        </div>

        <form action="{{ route('admin.settings.commission.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">

                <!-- Level 1 -->
                <div class="form-group">
                    <label>Niveau 1 (%) <span class="required">*</span></label>
                    <input type="number" name="level_1" step="0.01" min="0" max="100"
                           value="{{ old('level_1', $commissionSettings['levels'][1] ?? 0) }}"
                           class="form-control @error('level_1') form-control-error @enderror" required>
                    <span class="help-text">Distributeur - Starter (0%)</span>
                    @error('level_1')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 2 -->
                <div class="form-group">
                    <label>Niveau 2 (%) <span class="required">*</span></label>
                    <input type="number" name="level_2" step="0.01" min="0" max="100"
                           value="{{ old('level_2', $commissionSettings['levels'][2] ?? 0) }}"
                           class="form-control @error('level_2') form-control-error @enderror" required>
                    <span class="help-text">Qualification - Supervisor (0%)</span>
                    @error('level_2')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 3 -->
                <div class="form-group">
                    <label>Niveau 3 (%) <span class="required">*</span></label>
                    <input type="number" name="level_3" step="0.01" min="0" max="100"
                           value="{{ old('level_3', $commissionSettings['levels'][3] ?? 22) }}"
                           class="form-control @error('level_3') form-control-error @enderror" required>
                    <span class="help-text">Cumul Directeur - Commission directe</span>
                    @error('level_3')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 4 -->
                <div class="form-group">
                    <label>Niveau 4 (%) <span class="required">*</span></label>
                    <input type="number" name="level_4" step="0.01" min="0" max="100"
                           value="{{ old('level_4', $commissionSettings['levels'][4] ?? 26) }}"
                           class="form-control @error('level_4') form-control-error @enderror" required>
                    <span class="help-text">Directeur - Commission indirecte</span>
                    @error('level_4')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 5 -->
                <div class="form-group">
                    <label>Niveau 5 (%) <span class="required">*</span></label>
                    <input type="number" name="level_5" step="0.01" min="0" max="100"
                           value="{{ old('level_5', $commissionSettings['levels'][5] ?? 30) }}"
                           class="form-control @error('level_5') form-control-error @enderror" required>
                    <span class="help-text">Manager Senior - Leadership</span>
                    @error('level_5')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 6 -->
                <div class="form-group">
                    <label>Niveau 6 (%) <span class="required">*</span></label>
                    <input type="number" name="level_6" step="0.01" min="0" max="100"
                           value="{{ old('level_6', $commissionSettings['levels'][6] ?? 34) }}"
                           class="form-control @error('level_6') form-control-error @enderror" required>
                    <span class="help-text">Directeur Envolée</span>
                    @error('level_6')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 7 -->
                <div class="form-group">
                    <label>Niveau 7 (%) <span class="required">*</span></label>
                    <input type="number" name="level_7" step="0.01" min="0" max="100"
                           value="{{ old('level_7', $commissionSettings['levels'][7] ?? 40) }}"
                           class="form-control @error('level_7') form-control-error @enderror" required>
                    <span class="help-text">Saphire Manager</span>
                    @error('level_7')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 8 -->
                <div class="form-group">
                    <label>Niveau 8 (%) <span class="required">*</span></label>
                    <input type="number" name="level_8" step="0.01" min="0" max="100"
                           value="{{ old('level_8', $commissionSettings['levels'][8] ?? 43) }}"
                           class="form-control @error('level_8') form-control-error @enderror" required>
                    <span class="help-text">Diamant Bleu</span>
                    @error('level_8')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level 9 -->
                <div class="form-group">
                    <label>Niveau 9 (%) <span class="required">*</span></label>
                    <input type="number" name="level_9" step="0.01" min="0" max="100"
                           value="{{ old('level_9', $commissionSettings['levels'][9] ?? 45) }}"
                           class="form-control @error('level_9') form-control-error @enderror" required>
                    <span class="help-text">Perle Diamant</span>
                    @error('level_9')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <hr class="section-divider">

            <!-- Leadership Bonus Rates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">
                <div class="form-group">
                    <label>Leadership Niveau 5 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_5" step="0.01" min="0" max="100"
                           value="{{ old('leadership_5', $commissionSettings['rates']['leadership'][5] ?? 0.5) }}"
                           class="form-control @error('leadership_5') form-control-error @enderror" required>
                    <span class="help-text">Manager Senior</span>
                    @error('leadership_5')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Niveau 6 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_6" step="0.01" min="0" max="100"
                           value="{{ old('leadership_6', $commissionSettings['rates']['leadership'][6] ?? 1.1) }}"
                           class="form-control @error('leadership_6') form-control-error @enderror" required>
                    <span class="help-text">Directeur Envolée</span>
                    @error('leadership_6')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Niveau 7 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_7" step="0.01" min="0" max="100"
                           value="{{ old('leadership_7', $commissionSettings['rates']['leadership'][7] ?? 1.8) }}"
                           class="form-control @error('leadership_7') form-control-error @enderror" required>
                    <span class="help-text">Saphire Manager</span>
                    @error('leadership_7')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Niveau 8 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_8" step="0.01" min="0" max="100"
                           value="{{ old('leadership_8', $commissionSettings['rates']['leadership'][8] ?? 2.6) }}"
                           class="form-control @error('leadership_8') form-control-error @enderror" required>
                    <span class="help-text">Diamant Bleu</span>
                    @error('leadership_8')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Leadership Niveau 9 (%) <span class="required">*</span></label>
                    <input type="number" name="leadership_9" step="0.01" min="0" max="100"
                           value="{{ old('leadership_9', $commissionSettings['rates']['leadership'][9] ?? 3.5) }}"
                           class="form-control @error('leadership_9') form-control-error @enderror" required>
                    <span class="help-text">Perle Diamant</span>
                    @error('leadership_9')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="section-divider">

            <!-- Retail, Consumer, Global -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 grid-commission">
                <div class="form-group">
                    <label>Profit vente au détail (%) <span class="required">*</span></label>
                    <input type="number" name="retail_rate" step="0.01" min="0" max="100"
                           value="{{ old('retail_rate', $commissionSettings['rates']['retail'] ?? 25) }}"
                           class="form-control @error('retail_rate') form-control-error @enderror" required>
                    <span class="help-text">Profit sur les ventes de produits au détail</span>
                    @error('retail_rate')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Bonus consommateur (%) <span class="required">*</span></label>
                    <input type="number" name="consumer_bonus" step="0.01" min="0" max="100"
                           value="{{ old('consumer_bonus', $commissionSettings['rates']['consumer_bonus'] ?? 6) }}"
                           class="form-control @error('consumer_bonus') form-control-error @enderror" required>
                    <span class="help-text">Bonus consommateur sur achats personnels</span>
                    @error('consumer_bonus')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Pool bonus global (%) <span class="required">*</span></label>
                    <input type="number" name="global_bonus" step="0.01" min="0" max="100"
                           value="{{ old('global_bonus', $commissionSettings['rates']['global_bonus_pool'] ?? 6) }}"
                           class="form-control @error('global_bonus') form-control-error @enderror" required>
                    <span class="help-text">Distribution du pool de bonus global</span>
                    @error('global_bonus')
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

    <!-- Leadership & Withdrawal Settings -->
    <div class="commission-card animate-fadeInUp delay-2">
        <div class="card-title">
            Paramètres Leadership & Retraits
            <span class="badge">Seuils</span>
        </div>

        <form action="{{ route('admin.settings.commission.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 grid-commission">

                <div class="form-group">
                    <label>PV min Leadership <span class="required">*</span></label>
                    <input type="number" name="leadership_min_pv" min="0"
                           value="{{ old('leadership_min_pv', $commissionSettings['leadership']['min_pv'] ?? 30) }}"
                           class="form-control @error('leadership_min_pv') form-control-error @enderror" required>
                    <span class="help-text">PV minimum requis pour être leader</span>
                    @error('leadership_min_pv')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Niveaux max Leadership <span class="required">*</span></label>
                    <input type="number" name="leadership_max_levels" min="1" max="10"
                           value="{{ old('leadership_max_levels', $commissionSettings['leadership']['max_levels'] ?? 9) }}"
                           class="form-control @error('leadership_max_levels') form-control-error @enderror" required>
                    <span class="help-text">Niveaux maximum pour la commission leadership</span>
                    @error('leadership_max_levels')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Frais de retrait (%) <span class="required">*</span></label>
                    <input type="number" name="withdrawal_fee" step="0.01" min="0" max="100"
                           value="{{ old('withdrawal_fee', $commissionSettings['withdrawal_fee'] ?? 2.5) }}"
                           class="form-control @error('withdrawal_fee') form-control-error @enderror" required>
                    <span class="help-text">Frais appliqués sur chaque retrait</span>
                    @error('withdrawal_fee')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Retrait minimum ($) <span class="required">*</span></label>
                    <input type="number" name="min_withdrawal" step="0.01" min="0"
                           value="{{ old('min_withdrawal', $commissionSettings['min_withdrawal'] ?? 10) }}"
                           class="form-control @error('min_withdrawal') form-control-error @enderror" required>
                    <span class="help-text">Montant minimum autorisé pour un retrait</span>
                    @error('min_withdrawal')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Values Summary -->
            <div class="mt-4 p-3 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Résumé de la configuration actuelle</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs sm:text-sm">
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 3:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][3] ?? 22 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 4:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][4] ?? 26 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 5:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][5] ?? 30 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 6:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][6] ?? 34 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 7:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][7] ?? 40 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 8:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][8] ?? 43 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Niveau 9:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['levels'][9] ?? 45 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">PV min Leadership:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['leadership']['min_pv'] ?? 30 }}</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Frais de retrait:</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $commissionSettings['withdrawal_fee'] ?? 2.5 }}%</span>
                    </div>
                    <div>
                        <span class="text-[var(--text-tertiary)]">Retrait minimum:</span>
                        <span class="font-semibold text-[var(--text-primary)]">${{ $commissionSettings['min_withdrawal'] ?? 10 }}</span>
                    </div>
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

    <!-- Unilevel Levels Display -->
    <div class="commission-card animate-fadeInUp delay-3">
        <div class="card-title">
            Niveaux Unilevel
            <span class="badge">Distribution des commissions</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
            @php
                $levels = $commissionSettings['levels'] ?? [1 => 0, 2 => 0, 3 => 22, 4 => 26, 5 => 30, 6 => 34, 7 => 40, 8 => 43, 9 => 45];
            @endphp
            @foreach($levels as $level => $percentage)
                @if($level <= 9)
                <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau {{ $level }}</p>
                    <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $percentage }}%</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        @if($level == 1) Starter
                        @elseif($level == 2) Qualifié
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
                <span class="font-medium">Information :</span> Ces niveaux définissent la distribution des commissions sur le réseau unilevel.
                Niveaux 1-2 = Pas de commission, Niveau 3 = Parrain direct, Niveau 4 = Indirect, Niveaux 5+ = Leadership.
            </p>
        </div>
    </div>

</div>
@endsection