<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM - Authentification')</title>
    
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
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased min-h-screen flex flex-col items-center justify-center p-4">
    <!-- ===== CONTENU PRINCIPAL ===== -->
    <main class="w-full max-w-md mx-auto">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-8 text-center text-xs text-[var(--text-tertiary)]">
        <p>&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</p>
    </footer>

    @livewireScripts
    @vite(['resources/js/app.js'])
    {!! PwaKit::scripts() !!}
    @stack('scripts')
</body>
</html>