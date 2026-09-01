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

.rank-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.rank-status.active {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.rank-status.inactive {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.status-dot.active { background: #1C7E4A; }
.status-dot.inactive { background: #B91C1C; }

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
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    .edit-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    .edit-header .status-wrapper {
        margin-left: 0 !important;
        margin-top: 0.5rem;
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
        <div class="edit-header flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                    Modifier {{ $rank->name }}
                </h1>
                <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                    ID: #{{ $rank->id }}
                </p>
            </div>
            <div class="status-wrapper ml-auto flex-shrink-0">
                <span class="rank-status {{ $rank->is_active ? 'active' : 'inactive' }}">
                    <span class="status-dot {{ $rank->is_active ? 'active' : 'inactive' }}"></span>
                    {{ $rank->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4">
        <form action="{{ route('admin.ranks.update', $rank->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Name -->
                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $rank->name) }}"
                           class="form-control @error('name') form-control-error @enderror" required>
                    @error('name')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $rank->slug) }}"
                           class="form-control @error('slug') form-control-error @enderror" required>
                    <span class="help-text">Identifiant unique pour l'URL</span>
                    @error('slug')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Min PV -->
                <div class="form-group">
                    <label>PV minimum <span class="required">*</span></label>
                    <input type="number" name="min_pv" value="{{ old('min_pv', $rank->min_pv) }}"
                           class="form-control @error('min_pv') form-control-error @enderror" required>
                    @error('min_pv')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Min BV -->
                <div class="form-group">
                    <label>BV minimum</label>
                    <input type="number" name="min_bv" value="{{ old('min_bv', $rank->min_bv ?? 0) }}"
                           class="form-control">
                </div>

                <!-- Bonus Percentage -->
                <div class="form-group">
                    <label>Bonus (%) <span class="required">*</span></label>
                    <input type="number" name="bonus_percentage" step="0.01" value="{{ old('bonus_percentage', $rank->bonus_percentage) }}"
                           class="form-control @error('bonus_percentage') form-control-error @enderror" required>
                    @error('bonus_percentage')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Statut</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $rank->is_active ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ !$rank->is_active ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $rank->description) }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.ranks') }}" class="btn btn-outline flex-1 sm:flex-none">
                    Annuler
                </a>
            </div>
        </form>
    </div>

</div>
@endsection