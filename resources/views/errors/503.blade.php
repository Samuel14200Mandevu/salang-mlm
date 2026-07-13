{{-- resources/views/errors/503.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .error-icon {
        width: 6rem;
        height: 6rem;
        margin: 0 auto 1rem;
        color: #f59e0b;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-lg);
    }
    
    .maintenance-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    @media (max-width: 640px) {
        .error-icon { width: 4rem; height: 4rem; }
        .text-6xl { font-size: 3rem; }
        .card { padding: 1.25rem; }
        .btn { font-size: 0.813rem; padding: 0.5rem 1rem; }
        .error-actions { flex-direction: column; }
        .error-actions .btn { width: 100%; }
        .maintenance-icon { font-size: 3rem; }
    }
    
    @media (max-width: 480px) {
        .card { padding: 0.875rem; }
        .error-icon { width: 3rem; height: 3rem; }
        .text-4xl { font-size: 1.5rem; }
        .maintenance-icon { font-size: 2.5rem; }
    }
</style>
@endpush

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-3 sm:px-4 py-8">
    <div class="card text-center max-w-md w-full animate-fadeInUp">
        
        <span class="maintenance-icon">🔧</span>
        
        <h1 class="text-6xl sm:text-7xl font-bold text-yellow-500">503</h1>
        <h2 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] mt-2">Maintenance Mode</h2>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            Our website is currently undergoing scheduled maintenance. Please check back later.
        </p>
        
        @if(isset($estimated_time) && $estimated_time)
            <div class="mt-4 p-3 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    Estimated completion time:
                    <span class="font-semibold text-primary-500">{{ $estimated_time }}</span>
                </p>
            </div>
        @endif
        
        <div class="error-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Home
            </a>
            <button onclick="location.reload()" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>
</div>
@endsection