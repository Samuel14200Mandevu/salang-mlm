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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Add Rank</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Create a new rank</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.ranks.store') }}" method="POST">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" 
                           placeholder="Manager" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" 
                           placeholder="manager" required>
                    <p class="help-text">Unique URL identifier (e.g. manager)</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Min PV -->
                <div class="form-group">
                    <label>Minimum PV <span class="required">*</span></label>
                    <input type="number" name="min_pv" value="{{ old('min_pv', 0) }}" 
                           class="input text-sm sm:text-base @error('min_pv') input-error @enderror" 
                           placeholder="0" required>
                    @error('min_pv') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Min BV -->
                <div class="form-group">
                    <label>Minimum BV</label>
                    <input type="number" name="min_bv" value="{{ old('min_bv', 0) }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="0">
                </div>

                <!-- Bonus Percentage -->
                <div class="form-group">
                    <label>Bonus (%) <span class="required">*</span></label>
                    <input type="number" name="bonus_percentage" step="0.01" value="{{ old('bonus_percentage', 0) }}" 
                           class="input text-sm sm:text-base @error('bonus_percentage') input-error @enderror" 
                           placeholder="0" required>
                    @error('bonus_percentage') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Description (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="input text-sm sm:text-base" 
                              placeholder="Rank description...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Rank
                </button>
                <a href="{{ route('admin.ranks') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection