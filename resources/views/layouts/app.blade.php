<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] transition-colors duration-200 antialiased">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: window.innerWidth > 768 }" x-init="sidebarOpen = window.innerWidth > 768">
        
        <!-- ============================================================ -->
        <!-- SIDEBAR -->
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
                        
                        <!-- Dashboard -->
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

                        <!-- ===== SECTION : RÉSEAU ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Réseau
                            </div>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium flex-1 text-left whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Team
                                </span>
                                <!-- Flèche masquée quand sidebar est réduite -->
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" 
                                     :class="{
                                         'rotate-180': open,
                                         'hidden': !sidebarOpen
                                     }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">My Organization</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Signup a lead</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Tree View</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Advanced Genealogy</span></a></li>
                            </ul>
                        </li>

                        <!-- ===== SECTION : FINANCES ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Finances
                            </div>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium flex-1 text-left whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Wallet
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" 
                                     :class="{
                                         'rotate-180': open,
                                         'hidden': !sidebarOpen
                                     }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Fund Transfer</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">E-Wallet Management</span></a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="#" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Withdrawal Request
                                </span>
                            </a>
                        </li>

                        <!-- ===== SECTION : RAPPORTS ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Rapports
                            </div>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium flex-1 text-left whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Report
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" 
                                     :class="{
                                         'rotate-180': open,
                                         'hidden': !sidebarOpen
                                     }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">E Wallet History</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Cash Wallet History</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Withdrawal History</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">My Transactions History</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">My PV Details</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Package History</span></a></li>
                                <li><a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-all text-[var(--text-secondary)] text-sm"><span class="w-5 flex-shrink-0">•</span><span class="whitespace-nowrap" :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">Downline Sales Report</span></a></li>
                            </ul>
                        </li>

                        <!-- ===== SECTION : SERVICES ===== -->
                        <li>
                            <div class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wider px-3 py-2"
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Services
                            </div>
                        </li>

                        <li>
                            <a href="#" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Events
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="#" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Ticket Center
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="#" class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-secondary)] transition-all group text-[var(--text-secondary)]">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Message Center
                                </span>
                            </a>
                        </li>

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
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Dashboard</a>
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Profil</a>
                                        <a href="{{ route('packages.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-primary)] text-[var(--text-primary)]">Packages</a>
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
</body>
</html>
