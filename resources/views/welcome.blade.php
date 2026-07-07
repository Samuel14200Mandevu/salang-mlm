<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM - Plateforme E-Commerce & Reseau</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        .welcome-hero {
            background: radial-gradient(ellipse at top, rgba(99,102,241,0.08) 0%, transparent 70%);
        }
        .welcome-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .welcome-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }
        .welcome-card:hover::before {
            opacity: 1;
        }
        .feature-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: var(--radius-full);
            background: rgba(99,102,241,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-500);
            margin: 0 auto;
            transition: all 0.4s ease;
        }
        .feature-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }
        .welcome-card:hover .feature-icon {
            transform: scale(1.1) rotate(-6deg);
            background: rgba(99,102,241,0.2);
        }
        .btn-hero {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
        }
        .btn-hero-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 24px rgba(99,102,241,0.4);
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 40px rgba(99,102,241,0.5);
        }
        .btn-hero-secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }
        .btn-hero-secondary:hover {
            border-color: var(--primary-500);
            color: var(--primary-500);
            transform: translateY(-3px);
        }
        .dark .btn-hero-secondary {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.1);
        }
        .package-badge {
            position: absolute;
            top: -1px;
            right: 1rem;
            padding: 0.25rem 1rem;
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .package-badge-popular {
            background: var(--gradient-primary);
            color: white;
        }
        .package-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            background: rgba(99,102,241,0.08);
            color: var(--primary-500);
            transition: all 0.4s ease;
        }
        .package-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .welcome-card:hover .package-icon {
            transform: scale(1.1) rotate(-6deg);
            background: rgba(99,102,241,0.15);
        }
        .package-features {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0 0;
            text-align: left;
        }
        .package-features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
            padding: 0.15rem 0;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .package-features li svg {
            width: 0.875rem;
            height: 0.875rem;
            flex-shrink: 0;
            color: #22c55e;
        }
        
        @media (min-width: 640px) {
            .btn-hero {
                width: auto;
                padding: 0.875rem 2.5rem;
                font-size: 1.125rem;
            }
            .package-icon {
                width: 3.5rem;
                height: 3.5rem;
                margin-bottom: 0.75rem;
            }
            .package-icon svg {
                width: 1.5rem;
                height: 1.5rem;
            }
            .feature-icon {
                width: 4rem;
                height: 4rem;
            }
            .feature-icon svg {
                width: 1.75rem;
                height: 1.75rem;
            }
            .package-features li {
                font-size: 0.8rem;
            }
            .package-features li svg {
                width: 1rem;
                height: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-hero { padding: 3rem 0; }
            .feature-icon { width: 3rem; height: 3rem; }
            .feature-icon svg { width: 1.25rem; height: 1.25rem; }
            .package-icon { width: 2.5rem; height: 2.5rem; }
            .package-icon svg { width: 1rem; height: 1rem; }
            .package-features li {
                font-size: 0.6rem;
            }
            .package-features li svg {
                width: 0.75rem;
                height: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)]">
    
    <!-- Navigation -->
    <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-50 backdrop-blur-sm bg-opacity-80">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                
               <!-- Logo -->
<div class="flex items-center">
    <a href="/" class="flex items-center gap-2 group">
        <img src="{{ asset('images/salang_logo.png') }}" 
             alt="Salang" 
             class="logo-themeable h-16 sm:h-19 w-auto transition-transform group-hover:scale-105">
        <span class="text-lg sm:text-2xl font-extrabold bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent hidden xs:inline">
            Salang
        </span>
    </a>
</div>
                
                <!-- Actions -->
                <div class="flex items-center gap-1.5 sm:gap-3">
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" 
                            class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors" 
                            aria-label="Changer de theme">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                        </svg>
                    </button>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm hidden xs:inline-flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex xs:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                            <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs sm:text-sm text-[var(--text-secondary)] hover:text-primary-500 transition font-medium hidden xs:inline">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden xs:inline-flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Inscription
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex xs:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                            <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex xs:hidden p-2 rounded-lg bg-primary-500/10 hover:bg-primary-500/20 transition-colors">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-12 sm:py-20 lg:py-32 welcome-hero">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent"></div>
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-fadeInUp">
            
                <h1 class="text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-[var(--text-primary)] leading-tight px-2">
                    Gagnez de l'argent avec
                    <span class="bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent block sm:inline">
                        Salang MLM
                    </span>
                </h1>
                
                <p class="mt-3 sm:mt-4 text-sm sm:text-lg lg:text-xl text-[var(--text-secondary)] max-w-2xl mx-auto px-4">
                    Plateforme E-Commerce et Marketing de Reseau. 
                    Achetez des produits, recrutez des membres, gagnez des commissions.
                </p>
                
                <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 px-4">
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Commencer maintenant
                    </a>
                    <a href="#packages" class="btn-hero btn-hero-secondary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        Voir les packages
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="mt-8 sm:mt-12 grid grid-cols-3 gap-2 sm:gap-4 max-w-xs sm:max-w-lg mx-auto">
                    <div class="text-center">
                        <p class="text-lg sm:text-2xl font-bold text-primary-500">500+</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Membres actifs</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg sm:text-2xl font-bold text-primary-500">$2M+</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commissions versees</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg sm:text-2xl font-bold text-primary-500">50+</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Pays representes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages -->
    <section id="packages" class="py-12 sm:py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12 animate-fadeInUp">
                <span class="inline-block px-3 sm:px-4 py-1 sm:py-1.5 rounded-full bg-primary-500/10 text-primary-500 text-[10px] sm:text-sm font-semibold mb-3 sm:mb-4 border border-primary-500/20">
                    Nos offres
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">Choisissez votre package</h2>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2 px-4">
                    Commencez avec le package qui correspond a vos objectifs
                </p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 md:gap-6">
                @php
                    $packages = [
                        [
                            'name' => 'Starter', 
                            'price' => 30, 
                            'pv' => 0, 
                            'popular' => false,
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>'
                        ],
                        [
                            'name' => 'Silver', 
                            'price' => 85, 
                            'pv' => 0, 
                            'popular' => false,
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                        ],
                        [
                            'name' => 'Bronze', 
                            'price' => 350, 
                            'pv' => 200, 
                            'popular' => false,
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'
                        ],
                        [
                            'name' => 'Gold', 
                            'price' => 1450, 
                            'pv' => 1000, 
                            'popular' => true,
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'
                        ],
                        [
                            'name' => 'Emerald', 
                            'price' => 4850, 
                            'pv' => 3800, 
                            'popular' => false,
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>'
                        ],
                    ];
                @endphp
                
                @foreach($packages as $index => $package)
                    <div class="welcome-card text-center p-3 sm:p-6 animate-fadeInUp delay-{{ $index + 1 }}">
                        @if($package['popular'])
                            <span class="package-badge package-badge-popular text-[8px] sm:text-[10px]">
                                Populaire
                            </span>
                        @endif
                        <div class="package-icon mx-auto">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $package['icon'] !!}
                            </svg>
                        </div>
                        <h3 class="text-sm sm:text-xl font-bold text-[var(--text-primary)]">{{ $package['name'] }}</h3>
                        <p class="text-lg sm:text-3xl font-bold text-primary-500 mt-1 sm:mt-2">
                            ${{ $package['price'] }}
                        </p>
                        <p class="text-[10px] sm:text-sm text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                            {{ $package['pv'] }} PV
                        </p>
                        <ul class="package-features">
                            <li>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Commission 30%
                            </li>
                            <li>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $package['pv'] }} PV
                            </li>
                            <li>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Acces boutique
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary w-full mt-2 sm:mt-4 text-[10px] sm:text-sm py-1.5 sm:py-2">
                            Demarrer
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12 animate-fadeInUp">
                <span class="inline-block px-3 sm:px-4 py-1 sm:py-1.5 rounded-full bg-primary-500/10 text-primary-500 text-[10px] sm:text-sm font-semibold mb-3 sm:mb-4 border border-primary-500/20">
                    Comment ca marche
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">3 etapes pour reussir</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8">
                
                <div class="text-center animate-fadeInUp delay-1 px-4 sm:px-0">
                    <div class="feature-icon">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)] mt-3 sm:mt-4">Inscription</h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1">
                        Creez votre compte avec un parrain et rejoignez la communaute
                    </p>
                </div>
                
                <div class="text-center animate-fadeInUp delay-2 px-4 sm:px-0">
                    <div class="feature-icon">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)] mt-3 sm:mt-4">Achetez un package</h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1">
                        Choisissez votre package et commencez a gagner des commissions
                    </p>
                </div>
                
                <div class="text-center animate-fadeInUp delay-3 px-4 sm:px-0">
                    <div class="feature-icon">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)] mt-3 sm:mt-4">Gagnez des commissions</h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1">
                        Recrutez et gagnez sur tout votre reseau jusqu'au niveau 3
                    </p>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-3 sm:gap-4">
                
                <!-- Logo dans le footer -->
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/salang_logo.png') }}" 
                         alt="Salang" 
                         class="logo-themeable h-10 sm:h-14 w-auto">
                    <span class="font-bold text-[var(--text-primary)] text-sm sm:text-base">Salang Group</span>
                </div>
                
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] text-center">
                    &copy; {{ date('Y') }} Salang Group. Tous droits reserves.
                </p>
                
                <div class="flex gap-3 sm:gap-4 text-xs sm:text-sm text-[var(--text-secondary)]">
                    <a href="#" class="hover:text-primary-500 transition">Mentions</a>
                    <a href="#" class="hover:text-primary-500 transition">Confidentialite</a>
                    <a href="#" class="hover:text-primary-500 transition hidden xs:inline">Contact</a>
                </div>
                
            </div>
        </div>
    </footer>

    @livewireScripts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</body>
</html>