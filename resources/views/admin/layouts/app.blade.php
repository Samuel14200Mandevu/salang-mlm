<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Salang MLM')</title>
    
    <!-- ===== META TAGS POUR MOBILE ET PWA ===== -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="theme-color" content="#5ab638">
    
    <!-- ===== FAVICON ===== -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#5ab638">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
    
    <!-- ===== PWA ===== -->
    {!! PwaKit::head() !!}
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] transition-colors duration-200 antialiased">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: window.innerWidth > 768 }" x-init="sidebarOpen = window.innerWidth > 768">
        
        <!-- ============================================================ -->
        <!-- SIDEBAR ADMIN -->
        <!-- ============================================================ -->
        <aside id="sidebar" class="fixed top-0 left-0 z-50 h-full transition-all duration-300 ease-in-out" 
               :class="sidebarOpen ? 'w-64' : 'w-20'">
            
            <div class="h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden">
                
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 border-b border-[var(--border-color)] flex-shrink-0">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center w-full">
                        <div class="logo-light transition-all duration-300" :class="sidebarOpen ? 'scale-100' : 'scale-90'">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" 
                                 :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
                        <div class="logo-dark transition-all duration-300" :class="sidebarOpen ? 'scale-100' : 'scale-90'">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" 
                                 class="transition-all duration-300" 
                                 :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'">
                        </div>
                    </a>
                </div>

                <!-- Menu Admin -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar">
                    <ul class="space-y-1">
                        
                        <!-- Dashboard Admin -->
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Dashboard Admin
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : GESTION ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Gestion
                            </div>
                        </li>

                        <!-- Utilisateurs -->
                        <li>
                            <a href="{{ route('admin.users') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.users*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Utilisateurs
                                </span>
                            </a>
                        </li>

                        <!-- Packages -->
                        <li>
                            <a href="{{ route('admin.packages') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.packages*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Packages
                                </span>
                            </a>
                        </li>

                        <!-- Produits -->
                        <li>
                            <a href="{{ route('admin.products') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.products*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Produits
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : FINANCES ADMIN ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Finances
                            </div>
                        </li>

                        <!-- Commissions Admin -->
                        <li>
                            <a href="{{ route('admin.commissions') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.commissions') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Commissions
                                </span>
                            </a>
                        </li>

                        <!-- Portefeuilles -->
                        <li>
                            <a href="{{ route('admin.wallets') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.wallets') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Portefeuilles
                                </span>
                            </a>
                        </li>

                        <!-- Retraits Admin -->
                        <li>
                            <a href="{{ route('admin.withdrawals') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.withdrawals*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Retraits
                                </span>
                            </a>
                        </li>

                        <!-- KYC Admin -->
                        <li>
                            <a href="{{ route('admin.kyc') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.kyc*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    KYC
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : RANGS ADMIN ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Rangs
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.ranks') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.ranks*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Gestion des Rangs
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : RAPPORTS ADMIN ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Rapports
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.reports') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.reports*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Rapports
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : ADMINISTRATION ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Administration
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('admin.settings') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.settings*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Paramètres
                                </span>
                            </a>
                        </li>

                        <!-- ===== RETOUR AU SITE ===== -->
                        <li class="pt-4 mt-4 border-t border-[var(--border-color)]">
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Voir le site
                                </span>
                            </a>
                        </li>

                    </ul>
                </nav>

                <!-- Footer sidebar -->
                <div class="p-4 border-t border-[var(--border-color)] flex-shrink-0">
                    <div class="flex items-center justify-center lg:justify-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="transition-all duration-300 overflow-hidden" 
                             :class="sidebarOpen ? 'opacity-100 max-w-xs' : 'opacity-0 max-w-0'">
                            <p class="text-sm font-medium text-[var(--text-primary)] truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-xs text-[var(--text-secondary)] truncate">Administrateur</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ============================================================ -->
        <!-- CONTENU PRINCIPAL -->
        <!-- ============================================================ -->
        <div class="flex-1 transition-all duration-300 ease-in-out" 
             :style="sidebarOpen ? 'margin-left: 16rem;' : 'margin-left: 5rem;'">
            
            <!-- Top Navigation -->
            <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center gap-3">
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <div>
                                @if(isset($header))
                                    {{ $header }}
                                @else
                                    <h1 class="text-xl font-semibold text-[var(--text-primary)]">Administration</h1>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <button id="theme-toggle" class="p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>
                            
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                        <span class="hidden sm:inline text-sm text-[var(--text-primary)]">{{ Auth::user()->name }}</span>
                                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm">
                                            @if(Auth::user()->avatar)
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                                     alt="Avatar" 
                                                     class="w-8 h-8 rounded-full object-cover">
                                            @else
                                                {{ substr(Auth::user()->name, 0, 1) }}
                                            @endif
                                        </div>
                                    </button>
                                    <div x-show="open" @click.away="open = false" 
                                         class="absolute right-0 mt-2 w-48 bg-[var(--bg-secondary)] rounded-xl shadow-lg py-1 border border-[var(--border-color)]">
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Dashboard Admin</a>
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Voir le site</a>
                                        <hr class="border-[var(--border-color)]">
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]"> Mon Profil</a>
                                        <hr class="border-[var(--border-color)]">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-[var(--bg-primary)] text-red-500">Déconnexion</button>
                                        </form>
                                    </div>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenu -->
            <main class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-4">
                <div class="max-w-7xl mx-auto px-4 text-center text-[var(--text-secondary)] text-sm">
                    <div class="flex justify-center items-center gap-2">
                        <div class="logo-light">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Logo" class="h-6 w-auto">
                        </div>
                        <div class="logo-dark">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Logo" class="h-6 w-auto">
                        </div>
                        <span>&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</span>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    
    <!-- ===== PWA SCRIPTS ===== -->
    {!! PwaKit::scripts() !!}
    @stack('scripts')
</body>
</html>