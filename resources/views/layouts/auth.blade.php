<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM - Authentification')</title>
    
    <!-- Meta tags pour mobile et PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="theme-color" content="#5ab638">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#5ab638">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
    
    <!-- PWA -->
    {!! PwaKit::head() !!}
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased min-h-screen flex flex-col items-center justify-center p-3 sm:p-4 md:p-6">
    
    <!-- Conteneur principal -->
    <main class="w-full max-w-md mx-auto animate-fadeInUp">
        
        <!-- ===== LOGO SUPPRIME ===== -->
        <!-- Le logo a ete retire sur demande -->
        
        <!-- Card Auth -->
        <div class="auth-card-responsive bg-[var(--bg-card)] border border-[var(--border-color)] rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl p-4 sm:p-6 md:p-8">
            @yield('content')
        </div>

        <!-- Liens Auth -->
        <div class="mt-4 sm:mt-6 text-center text-xs sm:text-sm text-[var(--text-secondary)]">
            <p>
                &copy; {{ date('Y') }} Salang Group. 
                <span class="hidden xs:inline">Tous droits reserves.</span>
            </p>
        </div>
    </main>

    @livewireScripts
    @vite(['resources/js/app.js'])
    {!! PwaKit::scripts() !!}
    @stack('scripts')
</body>
</html>