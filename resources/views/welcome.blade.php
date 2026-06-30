<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM - Plateforme E-Commerce & Réseau</title>
    
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
            width: 4rem;
            height: 4rem;
            border-radius: var(--radius-full);
            background: rgba(99,102,241,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-500);
            margin: 0 auto;
            transition: all 0.4s ease;
        }
        .welcome-card:hover .feature-icon {
            transform: scale(1.1) rotate(-6deg);
            background: rgba(99,102,241,0.2);
        }
        .btn-hero {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2.5rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1.125rem;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
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
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)]">
    
    <!-- Navigation -->
    <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-50 backdrop-blur-sm bg-opacity-80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-2 group">
                        <div class="logo-light">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" class="h-10 w-auto transition-transform group-hover:scale-105">
                        </div>
                        <div class="logo-dark">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" class="h-10 w-auto transition-transform group-hover:scale-105">
                        </div>
                        <span class="text-2xl font-extrabold bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent">Salang</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-3">
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors" aria-label="Changer de thème">
                        <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                        </svg>
                    </button>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-[var(--text-secondary)] hover:text-primary-500 transition font-medium text-sm">Connexion</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Inscription
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 sm:py-32 welcome-hero">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent"></div>
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-fadeInUp">
            
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[var(--text-primary)] leading-tight">
                    Gagnez de l'argent avec
                    <span class="bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent">Salang MLM</span>
                </h1>
                <p class="mt-4 text-lg sm:text-xl text-[var(--text-secondary)] max-w-2xl mx-auto">
                    Plateforme E-Commerce et Marketing de Réseau. 
                    Achetez des produits, recrutez des membres, gagnez des commissions.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Commencer maintenant
                    </a>
                    <a href="#packages" class="btn-hero btn-hero-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        Voir les packages
                    </a>
                </div>
                <br>
                
                <!-- Stats -->
                <div class="mt-12 grid grid-cols-3 gap-4 max-w-lg mx-auto">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-primary-500">500+</p>
                        <p class="text-xs text-[var(--text-secondary)]">Membres actifs</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-primary-500">$2M+</p>
                        <p class="text-xs text-[var(--text-secondary)]">Commissions versées</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-primary-500">50+</p>
                        <p class="text-xs text-[var(--text-secondary)]">Pays représentés</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages -->
    <section id="packages" class="py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 animate-fadeInUp">
                <span class="inline-block px-4 py-1.5 rounded-full bg-primary-500/10 text-primary-500 text-sm font-semibold mb-4 border border-primary-500/20">
                     Nos offres
                </span>
                <h2 class="text-3xl font-bold text-[var(--text-primary)]">Choisissez votre package</h2>
                <p class="text-[var(--text-secondary)] mt-2">Commencez avec le package qui correspond à vos objectifs</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
                @php
                    $packages = [
                        ['name' => 'Starter', 'price' => 30, 'pv' => 0, 'icon' => '🚀', 'popular' => false],
                        ['name' => 'Silver', 'price' => 85, 'pv' => 0, 'icon' => '🥈', 'popular' => false],
                        ['name' => 'Bronze', 'price' => 350, 'pv' => 200, 'icon' => '🥉', 'popular' => false],
                        ['name' => 'Gold', 'price' => 1450, 'pv' => 1000, 'icon' => '🥇', 'popular' => true],
                        ['name' => 'Emerald', 'price' => 4850, 'pv' => 3800, 'icon' => '💎', 'popular' => false],
                    ];
                @endphp
                
                @foreach($packages as $index => $package)
                    <div class="welcome-card text-center p-6 animate-fadeInUp delay-{{ $index + 1 }}">
                        @if($package['popular'])
                            <span class="package-badge package-badge-popular">⭐ Populaire</span>
                        @endif
                        <div class="text-4xl mb-3 animate-float">{{ $package['icon'] }}</div>
                        <h3 class="text-xl font-bold text-[var(--text-primary)]">{{ $package['name'] }}</h3>
                        <p class="text-3xl font-bold text-primary-500 mt-2">${{ $package['price'] }}</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $package['pv'] }} PV</p>
                        <ul class="mt-4 space-y-1.5 text-sm text-[var(--text-secondary)] text-left">
                            <li class="flex items-center gap-2">✓ Commission jusqu'à 30%</li>
                            <li class="flex items-center gap-2">✓ {{ $package['pv'] }} PV</li>
                            <li class="flex items-center gap-2">✓ Accès à la boutique</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary w-full mt-4 text-sm">
                            Démarrer
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 animate-fadeInUp">
                <span class="inline-block px-4 py-1.5 rounded-full bg-primary-500/10 text-primary-500 text-sm font-semibold mb-4 border border-primary-500/20">
                     Comment ça marche
                </span>
                <h2 class="text-3xl font-bold text-[var(--text-primary)]">3 étapes pour réussir</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center animate-fadeInUp delay-1">
                    <div class="feature-icon">1</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Inscription</h3>
                    <p class="text-[var(--text-secondary)]">Créez votre compte avec un parrain et rejoignez la communauté</p>
                </div>
                <div class="text-center animate-fadeInUp delay-2">
                    <div class="feature-icon">2</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Achetez un package</h3>
                    <p class="text-[var(--text-secondary)]">Choisissez votre package et commencez à gagner des commissions</p>
                </div>
                <div class="text-center animate-fadeInUp delay-3">
                    <div class="feature-icon">3</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Gagnez des commissions</h3>
                    <p class="text-[var(--text-secondary)]">Recrutez et gagnez sur tout votre réseau jusqu'au niveau 3</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="logo-light">
                        <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" class="h-8 w-auto">
                    </div>
                    <div class="logo-dark">
                        <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" class="h-8 w-auto">
                    </div>
                    <span class="font-bold text-[var(--text-primary)]">Salang Group</span>
                </div>
                <p class="text-sm text-[var(--text-secondary)]">&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</p>
                <div class="flex gap-4 text-sm text-[var(--text-secondary)]">
                    <a href="#" class="hover:text-primary-500 transition">Mentions légales</a>
                    <a href="#" class="hover:text-primary-500 transition">Confidentialité</a>
                    <a href="#" class="hover:text-primary-500 transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    @vite(['resources/js/app.js'])
</body>
</html>