<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Salang MLM')</title>
    
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
    
    {!! PwaKit::head() !!}
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
        /* ===== SIDEBAR LINKS - CORRECTION ALIGNEMENT ===== */
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

        <!-- Sidebar Admin -->
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
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center flex-1">
                        <div class="logo-light transition-all duration-300" 
                             :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
                        <div class="logo-dark transition-all duration-300" 
                             :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" 
                            class="lg:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                        <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Menu Admin -->
                <nav class="flex-1 overflow-y-auto py-4 px-2 custom-scrollbar">
                    <ul class="space-y-0.5">
                        
                        <!-- Dashboard Admin -->
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Dashboard Admin
                                </span>
                            </a>
                        </li>

                        <!-- Section : Gestion -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Gestion
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.users') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Utilisateurs
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.packages') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
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
                            <a href="{{ route('admin.products') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Produits
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.kyc') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.kyc*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    KYC
                                </span>
                            </a>
                        </li>

                        <!-- Section : Finances -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Finances
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.commissions') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.commissions') ? 'active' : '' }}">
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
                            <a href="{{ route('admin.wallets') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.wallets') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Portefeuilles
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.withdrawals') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.withdrawals*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Retraits
                                </span>
                            </a>
                        </li>

                        <!-- Section : Rangs -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Rangs
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.ranks') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.ranks*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Gestion des Rangs
                                </span>
                            </a>
                        </li>

                        <!-- Section : Rapports -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Rapports
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.reports') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Rapports
                                </span>
                            </a>
                        </li>

                        <!-- Section : Administration -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Administration
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.settings') }}" 
                               class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Parametres
                                </span>
                            </a>
                        </li>

                        <!-- Retour au site -->
                        <li class="pt-4 mt-4 border-t border-[var(--border-color)]">
                            <a href="{{ route('dashboard') }}" 
                               class="sidebar-link text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Voir le site
                                </span>
                            </a>
                        </li>

                    </ul>
                </nav>

                <!-- Profil sidebar -->
                <div class="p-4 border-t border-[var(--border-color)] flex-shrink-0">
                    <div class="flex items-center gap-3" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            @auth
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                         alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    {{ substr(Auth::user()->name, 0, 1) }}
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

        <!-- Contenu principal -->
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
                                    <div class="truncate text-sm sm:text-base">{{ $header }}</div>
                                @else
                                    <h1 class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">Administration</h1>
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
                            
                            <!-- Profil dropdown -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                        <span class="hidden sm:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[60px] md:max-w-[100px]">
                                            {{ Auth::user()->name }}
                                        </span>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-xs sm:text-sm flex-shrink-0">
                                            @if(Auth::user()->avatar)
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                                     alt="Avatar" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover">
                                            @else
                                                {{ substr(Auth::user()->name, 0, 1) }}
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
                                        
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)]">Dashboard Admin</a>
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)]">Voir le site</a>
                                        <hr class="border-[var(--border-color)]">
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)]">Mon Profil</a>
                                        <hr class="border-[var(--border-color)]">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-red-500">Deconnexion</button>
                                        </form>
                                    </div>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenu -->
            <main class="p-3 sm:p-4 md:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-3 sm:py-4">
                <div class="max-w-7xl mx-auto px-3 sm:px-4 text-center text-[var(--text-secondary)] text-xs sm:text-sm">
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-1 sm:gap-2">
                        <span>&copy; {{ date('Y') }} Salang Group. Tous droits reserves.</span>
                    </div>
                </div>
            </footer>
        </div>

        <!-- ===== MENU MOBILE EN BAS POUR ADMIN ===== -->
        <nav class="mobile-bottom-nav" id="mobileBottomNav">
            <!-- 1: Dashboard Admin -->
            <a href="{{ route('admin.dashboard') }}" 
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- 2: Utilisateurs -->
            <a href="{{ route('admin.users') }}" 
               class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Utilisateurs</span>
            </a>

            <!-- 3: Commissions ou Packages -->
            <a href="{{ route('admin.commissions') }}" 
               class="nav-item {{ request()->routeIs('admin.commissions') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Commissions</span>
            </a>

            <!-- 4: Profil -->
            <a href="{{ route('profile.index') }}" 
               class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profil</span>
            </a>
        </nav>

    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    {!! PwaKit::scripts() !!}
    @stack('scripts')
</body>
</html>