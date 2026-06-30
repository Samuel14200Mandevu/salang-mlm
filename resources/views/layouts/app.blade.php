<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM</title>
    
    <!-- ===== META TAGS POUR MOBILE ET PWA ===== -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="theme-color" content="#6366f1">
    
    <!-- ===== FAVICON ===== -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#6366f1">
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
    <div class="min-h-screen flex" x-data="{ sidebarOpen: window.innerWidth > 768, notificationOpen: false }" x-init="sidebarOpen = window.innerWidth > 768">
        
        <!-- ============================================================ -->
        <!-- SIDEBAR UTILISATEUR -->
        <!-- ============================================================ -->
        <aside id="sidebar" class="fixed top-0 left-0 z-50 h-full transition-all duration-300 ease-in-out" 
               :class="sidebarOpen ? 'w-64' : 'w-20'">
            
            <div class="h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden">
                
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 border-b border-[var(--border-color)] flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full">
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

                <!-- Menu -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar">
                    <ul class="space-y-1">
                        
                        <!-- ========================================================== -->
                        <!-- DASHBOARD -->
                        <!-- ========================================================== -->
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('dashboard') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Dashboard
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : BOUTIQUE -->
                        <!-- ========================================================== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Boutique
                            </div>
                        </li>

                        <!-- Produits -->
                        <li>
                            <a href="{{ route('products.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('products.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Produits
                                </span>
                            </a>
                        </li>

                        <!-- Abonnements -->
                        <li>
                            <a href="{{ route('subscriptions.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('subscriptions.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Packages
                                </span>
                            </a>
                        </li>

                        <!-- Mes Commandes -->
                        <li>
                            <a href="{{ route('orders.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('orders.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mes Commandes
                                </span>
                            </a>
                        </li>

                        <!-- Panier -->
                        <li>
                            <a href="{{ route('cart.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('cart.index') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Panier
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : RÉSEAU -->
                        <!-- ========================================================== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Réseau
                            </div>
                        </li>

                        <!-- Mon Réseau -->
                        <li>
                            <a href="{{ route('network.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('network.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mon Réseau
                                </span>
                            </a>
                        </li>

                        <!-- Mon Grade -->
                        <li>
                            <a href="{{ route('rank.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('rank.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mon Grade
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : FINANCES -->
                        <!-- ========================================================== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Finances
                            </div>
                        </li>

                        <!-- Portefeuille -->
                        <li>
                            <a href="{{ route('wallet.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('wallet.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Portefeuille
                                </span>
                            </a>
                        </li>

                        <!-- Mes Commissions -->
                        <li>
                            <a href="{{ route('commissions.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('commissions.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mes Commissions
                                </span>
                            </a>
                        </li>

                        <!-- Vérification KYC -->
                        <li>
                            <a href="{{ route('kyc.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('kyc.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Vérification KYC
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : RAPPORTS -->
                        <!-- ========================================================== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Rapports
                            </div>
                        </li>

                        <!-- Rapports -->
                        <li>
                            <a href="{{ route('report.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('report.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Rapports
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : SERVICES -->
                        <!-- ========================================================== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Services
                            </div>
                        </li>

                        <!-- Événements -->
                        <li>
                            <a href="{{ route('events.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('events.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Événements
                                </span>
                            </a>
                        </li>

                        <!-- Centre de tickets -->
                        <li>
                            <a href="{{ route('ticket.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('ticket.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Centre de tickets
                                </span>
                            </a>
                        </li>

                        <!-- Centre de messages -->
                        <li>
                            <a href="{{ route('message.index') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('message.*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Centre de messages
                                </span>
                            </a>
                        </li>

                        <!-- ========================================================== -->
                        <!-- SECTION : ADMINISTRATION -->
                        <!-- ========================================================== -->
                        @if(Auth::check() && Auth::user()->hasRole('admin'))
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                 Administration
                            </div>
                        </li>

                        <!-- Dashboard Admin -->
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Dashboard Admin
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des utilisateurs -->
                        <li>
                            <a href="{{ route('admin.users') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.users*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Utilisateurs
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des packages -->
                        <li>
                            <a href="{{ route('admin.packages') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.packages*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Packages
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des produits -->
                        <li>
                            <a href="{{ route('admin.products') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.products*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Produits
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des commissions -->
                        <li>
                            <a href="{{ route('admin.commissions') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.commissions') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Commissions
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des portefeuilles -->
                        <li>
                            <a href="{{ route('admin.wallets') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.wallets') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Portefeuilles
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des retraits -->
                        <li>
                            <a href="{{ route('admin.withdrawals') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.withdrawals*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Retraits
                                </span>
                            </a>
                        </li>

                        <!-- Gestion des rangs -->
                        <li>
                            <a href="{{ route('admin.ranks') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.ranks*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Rangs
                                </span>
                            </a>
                        </li>

                        <!-- KYC Admin -->
                        <li>
                            <a href="{{ route('admin.kyc') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.kyc*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    KYC
                                </span>
                            </a>
                        </li>

                        <!-- Rapports Admin -->
                        <li>
                            <a href="{{ route('admin.reports') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.reports*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Rapports
                                </span>
                            </a>
                        </li>

                        <!-- Paramètres Admin -->
                        <li>
                            <a href="{{ route('admin.settings') }}" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group {{ request()->routeIs('admin.settings*') ? 'bg-primary-500/10 text-primary-600' : 'text-[var(--text-secondary)]' }}">
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
                        @endif

                    </ul>
                </nav>

                <!-- Footer sidebar -->
                <div class="p-4 border-t border-[var(--border-color)] flex-shrink-0">
                    <div class="flex items-center justify-center lg:justify-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="transition-all duration-300 overflow-hidden" 
                             :class="sidebarOpen ? 'opacity-100 max-w-xs' : 'opacity-0 max-w-0'">
                            <p class="text-sm font-medium text-[var(--text-primary)] truncate">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()->rank ?? 'Distributor' }}</p>
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
                            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <div>
                                @if(isset($header))
                                    {{ $header }}
                                @else
                                    <h1 class="text-xl font-semibold text-[var(--text-primary)]">Dashboard</h1>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <!-- ===== ICÔNE PANIER AVEC COMPTEUR ===== -->
                            <a href="{{ route('cart.index') }}" class="relative p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors group">
                                <svg class="w-6 h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @php
                                    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0;
                                @endphp
                                @if($cartCount > 0)
                                    <span class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse" id="cartCount">
                                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                                    </span>
                                @endif
                            </a>

                            <!-- ===== ICÔNE NOTIFICATIONS ===== -->
                            <div class="relative" x-data="{ open: false, unreadCount: 3 }">
                                <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                    <svg class="w-6 h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                                          class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-500 rounded-full">
                                        3
                                    </span>
                                </button>

                                <!-- Dropdown des notifications -->
                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-[var(--bg-card)] rounded-xl shadow-lg py-2 border border-[var(--border-color)] max-h-96 overflow-y-auto z-50"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     style="display: none;">
                                    
                                    <div class="px-4 py-2 border-b border-[var(--border-color)] flex justify-between items-center">
                                        <h4 class="font-semibold text-[var(--text-primary)]"> Notifications</h4>
                                        <a href="{{ route('message.index') }}" class="text-xs text-primary-500 hover:text-primary-600 transition">Voir tout</a>
                                    </div>

                                    <div class="divide-y divide-[var(--border-color)]" id="notificationList">
                                        <!-- Notification 1 -->
                                        <div class="px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="1">
                                            <p class="text-sm font-medium text-[var(--text-primary)]"> Nouvelle commission</p>
                                            <p class="text-xs text-[var(--text-secondary)]">Vous avez reçu $25.00 de commission directe</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">Il y a 2 heures</p>
                                        </div>
                                        
                                        <!-- Notification 2 -->
                                        <div class="px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="2">
                                            <p class="text-sm font-medium text-[var(--text-primary)]"> Nouveau filleul</p>
                                            <p class="text-xs text-[var(--text-secondary)]">Jean Dupont s'est inscrit avec votre lien</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">Il y a 5 heures</p>
                                        </div>
                                        
                                        <!-- Notification 3 -->
                                        <div class="px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="3">
                                            <p class="text-sm font-medium text-[var(--text-primary)]"> Promotion de rang</p>
                                            <p class="text-xs text-[var(--text-secondary)]">Félicitations ! Vous êtes maintenant Manager</p>
                                            <p class="text-xs text-[var(--text-tertiary)] mt-1">Il y a 1 jour</p>
                                        </div>
                                    </div>

                                    <div class="px-4 py-2 border-t border-[var(--border-color)] text-center">
                                        <button @click="markAllAsRead()" 
                                                class="text-xs text-primary-500 hover:text-primary-600 transition font-medium hover:underline cursor-pointer">
                                            Marquer tout comme lu
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== BOUTON THÈME ===== -->
                            <button id="theme-toggle" class="p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>

                            <!-- ===== DROPDOWN PROFIL ===== -->
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
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Dashboard</a>
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Profil</a>
                                        <a href="{{ route('subscriptions.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]"> Packages</a>
                                        
                                        @if(Auth::user()->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-primary-600 font-semibold">
                                                 Administration
                                            </a>
                                        @endif
                                        
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
    
    <!-- ===== COOKIE CONSENT ===== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('cookie_consent')) {
            const banner = document.createElement('div');
            banner.id = 'cookie-consent-banner';
            banner.style.cssText = `
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--bg-card);
                border-top: 1px solid var(--border-color);
                padding: 1rem 2rem;
                z-index: 9999;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                box-shadow: 0 -4px 24px rgba(0,0,0,0.1);
            `;
            banner.innerHTML = `
                <div style="flex: 1; min-width: 200px;">
                    <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                        🍪 Nous utilisons des cookies pour améliorer votre expérience.
                        <a href="{{ route('cookie-policy') }}" style="color: var(--primary-500); text-decoration: underline;">
                            En savoir plus
                        </a>
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="acceptCookies()" style="
                        padding: 0.5rem 1.5rem;
                        border-radius: var(--radius-md);
                        background: var(--gradient-primary);
                        color: white;
                        border: none;
                        font-weight: 600;
                        font-size: 0.875rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">
                        Accepter
                    </button>
                    <button onclick="rejectCookies()" style="
                        padding: 0.5rem 1.5rem;
                        border-radius: var(--radius-md);
                        background: transparent;
                        color: var(--text-secondary);
                        border: 1px solid var(--border-color);
                        font-weight: 600;
                        font-size: 0.875rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">
                        Refuser
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
    
    <!-- ===== SCRIPT NOTIFICATIONS ===== -->
    <script>
    // Fonction pour marquer toutes les notifications comme lues
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
        
        // Afficher un message de confirmation
        showToast('✅ Toutes les notifications ont été marquées comme lues', 'success');
    }

    // Fonction pour afficher un toast
    function showToast(message, type = 'success') {
        document.querySelectorAll('.custom-toast').forEach(el => el.remove());
        
        const toast = document.createElement('div');
        toast.className = `custom-toast fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50 transform transition-all duration-500 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.style.animation = 'fadeInUp 0.3s ease forwards';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // Marquer une notification comme lue au clic
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                this.style.opacity = '0.5';
                this.style.backgroundColor = 'var(--bg-secondary)';
            });
        });
    });
    </script>
    
    @stack('scripts')
</body>
</html>