<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM</title>
    
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="theme-color" content="#5ab638">
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#5ab638">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
    
    @if(class_exists('PwaKit'))
        {!! PwaKit::head() !!}
    @endif
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
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

        /* ===== TOAST CUSTOM ===== */
        .custom-toast {
            animation: slideUp 0.3s ease forwards;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767px) {
            .mobile-bottom-nav {
                display: flex;
            }
            main {
                padding-bottom: 80px !important;
            }
            footer {
                padding-bottom: 80px !important;
            }
        }
    </style>
</head>
<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] transition-colors duration-200 antialiased">
    <div class="min-h-screen flex" 
         x-data="{ 
            sidebarOpen: window.innerWidth > 1024, 
            notificationOpen: false,
            profileOpen: false,
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
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center flex-1">
                        <div class="logo-light transition-all duration-300" 
                             :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" 
                                 :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
                        <div class="logo-dark transition-all duration-300" 
                             :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" 
                                 :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
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
                            <a href="{{ route('dashboard') }}" 
                               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Dashboard
                                </span>
                            </a>
                        </li>

                        <!-- Boutique -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Shop
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('products.index') }}" 
                               class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Products
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('subscriptions.index') }}" 
                               class="sidebar-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Packages
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('orders.index') }}" 
                               class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    My Orders
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cart.index') }}" 
                               class="sidebar-link {{ request()->routeIs('cart.index') ? 'active' : '' }}">
                                <div class="relative">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    @php
                                        $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0;
                                    @endphp
                                    @if($cartCount > 0)
                                        <span class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                                        </span>
                                    @endif
                                </div>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Cart
                                </span>
                            </a>
                        </li>

                        <!-- Network -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Network
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('network.index') }}" 
                               class="sidebar-link {{ request()->routeIs('network.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    My Network
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('rank.index') }}" 
                               class="sidebar-link {{ request()->routeIs('rank.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    My Rank
                                </span>
                            </a>
                        </li>

                        <!-- Finances -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Finances
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('wallet.index') }}" 
                               class="sidebar-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Wallet
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('commissions.index') }}" 
                               class="sidebar-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    My Commissions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('withdrawal.index') }}" 
                               class="sidebar-link {{ request()->routeIs('withdrawal.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Withdrawals
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('kyc.index') }}" 
                               class="sidebar-link {{ request()->routeIs('kyc.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    KYC Verification
                                </span>
                            </a>
                        </li>

                        <!-- Reports -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Reports
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('report.index') }}" 
                               class="sidebar-link {{ request()->routeIs('report.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Reports
                                </span>
                            </a>
                        </li>

                        <!-- Admin (visible uniquement pour les admins) -->
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
                                            Admin Panel
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth
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
                                @auth {{ Auth::user()->email }} @endauth
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 transition-all duration-300 ease-in-out w-full"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 5rem)' : '100%'
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
                                @if(isset($header))
                                    <div class="truncate">{{ $header }}</div>
                                @else
                                    <h1 class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">Dashboard</h1>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-4 flex-shrink-0">
                            
                            <!-- Cart -->
                            <a href="{{ route('cart.index') }}" 
                               class="relative p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors group">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @php
                                    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0;
                                @endphp
                                @if($cartCount > 0)
                                    <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full" id="cartCount">
                                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                                    </span>
                                @endif
                            </a>

                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false, unreadCount: 3 }">
                                <button @click="open = !open" 
                                        class="relative p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                                          class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-primary-500 rounded-full">
                                        3
                                    </span>
                                </button>

                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-80 md:w-96 bg-[var(--bg-card)] rounded-xl shadow-lg py-2 border border-[var(--border-color)] max-h-[80vh] overflow-y-auto z-50"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     style="display: none;">
                                    
                                    <div class="px-3 sm:px-4 py-2 border-b border-[var(--border-color)] flex justify-between items-center">
                                        <h4 class="font-semibold text-sm sm:text-base text-[var(--text-primary)]">Notifications</h4>
                                        <a href="{{ route('message.index') }}" class="text-xs text-primary-500 hover:text-primary-600 transition font-medium">View all</a>
                                    </div>

                                    <div class="divide-y divide-[var(--border-color)]" id="notificationList">
                                        <div class="px-3 sm:px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="1">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">New Commission</p>
                                            <p class="text-xs text-[var(--text-secondary)]">You received $25.00 in direct commission</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">2 hours ago</p>
                                        </div>
                                        
                                        <div class="px-3 sm:px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="2">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">New Downline</p>
                                            <p class="text-xs text-[var(--text-secondary)]">Jean Dupont registered with your link</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">5 hours ago</p>
                                        </div>
                                        
                                        <div class="px-3 sm:px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="3">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">Rank Promotion</p>
                                            <p class="text-xs text-[var(--text-secondary)]">Congratulations! You are now Manager</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">1 day ago</p>
                                        </div>
                                    </div>

                                    <div class="px-3 sm:px-4 py-2 border-t border-[var(--border-color)] text-center">
                                        <button @click="markAllAsRead()" 
                                                class="text-xs text-primary-500 hover:text-primary-600 transition font-medium hover:underline cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Theme Toggle -->
                            <button id="theme-toggle" 
                                    class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
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
                                        
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                </svg>
                                                Dashboard
                                            </span>
                                        </a>
                                        
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Profile
                                            </span>
                                        </a>
                                        
                                        <a href="{{ route('subscriptions.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                                </svg>
                                                Packages
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
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-red-500 transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Logout
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
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-1 sm:gap-2">
                        <div class="flex items-center gap-2">
                            <div class="logo-light">
                                <img src="{{ asset('images/light_logo.jpeg') }}" alt="Logo" class="h-5 sm:h-6 w-auto">
                            </div>
                            <div class="logo-dark">
                                <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Logo" class="h-5 sm:h-6 w-auto">
                            </div>
                            <span>&copy; {{ date('Y') }} Salang Group.</span>
                        </div>
                        <span class="hidden xs:inline">All rights reserved.</span>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Mobile Bottom Nav -->
        <nav class="mobile-bottom-nav" id="mobileBottomNav">
            <a href="{{ route('dashboard') }}" 
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Home</span>
            </a>

            <a href="{{ route('products.index') }}" 
               class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Shop</span>
                @php $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0; @endphp
                @if($cartCount > 0)
                    <span class="badge-count">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>

            <a href="{{ route('network.index') }}" 
               class="nav-item {{ request()->routeIs('network.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Network</span>
            </a>

            <a href="{{ route('profile.index') }}" 
               class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profile</span>
            </a>
        </nav>

    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    
    @if(class_exists('PwaKit'))
        {!! PwaKit::scripts() !!}
    @endif
    
    <!-- Cookie Consent -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('cookie_consent')) {
            const banner = document.createElement('div');
            banner.id = 'cookie-consent-banner';
            banner.style.cssText = `
                position: fixed;
                bottom: 60px;
                left: 0;
                right: 0;
                background: var(--bg-card);
                border-top: 1px solid var(--border-color);
                padding: 0.75rem 1rem;
                z-index: 9999;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                box-shadow: 0 -4px 24px rgba(0,0,0,0.1);
            `;
            
            banner.innerHTML = `
                <div style="flex: 1; min-width: 150px; text-align: center; font-size: 0.75rem; color: var(--text-secondary);">
                    We use cookies.
                    <a href="{{ route('cookie-policy') }}" style="color: var(--primary-500); text-decoration: underline; white-space: nowrap;">
                        Learn more
                    </a>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                    <button onclick="acceptCookies()" style="
                        padding: 0.375rem 1.25rem;
                        border-radius: var(--radius-md);
                        background: var(--gradient-primary);
                        color: white;
                        border: none;
                        font-weight: 600;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        white-space: nowrap;
                    ">
                        Accept
                    </button>
                    <button onclick="rejectCookies()" style="
                        padding: 0.375rem 1.25rem;
                        border-radius: var(--radius-md);
                        background: transparent;
                        color: var(--text-secondary);
                        border: 1px solid var(--border-color);
                        font-weight: 600;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        white-space: nowrap;
                    ">
                        Reject
                    </button>
                </div>
            `;
            document.body.appendChild(banner);
        }
    });

    function acceptCookies() {
        localStorage.setItem('cookie_consent', 'accepted');
        document.getElementById('cookie-consent-banner').remove();
    }

    function rejectCookies() {
        localStorage.setItem('cookie_consent', 'rejected');
        document.getElementById('cookie-consent-banner').remove();
    }
    </script>
    
    <!-- Notifications Script -->
    <script>
    function markAllAsRead() {
        const items = document.querySelectorAll('.notification-item');
        const badge = document.querySelector('[x-show="unreadCount > 0"]');
        
        items.forEach(item => {
            item.style.opacity = '0.5';
            item.style.backgroundColor = 'var(--bg-secondary)';
        });
        
        if (badge) {
            badge.textContent = '0';
            badge.style.display = 'none';
        }
        
        showToast('All notifications marked as read', 'success');
    }

    function showToast(message, type = 'success') {
        document.querySelectorAll('.custom-toast').forEach(el => el.remove());
        
        const toast = document.createElement('div');
        toast.className = `custom-toast fixed bottom-20 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.style.animation = 'fadeInUp 0.3s ease forwards';
        toast.style.fontSize = '0.875rem';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                this.style.opacity = '0.5';
                this.style.backgroundColor = 'var(--bg-secondary)';
            });
        });

        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                document.documentElement.classList.toggle('dark');
                const icon = document.getElementById('theme-icon');
                if (document.documentElement.classList.contains('dark')) {
                    icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
                } else {
                    icon.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
                }
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            });
        }
        
        // Restore theme
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
            }
        }
    });
    </script>
    
    @stack('scripts')
</body>
</html>