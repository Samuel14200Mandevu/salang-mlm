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
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)]">
    
    <!-- Navigation -->
    <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-2">
                        <div class="logo-light">
                            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang" class="h-10 w-auto">
                        </div>
                        <div class="logo-dark">
                            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang" class="h-10 w-auto">
                        </div>
                        <span class="text-2xl font-extrabold text-primary-600">Salang</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-[var(--bg-primary)] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" 
                                  id="theme-icon"/>
                        </svg>
                    </button>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-[var(--text-primary)] hover:text-primary-600">Connexion</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-transparent"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[var(--text-primary)] leading-tight">
                    Gagnez de l'argent avec
                    <span class="text-primary-600">Salang MLM</span>
                </h1>
                <p class="mt-4 text-lg sm:text-xl text-[var(--text-secondary)] max-w-2xl mx-auto">
                    Plateforme E-Commerce et Marketing de Réseau. 
                    Achetez des produits, recrutez des membres, gagnez des commissions.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-3">
                        🚀 Commencer maintenant
                    </a>
                    <a href="#packages" class="btn-secondary text-lg px-8 py-3">
                        Voir les packages
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages -->
    <section id="packages" class="py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-[var(--text-primary)]">Nos Packages</h2>
                <p class="text-[var(--text-secondary)] mt-2">Choisissez le package qui vous correspond</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @php
                    $packages = [
                        ['name' => 'Starter', 'price' => 30, 'pv' => 0, 'color' => 'gray', 'bg' => 'bg-gray-100 dark:bg-gray-800'],
                        ['name' => 'Silver', 'price' => 85, 'pv' => 0, 'color' => 'gray-400', 'bg' => 'bg-gray-200 dark:bg-gray-700'],
                        ['name' => 'Bronze', 'price' => 350, 'pv' => 200, 'color' => 'amber-600', 'bg' => 'bg-amber-100 dark:bg-amber-900'],
                        ['name' => 'Gold', 'price' => 1450, 'pv' => 1000, 'color' => 'yellow-500', 'bg' => 'bg-yellow-100 dark:bg-yellow-900'],
                        ['name' => 'Emerald', 'price' => 4850, 'pv' => 3800, 'color' => 'emerald-600', 'bg' => 'bg-emerald-100 dark:bg-emerald-900'],
                    ];
                @endphp
                
                @foreach($packages as $package)
                    <div class="card hover:scale-105 transition-all duration-300 text-center">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-[var(--text-primary)]">{{ $package['name'] }}</h3>
                            <p class="text-3xl font-bold text-primary-600 mt-2">${{ $package['price'] }}</p>
                            <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $package['pv'] }} PV</p>
                            <a href="{{ route('register') }}" class="btn-primary text-sm mt-4 inline-block w-full text-center">
                                Démarrer
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-[var(--text-primary)]">Comment ça marche ?</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-2xl mx-auto">1</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Inscription</h3>
                    <p class="text-[var(--text-secondary)]">Créez votre compte avec un parrain</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-2xl mx-auto">2</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Achetez un package</h3>
                    <p class="text-[var(--text-secondary)]">Choisissez votre package et commencez</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-2xl mx-auto">3</div>
                    <h3 class="text-xl font-bold text-[var(--text-primary)] mt-4">Gagnez des commissions</h3>
                    <p class="text-[var(--text-secondary)]">Recrutez et gagnez sur tout votre réseau</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-[var(--text-secondary)]">
            <p>&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</p>
        </div>
    </footer>

    @livewireScripts
    @vite(['resources/js/app.js'])
</body>
</html>
