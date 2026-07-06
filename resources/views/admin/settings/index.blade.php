@extends('admin.layouts.app')

@push('styles')
<style>
    .setting-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        text-decoration: none !important;
        display: block;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        background: var(--bg-card);
    }
    .setting-card:hover {
        transform: translateY(-6px);
        border-color: var(--primary-500);
        box-shadow: var(--shadow-hover);
    }
    .setting-card .card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .setting-card:hover .card-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    .setting-card .card-icon-primary {
        background: rgba(90, 182, 56, 0.12);
        color: var(--primary-500);
    }
    .setting-card .card-icon-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .setting-card .card-icon-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .setting-card .card-icon-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .setting-card .card-icon-purple {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .setting-card .card-icon-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .setting-card h3 {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.125rem;
    }
    .setting-card p {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        margin: 0;
    }
    .setting-card .badge-status {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-status-enabled {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-status-disabled {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    
    .maintenance-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        background: var(--bg-card);
    }
    .maintenance-card h3 {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }
    
    @media (max-width: 640px) {
        .setting-card {
            padding: 0.875rem;
        }
        .setting-card .card-icon {
            width: 2.5rem;
            height: 2.5rem;
        }
        .setting-card .card-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .setting-card h3 {
            font-size: 0.8125rem;
        }
        .setting-card p {
            font-size: 0.7rem;
        }
        .maintenance-card {
            padding: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Settings</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Platform configuration</p>
        </div>
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

    <!-- Settings Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        
        <!-- General Settings -->
        <a href="{{ route('admin.settings') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-primary">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>General</h3>
                    <p>Site name, timezone, locale</p>
                </div>
            </div>
        </a>

        <!-- Commission Settings -->
        <a href="{{ route('admin.settings.commission') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-success">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Commissions</h3>
                    <p>Rates and thresholds</p>
                </div>
            </div>
        </a>

        <!-- Payment Settings -->
        <a href="{{ route('admin.settings.payment') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-info">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Payments</h3>
                    <p>Gateways and fees</p>
                </div>
            </div>
        </a>

        <!-- Maintenance -->
        <div class="setting-card" style="cursor: default; border-color: var(--border-color);">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-warning">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Maintenance</h3>
                    <p>Cache and optimization</p>
                </div>
            </div>
        </div>
    </div>

    <!-- General Settings Form -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4 md:p-6">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4 border-b border-[var(--border-color)] pb-2">
            General Settings
        </h3>
        
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <!-- Site Name -->
                <div class="form-group">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Site Name <span class="required" style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="site_name" 
                           value="{{ old('site_name', config('app.name')) }}" 
                           class="input text-sm sm:text-base @error('site_name') input-error @enderror" 
                           required>
                    @error('site_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Site URL -->
                <div class="form-group">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Site URL <span class="required" style="color: #ef4444;">*</span>
                    </label>
                    <input type="url" name="site_url" 
                           value="{{ old('site_url', config('app.url')) }}" 
                           class="input text-sm sm:text-base @error('site_url') input-error @enderror" 
                           required>
                    @error('site_url')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Timezone -->
                <div class="form-group">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Timezone <span class="required" style="color: #ef4444;">*</span>
                    </label>
                    <select name="timezone" class="input text-sm sm:text-base" required>
                        <option value="UTC" {{ config('app.timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Africa/Lagos" {{ config('app.timezone') == 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos</option>
                        <option value="Africa/Kinshasa" {{ config('app.timezone') == 'Africa/Kinshasa' ? 'selected' : '' }}>Africa/Kinshasa</option>
                        <option value="Africa/Lubumbashi" {{ config('app.timezone') == 'Africa/Lubumbashi' ? 'selected' : '' }}>Africa/Lubumbashi</option>
                        <option value="Europe/Paris" {{ config('app.timezone') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                        <option value="Europe/London" {{ config('app.timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                        <option value="America/New_York" {{ config('app.timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                        <option value="America/Los_Angeles" {{ config('app.timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                        <option value="Asia/Dubai" {{ config('app.timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                        <option value="Asia/Singapore" {{ config('app.timezone') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore</option>
                    </select>
                    @error('timezone')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Locale -->
                <div class="form-group">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Language <span class="required" style="color: #ef4444;">*</span>
                    </label>
                    <select name="locale" class="input text-sm sm:text-base" required>
                        <option value="en" {{ config('app.locale') == 'en' ? 'selected' : '' }}>English</option>
                        <option value="fr" {{ config('app.locale') == 'fr' ? 'selected' : '' }}>Français</option>
                    </select>
                    @error('locale')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Debug Mode -->
                <div class="form-group md:col-span-2">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="debug_mode" value="1" 
                               {{ config('app.debug') ? 'checked' : '' }}>
                        Enable Debug Mode
                    </label>
                    <p class="help-text text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">
                        Only enable for development. Always disable in production.
                    </p>
                </div>
            </div>

            <div class="mt-4 sm:mt-6">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Maintenance -->
    <div class="maintenance-card animate-fadeInUp delay-3">
        <h3>Maintenance</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear Cache
                </button>
            </form>
            
            <form action="{{ route('admin.settings.optimize') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Optimize Application
                </button>
            </form>
        </div>
        
        <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                <span class="font-medium">Info:</span> Clearing cache will remove all cached data.
                Optimizing will cache routes, views, and configuration for better performance.
            </p>
        </div>
    </div>

    <!-- Environment Info -->
    <div class="maintenance-card animate-fadeInUp delay-4 border-l-4 border-primary-500">
        <h3>Environment</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 text-xs sm:text-sm">
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Environment</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.env') }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Debug Mode</span>
                <span class="font-semibold {{ config('app.debug') ? 'text-red-500' : 'text-green-500' }}">
                    {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Laravel Version</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ app()->version() }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">PHP Version</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ phpversion() }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Timezone</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.timezone') }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Locale</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.locale') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection