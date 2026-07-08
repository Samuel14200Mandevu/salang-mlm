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
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Add User</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Create a new user account
        </p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" 
                           placeholder="John Doe" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="input text-sm sm:text-base @error('email') input-error @enderror" 
                           placeholder="john@email.com" required>
                    @error('email') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" 
                           class="input text-sm sm:text-base @error('password') input-error @enderror" 
                           placeholder="........" required>
                    <p class="help-text">Minimum 8 characters</p>
                    @error('password') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" 
                           class="input text-sm sm:text-base" 
                           placeholder="........" required>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="+243 XX XXX XXXX">
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="Congo">
                </div>

                <!-- Package -->
                <div class="form-group">
                    <label>Package</label>
                    <select name="package_id" class="input text-sm sm:text-base">
                        <option value="">None</option>
                        @if(isset($packages) && $packages->count() > 0)
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} (${{ number_format($package->price, 2) }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- ✅ CORRIGÉ : Sponsor avec code de parrain -->
                <div class="form-group">
                    <label>Sponsor</label>
                    <select name="sponsor_id" class="input text-sm sm:text-base">
                        <option value="">None</option>
                        @if(isset($users) && $users->count() > 0)
                            @foreach($users as $sponsor)
                                <option value="{{ $sponsor->sponsor_id }}" {{ old('sponsor_id') == $sponsor->sponsor_id ? 'selected' : '' }}>
                                    {{ $sponsor->name }} ({{ $sponsor->sponsor_id }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="help-text">Sélectionnez le parrain par son code unique</p>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label>Role</label>
                    <select name="is_admin" class="input text-sm sm:text-base">
                        <option value="0" {{ old('is_admin') == 0 ? 'selected' : '' }}>User</option>
                        <option value="1" {{ old('is_admin') == 1 ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Address (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Address</label>
                    <textarea name="address" rows="2" class="input text-sm sm:text-base" 
                              placeholder="Full address...">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Note sur le code de parrain -->
            <div class="mt-4 p-3 bg-primary-500/5 border border-primary-500/20 rounded-lg">
                <p class="text-sm text-[var(--text-secondary)]">
                    <span class="font-semibold text-primary-500">Note:</span> 
                    Un code de parrain unique sera généré automatiquement pour ce nouvel utilisateur.
                </p>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create User
                </button>
                <a href="{{ route('admin.users') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection