{{-- resources/views/admin/commissions/periods/create.blade.php --}}
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
    margin-bottom: 1rem;
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

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

.info-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}
.info-box .info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    border-bottom: 1px solid var(--border-light);
}
.info-box .info-item:last-child {
    border-bottom: none;
}
.info-box .info-item .label {
    font-size: 0.75rem;
    color: var(--text-secondary);
}
.info-box .info-item .value {
    font-weight: 600;
    color: var(--text-primary);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

@media (max-width: 640px) {
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
    .info-box .info-item {
        flex-direction: column;
        gap: 0.125rem;
    }
    .info-box .info-item .value {
        font-size: 0.875rem;
    }
    .card {
        padding: 0.875rem;
    }
    .btn {
        width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
            Créer une période
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            Créer une nouvelle période de calcul des commissions
        </p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4">
        <form action="{{ route('admin.commissions.periods.create') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Year -->
                <div class="form-group">
                    <label>Année <span class="required">*</span></label>
                    <select name="year" class="form-control @error('year') form-control-error @enderror" required>
                        @for($i = date('Y') + 1; $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ old('year', date('Y')) == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Month -->
                <div class="form-group">
                    <label>Mois <span class="required">*</span></label>
                    <select name="month" class="form-control @error('month') form-control-error @enderror" required>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('month', date('m')) == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                    @error('month')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Period Info -->
            <div class="info-box">
                <p class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-2">
                    Informations sur la période
                </p>
                <div class="info-item">
                    <span class="label">Période</span>
                    <span class="value" id="periodDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="label">Début</span>
                    <span class="value" id="startDateDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="label">Fin</span>
                    <span class="value" id="endDateDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="label">Jours</span>
                    <span class="value" id="daysDisplay">-</span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer la période
                </button>
                <a href="{{ route('admin.commissions.periods') }}" class="btn btn-outline flex-1 sm:flex-none">
                    Annuler
                </a>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.querySelector('select[name="year"]');
    const monthSelect = document.querySelector('select[name="month"]');

    const periodDisplay = document.getElementById('periodDisplay');
    const startDateDisplay = document.getElementById('startDateDisplay');
    const endDateDisplay = document.getElementById('endDateDisplay');
    const daysDisplay = document.getElementById('daysDisplay');

    function updatePeriodInfo() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);

        if (isNaN(year) || isNaN(month)) {
            periodDisplay.textContent = '-';
            startDateDisplay.textContent = '-';
            endDateDisplay.textContent = '-';
            daysDisplay.textContent = '-';
            return;
        }

        const startDate = new Date(year, month - 1, 1);
        const endDate = new Date(year, month, 0);
        const days = endDate.getDate();

        const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        periodDisplay.textContent = year + '-' + String(month).padStart(2, '0');
        startDateDisplay.textContent = '1er ' + monthNames[month - 1] + ' ' + year;
        endDateDisplay.textContent = days + ' ' + monthNames[month - 1] + ' ' + year;
        daysDisplay.textContent = days + ' jours';
    }

    yearSelect.addEventListener('change', updatePeriodInfo);
    monthSelect.addEventListener('change', updatePeriodInfo);

    updatePeriodInfo();
});
</script>
@endpush
@endsection