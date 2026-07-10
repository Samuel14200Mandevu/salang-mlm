@extends('admin.layouts.app')

@push('styles')
<style>
    .form-group {
        margin-bottom: 1rem;
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
    .package-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .package-status.active {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .package-status.inactive {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-top: 0.5rem;
    }
    .checkbox-wrapper input[type="checkbox"] {
        width: 1.1rem;
        height: 1.1rem;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        cursor: pointer;
        accent-color: var(--primary-500);
    }
    .checkbox-wrapper label {
        margin-bottom: 0;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    .checkbox-wrapper .help-text {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 0;
    }
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <div class="edit-header flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                    Modifier {{ $package->name }}
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    ID: #{{ $package->id }}
                </p>
            </div>
            <div class="status-wrapper ml-auto flex-shrink-0">
                <span class="package-status {{ $package->is_active ? 'active' : 'inactive' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $package->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $package->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $package->slug) }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    <p class="help-text">Identifiant unique pour l'URL</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label>Prix (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $package->price) }}" 
                           class="input text-sm sm:text-base @error('price') input-error @enderror" required>
                    @error('price') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- PV Value -->
                <div class="form-group">
                    <label>Valeur PV <span class="required">*</span></label>
                    <input type="number" name="pv_value" value="{{ old('pv_value', $package->pv_value) }}" 
                           class="input text-sm sm:text-base @error('pv_value') input-error @enderror" required>
                    @error('pv_value') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- BV Value -->
                <div class="form-group">
                    <label>Valeur BV</label>
                    <input type="number" name="bv_value" value="{{ old('bv_value', $package->bv_value) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Commission Rate -->
                <div class="form-group">
                    <label>Taux de commission (%) <span class="required">*</span></label>
                    <input type="number" name="commission_rate" step="0.01" value="{{ old('commission_rate', $package->commission_rate) }}" 
                           class="input text-sm sm:text-base @error('commission_rate') input-error @enderror" required>
                    @error('commission_rate') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Description (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base">{{ old('description', $package->description) }}</textarea>
                </div>

                <!-- Status (Checkbox) -->
                <div class="form-group md:col-span-2">
                    <div class="checkbox-wrapper">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1" 
                               {{ $package->is_active ? 'checked' : '' }}>
                        <label for="is_active">
                            Package actif
                            <span class="help-text block">Décochez pour désactiver ce package</span>
                        </label>
                    </div>
                </div>

                <!-- Level (hidden) -->
                <input type="hidden" name="level" value="{{ $package->level ?? 1 }}">
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.packages') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection