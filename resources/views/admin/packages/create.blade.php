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
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Add Package</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Create a new membership package</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.packages.store') }}" method="POST">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" 
                           placeholder="Bronze Package" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" 
                           placeholder="bronze-package" required>
                    <p class="help-text">Unique URL identifier (e.g. bronze-package)</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label>Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}" 
                           class="input text-sm sm:text-base @error('price') input-error @enderror" 
                           placeholder="99.99" required>
                    @error('price') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- PV Value -->
                <div class="form-group">
                    <label>PV Value <span class="required">*</span></label>
                    <input type="number" name="pv_value" value="{{ old('pv_value') }}" 
                           class="input text-sm sm:text-base @error('pv_value') input-error @enderror" 
                           placeholder="100" required>
                    @error('pv_value') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- BV Value -->
                <div class="form-group">
                    <label>BV Value</label>
                    <input type="number" name="bv_value" value="{{ old('bv_value', 0) }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="0">
                </div>

                <!-- Commission Rate -->
                <div class="form-group">
                    <label>Commission Rate (%) <span class="required">*</span></label>
                    <input type="number" name="commission_rate" step="0.01" value="{{ old('commission_rate', 30) }}" 
                           class="input text-sm sm:text-base @error('commission_rate') input-error @enderror" 
                           placeholder="30" required>
                    @error('commission_rate') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Description (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base" 
                              placeholder="Package description...">{{ old('description') }}</textarea>
                </div>

                <!-- Level (hidden or optional) -->
                <input type="hidden" name="level" value="{{ old('level', 1) }}">
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Package
                </button>
                <a href="{{ route('admin.packages') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection