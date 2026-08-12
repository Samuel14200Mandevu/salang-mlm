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
    .rank-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .rank-status.active {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .rank-status.inactive {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
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
                    Edit {{ $rank->name }}
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    ID: #{{ $rank->id }}
                </p>
            </div>
            <div class="status-wrapper ml-auto flex-shrink-0">
                <span class="rank-status {{ $rank->is_active ? 'active' : 'inactive' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $rank->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $rank->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.ranks.update', $rank->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $rank->name) }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $rank->slug) }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    <p class="help-text">Unique URL identifier</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Min PV -->
                <div class="form-group">
                    <label>Minimum PV <span class="required">*</span></label>
                    <input type="number" name="min_pv" value="{{ old('min_pv', $rank->min_pv) }}" 
                           class="input text-sm sm:text-base @error('min_pv') input-error @enderror" required>
                    @error('min_pv') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Min BV -->
                <div class="form-group">
                    <label>Minimum BV</label>
                    <input type="number" name="min_bv" value="{{ old('min_bv', $rank->min_bv ?? 0) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Bonus Percentage -->
                <div class="form-group">
                    <label>Bonus (%) <span class="required">*</span></label>
                    <input type="number" name="bonus_percentage" step="0.01" value="{{ old('bonus_percentage', $rank->bonus_percentage) }}" 
                           class="input text-sm sm:text-base @error('bonus_percentage') input-error @enderror" required>
                    @error('bonus_percentage') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ $rank->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$rank->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Description (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="input text-sm sm:text-base">{{ old('description', $rank->description) }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Rank
                </button>
                <a href="{{ route('admin.ranks') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection