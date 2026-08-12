{{-- resources/views/errors/401.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .error-icon {
        width: 6rem;
        height: 6rem;
        margin: 0 auto 1rem;
        color: #8b5cf6;
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
    
    @media (max-width: 640px) {
        .error-icon { width: 4rem; height: 4rem; }
        .text-6xl { font-size: 3rem; }
        .card { padding: 1.25rem; }
        .btn { font-size: 0.813rem; padding: 0.5rem 1rem; }
        .error-actions { flex-direction: column; }
        .error-actions .btn { width: 100%; }
    }
    
    @media (max-width: 480px) {
        .card { padding: 0.875rem; }
        .error-icon { width: 3rem; height: 3rem; }
        .text-4xl { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-3 sm:px-4 py-8">
    <div class="card text-center max-w-md w-full animate-fadeInUp">
        
        <svg class="error-icon mx-auto text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        
        <h1 class="text-6xl sm:text-7xl font-bold text-purple-500">401</h1>
        <h2 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] mt-2">Unauthorized</h2>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            You need to be logged in to access this page.
        </p>
        
        <div class="error-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('login') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Login
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Register
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection