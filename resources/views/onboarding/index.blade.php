<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM - Welcome</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        /* ===== SPLASH SCREEN ===== */
        .splash-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--splash-bg, #ffffff);
            transition: opacity 0.8s ease, visibility 0.8s ease;
        }
        .splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .splash-loader {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 4px solid var(--border-color, #e5e7eb);
            border-top-color: var(--primary-500, #5ab638);
            animation: spin 0.8s linear infinite;
            margin-top: 1rem;
        }
        .splash-logo {
            max-height: 60px;
            width: auto;
        }
        
        /* ===== ONBOARDING ===== */
        .onboarding-slide {
            display: none;
            animation: fadeInUp 0.5s ease forwards;
        }
        .onboarding-slide.active {
            display: block;
        }
        .onboarding-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--border-color, #e5e7eb);
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .onboarding-dot.active {
            background: var(--primary-500, #5ab638);
            width: 28px;
            border-radius: 10px;
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        /* ===== BUTTONS ===== */
        .btn-primary {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--gradient-primary, #5ab638);
            color: white;
            border-radius: var(--radius-md, 0.5rem);
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
            width: 100%;
            text-align: center;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover, 0 8px 24px rgba(90, 182, 56, 0.2));
        }
        .btn-secondary {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: transparent;
            color: var(--text-primary, #111827);
            border: 2px solid var(--border-color, #e5e7eb);
            border-radius: var(--radius-md, 0.5rem);
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        .btn-secondary:hover {
            border-color: var(--primary-500, #5ab638);
            color: var(--primary-500, #5ab638);
        }
        
        /* ===== CARD ===== */
        .card {
            background: var(--bg-card, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: var(--radius-lg, 1rem);
            padding: 1.5rem;
        }
        
        /* ===== KEYFRAMES ===== */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        /* ===== DARK MODE ===== */
        .dark .splash-screen {
            background: var(--splash-bg, #000000);
        }
        .dark .btn-secondary {
            color: var(--text-primary, #f0f4ff);
            border-color: var(--border-color, #1a1a2e);
        }
        .dark .card {
            background: var(--bg-card, #0a0a0a);
            border-color: var(--border-color, #1a1a2e);
        }
        .dark .onboarding-dot {
            background: var(--border-color, #1a1a2e);
        }
        .dark .onboarding-dot.active {
            background: var(--primary-500, #5ab638);
        }
        
        /* ===== RESPONSIVE ===== */
        @media (min-width: 640px) {
            .btn-primary, .btn-secondary {
                width: auto;
            }
            .splash-logo {
                max-height: 80px;
            }
        }
        
        @media (max-width: 640px) {
            .card {
                padding: 1rem;
            }
            .btn-primary, .btn-secondary {
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
            }
            .onboarding-slide .grid-cols-2 {
                grid-template-columns: 1fr 1fr;
            }
            .onboarding-slide .grid-cols-3 {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 0.75rem;
            }
            .onboarding-slide .grid-cols-2 {
                grid-template-columns: 1fr;
            }
            .onboarding-slide .grid-cols-3 {
                grid-template-columns: 1fr;
            }
            .float-animation svg {
                width: 4rem;
                height: 4rem;
            }
            .btn-primary, .btn-secondary {
                padding: 0.5rem 0.75rem;
                font-size: 0.813rem;
            }
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] min-h-screen transition-colors duration-200">
    
    <!-- Splash Screen -->
    <div id="splashScreen" class="splash-screen">
        <div class="flex flex-col items-center text-center px-4">
            <div class="logo-light">
                <img src="{{ asset('images/light_logo.jpeg') }}" 
                     alt="Salang MLM" 
                     class="splash-logo">
            </div>
            <div class="logo-dark">
                <img src="{{ asset('images/dark_logo.jpeg') }}" 
                     alt="Salang MLM" 
                     class="splash-logo">
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-600 mt-3 sm:mt-4">Salang MLM</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">E-Commerce & Network Platform</p>
            <div class="splash-loader"></div>
        </div>
    </div>

    <!-- Onboarding -->
    <div id="onboardingContainer" class="hidden min-h-screen">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 py-6 sm:py-8">
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-primary-600">Welcome to Salang MLM</h2>
                <p class="text-sm sm:text-base text-[var(--text-secondary)]">Your journey to financial freedom starts here</p>
            </div>

            <!-- Slides -->
            <div class="relative">
                
                <!-- Slide 1: Earn Commissions -->
                <div class="onboarding-slide active" data-slide="0">
                    <div class="card text-center p-4 sm:p-6 md:p-8">
                        <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto text-primary-500 mb-3 sm:mb-4 float-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Earn Commissions</h3>
                        <p class="text-sm sm:text-base text-[var(--text-secondary)]">
                            Earn up to 30% commission on every sale. 
                            Build your network and watch your income grow.
                        </p>
                        <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-2 sm:p-3">
                                <span class="block font-bold text-primary-600 text-lg sm:text-xl">30%</span>
                                <span class="text-[var(--text-secondary)] text-[10px] sm:text-xs">Direct Bonus</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-2 sm:p-3">
                                <span class="block font-bold text-primary-600 text-lg sm:text-xl">25%</span>
                                <span class="text-[var(--text-secondary)] text-[10px] sm:text-xs">Retail Profit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Choose Your Package -->
                <div class="onboarding-slide" data-slide="1">
                    <div class="card text-center p-4 sm:p-6 md:p-8">
                        <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto text-primary-500 mb-3 sm:mb-4 float-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        <h3 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Choose Your Package</h3>
                        <p class="text-sm sm:text-base text-[var(--text-secondary)]">
                            Start with a package that fits your goals.
                            Each package gives you more earning potential.
                        </p>
                        <div class="mt-3 sm:mt-4 grid grid-cols-3 gap-1 sm:gap-2 text-xs sm:text-sm">
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-1.5 sm:p-2">
                                <span class="block font-bold text-sm sm:text-base">$30</span>
                                <span class="text-[var(--text-secondary)] text-[10px] sm:text-xs">Starter</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-1.5 sm:p-2">
                                <span class="block font-bold text-sm sm:text-base">$350</span>
                                <span class="text-[var(--text-secondary)] text-[10px] sm:text-xs">Bronze</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-1.5 sm:p-2">
                                <span class="block font-bold text-sm sm:text-base">$1,450</span>
                                <span class="text-[var(--text-secondary)] text-[10px] sm:text-xs">Gold</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Build Your Network -->
                <div class="onboarding-slide" data-slide="2">
                    <div class="card text-center p-4 sm:p-6 md:p-8">
                        <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto text-primary-500 mb-3 sm:mb-4 float-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Build Your Network</h3>
                        <p class="text-sm sm:text-base text-[var(--text-secondary)]">
                            Grow your team and earn commissions from 
                            your entire network. The bigger your team, 
                            the more you earn.
                        </p>
                        <div class="mt-3 sm:mt-4 flex flex-wrap justify-center gap-1 sm:gap-2 text-xs sm:text-sm">
                            <span class="bg-[var(--bg-secondary)] rounded-full px-2 sm:px-3 py-1 sm:py-1.5">You</span>
                            <span class="bg-[var(--bg-secondary)] rounded-full px-2 sm:px-3 py-1 sm:py-1.5">Level 1</span>
                            <span class="bg-[var(--bg-secondary)] rounded-full px-2 sm:px-3 py-1 sm:py-1.5">Level 2</span>
                            <span class="bg-[var(--bg-secondary)] rounded-full px-2 sm:px-3 py-1 sm:py-1.5">Level 3+</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 4: Ready to Start -->
                <div class="onboarding-slide" data-slide="3">
                    <div class="card text-center p-4 sm:p-6 md:p-8">
                        <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto text-primary-500 mb-3 sm:mb-4 float-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Ready to Start?</h3>
                        <p class="text-sm sm:text-base text-[var(--text-secondary)]">
                            Join Salang MLM today and start building 
                            your financial freedom. Your journey starts now!
                        </p>
                        <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-center gap-2 sm:gap-3">
                            <a href="{{ route('register') }}" class="btn-primary text-center text-sm sm:text-base">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Create Account
                            </a>
                            <a href="{{ route('login') }}" class="btn-secondary text-center text-sm sm:text-base">
                                Already have an account? Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex flex-wrap justify-between items-center mt-4 sm:mt-6 gap-2">
                    <button id="prevSlide" class="btn-secondary text-xs sm:text-sm px-3 sm:px-4 py-1.5 sm:py-2 w-auto">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Previous
                    </button>
                    
                    <div class="flex gap-1.5 sm:gap-2" id="dotContainer">
                        <span class="onboarding-dot active" data-dot="0"></span>
                        <span class="onboarding-dot" data-dot="1"></span>
                        <span class="onboarding-dot" data-dot="2"></span>
                        <span class="onboarding-dot" data-dot="3"></span>
                    </div>
                    
                    <button id="nextSlide" class="btn-primary text-xs sm:text-sm px-3 sm:px-4 py-1.5 sm:py-2 w-auto">
                        Next
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>

                <!-- Skip button -->
                <div class="text-center mt-3 sm:mt-4">
                    <a href="{{ route('onboarding.skip') }}" class="text-xs sm:text-sm text-[var(--text-secondary)] hover:text-primary-600 transition">
                        Skip onboarding →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var splashScreen = document.getElementById('splashScreen');
            var onboardingContainer = document.getElementById('onboardingContainer');

            // Hide splash screen after 2.5s
            setTimeout(function() {
                splashScreen.classList.add('hidden');
                onboardingContainer.classList.remove('hidden');
                onboardingContainer.style.display = 'block';
            }, 2500);

            // ============================================================
            // ONBOARDING NAVIGATION
            // ============================================================
            var currentSlide = 0;
            var slides = document.querySelectorAll('.onboarding-slide');
            var dots = document.querySelectorAll('.onboarding-dot');
            var totalSlides = slides.length;

            function showSlide(index) {
                // Validate index
                if (index < 0) index = 0;
                if (index >= totalSlides) index = totalSlides - 1;
                
                slides.forEach(function(slide) {
                    slide.classList.remove('active');
                });
                dots.forEach(function(dot) {
                    dot.classList.remove('active');
                });
                
                slides[index].classList.add('active');
                dots[index].classList.add('active');
                currentSlide = index;
            }

            // Next button
            document.getElementById('nextSlide').addEventListener('click', function() {
                if (currentSlide < totalSlides - 1) {
                    showSlide(currentSlide + 1);
                }
            });

            // Previous button
            document.getElementById('prevSlide').addEventListener('click', function() {
                if (currentSlide > 0) {
                    showSlide(currentSlide - 1);
                }
            });

            // Dots
            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    var index = parseInt(this.dataset.dot);
                    showSlide(index);
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight' && currentSlide < totalSlides - 1) {
                    showSlide(currentSlide + 1);
                } else if (e.key === 'ArrowLeft' && currentSlide > 0) {
                    showSlide(currentSlide - 1);
                }
            });

            // Touch swipe support
            var touchStartX = 0;
            var touchEndX = 0;
            var container = document.querySelector('.onboarding-slide.active');

            document.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            document.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                var diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0 && currentSlide < totalSlides - 1) {
                        showSlide(currentSlide + 1);
                    } else if (diff < 0 && currentSlide > 0) {
                        showSlide(currentSlide - 1);
                    }
                }
            }, { passive: true });

            // Mark onboarding as completed after 30 seconds (in case user doesn't navigate)
            setTimeout(function() {
                fetch('{{ route('onboarding.complete') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).catch(function() {
                    // Silent fail
                });
            }, 30000);
        });
    </script>
</body>
</html>