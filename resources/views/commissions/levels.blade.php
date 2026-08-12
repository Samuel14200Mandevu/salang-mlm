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
        </div>
    </div>

    <!-- Notice importante -->
    <div class="notice-box animate-fadeInUp delay-1">
        <div class="notice-icon">i</div>
        <div class="notice-content">
            <div class="notice-title">Comment sont calculées les commissions ?</div>
            <div class="notice-text">
                <strong>Sponsor Bonus (Parrainage) :</strong> Niveau 1 = 10$ fixe, Niveau 2-9 = 30% du prix<br>
                <strong>Direct :</strong> Taux du sponsor × PV du package (Niveaux 3-9)<br>
                <strong>Indirect :</strong> Différence de taux × PV du package (Niveaux 3-9)<br>
                <strong>Leadership :</strong> % du PV du package (Niveaux 5-9)
            </div>
        </div>
    </div>

    <!-- Explication des niveaux (cartes cliquables) -->
    <div class="animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Comprendre les niveaux de commission</h3>
        <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-3 sm:mb-4">Cliquez sur une carte pour obtenir une explication détaillée</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3">
            
            <!-- Niveau 1 - Sponsor -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(1)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-1 flex-shrink-0">
                        1
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Sponsor Bonus (Parrainage)</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">Niv 1: 10$, Niv 2-9: 30% du prix</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 2 - Direct -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(2)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-2 flex-shrink-0">
                        2
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Directe</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">Taux sponsor × PV (Niveaux 3-9)</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 3 - Indirect -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(3)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-3 flex-shrink-0">
                        3
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Indirecte</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">Différence de taux × PV (Niveaux 3-9)</p>
                    </div>
                    <svg class="card-arrow w-4 h-4 text-[var(--text-secondary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>

            <!-- Niveau 4 - Leadership -->
            <div class="explanation-card rounded-lg p-3 sm:p-4" onclick="openLevelModal(4)">
                <div class="flex items-center gap-3">
                    <div class="card-icon card-icon-level-4 flex-shrink-0">
                        4
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commission Leadership</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">0.5% à 3.5% × PV (Niveaux 5-9)</p>
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
// Données complètes avec les nouvelles règles
const levelData = {
    1: {
        icon: '1',
        iconClass: 'modal-icon-level-1',
        title: 'Sponsor Bonus (Commission de Parrainage)',
        subtitle: 'Niveau 1: 10$ fixe, Niveau 2-9: 30% du prix du package',
        description: 'Le Sponsor Bonus est la commission que vous recevez lorsque quelqu\'un que vous avez parrainé directement achète un package. C\'est la récompense pour avoir recruté un nouveau membre.',
        details: [
            'Niveau 1: 10$ fixe (indépendant du prix du package)',
            'Niveaux 2-9: 30% du prix du package',
            'Versé directement sur votre portefeuille',
            'Le parrain direct doit être actif'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Tous les niveaux' },
            { label: 'PV mensuel requis', value: 'Selon grade (10 à 300 PV)' },
            { label: 'Compte actif', value: 'Obligatoire' }
        ],
        pvPayment: 'PV mensuel selon grade (Niv 2: 10 PV, Niv 3: 20 PV, Niv 4: 25 PV, Niv 5: 30 PV, Niv 6: 50 PV, Niv 7: 100 PV, Niv 8: 200 PV, Niv 9: 300 PV)',
        table: {
            headers: ['Niveau du parrain', 'Taux', 'Calcul'],
            rows: [
                ['Niveau 1', '10$ fixe', '10$ par activation'],
                ['Niveau 2', '30%', '30% du prix du package'],
                ['Niveau 3', '30%', '30% du prix du package'],
                ['Niveau 4', '30%', '30% du prix du package'],
                ['Niveau 5', '30%', '30% du prix du package'],
                ['Niveau 6', '30%', '30% du prix du package'],
                ['Niveau 7', '30%', '30% du prix du package'],
                ['Niveau 8', '30%', '30% du prix du package'],
                ['Niveau 9', '30%', '30% du prix du package']
            ]
        },
        info: [
            { label: 'Taux min', value: '10$ fixe' },
            { label: 'Taux max', value: '30%' },
            { label: 'Niveau', value: '1 (Parrainage)' }
        ],
        formula: 'Niv 1: 10$ | Niv 2-9: Prix du package × 30%',
        example: {
            scenario: 'Vous êtes Niveau 4. Votre filleul direct achète un package à 350$ (Bronze).',
            calculation: '350$ × 30% = 105$',
            result: 'Vous gagnez 105$ de Sponsor Bonus.'
        },
        summary: 'Le Sponsor Bonus récompense le recrutement direct. Plus vous recrutez, plus vous gagnez.'
    },
    2: {
        icon: '2',
        iconClass: 'modal-icon-level-2',
        title: 'Commission Directe',
        subtitle: 'Taux du sponsor × PV du package (Niveaux 3 à 9)',
        description: 'La commission directe est calculée sur les achats de vos filleuls directs. Vous gagnez un pourcentage du PV du package acheté par votre filleul direct.',
        details: [
            'Calculée sur le PV du package acheté',
            'Taux = votre pourcentage de grade',
            'Niveaux 3 à 9 uniquement',
            'Le sponsor doit être Niveau 3 minimum'
        ],
        conditions: [
            { label: 'Grade du sponsor', value: 'Niveau 3 à 9' },
            { label: 'Grade du filleul', value: 'Niveau 3 à 9' },
            { label: 'PV mensuel sponsor', value: '20 à 300 PV' }
        ],
        pvPayment: 'PV mensuel ≥ 20 à 300 PV (selon grade)',
        table: {
            headers: ['Grade du sponsor', 'Taux', 'Exemple avec 200 PV'],
            rows: [
                ['Niveau 3', '22%', '200 × 22% = 44$'],
                ['Niveau 4', '26%', '200 × 26% = 52$'],
                ['Niveau 5', '30%', '200 × 30% = 60$'],
                ['Niveau 6', '34%', '200 × 34% = 68$'],
                ['Niveau 7', '40%', '200 × 40% = 80$'],
                ['Niveau 8', '43%', '200 × 43% = 86$'],
                ['Niveau 9', '45%', '200 × 45% = 90$']
            ]
        },
        info: [
            { label: 'Taux min', value: '22%' },
            { label: 'Taux max', value: '45%' },
            { label: 'Niveaux', value: '3 à 9' }
        ],
        formula: 'Commission = PV du package × Votre taux (%)',
        example: {
            scenario: 'Vous êtes Niveau 7 (40%). Votre filleul direct achète un package Bronze (200 PV).',
            calculation: '200 PV × 40% = 80$',
            result: 'Vous gagnez 80$ de commission directe.'
        },
        summary: 'Plus votre grade est élevé, plus vous gagnez sur les achats de vos filleuls directs.'
    },
    3: {
    icon: '3',
    iconClass: 'modal-icon-level-3',
    title: 'Commission Indirecte',
    subtitle: 'Différence de taux × PV total (Niveaux 3 à 9)',
    description: 'La commission indirecte est calculée sur la différence entre votre taux et celui de vos downlines. Vous gagnez sur les achats des membres de votre réseau à partir du niveau 3. Le calcul prend en compte le PV total (packages + produits).',
    details: [
        'Calculée sur la différence des taux',
        'Applicable aux niveaux 3 à 9',
        'Plus la différence est grande, plus vous gagnez',
        'Basée sur le PV total (packages + produits)'
    ],
    conditions: [
        { label: 'Grade requis', value: 'Niveau 3 à 9' },
        { label: 'Avoir des downlines', value: 'Obligatoire' }
    ],
    pvPayment: 'PV mensuel ≥ 20 à 300 PV (selon grade)',
    table: {
        headers: ['Votre Grade', 'Taux', 'Taux du niveau inférieur', 'Différence', 'Gain sur 200 PV', 'Gain sur 250 PV'],
        rows: [
            ['Niveau 3', '22%', '-', '-', '-', '-'],
            ['Niveau 4', '26%', '22% (Niv 3)', '4%', '200 × 4% = 8$', '250 × 4% = 10$'],
            ['Niveau 5', '30%', '26% (Niv 4)', '4%', '200 × 4% = 8$', '250 × 4% = 10$'],
            ['Niveau 6', '34%', '30% (Niv 5)', '4%', '200 × 4% = 8$', '250 × 4% = 10$'],
            ['Niveau 7', '40%', '34% (Niv 6)', '6%', '200 × 6% = 12$', '250 × 6% = 15$'],
            ['Niveau 8', '43%', '40% (Niv 7)', '3%', '200 × 3% = 6$', '250 × 3% = 7.50$'],
            ['Niveau 9', '45%', '43% (Niv 8)', '2%', '200 × 2% = 4$', '250 × 2% = 5$']
        ]
    },
    info: [
        { label: 'Taux min', value: '2%' },
        { label: 'Taux max', value: '6%' },
        { label: 'Niveaux', value: '3 à 9' }
    ],
    formula: 'Commission = PV total (packages + produits) × (Votre taux - Taux du niveau inférieur)',
    example: {
        scenario: 'Vous êtes Niveau 9 (45%). Le niveau inférieur est Niveau 8 (43%). Un membre de votre réseau achète un package Bronze (200 PV) et des produits (50 PV), total 250 PV.',
        calculation: '250 PV × (45% - 43%) = 250 × 2% = 5$',
        result: 'Vous gagnez 5$ de commission indirecte.'
    },
    summary: 'La commission indirecte récompense la profondeur de votre réseau, en prenant en compte tous les PV générés (packages + produits).'
},
    4: {
        icon: '4',
        iconClass: 'modal-icon-level-4',
        title: 'Commission Leadership',
        subtitle: '0.5% à 3.5% × PV du package (Niveaux 5 à 9)',
        description: 'La commission de leadership récompense les leaders qui développent des équipes profondes. Elle s\'applique à partir du niveau 5.',
        details: [
            'Applicable du niveau 5 au niveau 9',
            'Taux selon votre grade (0.5% à 3.5%)',
            'Conditions : PV mensuel et PV d\'équipe minimum',
            'Basée sur le PV du package acheté'
        ],
        conditions: [
            { label: 'Grade requis', value: 'Niveau 5 à 9' },
            { label: 'PV mensuel', value: '30 à 300 PV' },
            { label: 'PV d\'équipe', value: '300 à 3000 PV' }
        ],
        pvPayment: 'PV mensuel ≥ 30 à 300 PV (selon grade)',
        table: {
            headers: ['Grade', 'Taux', 'PV Perso/mois', 'PV Équipe/mois', 'Gain sur 200 PV'],
            rows: [
                ['Niveau 5', '0.5%', '30 PV', '300 PV', '200 × 0.5% = 1$'],
                ['Niveau 6', '1.1%', '50 PV', '500 PV', '200 × 1.1% = 2.20$'],
                ['Niveau 7', '1.8%', '100 PV', '1000 PV', '200 × 1.8% = 3.60$'],
                ['Niveau 8', '2.6%', '200 PV', '2000 PV', '200 × 2.6% = 5.20$'],
                ['Niveau 9', '3.5%', '300 PV', '3000 PV', '200 × 3.5% = 7$']
            ]
        },
        info: [
            { label: 'Taux min', value: '0.5%' },
            { label: 'Taux max', value: '3.5%' },
            { label: 'Niveaux', value: '5 à 9' }
        ],
        formula: 'Commission = PV package × Taux de leadership (%)',
        example: {
            scenario: 'Vous êtes Niveau 9 (3.5%). Un membre de votre réseau achète un package Bronze (200 PV).',
            calculation: '200 PV × 3.5% = 7$',
            result: 'Vous gagnez 7$ de commission de leadership.'
        },
        summary: 'La commission de leadership récompense les leaders avec un réseau profond.'
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
                    <div class="notice-title">Basé sur le PV du package</div>
                    <div class="notice-text">
                        Les commissions sont calculées sur le <strong>PV du package acheté</strong>.
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
                    <li>• Les commissions sont calculées sur le <strong>PV du package</strong></li>
                    <li>• Plus vous montez en grade, plus vos taux augmentent</li>
                    <li>• Les conditions de PV mensuel doivent être remplies</li>
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