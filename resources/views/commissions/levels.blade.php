{{-- resources/views/commissions/levels.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .level-card {
        transition: all 0.3s ease;
        cursor: default;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        text-align: center;
    }
    .level-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    
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
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }

    /* Tableau des taux et conditions */
    .rate-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.7rem;
        margin: 0.5rem 0;
    }
    .rate-table thead th {
        background: var(--bg-secondary);
        padding: 0.4rem 0.5rem;
        text-align: left;
        font-size: 0.55rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .rate-table tbody td {
        padding: 0.3rem 0.5rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        font-size: 0.65rem;
        vertical-align: top;
    }
    .rate-table tbody tr:hover {
        background: var(--bg-hover);
    }
    .rate-table .highlight-row {
        background: rgba(90, 182, 56, 0.08);
    }
    .rate-table .highlight-row td {
        font-weight: 600;
    }
    .rate-table .rate-percent {
        font-weight: 700;
        color: var(--primary-500);
    }
    .rate-table .rate-level {
        font-weight: 700;
        color: var(--text-primary);
    }
    .rate-table .pv-required {
        font-weight: 700;
        color: #f59e0b;
    }
    .rate-table .table-scroll {
        max-height: 300px;
        overflow-y: auto;
    }
    .rate-table .condition-list {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.6rem;
    }
    .rate-table .condition-list li {
        padding: 0.1rem 0;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: flex-start;
        gap: 0.25rem;
    }
    .rate-table .condition-list li:last-child {
        border-bottom: none;
    }
    .rate-table .condition-list .bullet {
        color: var(--primary-500);
        font-weight: 700;
    }
    .rate-table .option-tag {
        display: inline-block;
        padding: 0.05rem 0.3rem;
        border-radius: 9999px;
        font-size: 0.5rem;
        font-weight: 600;
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
        margin-right: 0.15rem;
    }
    .rate-table .option-tag-success {
        background: rgba(34,197,94,0.12);
        color: #22c55e;
    }
    .rate-table .option-tag-warning {
        background: rgba(245,158,11,0.12);
        color: #f59e0b;
    }
    
    /* Conditions cartes */
    .conditions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }
    .condition-item {
        background: var(--bg-secondary);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--primary-500);
    }
    .condition-item .cond-label {
        font-size: 0.55rem;
        text-transform: uppercase;
        color: var(--text-tertiary);
        letter-spacing: 0.05em;
    }
    .condition-item .cond-value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.7rem;
    }
    
    /* Notice importante */
    .notice-box {
        background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(59,130,246,0.02));
        border: 1px solid rgba(59,130,246,0.2);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        margin: 0.5rem 0;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .notice-box .notice-icon {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: rgba(59,130,246,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .notice-box .notice-content {
        flex: 1;
    }
    .notice-box .notice-content .notice-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.8rem;
        margin-bottom: 0.15rem;
    }
    .notice-box .notice-content .notice-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }
    .notice-box .notice-content .notice-text strong {
        color: var(--primary-500);
    }
    
    /* Carte d'explication cliquable */
    .explanation-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid var(--border-color);
        background: var(--bg-card);
    }
    .explanation-card:hover {
        transform: translateY(-4px);
        border-color: var(--primary-500);
        box-shadow: var(--shadow-hover);
    }
    .explanation-card .card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 1rem;
    }
    .explanation-card .card-icon-level-1 { background: rgba(99,102,241,0.15); color: #6366f1; }
    .explanation-card .card-icon-level-2 { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .explanation-card .card-icon-level-3 { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .explanation-card .card-icon-level-4 { background: rgba(34,197,94,0.15); color: #22c55e; }
    .explanation-card .card-icon-level-5 { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    .explanation-card .card-arrow {
        transition: all 0.3s ease;
    }
    .explanation-card:hover .card-arrow {
        transform: translateX(4px);
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        max-width: 900px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9) translateY(20px);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
    }
    .modal-overlay.active .modal-box {
        transform: scale(1) translateY(0);
    }
    .modal-box .modal-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: var(--bg-secondary);
        border: none;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text-secondary);
    }
    .modal-box .modal-close:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        transform: rotate(90deg);
    }
    .modal-box .modal-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.5rem;
        font-weight: 700;
    }
    .modal-box .modal-icon-level-1 { background: rgba(99,102,241,0.15); color: #6366f1; }
    .modal-box .modal-icon-level-2 { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .modal-box .modal-icon-level-3 { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .modal-box .modal-icon-level-4 { background: rgba(34,197,94,0.15); color: #22c55e; }
    .modal-box .modal-icon-level-5 { background: rgba(236,72,153,0.15); color: #ec4899; }
    .modal-box .modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 0.25rem;
    }
    .modal-box .modal-subtitle {
        font-size: 0.8rem;
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: 1rem;
    }
    .modal-box .modal-body {
        color: var(--text-secondary);
        font-size: 0.85rem;
        line-height: 1.6;
    }
    .modal-box .modal-body ul {
        list-style: none;
        padding: 0;
        margin: 0.4rem 0;
    }
    .modal-box .modal-body ul li {
        padding: 0.3rem 0;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .modal-box .modal-body ul li:last-child {
        border-bottom: none;
    }
    .modal-box .modal-body ul li .check {
        color: #22c55e;
        font-weight: 700;
    }
    .modal-box .modal-body .highlight {
        color: var(--primary-500);
        font-weight: 600;
    }
    .modal-box .modal-body .formula {
        background: var(--bg-secondary);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        font-family: monospace;
        font-size: 0.75rem;
        text-align: center;
        margin: 0.4rem 0;
        border: 1px solid var(--border-color);
    }
    .modal-box .modal-body .example-box {
        background: rgba(90, 182, 56, 0.06);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        border-left: 3px solid var(--primary-500);
        margin: 0.4rem 0;
    }
    .modal-box .modal-body .example-box .label {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .modal-box .modal-body .example-box .calculation {
        font-family: monospace;
        font-size: 0.75rem;
        margin-top: 0.15rem;
        color: var(--text-primary);
    }
    .modal-box .modal-body .example-box .result {
        font-weight: 700;
        color: var(--primary-500);
        font-size: 0.85rem;
        margin-top: 0.15rem;
    }
    .modal-box .modal-body .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.4rem;
        margin: 0.4rem 0;
    }
    .modal-box .modal-body .info-grid .info-item {
        background: var(--bg-secondary);
        padding: 0.4rem 0.6rem;
        border-radius: var(--radius-sm);
        text-align: center;
    }
    .modal-box .modal-body .info-grid .info-item .info-label {
        font-size: 0.55rem;
        text-transform: uppercase;
        color: var(--text-tertiary);
        letter-spacing: 0.05em;
    }
    .modal-box .modal-body .info-grid .info-item .info-value {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.85rem;
    }
    .modal-box .modal-body .info-grid .info-item .info-value.primary {
        color: var(--primary-500);
    }
    .modal-box .modal-footer {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .modal-box .summary-box {
        background: linear-gradient(135deg, rgba(90, 182, 56, 0.05), rgba(90, 182, 56, 0.02));
        border: 1px solid rgba(90, 182, 56, 0.2);
        border-radius: var(--radius-md);
        padding: 0.5rem 0.75rem;
        margin: 0.4rem 0;
    }
    .modal-box .summary-box .summary-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.75rem;
        margin-bottom: 0.15rem;
    }
    .modal-box .summary-box .summary-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .modal-box .pv-payment-required {
        background: rgba(245,158,11,0.08);
        border: 1px solid rgba(245,158,11,0.2);
        border-radius: var(--radius-md);
        padding: 0.5rem 0.75rem;
        margin: 0.4rem 0;
    }
    .modal-box .pv-payment-required .pv-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.7rem;
    }
    .modal-box .pv-payment-required .pv-value {
        font-weight: 700;
        color: #f59e0b;
        font-size: 0.85rem;
    }
    
    .conditions-section {
        margin: 0.4rem 0;
    }
    .conditions-section .section-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.75rem;
        margin-bottom: 0.35rem;
    }
    
    .progress-bar {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.25rem;
    }
    .progress-bar .fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.8s ease;
    }
    
    @media (max-width: 640px) {
        .explanation-card {
            padding: 0.75rem;
        }
        .explanation-card .card-icon {
            width: 2.25rem;
            height: 2.25rem;
            font-size: 0.65rem;
        }
        .modal-box {
            padding: 1rem;
            max-width: 98%;
        }
        .modal-box .modal-title {
            font-size: 0.95rem;
        }
        .modal-box .modal-body {
            font-size: 0.78rem;
        }
        .modal-box .modal-body .info-grid {
            grid-template-columns: 1fr;
        }
        .modal-box .modal-footer {
            flex-direction: column;
            align-items: center;
        }
        .rate-table {
            font-size: 0.5rem;
        }
        .rate-table thead th, .rate-table tbody td {
            padding: 0.1rem 0.2rem;
        }
        .rate-table .condition-list {
            font-size: 0.5rem;
        }
        .conditions-grid {
            grid-template-columns: 1fr;
        }
        .notice-box {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .levels-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .levels-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Commissions par niveau</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Détail des commissions par niveau</p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <a href="{{ route('commissions.dashboard') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Tableau de bord
            </a>
            <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Toutes les commissions
            </a>
            <a href="{{ route('commissions.export') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Notice importante -->
    <div class="notice-box animate-fadeInUp delay-1">
        <div class="notice-icon">i</div>
        <div class="notice-content">
            <div class="notice-title">Comment sont calculées les commissions ?</div>
            <div class="notice-text">
                Les commissions sont calculées sur le <strong>PV vendu du mois (PV mensuel)</strong>, 
                et non sur le PV total cumulé depuis l'inscription. 
                Chaque mois, les ventes du mois sont prises en compte pour le calcul des commissions.
            </div>
        </div>
    </div>

    <!-- Total -->
    <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total des commissions par niveau</p>
        <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format($total ?? 0, 2) }}</p>
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Tous niveaux confondus</p>
    </div>

    <!-- Niveaux -->
    <div class="levels-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 animate-fadeInUp delay-2">
        @foreach($levels ?? [] as $level => $data)
            @php
                $percent = ($total ?? 1) > 0 ? ($data['amount'] / ($total ?? 1)) * 100 : 0;
                $colors = ['', 'level-number-1', 'level-number-2', 'level-number-3', 'level-number-4', 'level-number-5'];
                $color = $colors[$level] ?? 'level-number-1';
                $colorMap = [
                    'primary' => '#6366f1',
                    'success' => '#22c55e',
                    'warning' => '#f59e0b',
                    'danger' => '#ef4444',
                    'purple' => '#8b5cf6',
                    'info' => '#3b82f6'
                ];
                $barColor = $colorMap[$data['color'] ?? 'primary'] ?? '#6366f1';
            @endphp
            <div class="level-card">
                <div class="level-number {{ $color }} mx-auto mb-2 sm:mb-3">
                    {{ $level }}
                </div>
                <h4 class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">{{ $data['label'] }}</h4>
                <p class="text-lg sm:text-2xl font-bold text-primary-500 mt-1 sm:mt-2">
                    ${{ number_format($data['amount'], 2) }}
                </p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $data['count'] ?? 0 }} commission(s)
                </p>
                <div class="mt-2 sm:mt-3 p-1.5 sm:p-2 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Taux</p>
                    <p class="font-bold text-primary-500 text-xs sm:text-sm">{{ $data['percentage'] }}%</p>
                </div>
                <div class="mt-2 sm:mt-3 w-full h-1 bg-[var(--bg-secondary)] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%; background: {{ $barColor }};"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Explication des niveaux (cartes cliquables) -->
    <div class="animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Comprendre les niveaux de commission</h3>
        <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-3 sm:mb-4">Cliquez sur une carte pour obtenir une explication détaillée</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3">
            
            <!-- Niveau 1 - Direct -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(1)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-1 flex-shrink-0">
                        1
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Directe</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">6% à 45% sur vos filleuls directs</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 2 - Indirect -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(2)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-2 flex-shrink-0">
                        2
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Indirecte</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">2% à 39% sur les niveaux inférieurs</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 3 - Leadership -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(3)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-3 flex-shrink-0">
                        3
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Leadership</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">0.5% à 3.5% sur 2 à 9 générations</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 4 - Bonus Consommateur -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(4)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-4 flex-shrink-0">
                        4
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Bonus Consommateur</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">6% sur vos achats personnels</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-4">
        <a href="{{ route('commissions.dashboard') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Tableau de bord
        </a>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Toutes les commissions
        </a>
    </div>
</div>

<!-- Modal d'explication détaillée -->
<div id="levelModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeLevelModal()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <div id="modalContent">
            <!-- Le contenu est injecté par JavaScript -->
        </div>
    </div>
</div>

@push('scripts')
<script>
// Données complètes avec conditions précises
const levelData = {
    1: {
        icon: '1',
        iconClass: 'modal-icon-level-1',
        title: 'Commission Directe - Niveau 1',
        subtitle: '6% à 45% sur les achats mensuels de vos filleuls directs',
        description: 'La commission directe est la commission principale. Vous gagnez un pourcentage sur chaque achat effectué par vos filleuls directs (ceux que vous avez parrainés personnellement). Le calcul est basé sur le PV vendu dans le mois en cours.',
        details: [
            'Applicable à tous les filleuls directs (niveau 1)',
            'Le taux dépend de votre grade actuel',
            'Plus vous montez en grade, plus le taux est élevé',
            'Commission versée mensuellement sur le PV du mois'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Niveau 2 à 9' },
            { label: 'PV mensuel requis', value: '100 à 400 000 PV/mois' },
            { label: 'Achat mensuel', value: 'Obligatoire pour maintenir le grade' }
        ],
        pvPayment: 'PV mensuel ≥ 20 à 300 PV (selon grade)',
        table: {
            headers: ['Grade', 'Niveau', 'Taux', 'PV Mensuel', 'PV Groupe', 'PV pour toucher'],
            rows: [
                ['Qualification', '2', '6%', '100 PV', '-', '≥20 PV'],
                ['Cumul Directeur', '3', '22%', '200 PV', '-', '≥20 PV'],
                ['Directeur', '4', '26%', '1000 PV', 'Option: 1000 PV', '≥25 PV'],
                ['Manager Senior', '5', '30%', '3800 PV', '3800 PV', '≥30 PV'],
                ['Directeur Envolée', '6', '34%', '16000 PV', '16000 PV', '≥50 PV'],
                ['Saphire Manager', '7', '40%', '73000 PV', '73000 PV', '≥100 PV'],
                ['Diamant Bleu', '8', '43%', '280000 PV', '280000 PV', '≥200 PV'],
                ['Perle Diamant', '9', '45%', '400000 PV', '400000 PV', '≥300 PV']
            ]
        },
        info: [
            { label: 'Taux min', value: '6%' },
            { label: 'Taux max', value: '45%' },
            { label: 'Niveau', value: '1 (Direct)' }
        ],
        formula: 'Commission = PV mensuel du filleul × Votre taux (%)',
        example: {
            scenario: 'Vous êtes Grade 7 (40%). Votre filleul direct a vendu 1000 PV ce mois-ci.',
            calculation: '1000 PV × 40% = 400$',
            result: 'Vous gagnez 400$ sur les ventes de ce mois.'
        },
        summary: 'Plus votre grade est élevé, plus vous gagnez sur les ventes mensuelles de vos filleuls directs.'
    },
    2: {
        icon: '2',
        iconClass: 'modal-icon-level-2',
        title: 'Commission Indirecte - Niveau 2',
        subtitle: '2% à 39% sur les ventes mensuelles des niveaux inférieurs',
        description: 'La commission indirecte est calculée sur la différence entre votre taux et celui de vos downlines. Vous gagnez sur les ventes mensuelles des membres de votre réseau à partir du niveau 2.',
        details: [
            'Applicable à tous les niveaux inférieurs (2 à 9)',
            'Calculée sur la différence des pourcentages',
            'Plus la différence est grande, plus vous gagnez',
            'Basée sur le PV vendu dans le mois en cours'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Niveau 3 à 9' },
            { label: 'PV mensuel min', value: '10 PV/mois' },
            { label: 'Avoir des filleuls', value: 'Obligatoire' }
        ],
        pvPayment: 'PV mensuel ≥ 10 PV',
        table: {
            headers: ['Votre Grade', 'Votre Taux', 'Taux Filleul', 'Différence', 'Gain /1000 PV'],
            rows: [
                ['Grade 3', '22%', '6% (Niv 2)', '16%', '160$'],
                ['Grade 4', '26%', '6% (Niv 2)', '20%', '200$'],
                ['Grade 5', '30%', '22% (Niv 3)', '8%', '80$'],
                ['Grade 6', '34%', '22% (Niv 3)', '12%', '120$'],
                ['Grade 7', '40%', '26% (Niv 4)', '14%', '140$'],
                ['Grade 8', '43%', '30% (Niv 5)', '13%', '130$'],
                ['Grade 9', '45%', '22% (Niv 3)', '23%', '230$']
            ]
        },
        info: [
            { label: 'Taux min', value: '2%' },
            { label: 'Taux max', value: '39%' },
            { label: 'Niveaux', value: '2 à 9' }
        ],
        formula: 'Commission = PV mensuel × (Votre taux - Taux du parrain direct)',
        example: {
            scenario: 'Vous êtes Grade 8 (43%). Votre filleul A est Grade 4 (26%). A1 a vendu 1000 PV ce mois.',
            calculation: '1000 PV × (43% - 26%) = 170$',
            result: 'Vous gagnez 170$ sur les ventes de ce mois.'
        },
        summary: 'La commission indirecte récompense la profondeur de votre réseau.'
    },
    3: {
        icon: '3',
        iconClass: 'modal-icon-level-3',
        title: 'Commission Leadership - Niveaux 3 à 9',
        subtitle: '0.5% à 3.5% sur les ventes mensuelles de 2 à 9 générations',
        description: 'La commission de leadership récompense les leaders qui développent des équipes profondes. Elle s\'applique à partir du niveau 3 et peut toucher jusqu\'à 9 générations selon votre grade.',
        details: [
            'Applicable du niveau 3 au niveau 9',
            'Taux selon votre grade (0.5% à 3.5%)',
            'Nombre de générations variable (2 à 9)',
            'Conditions : PV mensuel et PV d\'équipe minimum',
            'Versée mensuellement sur les ventes du mois'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Niveau 5 à 9' },
            { label: 'PV mensuel', value: '30 à 300 PV/mois' },
            { label: 'PV d\'équipe mensuel', value: '500 à 5000 PV/mois' }
        ],
        pvPayment: 'PV mensuel ≥ 30 à 300 PV (selon grade)',
        table: {
            headers: ['Grade', 'Taux', 'Générations', 'PV Perso/mois', 'PV Équipe/mois', 'PV pour toucher'],
            rows: [
                ['Niveau 5 - Manager Senior', '0.5%', '2', '30 PV', '500 PV', '≥30 PV'],
                ['Niveau 6 - Directeur Envolée', '1.1%', '4', '50 PV', '1000 PV', '≥50 PV'],
                ['Niveau 7 - Saphire Manager', '1.8%', '6', '100 PV', '2000 PV', '≥100 PV'],
                ['Niveau 8 - Diamant Bleu', '2.6%', '8', '180 PV', '3000 PV', '≥200 PV'],
                ['Niveau 9 - Perle Diamant', '3.5%', '9', '300 PV', '5000 PV', '≥300 PV']
            ]
        },
        info: [
            { label: 'Taux min', value: '0.5%' },
            { label: 'Taux max', value: '3.5%' },
            { label: 'Générations', value: '2 à 9' }
        ],
        formula: 'Commission = PV mensuel du membre × Taux de leadership (%)',
        example: {
            scenario: 'Vous êtes Grade 9 (3.5%). Un membre de niveau 5 a vendu 1000 PV ce mois.',
            calculation: '1000 PV × 3.5% = 35$',
            result: 'Vous gagnez 35$ sur les ventes de ce mois. Avec 50 membres actifs, cela fait 1750$/mois.'
        },
        summary: 'La commission de leadership récompense les leaders avec un réseau profond.'
    },
    4: {
        icon: '4',
        iconClass: 'modal-icon-level-4',
        title: 'Bonus Consommateur',
        subtitle: '6% sur vos achats personnels mensuels',
        description: 'Le bonus consommateur est une récompense pour vos achats personnels. Chaque mois, lorsque vous achetez des produits, vous recevez 6% de cashback sur vos achats.',
        details: [
            'Applicable à tous les distributeurs',
            'Taux fixe de 6% sur vos achats personnels',
            'Calculé sur le PV de vos achats du mois',
            'Versé mensuellement sur votre portefeuille',
            'Obligatoire pour maintenir votre grade actif'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Niveau 1 à 9' },
            { label: 'PV mensuel', value: '100 PV/mois minimum' },
            { label: 'Achat personnel', value: 'Obligatoire chaque mois' }
        ],
        pvPayment: 'PV mensuel ≥ 100 PV',
        table: {
            headers: ['Grade', 'Taux', 'PV Mensuel', 'Gain /100 PV'],
            rows: [
                ['Tous les grades', '6%', '100 PV/mois', '6$'],
                ['Tous les grades', '6%', '500 PV/mois', '30$'],
                ['Tous les grades', '6%', '1000 PV/mois', '60$'],
                ['Tous les grades', '6%', '5000 PV/mois', '300$']
            ]
        },
        info: [
            { label: 'Taux', value: '6%' },
            { label: 'PV minimum', value: '100 PV/mois' },
            { label: 'Versement', value: 'Mensuel' }
        ],
        formula: 'Bonus = PV mensuel personnel × 6%',
        example: {
            scenario: 'Vous avez acheté pour 500 PV de produits ce mois-ci.',
            calculation: '500 PV × 6% = 30$',
            result: 'Vous recevez 30$ de cashback sur vos achats personnels.'
        },
        summary: 'Le bonus consommateur vous permet de gagner 6% de cashback sur tous vos achats personnels.'
    }
};

// Fonctions du modal
function openLevelModal(level) {
    const data = levelData[level];
    if (!data) return;
    
    const modal = document.getElementById('levelModal');
    const content = document.getElementById('modalContent');
    
    let conditionsHtml = '';
    if (data.conditions) {
        conditionsHtml = `
            <div class="conditions-section">
                <div class="section-title">Conditions requises (par mois)</div>
                <div class="conditions-grid">
                    ${data.conditions.map(cond => `
                        <div class="condition-item">
                            <div class="cond-label">${cond.label}</div>
                            <div class="cond-value">${cond.value}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    let tableHtml = '';
    if (data.table) {
        tableHtml = `
            <p class="font-semibold text-[var(--text-primary)] mt-2 mb-1">Tableau des conditions :</p>
            <div class="table-scroll">
                <table class="rate-table">
                    <thead>
                        <tr>
                            ${data.table.headers.map(h => `<th>${h}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${data.table.rows.map(row => `
                            <tr>
                                ${row.map(cell => `<td>${cell}</td>`).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    let pvPaymentHtml = '';
    if (data.pvPayment) {
        pvPaymentHtml = `
            <div class="pv-payment-required">
                <div class="pv-title">PV mensuel requis pour toucher la commission</div>
                <div class="pv-value">${data.pvPayment}</div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <div class="modal-icon ${data.iconClass}">
            ${data.icon}
        </div>
        <h2 class="modal-title">${data.title}</h2>
        <p class="modal-subtitle">${data.subtitle}</p>
        
        <div class="modal-body">
            <p>${data.description}</p>
            
            <div class="notice-box" style="margin:0.5rem 0; background:rgba(245,158,11,0.06); border-color:rgba(245,158,11,0.2);">
                <div class="notice-icon" style="background:rgba(245,158,11,0.15); color:#f59e0b;">!</div>
                <div class="notice-content">
                    <div class="notice-title">Basé sur le PV mensuel</div>
                    <div class="notice-text">
                        Les commissions sont calculées sur le <strong>PV vendu dans le mois en cours</strong>, 
                        pas sur le PV total cumulé.
                    </div>
                </div>
            </div>
            
            ${pvPaymentHtml}
            
            <ul>
                ${data.details.map(detail => `
                    <li><span class="check">✓</span> ${detail}</li>
                `).join('')}
            </ul>
            
            ${conditionsHtml}
            ${tableHtml}
            
            <div class="info-grid">
                ${data.info.map(item => `
                    <div class="info-item">
                        <div class="info-label">${item.label}</div>
                        <div class="info-value primary">${item.value}</div>
                    </div>
                `).join('')}
            </div>
            
            <p class="font-semibold text-[var(--text-primary)] mt-2 mb-1">Formule :</p>
            <div class="formula">${data.formula}</div>
            
            <div class="example-box">
                <div class="label">Exemple</div>
                <p class="text-[var(--text-secondary)] text-sm">${data.example.scenario}</p>
                <div class="calculation">${data.example.calculation}</div>
                <div class="result">${data.example.result}</div>
            </div>
            
            <div class="summary-box">
                <div class="summary-title">Résumé</div>
                <div class="summary-text">${data.summary}</div>
            </div>
            
            <div class="mt-2 p-2 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-[10px] text-[var(--text-tertiary)] uppercase font-semibold">À retenir</p>
                <ul class="text-xs text-[var(--text-secondary)] space-y-1 mt-1">
                    <li>• Les commissions sont calculées sur le <strong>PV du mois</strong></li>
                    <li>• Plus vous montez en grade, plus vos taux augmentent</li>
                    <li>• Les PV doivent être atteints <strong>chaque mois</strong></li>
                    <li>• Les commissions sont versées mensuellement</li>
                </ul>
            </div>
        </div>
        
        <div class="modal-footer">
            <button onclick="closeLevelModal()" class="btn btn-primary btn-sm">Compris</button>
            <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm">Voir mes commissions</a>
            <a href="{{ route('rank.index') }}" class="btn btn-outline btn-sm">Ma progression</a>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLevelModal() {
    const modal = document.getElementById('levelModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('levelModal').addEventListener('click', function(e) {
    if (e.target === this) closeLevelModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLevelModal();
});
</script>
@endpush
@endsection