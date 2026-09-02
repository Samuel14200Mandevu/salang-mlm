<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Salang MLM')</title>

    @if(class_exists('PwaKit'))
        {!! PwaKit::head() !!}
    @endif

    <meta name="theme-color" content="#0F2B4F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
    :root {
        --sidebar-width: 260px;
        --sidebar-collapsed: 72px;

        --primary-navy: #0F2B4F;
        --primary-navy-dark: #091E3B;
        --primary-navy-light: #1A3F6A;

        --bg-page: #F5F6F8;
        --bg-card: #FFFFFF;
        --bg-input: #F8F9FA;
        --bg-navbar: #FFFFFF;
        --bg-secondary: #EEF0F3;
        --bg-hover: #E8EAEE;
        --bg-footer: #E8EAEE;

        --text-primary: #1A1A1E;
        --text-secondary: #4A4A52;
        --text-tertiary: #7A7A82;

        --border-color: #DCDEE3;
        --border-light: #E8EAEE;

        --radius: 8px;
        --radius-lg: 10px;

        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .dark {
        --bg-page: #111827;
        --bg-card: #1A1D23;
        --bg-input: #1F2937;
        --bg-navbar: #111827;
        --bg-secondary: #1F2937;
        --bg-hover: #2D333B;
        --bg-footer: #111827;

        --text-primary: #F3F4F6;
        --text-secondary: #9CA3AF;
        --text-tertiary: #6B7280;

        --border-color: #374151;
        --border-light: #2D333B;
    }

    * {
        font-family: var(--font-family);
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html, body {
        height: 100%;
        margin: 0;
    }

    body {
        background: var(--bg-page);
        color: var(--text-primary);
        transition: background 0.2s ease, color 0.2s ease;
        -webkit-font-smoothing: antialiased;
        line-height: 1.6;
        display: flex;
        flex-direction: column;
    }

    ::selection {
        background: var(--primary-navy);
        color: white;
    }

    ::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--text-tertiary);
    }

    /* ===== LAYOUT PRINCIPAL ===== */
    .app-container {
        display: flex;
        min-height: 100vh;
        height: 100%;
    }

    .main-wrapper {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 100vh;
        transition: margin-left 0.25s ease-in-out, width 0.25s ease-in-out;
    }

    .main-content {
        flex: 1 0 auto;
        padding: 1rem;
    }

    .main-footer {
        flex-shrink: 0;
        background: var(--bg-footer);
        border-top: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        margin-top: auto;
    }

    /* ===== SIDEBAR ===== */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius);
        color: var(--text-secondary);
        transition: background 0.15s ease, color 0.15s ease;
        text-decoration: none;
        font-size: 0.813rem;
        font-weight: 500;
        position: relative;
        white-space: nowrap;
        overflow: hidden;
        cursor: pointer;
    }

    .sidebar-link svg {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
        min-width: 1.25rem;
        color: var(--text-tertiary);
        transition: color 0.15s ease;
    }

    .sidebar-link .label {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: opacity 0.2s ease;
    }

    .sidebar-link:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }

    .sidebar-link:hover svg {
        color: var(--text-primary);
    }

    .sidebar-link.active {
        background: var(--primary-navy);
        color: white;
    }

    .sidebar-link.active svg {
        color: white;
    }

    .sidebar-link .badge-count {
        display: inline-block;
        background: #B32A2A;
        color: white;
        font-size: 0.6rem;
        font-weight: 700;
        padding: 0.1rem 0.4rem;
        border-radius: 6px;
        min-width: 18px;
        text-align: center;
        line-height: 1.3;
        flex-shrink: 0;
    }

    .sidebar-section {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        padding: 0.75rem 0.75rem 0.375rem;
    }

    .notification-dot {
        position: absolute;
        top: -2px;
        right: -4px;
        width: 8px;
        height: 8px;
        background: #B32A2A;
        border-radius: 50%;
        border: 2px solid var(--bg-navbar);
    }

    /* ===== CONFIRM DIALOG ===== */
    .confirm-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    .confirm-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .confirm-dialog {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        max-width: 420px;
        width: 90%;
        border: 1px solid var(--border-color);
        transform: scale(0.95);
        transition: transform 0.25s ease;
    }
    .confirm-overlay.active .confirm-dialog {
        transform: scale(1);
    }
    .confirm-dialog .icon {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
    }
    .confirm-dialog .icon.danger {
        background: #FDE8E8;
        color: #B32A2A;
    }
    .confirm-dialog .icon.warning {
        background: #FEF1E6;
        color: #A65A0E;
    }
    .confirm-dialog .icon.success {
        background: #E6F4EC;
        color: #1F7B4D;
    }
    .confirm-dialog h3 {
        font-size: 1.0625rem;
        font-weight: 600;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 0.375rem;
    }
    .confirm-dialog p {
        font-size: 0.875rem;
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }
    .confirm-dialog .actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .confirm-dialog .actions .btn {
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        border: 1px solid transparent;
        transition: background 0.15s ease, border-color 0.15s ease;
        min-width: 90px;
    }
    .confirm-dialog .actions .btn-cancel {
        background: transparent;
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .confirm-dialog .actions .btn-cancel:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }
    .confirm-dialog .actions .btn-confirm {
        background: #B32A2A;
        color: white;
        border-color: #B32A2A;
    }
    .confirm-dialog .actions .btn-confirm:hover {
        background: #8F2121;
        border-color: #8F2121;
    }
    .confirm-dialog .actions .btn-confirm.success {
        background: #1F7B4D;
        border-color: #1F7B4D;
    }
    .confirm-dialog .actions .btn-confirm.success:hover {
        background: #16633D;
        border-color: #16633D;
    }

    /* ===== FOOTER ===== */
    .footer-links a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .footer-links a:hover {
        color: var(--text-primary);
        text-decoration: underline;
    }

    /* ===== PAGE TITLE ===== */
    .page-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        transition: color 0.2s ease;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 767px) {
        .confirm-dialog {
            padding: 1.25rem;
            max-width: 95%;
        }
        .confirm-dialog .icon {
            width: 2.5rem;
            height: 2.5rem;
        }
        .confirm-dialog h3 {
            font-size: 1rem;
        }
        .confirm-dialog p {
            font-size: 0.813rem;
        }
        .confirm-dialog .actions .btn {
            padding: 0.375rem 1rem;
            font-size: 0.75rem;
            min-width: 70px;
        }
        .confirm-dialog .actions {
            flex-wrap: wrap;
        }
        .main-content {
            padding: 0.75rem;
        }
        .main-footer {
            padding: 0.5rem 0.75rem;
        }
        .main-footer .footer-links {
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }
        .footer-separator {
            display: none;
        }
        .page-title {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 0.5rem;
        }
        .main-footer {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .page-title {
            font-size: 0.875rem;
        }
    }
    </style>
</head>
<body>

    <div class="app-container"
         x-data="{
            sidebarOpen: localStorage.getItem('sidebar_open') === 'true' ? true : (window.innerWidth > 1024),
            isMobile: window.innerWidth < 768
         }"
         x-init="
            sidebarOpen = localStorage.getItem('sidebar_open') === 'true' ? true : (window.innerWidth > 1024);
            isMobile = window.innerWidth < 768;
            if (window.innerWidth < 768) sidebarOpen = false;
            window.addEventListener('resize', () => {
                isMobile = window.innerWidth < 768;
                if (window.innerWidth > 1024) sidebarOpen = true;
                if (window.innerWidth < 768) sidebarOpen = false;
            });
         "
         @sidebar-toggle.window="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar_open', sidebarOpen)">

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen && isMobile"
             @click="sidebarOpen = false; localStorage.setItem('sidebar_open', false)"
             class="fixed inset-0 bg-black/40 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-250"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-250"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside id="sidebar"
               class="fixed top-0 left-0 z-50 h-full transition-all duration-250 ease-in-out bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-[72px]': !sidebarOpen && !isMobile,
                  'w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">

            <!-- Logo -->
            <div class="flex items-center justify-between h-14 px-3 border-b border-[var(--border-color)] flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center flex-1">
                    <img src="{{ asset('images/salang_logo.png') }}"
                         alt="Salang"
                         class="logo-themeable transition-all duration-250"
                         :class="sidebarOpen ? 'h-12 w-auto' : 'h-8 w-auto'">
                </a>
                <button @click="sidebarOpen = false; localStorage.setItem('sidebar_open', false)"
                        class="lg:hidden p-1.5 rounded hover:bg-[var(--bg-hover)] transition-colors">
                    <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Menu -->
            <nav class="flex-1 overflow-y-auto py-2 px-2 custom-scrollbar">
                <ul class="space-y-0.5">

                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                           data-title="Accueil">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Accueil</span>
                        </a>
                    </li>

                    <!-- Section Gestion -->
                    <li>
                        <div class="sidebar-section" :class="!sidebarOpen ? 'hidden' : ''">Gestion</div>
                    </li>

                    <li>
                        <a href="{{ route('admin.users') }}"
                           class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}"
                           data-title="Utilisateurs">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Utilisateurs</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.cashiers.index') }}"
                           class="sidebar-link {{ request()->routeIs('admin.cashiers*') ? 'active' : '' }}"
                           data-title="Caissiers">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Caissiers</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.packages') }}"
                           class="sidebar-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}"
                           data-title="Packages">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Packages</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.products') }}"
                           class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}"
                           data-title="Produits">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Produits</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.orders.index') }}"
                           class="sidebar-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"
                           data-title="Commandes">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Commandes</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.kyc') }}"
                           class="sidebar-link {{ request()->routeIs('admin.kyc*') ? 'active' : '' }}"
                           data-title="KYC">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">KYC</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.consultations.index') }}"
                           class="sidebar-link {{ request()->routeIs('admin.consultations*') ? 'active' : '' }}"
                           data-title="Consultations">
                            <div class="relative">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span id="adminConsultationDot" class="notification-dot" style="display:none;"></span>
                            </div>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">
                                Consultations
                                <span id="adminConsultationBadge" class="badge-count" style="display:none;">0</span>
                            </span>
                        </a>
                    </li>

                    <!-- Section Finances -->
                    <li>
                        <div class="sidebar-section" :class="!sidebarOpen ? 'hidden' : ''">Finances</div>
                    </li>

                    <li>
                        <a href="{{ route('admin.commissions') }}"
                           class="sidebar-link {{ request()->routeIs('admin.commissions*') ? 'active' : '' }}"
                           data-title="Commissions">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Commissions</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.wallets') }}"
                           class="sidebar-link {{ request()->routeIs('admin.wallets*') ? 'active' : '' }}"
                           data-title="Portefeuilles">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Portefeuilles</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.withdrawals') }}"
                           class="sidebar-link {{ request()->routeIs('admin.withdrawals*') ? 'active' : '' }}"
                           data-title="Retraits">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Retraits</span>
                        </a>
                    </li>

                    <!-- Section Rangs -->
                    <li>
                        <div class="sidebar-section" :class="!sidebarOpen ? 'hidden' : ''">Rangs</div>
                    </li>

                    <li>
                        <a href="{{ route('admin.ranks') }}"
                           class="sidebar-link {{ request()->routeIs('admin.ranks*') ? 'active' : '' }}"
                           data-title="Gestion des Rangs">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Gestion des Rangs</span>
                        </a>
                    </li>

                    <!-- Section Rapports -->
                    <li>
                        <div class="sidebar-section" :class="!sidebarOpen ? 'hidden' : ''">Rapports</div>
                    </li>

                    <li>
                        <a href="{{ route('admin.reports') }}"
                           class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
                           data-title="Rapports">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Rapports</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.pos-reports.index') }}"
                           class="sidebar-link {{ request()->routeIs('admin.pos-reports.*') ? 'active' : '' }}"
                           data-title="Rapports POS">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Rapports POS</span>
                        </a>
                    </li>

                    <!-- Section Administration -->
                    <li>
                        <div class="sidebar-section" :class="!sidebarOpen ? 'hidden' : ''">Administration</div>
                    </li>

                    <li>
                        <a href="{{ route('admin.settings') }}"
                           class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"
                           data-title="Paramètres">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Paramètres</span>
                        </a>
                    </li>

                    <!-- Séparateur -->
                    <li class="pt-3 mt-3 border-t border-[var(--border-color)]">
                        <a href="{{ route('dashboard') }}" class="sidebar-link" data-title="Voir le site">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span class="label" :class="!sidebarOpen ? 'hidden' : ''">Voir le site</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Profil sidebar -->
            <div class="p-3 border-t border-[var(--border-color)] flex-shrink-0">
                <div class="flex items-center gap-3" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                    <div class="w-9 h-9 rounded-full bg-[var(--primary-navy)] flex items-center justify-center text-white font-medium text-sm flex-shrink-0">
                        @auth
                            @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}"
                                     alt="Avatar" class="w-9 h-9 rounded-full object-cover">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @endif
                        @endauth
                    </div>
                    <div class="transition-all duration-250 overflow-hidden min-w-0"
                         :class="sidebarOpen ? 'opacity-100 max-w-[200px]' : 'opacity-0 max-w-0 w-0'">
                        <p class="text-sm font-medium text-[var(--text-primary)] truncate">
                            @auth {{ Auth::user()->name }} @endauth
                        </p>
                        <p class="text-xs text-[var(--text-secondary)] truncate">
                            @auth {{ Auth::user()->email }} @endauth
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="main-wrapper"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '4.5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 4.5rem)' : '100%'
             }">

            <!-- Top Navigation -->
            <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 flex-shrink-0">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16">

                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                            <button @click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar_open', sidebarOpen)"
                                    class="p-1.5 sm:p-2 rounded hover:bg-[var(--bg-hover)] transition-colors flex-shrink-0">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1">
                                <h1 class="page-title" id="pageTitle">Administration</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-3 flex-shrink-0">

                            <!-- Theme Toggle -->
                            <button id="theme-toggle"
                                    class="p-1.5 sm:p-2 rounded hover:bg-[var(--bg-hover)] transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>

                            <!-- Profil dropdown -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                            class="flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded hover:bg-[var(--bg-hover)] transition-colors">
                                        <span class="hidden sm:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[60px] md:max-w-[100px]">
                                            {{ Auth::user()->name }}
                                        </span>
                                        <div class="w-8 h-8 rounded-full bg-[var(--primary-navy)] flex items-center justify-center text-white font-medium text-sm flex-shrink-0">
                                            @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}"
                                                     alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         class="absolute right-0 mt-2 w-48 sm:w-56 bg-[var(--bg-card)] rounded shadow-lg py-1 border border-[var(--border-color)] z-50"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">

                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()->email }}</p>
                                        </div>

                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-hover)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                </svg>
                                                Accueil Admin
                                            </span>
                                        </a>
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-hover)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                                </svg>
                                                Voir le site
                                            </span>
                                        </a>
                                        <hr class="border-[var(--border-color)]">
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-hover)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Mon Profil
                                            </span>
                                        </a>
                                        <hr class="border-[var(--border-color)]">

                                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="logout-form">
                                            @csrf
                                            <button type="button"
                                                    onclick="confirmLogout(event)"
                                                    class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-hover)] text-sm text-[#B32A2A] transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Déconnexion
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenu -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- Footer fixe en bas -->
            <footer class="main-footer">
                <div class="max-w-7xl mx-auto">
                    <div class="footer-links flex flex-wrap justify-center items-center gap-1 text-xs sm:text-sm text-[var(--text-secondary)]">
                        <span>&copy; 2026 Salang Group. Tous droits réservés.</span>
                        <span class="footer-separator text-[var(--text-tertiary)]">•</span>
                        <a href="{{ route('legal.terms') }}">CGU</a>
                        <span class="footer-separator text-[var(--text-tertiary)]">•</span>
                        <a href="{{ route('legal.privacy') }}">Confidentialité</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- ===== CONFIRMATION DIALOG ===== -->
    <div id="confirmDialog" class="confirm-overlay">
        <div class="confirm-dialog">
            <div class="icon danger">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </div>
            <h3>Confirmation de déconnexion</h3>
            <p>Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.</p>
            <div class="actions">
                <button type="button" class="btn btn-cancel" onclick="closeConfirmDialog()">Annuler</button>
                <button type="button" class="btn btn-confirm" id="confirmLogoutBtn">Se déconnecter</button>
            </div>
        </div>
    </div>

    @livewireScripts

    @if(class_exists('PwaKit'))
        {!! PwaKit::scripts() !!}
    @endif

    @stack('scripts')

    <script>
    // ============================================================
    // CONFIRMATION LOGOUT
    // ============================================================
    let confirmCallback = null;
    let confirmForm = null;

    function showConfirmDialog(options) {
        const dialog = document.getElementById('confirmDialog');
        const icon = dialog.querySelector('.icon');
        const title = dialog.querySelector('h3');
        const message = dialog.querySelector('p');
        const confirmBtn = document.getElementById('confirmLogoutBtn');

        icon.className = 'icon';
        icon.classList.add(options.type || 'danger');

        if (options.type === 'success') {
            icon.innerHTML = `
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            `;
        } else if (options.type === 'warning') {
            icon.innerHTML = `
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            `;
        } else {
            icon.innerHTML = `
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            `;
        }

        title.textContent = options.title || 'Confirmation';
        message.textContent = options.message || 'Êtes-vous sûr de vouloir continuer ?';
        confirmBtn.textContent = options.confirmText || 'Confirmer';
        confirmBtn.className = 'btn btn-confirm';

        if (options.type === 'success') {
            confirmBtn.classList.add('success');
        }

        confirmCallback = options.onConfirm || null;
        confirmForm = options.form || null;

        dialog.classList.add('active');
    }

    function closeConfirmDialog() {
        document.getElementById('confirmDialog').classList.remove('active');
        confirmCallback = null;
        confirmForm = null;
    }

    function confirmLogout(event) {
        event.preventDefault();
        const form = event.target.closest('form');

        showConfirmDialog({
            type: 'danger',
            title: 'Confirmation de déconnexion',
            message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
            confirmText: 'Se déconnecter',
            onConfirm: function() {
                if (form) form.submit();
                closeConfirmDialog();
            },
            form: form
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (typeof confirmCallback === 'function') {
                    confirmCallback();
                } else if (confirmForm) {
                    confirmForm.submit();
                }
                closeConfirmDialog();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeConfirmDialog();
        });

        document.getElementById('confirmDialog').addEventListener('click', function(e) {
            if (e.target === this) closeConfirmDialog();
        });

        // ============================================================
        // MISE À JOUR DU TITRE DYNAMIQUE (basé sur le menu actif)
        // ============================================================
        function updatePageTitle() {
            const pageTitle = document.getElementById('pageTitle');
            if (!pageTitle) return;

            // Récupérer le lien actif dans la sidebar
            const activeLink = document.querySelector('.sidebar-link.active');
            
            if (activeLink) {
                // Utiliser l'attribut data-title
                const title = activeLink.getAttribute('data-title');
                if (title) {
                    pageTitle.textContent = title;
                    return;
                }
                
                // Sinon, utiliser le texte du label
                const label = activeLink.querySelector('.label');
                if (label) {
                    pageTitle.textContent = label.textContent.trim();
                    return;
                }
            }

            // Fallback : utiliser le titre de la page
            const titleElement = document.querySelector('title');
            if (titleElement) {
                const fullTitle = titleElement.textContent;
                const cleanTitle = fullTitle.replace(/\s*[-|]\s*Salang\s*MLM\s*$/, '').trim();
                if (cleanTitle && cleanTitle !== 'Admin') {
                    pageTitle.textContent = cleanTitle;
                    return;
                }
            }

            // Fallback final
            pageTitle.textContent = 'Administration';
        }

        // Exécuter au chargement
        updatePageTitle();

        // Observer les changements dans le DOM
        const observer = new MutationObserver(function() {
            updatePageTitle();
        });

        const content = document.querySelector('.main-content');
        if (content) {
            observer.observe(content, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        // Écouter les événements Livewire
        document.addEventListener('livewire:update', function() {
            setTimeout(updatePageTitle, 100);
        });

        document.addEventListener('livewire:load', function() {
            setTimeout(updatePageTitle, 100);
        });

        // Observer les changements de classe active dans la sidebar
        const sidebarObserver = new MutationObserver(function() {
            updatePageTitle();
        });

        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            sidebarObserver.observe(link, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    });

    // ============================================================
    // COMPTEUR DE CONSULTATIONS
    // ============================================================
    (function() {
        'use strict';

        function updateConsultationCount() {
            const badge = document.getElementById('adminConsultationBadge');
            const dot = document.getElementById('adminConsultationDot');
            if (!badge) return;

            fetch('{{ route("admin.consultations.count") }}')
                .then(function(response) {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(function(data) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                        if (dot) dot.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                        if (dot) dot.style.display = 'none';
                    }
                })
                .catch(function(err) {
                    console.error('Erreur compteur consultations:', err);
                });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateConsultationCount);
        } else {
            updateConsultationCount();
        }

        setInterval(updateConsultationCount, 30000);

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) updateConsultationCount();
        });
    })();

    // ============================================================
    // THEME TOGGLE
    // ============================================================
    (function() {
        'use strict';

        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }

        function initTheme() {
            var toggle = document.getElementById('theme-toggle');
            var icon = document.getElementById('theme-icon');

            if (!toggle) return;

            function setTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
                updateIcon();
            }

            function updateIcon() {
                if (!icon) return;
                if (document.documentElement.classList.contains('dark')) {
                    icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
                } else {
                    icon.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
                }
            }

            var newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);

            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (document.documentElement.classList.contains('dark')) {
                    setTheme('light');
                } else {
                    setTheme('dark');
                }
            });

            setTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTheme);
        } else {
            initTheme();
        }
    })();
    </script>
</body>
</html>