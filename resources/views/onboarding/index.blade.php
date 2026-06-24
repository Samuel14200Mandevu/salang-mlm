<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang MLM - Welcome</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] min-h-screen">
    <!-- Splash Screen -->
    <div id="splashScreen" class="splash-screen">
        <div class="flex flex-col items-center">
            <!-- Logo selon le thème -->
            <div class="logo-light">
                <img src="{{ asset('images/light_logo.jpeg') }}" 
                     alt="Salang MLM" 
                     class="splash-logo w-48 h-auto">
            </div>
            <div class="logo-dark">
                <img src="{{ asset('images/dark_logo.jpeg') }}" 
                     alt="Salang MLM" 
                     class="splash-logo w-48 h-auto">
            </div>
            
            <h1 class="text-3xl font-bold text-primary-600 mt-4">Salang MLM</h1>
            <p class="text-[var(--text-secondary)] mt-2">E-Commerce & Network Platform</p>
            <div class="splash-loader"></div>
        </div>
    </div>

    <!-- Onboarding -->
    <div id="onboardingContainer" class="hidden min-h-screen">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-primary-600">Welcome to Salang MLM</h2>
                <p class="text-[var(--text-secondary)]">Your journey to financial freedom starts here</p>
            </div>

            <!-- Slides -->
            <div class="relative">
                <!-- Slide 1 -->
                <div class="onboarding-slide active" data-slide="0">
                    <div class="card text-center p-8">
                        <div class="text-7xl mb-4 float-animation">🚀</div>
                        <h3 class="text-2xl font-bold mb-2">Earn Commissions</h3>
                        <p class="text-[var(--text-secondary)]">
                            Earn up to 30% commission on every sale. 
                            Build your network and watch your income grow.
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-3">
                                <span class="block font-bold text-primary-600">30%</span>
                                <span class="text-[var(--text-secondary)]">Direct Bonus</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-3">
                                <span class="block font-bold text-primary-600">25%</span>
                                <span class="text-[var(--text-secondary)]">Retail Profit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="onboarding-slide" data-slide="1">
                    <div class="card text-center p-8">
                        <div class="text-7xl mb-4 float-animation">📦</div>
                        <h3 class="text-2xl font-bold mb-2">Choose Your Package</h3>
                        <p class="text-[var(--text-secondary)]">
                            Start with a package that fits your goals.
                            Each package gives you more earning potential.
                        </p>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-sm">
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-2">
                                <span class="block font-bold">$30</span>
                                <span class="text-[var(--text-secondary)]">Starter</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-2">
                                <span class="block font-bold">$350</span>
                                <span class="text-[var(--text-secondary)]">Bronze</span>
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-lg p-2">
                                <span class="block font-bold">$1,450</span>
                                <span class="text-[var(--text-secondary)]">Gold</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="onboarding-slide" data-slide="2">
                    <div class="card text-center p-8">
                        <div class="text-7xl mb-4 float-animation">👥</div>
                        <h3 class="text-2xl font-bold mb-2">Build Your Network</h3>
                        <p class="text-[var(--text-secondary)]">
                            Grow your team and earn commissions from 
                            your entire network. The bigger your team, 
                            the more you earn.
                        </p>
                        <div class="mt-4 flex justify-center gap-2">
                            <div class="bg-[var(--bg-secondary)] rounded-full p-2 px-4">
                                👤 You
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-full p-2 px-4">
                                👤 Level 1
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-full p-2 px-4">
                                👤 Level 2
                            </div>
                            <div class="bg-[var(--bg-secondary)] rounded-full p-2 px-4">
                                👤 Level 3+
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="onboarding-slide" data-slide="3">
                    <div class="card text-center p-8">
                        <div class="text-7xl mb-4 float-animation">💰</div>
                        <h3 class="text-2xl font-bold mb-2">Ready to Start?</h3>
                        <p class="text-[var(--text-secondary)]">
                            Join Salang MLM today and start building 
                            your financial freedom. Your journey starts now!
                        </p>
                        <div class="mt-6 flex flex-col gap-3">
                            <a href="{{ route('register') }}" class="btn-primary text-center">
                                🚀 Create Account
                            </a>
                            <a href="{{ route('login') }}" class="btn-secondary text-center">
                                Already have an account? Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between items-center mt-6">
                    <button id="prevSlide" class="btn-secondary text-sm px-4 py-2">
                        ⬅ Previous
                    </button>
                    
                    <div class="flex gap-2" id="dotContainer">
                        <span class="onboarding-dot active" data-dot="0"></span>
                        <span class="onboarding-dot" data-dot="1"></span>
                        <span class="onboarding-dot" data-dot="2"></span>
                        <span class="onboarding-dot" data-dot="3"></span>
                    </div>
                    
                    <button id="nextSlide" class="btn-primary text-sm px-4 py-2">
                        Next ➡
                    </button>
                </div>

                <!-- Skip button -->
                <div class="text-center mt-4">
                    <a href="{{ route('onboarding.skip') }}" class="text-sm text-[var(--text-secondary)] hover:text-primary-600">
                        Skip onboarding →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Splash Screen
            const splashScreen = document.getElementById('splashScreen');
            const onboardingContainer = document.getElementById('onboardingContainer');

            // Afficher le splash pendant 2 secondes
            setTimeout(() => {
                splashScreen.classList.add('hidden');
                onboardingContainer.classList.remove('hidden');
                onboardingContainer.style.display = 'block';
            }, 2500);

            // Gestion de l'onboarding
            let currentSlide = 0;
            const slides = document.querySelectorAll('.onboarding-slide');
            const dots = document.querySelectorAll('.onboarding-dot');
            const totalSlides = slides.length;

            function showSlide(index) {
                // Cacher tous les slides
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));
                
                // Afficher le slide sélectionné
                slides[index].classList.add('active');
                dots[index].classList.add('active');
                currentSlide = index;
            }

            document.getElementById('nextSlide').addEventListener('click', function() {
                if (currentSlide < totalSlides - 1) {
                    showSlide(currentSlide + 1);
                }
            });

            document.getElementById('prevSlide').addEventListener('click', function() {
                if (currentSlide > 0) {
                    showSlide(currentSlide - 1);
                }
            });

            dots.forEach(dot => {
                dot.addEventListener('click', function() {
                    const index = parseInt(this.dataset.dot);
                    showSlide(index);
                });
            });

            // Navigation au clavier
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight' && currentSlide < totalSlides - 1) {
                    showSlide(currentSlide + 1);
                } else if (e.key === 'ArrowLeft' && currentSlide > 0) {
                    showSlide(currentSlide - 1);
                }
            });

            // Marquer l'onboarding comme complété après un certain temps
            setTimeout(() => {
                fetch('{{ route('onboarding.complete') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            }, 30000);
        });
    </script>
</body>
</html>
