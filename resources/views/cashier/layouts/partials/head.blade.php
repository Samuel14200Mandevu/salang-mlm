<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM')</title>

    <meta name="theme-color" content="#F4F5F7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0c1017" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#F4F5F7" id="salang-theme-color">
    <meta name="color-scheme" content="light dark">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>

    <style>
    html.shell-is-desktop.shell-sidebar-expanded .cashier-app .main-wrapper {
        margin-left: 16rem;
        width: calc(100% - 16rem);
    }
    html.shell-is-desktop.shell-sidebar-expanded .cashier-app #sidebar {
        width: 16rem;
    }
    html.shell-is-desktop.shell-sidebar-rail .cashier-app .main-wrapper {
        margin-left: 5rem;
        width: calc(100% - 5rem);
    }
    html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar {
        width: 5rem;
    }
    html.shell-is-mobile .cashier-app .main-wrapper {
        margin-left: 0;
        width: 100%;
    }
    html.shell-is-mobile .cashier-app #sidebar {
        width: 16rem;
    }
    @media (max-width: 767px) {
        .cashier-app #sidebar:not(.translate-x-0) {
            transform: translateX(-100%);
        }
    }
    </style>
    @include('partials.shell.sidebar-rail-styles')
    @include('partials.shell.sidebar-state')

    @vite(['resources/css/app.css', 'resources/js/cashier.js'])
    @stack('styles')
</head>
