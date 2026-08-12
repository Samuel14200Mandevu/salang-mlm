<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM - Authentication')</title>
    
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
    @if(class_exists('PwaKit'))
        {!! PwaKit::head() !!}
    @endif
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
        .auth-card-responsive {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.6s ease forwards;
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        .auth-card-responsive::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo img {
            height: 60px;
            width: auto;
            margin: 0 auto;
        }
        .auth-logo .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        .auth-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .auth-title .highlight {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .auth-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }
        .form-group label .required {
            color: #ef4444;
            font-weight: 700;
        }
        .form-group .input {
            width: 100%;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-input);
            color: var(--text-primary);
            transition: all 0.2s ease;
            outline: none;
        }
        .form-group .input:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 4px var(--border-focus);
        }
        .form-group .input-error {
            border-color: #ef4444;
        }
        .form-group .input-error:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }
        .form-group .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .form-group .error-message svg {
            width: 0.875rem;
            height: 0.875rem;
            flex-shrink: 0;
        }
        .form-hint {
            font-size: 0.75rem;
            color: var(--text-tertiary);
            margin-top: 0.25rem;
        }
        
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .input {
            padding-right: 2.75rem;
        }
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-tertiary);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s ease;
        }
        .password-toggle:hover {
            color: var(--text-primary);
        }
        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
            width: 100%;
        }
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 20px rgba(90, 182, 56, 0.35);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(90, 182, 56, 0.45);
        }
        .btn-primary:active {
            transform: scale(0.96);
        }
        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }
        .btn-outline:hover {
            border-color: var(--primary-500);
            color: var(--primary-500);
        }
        
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--text-tertiary);
            font-size: 0.75rem;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .social-btn:hover {
            background: var(--bg-hover);
            border-color: var(--primary-500);
            transform: translateY(-1px);
        }
        .social-btn svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
        }
        
        /* Lien "Mot de passe oublié" */
        .forgot-link {
            color: var(--primary-500);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .forgot-link:hover {
            color: var(--primary-600);
            text-decoration: underline;
        }
        
        /* Lien "S'inscrire" / "Se connecter" */
        .auth-link {
            color: var(--primary-500);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }
        .auth-link:hover {
            color: var(--primary-600);
            text-decoration: underline;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @media (max-width: 640px) {
            .auth-card-responsive { padding: 1.5rem; max-width: 100%; }
            .auth-logo img { height: 50px; }
            .auth-logo .brand-name { font-size: 1.25rem; }
            .auth-title { font-size: 1.25rem; }
            .auth-subtitle { font-size: 0.813rem; }
            .form-group label { font-size: 0.813rem; }
            .form-group .input { font-size: 0.813rem; padding: 0.5rem 0.875rem; }
            .btn { font-size: 0.813rem; padding: 0.5rem 1rem; }
            .social-btn { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
            .forgot-link { font-size: 0.75rem; }
        }
        
        @media (max-width: 480px) {
            .auth-card-responsive { padding: 1.25rem; }
            .form-group .input { font-size: 0.75rem; padding: 0.5rem 0.75rem; }
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased min-h-screen flex flex-col items-center justify-center p-3 sm:p-4 md:p-6">
    
    <!-- Main Container -->
    <main class="w-full max-w-md mx-auto animate-fadeInUp">
        
        <!-- Auth Card -->
        <div class="auth-card-responsive">
            @yield('content')
        </div>

        <!-- Footer Links -->
        <div class="mt-4 sm:mt-6 text-center text-xs sm:text-sm text-[var(--text-secondary)]">
            <p>
                &copy; {{ date('Y') }} <span class="font-semibold text-primary-500">Salang Group</span>. 
                <span class="hidden xs:inline">All rights reserved.</span>
            </p>
        </div>
    </main>

    @livewireScripts
    @vite(['resources/js/app.js'])
    
    @if(class_exists('PwaKit'))
        {!! PwaKit::scripts() !!}
    @endif
    
    @stack('scripts')
</body>
</html>