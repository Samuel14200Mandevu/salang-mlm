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
    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-500);
        box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.15);
    }
    @media (max-width: 640px) {
        .form-group label {
            font-size: 0.75rem;
        }
        .form-group .help-text {
            font-size: 0.65rem;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
        }
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        .edit-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .edit-header .avatar-wrapper {
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
                    Edit {{ $user->name }}
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    ID: #{{ $user->id }}
                </p>
            </div>
            <div class="avatar-wrapper ml-auto flex-shrink-0">
                @if($user->avatar)
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                         alt="Avatar" class="user-avatar">
                @else
                    <div class="user-avatar bg-primary-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="input text-sm sm:text-base @error('email') input-error @enderror" required>
                    @error('email') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" 
                           class="input text-sm sm:text-base @error('password') input-error @enderror" 
                           placeholder="Leave blank to keep current">
                    <p class="help-text">Minimum 8 characters</p>
                    @error('password') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" 
                           class="input text-sm sm:text-base" 
                           placeholder="Confirm new password">
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Package -->
                <div class="form-group">
                    <label>Package</label>
                    <select name="package_id" class="input text-sm sm:text-base">
                        <option value="">None</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ $user->package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} (${{ number_format($package->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sponsor -->
                <div class="form-group">
                    <label>Sponsor</label>
                    <select name="sponsor_id" class="input text-sm sm:text-base">
                        <option value="">None</option>
                        @foreach($sponsors ?? [] as $sponsor)
                            <option value="{{ $sponsor->id }}" {{ $user->sponsor_id == $sponsor->id ? 'selected' : '' }}>
                                {{ $sponsor->name }} ({{ $sponsor->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label>Role</label>
                    <select name="is_admin" class="input text-sm sm:text-base">
                        <option value="0" {{ !$user->hasRole('admin') ? 'selected' : '' }}>User</option>
                        <option value="1" {{ $user->hasRole('admin') ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Address (full width) -->
                <div class="form-group md:col-span-2">
                    <label>Address</label>
                    <textarea name="address" rows="2" class="input text-sm sm:text-base">{{ old('address', $user->address) }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update User
                </button>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection