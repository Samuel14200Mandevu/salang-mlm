<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang Group — Connexion')</title>
    <meta name="theme-color" content="#1E5DAD">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page public-body">
    <div class="auth-split">
        @include('partials.auth.panel-brand')

        <div class="auth-form-panel">
            <div class="auth-form-panel-top">
                <a href="{{ url('/') }}" class="auth-back-home lg:hidden text-sm text-[var(--text-secondary)]">
                    ← Accueil
                </a>
                <button
                    type="button"
                    id="theme-toggle"
                    class="public-theme-toggle auth-theme-toggle"
                    aria-label="Activer le thème sombre"
                    aria-pressed="false"
                >
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" id="theme-icon" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>

            <div class="auth-form-inner @yield('auth-inner-class')">
                <div class="auth-card @yield('auth-card-class')">
                    <span class="auth-card-accent" aria-hidden="true"></span>
                    <div class="auth-card-logo">
                        <a href="{{ url('/') }}" class="auth-card-logo-link" aria-label="Salang Group — accueil">
                            <img
                                src="{{ asset('images/salang_logo.png') }}"
                                alt=""
                                class="logo-themeable auth-card-logo-img"
                                width="320"
                                height="76"
                                decoding="async"
                                fetchpriority="high"
                            >
                        </a>
                    </div>
                    @yield('content')
                    @include('partials.auth.legal-footer')
                </div>

                <p class="mt-5 text-center text-xs text-[var(--text-muted)] lg:hidden">
                    &copy; {{ date('Y') }} Salang Group
                </p>
            </div>
        </div>
    </div>
    @include('partials.auth.toast-container')
    @stack('scripts')
</body>
</html>
