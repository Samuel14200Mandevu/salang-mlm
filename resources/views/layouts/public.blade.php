<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang Group — Health Care International')</title>
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
    @stack('head')
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-body bg-[var(--bg-page)] text-[var(--text-primary)] antialiased @yield('bodyClass')">
    @include('layouts.partials.public-nav')

    <main id="main-content">
        @yield('content')
    </main>

    @include('layouts.partials.public-footer')

    @stack('scripts')
</body>
</html>
