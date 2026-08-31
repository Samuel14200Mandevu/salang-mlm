{{-- resources/views/cashier/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reception - @yield('title', 'Salang MLM')</title>
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#5ab638">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
        /* ===== VARIABLES ===== */
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed: 72px;
            --header-height: 64px;
            --mobile-nav-height: 64px;
        }

        /* ===== SIDEBAR LINKS ===== */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: var(--radius-md, 0.5rem);
            color: var(--text-secondary);
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .sidebar-link svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            min-width: 1.25rem;
            transition: all 0.2s ease;
        }

        .sidebar-link .label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .sidebar-link.active {
            background: var(--gradient-primary, #5ab638);
            color: white;
            box-shadow: 0 4px 12px rgba(90, 182, 56, 0.3);
        }

        .sidebar-link.active svg {
            color: white;
        }

        .sidebar-link.danger {
            color: #ef4444;
        }
        .sidebar-link.danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }
        .sidebar-link.danger.active {
            background: #ef4444;
            color: white;
        }

        .sidebar-section {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            padding: 0.75rem 0.75rem 0.5rem;
            margin-top: 0.5rem;
        }

        /* ===== MOBILE BOTTOM NAV ===== */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: var(--bg-navbar);
            border-top: 1px solid var(--border-color);
            display: none;
            padding: 0.25rem 0 env(safe-area-inset-bottom, 0.25rem) 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            height: var(--mobile-nav-height);
        }

        .mobile-bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            color: var(--text-secondary);
            text-decoration: none;
            flex: 1;
            position: relative;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-bottom-nav .nav-item svg {
            width: 24px;
            height: 24px;
            transition: all 0.2s ease;
        }

        .mobile-bottom-nav .nav-item span {
            font-size: 10px;
            margin-top: 1px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .mobile-bottom-nav .nav-item.active {
            color: var(--primary-500);
        }

        .mobile-bottom-nav .nav-item.active svg {
            transform: scale(1.1);
        }

        .mobile-bottom-nav .nav-item .badge-count {
            position: absolute;
            top: 0;
            right: 50%;
            transform: translateX(calc(50% + 14px));
            background: #ef4444;
            color: white;
            font-size: 9px;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--bg-navbar);
        }

        /* ===== SCROLLBAR ===== */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        /* ===== CONFIRMATION DIALOG ===== */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }
        .confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .confirm-dialog {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        .confirm-overlay.active .confirm-dialog {
            transform: scale(1) translateY(0);
        }
        .confirm-dialog .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .confirm-dialog .icon.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        .confirm-dialog .icon.danger {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        .confirm-dialog .icon.success {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }
        .confirm-dialog .icon svg {
            width: 32px;
            height: 32px;
        }
        .confirm-dialog h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .confirm-dialog p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .confirm-dialog .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        .confirm-dialog .actions .btn {
            padding: 0.5rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            min-width: 100px;
        }
        .confirm-dialog .actions .btn-cancel {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .confirm-dialog .actions .btn-cancel:hover {
            background: var(--bg-hover);
        }
        .confirm-dialog .actions .btn-confirm {
            background: #ef4444;
            color: white;
        }
        .confirm-dialog .actions .btn-confirm:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .confirm-dialog .actions .btn-confirm.success {
            background: #22c55e;
        }
        .confirm-dialog .actions .btn-confirm.success:hover {
            background: #16a34a;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        /* ===== TOAST ===== */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 400px;
            width: 100%;
        }
        @media (max-width: 640px) {
            .toast-container {
                bottom: calc(var(--mobile-nav-height) + 1rem);
                right: 1rem;
                left: 1rem;
                max-width: none;
            }
        }
        .toast-item {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: toastIn 0.3s ease forwards;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateX(100%);
            opacity: 0;
        }
        .toast-item.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast-item.success { background: #22c55e; }
        .toast-item.error { background: #ef4444; }
        .toast-item.warning { background: #f59e0b; }
        .toast-item.info { background: #3b82f6; }
        .toast-item .toast-icon {
            flex-shrink: 0;
            width: 1.25rem;
            height: 1.25rem;
        }
        .toast-item .toast-close {
            margin-left: auto;
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .toast-item .toast-close:hover {
            background: rgba(255,255,255,0.15);
        }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 767px) {
            .mobile-bottom-nav {
                display: flex;
            }
            main {
                padding-bottom: calc(var(--mobile-nav-height) + 1rem) !important;
            }
            footer {
                padding-bottom: calc(var(--mobile-nav-height) + 1rem) !important;
            }
            .confirm-dialog {
                padding: 1.5rem;
                max-width: 95%;
            }
            .confirm-dialog .icon {
                width: 48px;
                height: 48px;
            }
            .confirm-dialog .icon svg {
                width: 28px;
                height: 28px;
            }
            .confirm-dialog h3 {
                font-size: 1rem;
            }
            .confirm-dialog p {
                font-size: 0.813rem;
            }
            .confirm-dialog .actions .btn {
                padding: 0.375rem 1rem;
                font-size: 0.813rem;
                min-width: 80px;
            }
        }
    </style>
</head>
<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] transition-colors duration-200 antialiased">
    <div class="min-h-screen flex" 
         x-data="{ 
            sidebarOpen: window.innerWidth > 1024, 
            isMobile: window.innerWidth < 768
         }" 
         x-init="
            sidebarOpen = window.innerWidth > 1024;
            isMobile = window.innerWidth < 768;
            window.addEventListener('resize', () => {
                isMobile = window.innerWidth < 768;
                if (window.innerWidth > 1024) sidebarOpen = true;
                if (window.innerWidth < 768) sidebarOpen = false;
            });
         ">
        
        <!-- Overlay mobile -->
        <div x-show="sidebarOpen && isMobile" 
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" 
               class="fixed top-0 left-0 z-50 h-full transition-all duration-300 ease-in-out"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-20': !sidebarOpen && !isMobile,
                  'w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">
            
            <div class="h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden">
                
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-[var(--border-color)] flex-shrink-0">
                    <a href="{{ route('cashier.dashboard') }}" class="flex items-center justify-center flex-1">
                        <img src="{{ asset('images/salang_logo.png') }}" 
                             alt="Salang" 
                             class="logo-themeable transition-all duration-300"
                             :class="sidebarOpen ? 'h-14 w-auto' : 'h-10 w-auto'">
                    </a>
                    <button @click="sidebarOpen = false" 
                            class="lg:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                        <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Menu -->
                <nav class="flex-1 overflow-y-auto py-4 px-2 custom-scrollbar">
                    <ul class="space-y-0.5">
                        
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('cashier.dashboard') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Accueil
                                </span>
                            </a>
                        </li>

                        <!-- Consultations -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Consultations
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('cashier.consultations.index') }}" 
                            class="sidebar-link {{ request()->routeIs('cashier.consultations*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                    :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mes Consultations
                                </span>
                            </a>
                        </li>

                        <!-- Ventes -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Ventes
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('cashier.pos') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Point de Vente
                                </span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('cashier.orders') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.orders*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Commandes
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cashier.daily-sales') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.daily-sales') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Ventes du jour
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cashier.commissions') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.commissions') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Commissions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cashier.history') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.history') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Historique
                                </span>
                            </a>
                        </li>

                        <!-- Clients -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Clients
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('cashier.customers') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.customers') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Clients
                                </span>
                            </a>
                        </li>

                        <!-- Membres -->
                        <li>
                            <a href="{{ route('cashier.members') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.members') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Membres
                                </span>
                            </a>
                        </li>

                        <!-- Profil -->
                        <li>
                            <a href="{{ route('cashier.profile') }}" 
                               class="sidebar-link {{ request()->routeIs('cashier.profile') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mon Profil
                                </span>
                            </a>
                        </li>

                        <!-- Admin -->
                        @auth
                            @if(Auth::user()->hasRole('admin'))
                                <li>
                                    <div class="sidebar-section transition-opacity duration-200" 
                                         :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                        Administration
                                    </div>
                                </li>
                                <li>
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="label transition-opacity duration-200" 
                                              :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                            Panel Admin
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth

                        <!-- Déconnexion -->
                        <li class="pt-4 mt-4 border-t border-[var(--border-color)]">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="logout-form">
                                @csrf
                                <button type="submit" class="sidebar-link danger">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="label transition-opacity duration-200" 
                                          :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                        Déconnexion
                                    </span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-[var(--border-color)] flex-shrink-0">
                    <div class="flex items-center gap-3" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            @auth
                                @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                         alt="Avatar" 
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            @endauth
                        </div>
                        <div class="transition-all duration-300 overflow-hidden" 
                             :class="sidebarOpen ? 'opacity-100 max-w-[200px]' : 'opacity-0 max-w-0'">
                            <p class="text-sm font-medium text-[var(--text-primary)] truncate whitespace-nowrap">
                                @auth {{ Auth::user()->name }} @endauth
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] truncate whitespace-nowrap">
                                @auth Caissier @endauth
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 transition-all duration-300 ease-in-out w-full"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? 'var(--sidebar-width)' : (!isMobile && !sidebarOpen) ? 'var(--sidebar-collapsed)' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - var(--sidebar-width))' : (!isMobile && !sidebarOpen) ? 'calc(100% - var(--sidebar-collapsed))' : '100%'
             }">
            
            <!-- Top Navigation -->
            <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 shadow-sm">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16">
                        
                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors flex-shrink-0">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            
                            <div class="min-w-0 flex-1">
                                @if(isset($header) && $header)
                                    <div class="truncate">{{ $header }}</div>
                                @else
                                    <h1 class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">
                                        @yield('title', 'Dashboard Caissier')
                                    </h1>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-4 flex-shrink-0">
                            
                            <!-- Theme Toggle -->
                            <button id="theme-toggle" 
                                    class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>

                            <!-- Panier -->
                            <button id="cartToggleBtn" 
                                    class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors relative">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span id="headerCartCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 hidden">0</span>
                            </button>

                            <!-- Profile -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                        <span class="hidden sm:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[80px] md:max-w-[120px]">
                                            {{ Auth::user()->name }}
                                        </span>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-xs sm:text-sm flex-shrink-0">
                                            @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                                     alt="Avatar" 
                                                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    </button>
                                    
                                    <div x-show="open" @click.away="open = false" 
                                         class="absolute right-0 mt-2 w-48 sm:w-56 bg-[var(--bg-secondary)] rounded-xl shadow-lg py-1 border border-[var(--border-color)] z-50"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">
                                        
                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                        
                                        <a href="{{ route('cashier.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                </svg>
                                                Dashboard
                                            </span>
                                        </a>
                                        
                                        <a href="{{ route('cashier.profile') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Mon Profil
                                            </span>
                                        </a>
                                        
                                        @if(Auth::user()->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-primary-600 font-semibold transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Administration
                                                </span>
                                            </a>
                                        @endif
                                        
                                        <hr class="border-[var(--border-color)]">
                                        
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile" class="logout-form">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-red-500 transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Déconnexion
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <main class="p-3 sm:p-4 md:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-3 sm:py-4">
                <div class="max-w-7xl mx-auto px-3 sm:px-4 text-center text-[var(--text-secondary)] text-xs sm:text-sm">
                    <span>&copy; {{ date('Y') }} Salang Group - Reception</span>
                </div>
            </footer>
        </div>

        <!-- Mobile Bottom Nav -->
        <nav class="mobile-bottom-nav" id="mobileBottomNav">
            <a href="{{ route('cashier.dashboard') }}" 
               class="nav-item {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Accueil</span>
            </a>

            <a href="{{ route('cashier.pos') }}" 
               class="nav-item {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>POS</span>
            </a>

            <a href="{{ route('cashier.orders') }}" 
               class="nav-item {{ request()->routeIs('cashier.orders*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Commandes</span>
            </a>

            <a href="{{ route('cashier.customers') }}" 
               class="nav-item {{ request()->routeIs('cashier.customers') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Clients</span>
            </a>

            <button id="mobileCartToggleBtn" class="nav-item relative">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Panier</span>
                <span id="mobileCartCount" class="badge-count hidden">0</span>
            </button>
        </nav>
    </div>

    <!-- ===== CART SIDEBAR ===== -->
    <div id="cartOverlay" class="fixed inset-0 bg-black/50 z-[998] hidden"></div>

    <div id="cartSidebar" class="fixed right-0 top-0 h-full w-[380px] bg-[var(--bg-card)] border-l border-[var(--border-color)] transform translate-x-full transition-transform duration-300 ease-in-out z-[999] flex flex-col shadow-[-4px_0_24px_rgba(0,0,0,0.1)]">
        <div class="p-4 border-b border-[var(--border-color)] flex justify-between items-center">
            <h3 class="font-bold text-[var(--text-primary)]">Panier</h3>
            <button id="cartCloseBtn" class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="cartBody" class="flex-1 overflow-y-auto p-4">
            <div id="cartEmpty" class="text-center py-8 text-[var(--text-tertiary)]">
                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Votre panier est vide</p>
                <p class="text-xs mt-1">Ajoutez des produits ou packages MLM</p>
            </div>
            <div id="cartItems" class="hidden"></div>
        </div>
        <div id="cartFooter" class="p-4 border-t border-[var(--border-color)] bg-[var(--bg-secondary)] hidden">
            <div class="flex justify-between text-lg font-bold text-[var(--text-primary)]">
                <span>Total</span>
                <span id="cartTotal" class="text-primary-500">$0.00</span>
            </div>
            <!-- Bouton "Passer la commande" avec un lien direct, pas un formulaire -->
            <a href="#" id="checkoutLink" class="btn btn-success w-full mt-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Passer la commande
            </a>
            <button id="clearCartBtn" class="btn btn-outline btn-sm w-full mt-2">
                Vider le panier
            </button>
        </div>
    </div>

    <!-- ===== MODAL VIDER LE PANIER ===== -->
    <div id="clearCartModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 invisible transition-all duration-300">
        <div class="bg-[var(--bg-card)] rounded-xl p-6 max-w-[420px] w-[90%] shadow-xl border border-[var(--border-color)] transform scale-90 transition-all duration-300">
            <div class="w-16 h-16 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-4 text-amber-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                </svg>
            </div>
            <h3 class="text-center text-xl font-bold text-[var(--text-primary)] mb-2">Vider le panier ?</h3>
            <p class="text-center text-[var(--text-secondary)] text-sm mb-6">
                Êtes-vous sûr de vouloir <strong>vider votre panier</strong> ?
                <br>
                Cette action est <strong>irréversible</strong> et tous les articles seront supprimés.
            </p>
            <div class="flex gap-3 justify-center">
                <button id="clearCartCancelBtn" class="btn btn-outline btn-sm">Annuler</button>
                <button id="clearCartConfirmBtn" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Vider
                </button>
            </div>
        </div>
    </div>

    <!-- ===== CONFIRMATION DIALOG ===== -->
    <div id="confirmDialog" class="confirm-overlay">
        <div class="confirm-dialog">
            <div class="icon danger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            </div>
            <h3 id="confirmTitle">Confirmation de déconnexion</h3>
            <p id="confirmMessage">Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.</p>
            <div class="actions">
                <button type="button" id="confirmCancelBtn" class="btn btn-cancel">Annuler</button>
                <button type="button" id="confirmDialogBtn" class="btn btn-confirm">Se déconnecter</button>
            </div>
        </div>
    </div>

    <!-- ===== TOAST CONTAINER ===== -->
    <div id="toastContainer" class="toast-container"></div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    
    @stack('scripts')

    <script>
    // ================================================================
    //  DÉFINITION DES FONCTIONS GLOBALES
    // ================================================================
    
    window.cart = [];

    // Fonction pour construire l'URL de checkout avec les items
    window.buildCheckoutUrl = function() {
        if (window.cart.length === 0) return null;
        // Construire la chaîne items=product:1,product:2,package:3
        const items = window.cart.map(item => item.type + ':' + item.id);
        return '{{ route('cashier.checkout') }}?items=' + items.join(',');
    };

    window.loadCart = function() {
        try {
            const saved = localStorage.getItem('pos_cart');
            if (saved) {
                window.cart = JSON.parse(saved);
                window.renderCart();
            }
        } catch (e) {
            window.cart = [];
        }
    };

    window.saveCart = function() {
        localStorage.setItem('pos_cart', JSON.stringify(window.cart));
        window.updateCartCount();
    };

    window.addToCart = function(itemId, type) {
        const existing = window.cart.find(item => item.id === itemId && item.type === type);
        if (existing) {
            existing.quantity += 1;
            window.saveCart();
            window.renderCart();
            window.showToast('Quantité augmentée', 'success');
            return;
        }
        
        let card;
        if (type === 'product') {
            card = document.querySelector(`.product-card[data-product-id="${itemId}"][data-type="product"]`);
        } else {
            card = document.querySelector(`.product-card[data-product-id="${itemId}"][data-type="package"]`);
        }
        
        if (!card) {
            window.showToast('Erreur: article non trouvé', 'error');
            return;
        }
        
        const name = card.querySelector('.product-name')?.textContent || 'Article';
        const priceText = card.querySelector('.product-price')?.textContent || '$0.00';
        const price = parseFloat(priceText.replace('$', '').replace(',', ''));
        const image = card.querySelector('.image-container img')?.getAttribute('src') || null;
        const source = type === 'product' ? 'pos' : 'mlm';
        const sourceLabel = type === 'product' ? 'POS' : 'MLM';
        const pvBadge = card.querySelector('.pv-badge');
        const pvValue = pvBadge ? parseFloat(pvBadge.textContent.replace(' PV', '')) || 0 : 0;
        const bvBadge = card.querySelector('.pv-badge[style*="color:#8b5cf6"]');
        const bvValue = bvBadge ? parseFloat(bvBadge.textContent.replace(' BV', '')) || 0 : 0;
        
        window.cart.push({
            id: itemId,
            type: type,
            name: name,
            price: price,
            image: image,
            source: source,
            sourceLabel: sourceLabel,
            pv_value: pvValue,
            bv_value: bvValue,
            quantity: 1
        });
        
        window.saveCart();
        window.renderCart();
        window.showToast('Article ajouté au panier', 'success');
    };

    window.removeFromCart = function(itemId, type) {
        window.cart = window.cart.filter(item => !(item.id === itemId && item.type === type));
        window.saveCart();
        window.renderCart();
    };

    window.updateQuantity = function(itemId, type, delta) {
        const item = window.cart.find(item => item.id === itemId && item.type === type);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                window.removeFromCart(itemId, type);
            } else {
                window.saveCart();
                window.renderCart();
            }
        }
    };

    window.renderCart = function() {
        const cartItemsContainer = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartFooter = document.getElementById('cartFooter');
        const cartTotal = document.getElementById('cartTotal');
        const checkoutLink = document.getElementById('checkoutLink');
        
        if (!cartItemsContainer) return;
        
        cartItemsContainer.innerHTML = '';
        
        if (window.cart.length === 0) {
            if (cartEmpty) cartEmpty.classList.remove('hidden');
            if (cartItemsContainer) cartItemsContainer.classList.add('hidden');
            if (cartFooter) cartFooter.classList.add('hidden');
            window.updateCartCount();
            return;
        }
        
        if (cartEmpty) cartEmpty.classList.add('hidden');
        if (cartItemsContainer) cartItemsContainer.classList.remove('hidden');
        if (cartFooter) cartFooter.classList.remove('hidden');
        
        let total = 0;
        window.cart.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            
            const div = document.createElement('div');
            div.className = 'flex gap-3 items-center py-3 border-b border-[var(--border-color)] last:border-b-0';
            div.innerHTML = `
                <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-[var(--bg-secondary)]">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">` : `
                        <svg class="w-full h-full p-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                    `}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-[var(--text-primary)] truncate">${item.name}</h4>
                    <div class="text-xs text-[var(--text-secondary)]">$${item.price.toFixed(2)} x ${item.quantity}</div>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full ${item.source === 'pos' ? 'bg-green-500/10 text-green-500' : 'bg-blue-500/10 text-blue-500'}">${item.sourceLabel}</span>
                    ${item.pv_value ? `<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-blue-500/10 text-blue-500 ml-1">${item.pv_value} PV</span>` : ''}
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.updateQuantity(${item.id}, '${item.type}', -1)" class="w-6 h-6 rounded-full border border-[var(--border-color)] hover:bg-primary-500 hover:text-white hover:border-primary-500 flex items-center justify-center text-xs">-</button>
                    <span class="w-5 text-center font-semibold text-sm">${item.quantity}</span>
                    <button onclick="window.updateQuantity(${item.id}, '${item.type}', 1)" class="w-6 h-6 rounded-full border border-[var(--border-color)] hover:bg-primary-500 hover:text-white hover:border-primary-500 flex items-center justify-center text-xs">+</button>
                </div>
                <button onclick="window.removeFromCart(${item.id}, '${item.type}')" class="text-red-500 hover:text-red-600 text-lg leading-none">×</button>
            `;
            cartItemsContainer.appendChild(div);
        });
        
        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
        window.updateCartCount();
        
        // Mettre à jour le lien "Passer la commande"
        if (checkoutLink) {
            const url = window.buildCheckoutUrl();
            if (url) {
                checkoutLink.href = url;
                checkoutLink.style.display = 'inline-flex';
            } else {
                checkoutLink.href = '#';
                checkoutLink.style.display = 'none';
            }
        }
    };

    window.updateCartCount = function() {
        const count = window.cart.reduce((sum, item) => sum + item.quantity, 0);
        const headerCartCount = document.getElementById('headerCartCount');
        const mobileCartCount = document.getElementById('mobileCartCount');
        
        if (count > 0) {
            if (headerCartCount) {
                headerCartCount.textContent = count;
                headerCartCount.classList.remove('hidden');
            }
            if (mobileCartCount) {
                mobileCartCount.textContent = count;
                mobileCartCount.classList.remove('hidden');
            }
        } else {
            if (headerCartCount) headerCartCount.classList.add('hidden');
            if (mobileCartCount) mobileCartCount.classList.add('hidden');
        }
    };

    window.toggleCart = function() {
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartOverlay');
        if (sidebar) {
            sidebar.classList.toggle('translate-x-full');
        }
        if (overlay) {
            overlay.classList.toggle('hidden');
        }
    };

    window.openClearCartModal = function() {
        if (window.cart.length === 0) {
            window.showToast('Le panier est déjà vide', 'info');
            return;
        }
        const modal = document.getElementById('clearCartModal');
        if (modal) {
            modal.classList.remove('opacity-0', 'invisible');
            const box = modal.querySelector('.bg-\\[var\\(--bg-card\\)\\]');
            if (box) box.classList.remove('scale-90');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeClearCartModal = function() {
        const modal = document.getElementById('clearCartModal');
        if (modal) {
            modal.classList.add('opacity-0', 'invisible');
            const box = modal.querySelector('.bg-\\[var\\(--bg-card\\)\\]');
            if (box) box.classList.add('scale-90');
            document.body.style.overflow = '';
        }
    };

    window.confirmClearCart = function() {
        window.cart = [];
        window.saveCart();
        window.renderCart();
        window.closeClearCartModal();
        window.showToast('Panier vidé avec succès', 'info');
    };

    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-item ${type}`;
        
        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        };
        
        toast.innerHTML = `
            <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type] || icons.info}
            </svg>
            <span>${message}</span>
            <button class="toast-close" onclick="this.closest('.toast-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'toastOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 400);
            }
        }, 3500);
    };

    // ================================================================
    //  CONFIRMATION DIALOG
    // ================================================================
    let confirmCallback = null;
    let confirmForm = null;

    window.showConfirmDialog = function(options) {
        const dialog = document.getElementById('confirmDialog');
        const icon = dialog.querySelector('.icon');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmDialogBtn');
        
        icon.className = 'icon';
        icon.classList.add(options.type || 'danger');
        
        if (options.type === 'success') {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            `;
        } else if (options.type === 'warning') {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            `;
        } else {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
        document.body.style.overflow = 'hidden';
    };

    window.closeConfirmDialog = function() {
        document.getElementById('confirmDialog').classList.remove('active');
        document.body.style.overflow = '';
        confirmCallback = null;
        confirmForm = null;
    };

    window.confirmLogout = function(event, form) {
        event.preventDefault();
        window.showConfirmDialog({
            type: 'danger',
            title: 'Confirmation de déconnexion',
            message: 'Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.',
            confirmText: 'Se déconnecter',
            onConfirm: function() {
                if (form) form.submit();
                window.closeConfirmDialog();
            },
            form: form
        });
    };

    // ================================================================
    //  INITIALISATION AU CHARGEMENT
    // ================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // ================================================================
        //  THEME TOGGLE
        // ================================================================
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
        
        const toggle = document.getElementById('theme-toggle');
        const icon = document.getElementById('theme-icon');
        if (toggle && icon) {
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
            
            setTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');
            
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (document.documentElement.classList.contains('dark')) {
                    setTheme('light');
                } else {
                    setTheme('dark');
                }
            });
        }

        // ================================================================
        //  CART - EVENT LISTENERS
        // ================================================================
        
        // Bouton d'ouverture du panier (header)
        document.getElementById('cartToggleBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        // Bouton d'ouverture du panier (mobile)
        document.getElementById('mobileCartToggleBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        // Bouton de fermeture du panier
        document.getElementById('cartCloseBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        // Overlay du panier
        document.getElementById('cartOverlay')?.addEventListener('click', function(e) {
            window.toggleCart();
        });

        // Lien "Passer la commande" - déjà géré par l'URL
        document.getElementById('checkoutLink')?.addEventListener('click', function(e) {
            if (window.cart.length === 0) {
                e.preventDefault();
                window.showToast('Le panier est vide', 'error');
                return;
            }
            // Le lien a déjà le bon href, on le laisse faire
        });

        // Bouton vider le panier
        document.getElementById('clearCartBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.openClearCartModal();
        });

        // Modal vider le panier - Annuler
        document.getElementById('clearCartCancelBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.closeClearCartModal();
        });

        // Modal vider le panier - Confirmer
        document.getElementById('clearCartConfirmBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.confirmClearCart();
        });

        // Fermer la modal en cliquant sur l'overlay
        document.getElementById('clearCartModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeClearCartModal();
            }
        });

        // ================================================================
        //  CONFIRMATION DIALOG - EVENT LISTENERS
        // ================================================================
        
        document.getElementById('confirmCancelBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.closeConfirmDialog();
        });

        document.getElementById('confirmDialogBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            } else if (confirmForm) {
                confirmForm.submit();
            }
            window.closeConfirmDialog();
        });

        document.getElementById('confirmDialog')?.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeConfirmDialog();
            }
        });

        // ================================================================
        //  LOGOUT FORMS - EVENT LISTENERS
        // ================================================================
        
        document.querySelectorAll('.logout-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                window.confirmLogout(e, this);
            });
        });

        // ================================================================
        //  KEYBOARD SHORTCUTS
        // ================================================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeConfirmDialog();
                window.closeClearCartModal();
                const sidebar = document.getElementById('cartSidebar');
                if (sidebar && !sidebar.classList.contains('translate-x-full')) {
                    window.toggleCart();
                }
            }
        });

        // ================================================================
        //  CHARGER LE PANIER
        // ================================================================
        window.loadCart();
    });
    </script>
</body>
</html>