@extends('layouts.app')

@push('styles')
<style>
    .level-card { transition: all 0.3s ease; cursor: default; }
    .level-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    
    .level-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        font-weight: 800;
        font-size: 1rem;
    }
    .level-number-1 { background: rgba(99,102,241,0.15); color: #6366f1; }
    .level-number-2 { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .level-number-3 { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .level-number-4 { background: rgba(34,197,94,0.15); color: #22c55e; }
    .level-number-5 { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
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
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-md { padding: 0.625rem 1.5rem; font-size: 0.875rem; }
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .level-number { width: 2rem; height: 2rem; font-size: 0.75rem; }
        .stat-number { font-size: 1.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .levels-grid { grid-template-columns: 1fr 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .levels-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Commissions par Niveau</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Détail des commissions par niveau Unilevel</p>
        </div>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux commissions
        </a>
    </div>

    <!-- Total -->
    <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500 p-3 sm:p-4">
        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total des commissions par niveau</p>
        <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format($total ?? 0, 2) }}</p>
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Tous niveaux confondus</p>
    </div>

    <!-- Niveaux -->
    <div class="levels-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 animate-fadeInUp delay-2">
        @foreach($levels ?? [] as $level => $data)
            <div class="level-card card text-center p-3 sm:p-4">
                <div class="level-number level-number-{{ $level }} mx-auto mb-2 sm:mb-3">
                    {{ $level }}
                </div>
                <h4 class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">{{ $data['label'] }}</h4>
                <p class="text-lg sm:text-2xl font-bold text-primary-500 mt-1 sm:mt-2">
                    ${{ number_format($data['amount'], 2) }}
                </p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $data['count'] ?? 0 }} membre(s)
                </p>
                <div class="mt-2 sm:mt-3 p-1.5 sm:p-2 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commission</p>
                    <p class="font-bold text-primary-500 text-xs sm:text-sm">{{ $data['percentage'] }}%</p>
                </div>
                <div class="mt-2 sm:mt-3 w-full h-1 bg-[var(--bg-secondary)] rounded-full overflow-hidden">
                    @php 
                        $percent = ($total ?? 1) > 0 ? ($data['amount'] / ($total ?? 1)) * 100 : 0;
                    @endphp
                    <div class="h-full bg-primary-500 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Explication -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Comment fonctionnent les niveaux</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
            <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="level-number level-number-1 text-xs w-7 h-7 sm:w-8 sm:h-8">1</span>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Niveau 1 - Direct</p>
                        <p class="text-[var(--text-secondary)] text-[10px] sm:text-xs">30% des commissions sur les achats de vos filleuls directs</p>
                    </div>
                </div>
            </div>
            <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="level-number level-number-2 text-xs w-7 h-7 sm:w-8 sm:h-8">2</span>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Niveau 2 - Indirect</p>
                        <p class="text-[var(--text-secondary)] text-[10px] sm:text-xs">15% des commissions sur les achats des filleuls de vos filleuls</p>
                    </div>
                </div>
            </div>
            <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="level-number level-number-3 text-xs w-7 h-7 sm:w-8 sm:h-8">3</span>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Niveau 3 - Leadership</p>
                        <p class="text-[var(--text-secondary)] text-[10px] sm:text-xs">10% des commissions sur les achats des niveaux 3</p>
                    </div>
                </div>
            </div>
            <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="level-number level-number-4 text-xs w-7 h-7 sm:w-8 sm:h-8">4-5</span>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Niveaux 4 & 5</p>
                        <p class="text-[var(--text-secondary)] text-[10px] sm:text-xs">5% des commissions sur les achats des niveaux 4 et 5</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-4">
        <a href="{{ route('commissions.export') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter en CSV
        </a>
        <a href="{{ route('commissions.pdf') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
            </svg>
            Exporter en PDF
        </a>
    </div>
</div>
@endsection