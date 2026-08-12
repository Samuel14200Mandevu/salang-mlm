<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM')</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
        .legal-page {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .legal-page .legal-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2rem 2.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .legal-page .legal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }
        .legal-page h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        .legal-page .last-update {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .legal-page h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
        }
        .legal-page h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .legal-page p {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 0.75rem;
        }
        .legal-page ul, .legal-page ol {
            color: var(--text-secondary);
            line-height: 1.7;
            padding-left: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .legal-page ul li, .legal-page ol li {
            margin-bottom: 0.25rem;
        }
        .legal-page .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-500);
            font-weight: 600;
            text-decoration: none;
            margin-top: 1.5rem;
            transition: color 0.2s ease;
        }
        .legal-page .back-link:hover {
            color: var(--primary-600);
        }
        .legal-page .highlight-box {
            background: rgba(99, 102, 241, 0.06);
            border-left: 4px solid var(--primary-500);
            padding: 1rem 1.25rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin: 1rem 0;
        }
        .legal-page .highlight-box p:last-child {
            margin-bottom: 0;
        }
        .legal-header {
            background: var(--bg-navbar);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .legal-header .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .legal-header .logo img {
            height: 40px;
            width: auto;
        }
        .legal-header .logo span {
            font-size: 1.25rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .legal-footer {
            background: var(--bg-footer);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.813rem;
        }
        .legal-footer a {
            color: var(--primary-500);
            text-decoration: none;
        }
        .legal-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .legal-page .legal-card { padding: 1.25rem; }
            .legal-page h1 { font-size: 1.5rem; }
            .legal-page h2 { font-size: 1.1rem; }
            .legal-header .logo span { font-size: 1rem; }
            .legal-header .logo img { height: 30px; }
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)]">

    <!-- En-tête simplifié avec détection automatique -->
    <header class="legal-header">
        <div class="logo">
            <img src="{{ asset('images/salang_logo.png') }}" alt="Salang">
            <span>Salang Group</span>
        </div>
    </header>

    <!-- Contenu -->
    <main>
        @yield('content')
    </main>

    <!-- Pied de page simplifié -->
    <footer class="legal-footer">
        <p>&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</p>
        <p style="margin-top: 0.25rem; font-size: 0.75rem;">
            <a href="{{ route('legal.mentions') }}">Mentions légales</a> &bull;
            <a href="{{ route('legal.privacy') }}">Confidentialité</a> &bull;
            <a href="{{ route('legal.terms') }}">Conditions générales</a>
        </p>
    </footer>

    @livewireScripts
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>