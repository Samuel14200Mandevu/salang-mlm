{{-- resources/views/admin/pv/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --bg-page: #F4F5F7;
    --bg-card: #FFFFFF;
    --bg-input: #F8F9FA;
    --bg-hover: #EEF0F2;
    --bg-secondary: #F4F5F7;
    
    --text-primary: #1A1D23;
    --text-secondary: #5A626A;
    --text-muted: #8E959C;
    
    --border-color: #DDE0E3;
    --border-light: #E8EBEE;
    --border-focus: rgba(26, 29, 35, 0.12);
    
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
    
    --primary: #1A1D23;
    --primary-hover: #2D333B;
    --primary-light: #E8EAEC;
    
    --success: #1C7E4A;
    --success-hover: #14633A;
    --success-light: #ECFDF3;
    
    --danger: #B91C1C;
    --danger-light: #FEF3F2;
    
    --warning: #B54708;
    --warning-light: #FFFAEB;
    
    --info: #065F9C;
    --info-light: #EFF8FF;
    
    --radius: 8px;
    --radius-lg: 12px;
    --radius-full: 9999px;
    
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

* {
    font-family: var(--font-family);
}

body {
    background: var(--bg-page);
    color: var(--text-primary);
}

.page-header {
    padding: 1.5rem 0 1rem 0;
    border-bottom: 1px solid var(--border-color);
}

.page-header .top-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.page-title {
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    margin: 0;
}

.page-subtitle {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.125rem;
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
}

.card-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.form-label .required {
    color: var(--danger);
    margin-left: 0.125rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px var(--border-focus);
}

.form-control[readonly] {
    background: var(--bg-secondary);
    cursor: default;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius);
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
}

.btn:active {
    transform: scale(0.97);
}

.btn-primary {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-primary:hover {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}

.btn-success {
    background: var(--success);
    color: white;
    border-color: var(--success);
}

.btn-success:hover {
    background: var(--success-hover);
    border-color: var(--success-hover);
}

.btn-danger {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}

.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

.btn .icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: var(--radius-full);
    font-size: 0.688rem;
    font-weight: 500;
    border: 1px solid transparent;
}

.badge-success {
    background: var(--success-light);
    color: var(--success);
    border-color: var(--success-light);
}

.badge-danger {
    background: var(--danger-light);
    color: var(--danger);
    border-color: var(--danger-light);
}

.badge-warning {
    background: var(--warning-light);
    color: var(--warning);
    border-color: var(--warning-light);
}

.badge-info {
    background: var(--info-light);
    color: var(--info);
    border-color: var(--info-light);
}

.badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}

.badge-sm {
    padding: 0.1rem 0.4rem;
    font-size: 0.625rem;
}

/* User profile */
.user-profile {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    flex-wrap: wrap;
}

.user-avatar {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    background: var(--primary);
    color: white;
    flex-shrink: 0;
}

.user-info .name {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.user-info .meta {
    font-size: 0.813rem;
    color: var(--text-secondary);
}

.user-info .meta .highlight {
    color: var(--primary);
}

.user-meta-right {
    margin-left: auto;
    text-align: right;
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.user-meta-right .value {
    font-weight: 500;
    color: var(--text-primary);
}

/* PV stats */
.pv-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
}

.pv-stat .label {
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
}

.pv-stat .value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 0.125rem;
}

.pv-stat .sub {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.pv-stat .value-success { color: var(--success); }
.pv-stat .value-info { color: var(--info); }

/* Team PV origin */
.team-origin {
    background: var(--bg-secondary);
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
    border: 1px solid var(--border-color);
}

.team-origin .title {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.team-origin .title .icon {
    width: 16px;
    height: 16px;
}

.team-origin .item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.375rem 0;
    border-bottom: 1px solid var(--border-light);
    font-size: 0.813rem;
}

.team-origin .item:last-child {
    border-bottom: none;
}

.team-origin .item .level {
    font-size: 0.625rem;
    font-weight: 600;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
    min-width: 44px;
    text-align: center;
    flex-shrink: 0;
}

.level-1 { background: #E8EAEC; color: var(--text-secondary); }
.level-2 { background: #EFF8FF; color: var(--info); }
.level-3 { background: var(--success-light); color: var(--success); }
.level-4 { background: var(--warning-light); color: var(--warning); }
.level-5 { background: var(--danger-light); color: var(--danger); }

.team-origin .item .name {
    flex: 1;
    color: var(--text-primary);
}

.team-origin .item .name a {
    color: var(--text-primary);
    text-decoration: none;
}

.team-origin .item .name a:hover {
    text-decoration: underline;
    color: var(--primary);
}

.team-origin .item .pv {
    font-weight: 600;
    color: var(--text-primary);
}

.team-origin .total {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    font-size: 0.875rem;
}

.team-origin .total .label {
    color: var(--text-secondary);
}

.team-origin .total .value {
    color: var(--text-primary);
}

.team-origin .empty {
    color: var(--text-muted);
    font-size: 0.875rem;
    text-align: center;
    padding: 0.5rem 0;
}

/* Table */
.table-wrap {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.table th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
    background: var(--bg-secondary);
}

.table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
}

.table tr:hover td {
    background: var(--bg-hover);
}

/* Toast */
.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 380px;
    width: 100%;
}

.toast {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    animation: slideIn 0.3s ease forwards;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.toast-success { border-left: 3px solid var(--success); }
.toast-error { border-left: 3px solid var(--danger); }
.toast-warning { border-left: 3px solid var(--warning); }

@keyframes slideIn {
    from { opacity: 0; transform: translateX(16px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(16px); }
}

/* Confirm overlay */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
}

.confirm-overlay.active {
    display: flex;
}

.confirm-box {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    max-width: 440px;
    width: 95%;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
}

.confirm-box .icon {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box p {
    color: var(--text-secondary);
    font-size: 0.938rem;
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box .warning {
    margin: 1rem 0 1.5rem 0;
    padding: 0.625rem 0.875rem;
    background: var(--danger-light);
    border-radius: var(--radius);
    border-left: 3px solid var(--danger);
    font-size: 0.813rem;
    color: var(--danger);
}

.confirm-box .actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.confirm-box .actions .btn {
    min-width: 100px;
}

/* Alert */
.alert {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: var(--success-light);
    border-color: #A6F4C5;
    color: var(--success);
}

.alert-error {
    background: var(--danger-light);
    border-color: #FECDCA;
    color: var(--danger);
}

/* Footer */
.footer-links {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.footer-links a {
    color: var(--text-secondary);
    text-decoration: none;
}

.footer-links a:hover {
    color: var(--text-primary);
    text-decoration: underline;
}

/* Utilities */
.flex { display: flex; }
.flex-wrap { flex-wrap: wrap; }
.items-center { align-items: center; }
.items-end { align-items: flex-end; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.flex-1 { flex: 1; }
.flex-shrink-0 { flex-shrink: 0; }

.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }

.text-center { text-align: center; }
.text-right { text-align: right; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: var(--text-muted); }
.text-secondary { color: var(--text-secondary); }
.text-success { color: var(--success); }
.text-danger { color: var(--danger); }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }

.hidden { display: none !important; }
.overflow-x-auto { overflow-x: auto; }
.w-full { width: 100%; }

.animate-fadeIn { animation: fadeIn 0.3s ease forwards; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }

@media (max-width: 640px) {
    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .user-profile {
        flex-direction: column;
        text-align: center;
    }
    .user-meta-right {
        margin-left: 0;
        text-align: center;
    }
    .user-avatar {
        width: 48px;
        height: 48px;
        font-size: 1.125rem;
    }
    .pv-stat .value {
        font-size: 1.25rem;
    }
    .btn {
        width: 100%;
    }
    .card {
        padding: 1rem;
    }
    .page-header .top-row {
        flex-direction: column;
        align-items: stretch;
    }
    .header-actions {
        justify-content: stretch;
    }
    .header-actions .btn {
        flex: 1;
    }
    .page-title {
        font-size: 1.125rem;
    }
    .confirm-box .actions {
        flex-direction: column;
    }
    .confirm-box .actions .btn {
        width: 100%;
    }
    .table {
        font-size: 0.75rem;
    }
    .table th,
    .table td {
        padding: 0.375rem 0.5rem;
    }
    .toast-container {
        top: 0.5rem;
        right: 0.5rem;
        left: 0.5rem;
        max-width: none;
    }
    .team-origin .item {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    .team-origin .item .name {
        flex: 1 1 100%;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .grid-3 {
        grid-template-columns: 1fr 1fr;
    }
}

/* Dark theme support */
.dark {
    --bg-page: #111827;
    --bg-card: #1A1D23;
    --bg-input: #1F2937;
    --bg-hover: #2D333B;
    --bg-secondary: #1F2937;
    
    --text-primary: #F3F4F6;
    --text-secondary: #9CA3AF;
    --text-muted: #6B7280;
    
    --border-color: #374151;
    --border-light: #2D333B;
    --border-focus: rgba(255, 255, 255, 0.12);
    
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.4);
    
    --primary: #E5E7EB;
    --primary-hover: #D1D5DB;
    --primary-light: #374151;
    
    --success: #34D399;
    --success-light: rgba(52, 211, 153, 0.12);
    
    --danger: #F87171;
    --danger-light: rgba(248, 113, 113, 0.12);
    
    --warning: #FBBF24;
    --warning-light: rgba(251, 191, 36, 0.12);
    
    --info: #60A5FA;
    --info-light: rgba(96, 165, 250, 0.12);
}

.dark .user-avatar {
    background: var(--primary);
    color: var(--bg-card);
}

.dark .badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
}

.dark .level-1 { background: #374151; color: var(--text-secondary); }
.dark .level-2 { background: rgba(96, 165, 250, 0.15); color: #60A5FA; }
.dark .level-3 { background: rgba(52, 211, 153, 0.15); color: #34D399; }
.dark .level-4 { background: rgba(251, 191, 36, 0.15); color: #FBBF24; }
.dark .level-5 { background: rgba(248, 113, 113, 0.15); color: #F87171; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="top-row">
        <div>
            <h1 class="page-title">Gestion des PV</h1>
            <p class="page-subtitle">{{ $user->name }} — Points de Volume</p>
        </div>
        <div class="header-actions">
            <a href="{{ url('/admin/pv/import?user_id=' . $user->id) }}" class="btn btn-success btn-sm">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Importer des PV</span>
            </a>
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div id="toastContainer" class="toast-container"></div>

@if(session('success'))
    <div class="alert alert-success animate-fadeIn">
        <svg class="icon flex-shrink-0" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error animate-fadeIn">
        <svg class="icon flex-shrink-0" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
@endif

<!-- User Profile -->
<div class="user-profile animate-fadeInUp">
    <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
    <div class="user-info flex-1">
        <div class="name">{{ $user->name }}</div>
        <div class="meta">
            {{ $user->email }}
            @if($user->phone)
                • {{ $user->phone }}
            @endif
        </div>
        <div class="flex flex-wrap gap-2 mt-1">
            <span class="badge badge-info">
                {{ $user->rank ?? 'Distributeur' }} (Niv. {{ $user->rank_level ?? 1 }})
            </span>
            <span class="badge badge-neutral">Code: {{ $user->sponsor_id ?? 'N/A' }}</span>
            @if($user->is_active)
                <span class="badge badge-success">Actif</span>
            @else
                <span class="badge badge-danger">Inactif</span>
            @endif
        </div>
    </div>
    <div class="user-meta-right">
        <div>Inscrit le</div>
        <div class="value">{{ $user->created_at->format('d/m/Y') }}</div>
        @if($user->parrain_id)
            <div class="mt-1">Parrain</div>
            <div class="value">
                @php
                    $parrain = App\Models\User::find($user->parrain_id);
                @endphp
                {{ $parrain?->name ?? 'Inconnu' }}
            </div>
        @endif
    </div>
</div> <br>

<!-- PV Stats -->
<div class="grid-3 animate-fadeInUp delay-1">
    <div class="pv-stat">
        <div class="label">PV Total</div>
        <div class="value" id="pv_balance_display">{{ number_format($user->pv_balance ?? 0, 1, ',', ' ') }}</div>
        <div class="sub">Points de Volume totaux</div>
    </div>
    <div class="pv-stat">
        <div class="label">PV Mensuel</div>
        <div class="value value-success" id="monthly_pv_display">{{ number_format($user->monthly_pv ?? 0, 1, ',', ' ') }}</div>
        <div class="sub">Points de Volume du mois</div>
    </div>
    <div class="pv-stat">
        <div class="label">PV Équipe</div>
        <div class="value value-info" id="team_pv_display">{{ number_format($user->team_pv ?? 0, 1, ',', ' ') }}</div>
        <div class="sub">PV générés par les filleuls</div>
    </div>
</div>
<br>
<!-- Edit Form + Team PV Origin -->
<div class="grid-2 animate-fadeInUp delay-2">
    <!-- Edit Form -->
    <div class="card">
        <div class="card-title">Modifier les PV</div>
        <form method="POST" action="{{ route('admin.pv.update', $user->id) }}" id="pvForm">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">PV Total <span class="required">*</span></label>
                <input type="text" name="pv_balance" class="form-control" 
                       value="{{ number_format($user->pv_balance ?? 0, 1, '.', '') }}" 
                       step="0.1" required>
                <span class="form-help">Points de Volume totaux</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">PV Mensuel <span class="required">*</span></label>
                <input type="text" name="monthly_pv" class="form-control" 
                       value="{{ number_format($user->monthly_pv ?? 0, 1, '.', '') }}" 
                       step="0.1" required>
                <span class="form-help">Points de Volume du mois en cours</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Package</label>
                <select name="package_id" class="form-control">
                    <option value="">Aucun package</option>
                    @foreach($packages ?? [] as $package)
                        <option value="{{ $package->id }}" {{ $user->package_id == $package->id ? 'selected' : '' }}>
                            {{ $package->name }} ({{ number_format($package->pv_value, 1, ',', ' ') }} PV)
                        </option>
                    @endforeach
                </select>
                <span class="form-help">Le package affecte le PV et le grade</span>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-4">
                <button type="submit" class="btn btn-primary flex-1">
                    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <button type="button" onclick="openResetModal()" class="btn btn-danger">
                    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </form>
    </div>

    <!-- Team PV Origin -->
    <div class="card">
        <div class="card-title">
            <svg class="icon inline-block" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Provenance du PV Équipe
        </div>
        <p class="text-sm text-secondary mb-3">
            Membres de l'équipe qui contribuent au PV d'équipe de <strong>{{ $user->name }}</strong>
        </p>
        
        @php
            $totalTeamPV = 0;
            $teamMembers = [];
            
            function getAllDescendantsWithPV($userId, $level = 1, &$members = []) {
                $children = App\Models\User::where('parrain_id', $userId)
                    ->where('is_active', true)
                    ->get();
                
                foreach ($children as $child) {
                    $childPV = $child->pv_balance ?? 0;
                    
                    if ($childPV > 0) {
                        $members[] = [
                            'id' => $child->id,
                            'name' => $child->name,
                            'pv' => $childPV,
                            'level' => $level,
                        ];
                    }
                    
                    getAllDescendantsWithPV($child->id, $level + 1, $members);
                }
                return $members;
            }
            
            $teamMembers = getAllDescendantsWithPV($user->id, 1);
            $totalTeamPV = array_sum(array_column($teamMembers, 'pv'));
            $maxLevel = !empty($teamMembers) ? max(array_column($teamMembers, 'level')) : 0;
        @endphp
        
        <div class="team-origin">
            <div class="title">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Origine des PV
                <span class="text-xs font-normal text-muted">
                    ({{ count($teamMembers) }} membres sur {{ $maxLevel }} niveaux)
                </span>
            </div>
            
            @if(count($teamMembers) > 0)
                @foreach($teamMembers as $member)
                    @php
                        $levelClass = 'level-' . min($member['level'], 5);
                    @endphp
                    <div class="item">
                        <span class="level {{ $levelClass }}">Niv {{ $member['level'] }}</span>
                        <span class="name">
                            <a href="{{ route('admin.users.show', $member['id']) }}">
                                {{ $member['name'] }}
                            </a>
                        </span>
                        <span class="pv">{{ number_format($member['pv'], 1, ',', ' ') }} PV</span>
                    </div>
                @endforeach
                
                <div class="total">
                    <span class="label">Total PV Équipe</span>
                    <span class="value">{{ number_format($totalTeamPV, 1, ',', ' ') }} PV</span>
                </div>
            @else
                <div class="empty">Aucun membre dans l'équipe avec des PV</div>
            @endif
        </div>
    </div>
</div>
<br>

<!-- History -->
<div class="card animate-fadeInUp delay-3">
    <div class="flex flex-wrap items-center justify-between mb-4">
        <div class="card-title">Historique des PV</div>
        <span class="text-xs text-muted" id="history_count">Total: {{ isset($pvHistory) ? count($pvHistory) : 0 }} entrées</span>
    </div>
    
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Période</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Notes</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                @if(isset($pvHistory) && count($pvHistory) > 0)
                    @foreach($pvHistory as $entry)
                        <tr id="history-row-{{ $entry->id }}">
                            <td>{{ $entry->period ?? '-' }}</td>
                            <td><span class="badge badge-sm badge-info">{{ ucfirst($entry->type) }}</span></td>
                            <td class="font-medium">{{ number_format($entry->amount, 1, ',', ' ') }}</td>
                            <td>{{ $entry->notes ?? '-' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteHistory({{ $entry->id }})">
                                    <svg class="icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="noHistoryRow">
                        <td colspan="5" class="text-center text-muted py-4">Aucun historique de PV trouvé</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Footer -->
<div class="footer-links">
    <a href="{{ route('legal.terms') }}">Conditions générales d'utilisation</a>
    <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>
    <span>© {{ date('Y') }} — Tous droits réservés</span>
</div>

<!-- Reset Modal -->
<div id="resetModal" class="confirm-overlay">
    <div class="confirm-box">
        <div class="icon">⚠</div>
        <h3>Réinitialisation complète</h3>
        <p>
            Êtes-vous sûr de vouloir réinitialiser <strong class="text-danger">{{ $user->name }}</strong> ?
        </p>
        <div class="warning">
            Cette action est irréversible.<br>
            Tous les PV seront remis à 0, le grade deviendra "Distributeur".
        </div>
        <div class="actions">
            <button class="btn btn-outline" onclick="closeModal('resetModal')">Annuler</button>
            <button class="btn btn-danger" onclick="submitReset()">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Confirmer
            </button>
        </div>
    </div>
</div>

<script>
// ============================================================
// Toast
// ============================================================
function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() { toast.remove(); }, 400);
    }, 4000);
}

// ============================================================
// Modal
// ============================================================
function openResetModal() {
    document.getElementById('resetModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('resetModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('resetModal');
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.confirm-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

function submitReset() {
    closeModal('resetModal');
    showToast('Réinitialisation en cours...', 'warning');
    
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.pv.reset', $user->id) }}';
    
    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    
    var method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'PUT';
    form.appendChild(method);
    
    var confirm = document.createElement('input');
    confirm.type = 'hidden';
    confirm.name = 'confirmed';
    confirm.value = '1';
    form.appendChild(confirm);
    
    document.body.appendChild(form);
    form.submit();
}

// ============================================================
// Delete History
// ============================================================
function deleteHistory(historyId) {
    if (!confirm('Supprimer cette entrée d\'historique ?')) {
        return;
    }
    
    showToast('Suppression en cours...', 'warning');
    
    var url = '{{ url('admin/pv/history') }}/' + historyId;
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            var row = document.getElementById('history-row-' + historyId);
            if (row) row.remove();
            
            var counter = document.getElementById('history_count');
            var match = counter.textContent.match(/\d+/);
            if (match) {
                var count = parseInt(match[0]) - 1;
                counter.textContent = 'Total: ' + count + ' entrées';
            }
            
            if (data.user) {
                document.getElementById('pv_balance_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.pv_balance);
                document.getElementById('monthly_pv_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.monthly_pv);
                document.getElementById('team_pv_display').textContent = 
                    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                    .format(data.user.team_pv);
                    
                document.querySelector('input[name="pv_balance"]').value = data.user.pv_balance;
                document.querySelector('input[name="monthly_pv"]').value = data.user.monthly_pv;
            }
            
            showToast('Historique supprimé', 'success');
        } else {
            showToast('Erreur: ' + data.message, 'error');
        }
    })
    .catch(function() {
        showToast('Erreur lors de la suppression', 'error');
    });
}

// ============================================================
// Form
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('pvForm');
    if (form) {
        form.addEventListener('submit', function() {
            var inputs = this.querySelectorAll('input[name="pv_balance"], input[name="monthly_pv"]');
            inputs.forEach(function(input) {
                if (input.value) {
                    input.value = input.value.replace(',', '.');
                }
            });
        });
    }
});
</script>
@endsection