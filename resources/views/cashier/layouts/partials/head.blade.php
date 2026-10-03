<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title', 'Salang MLM')</title>

    <!-- Theme Color -->
    <meta name="theme-color" content="#0F2B4F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Fonts : Inter élégante -->
    @stack('styles')

    <style>
        /* ============================================================
           THEME – Couleurs Navy comme l'admin
           ============================================================ */
        :root {
            --bg-page: #F5F6F8;
            --bg-primary: #F5F6F8;
            --bg-secondary: #EEF0F3;
            --bg-card: #FFFFFF;
            --bg-navbar: #FFFFFF;
            --bg-footer: #E8EAEE;
            --text-primary: #1A1A1E;
            --text-secondary: #4A4A52;
            --text-tertiary: #7A7A82;
            --border-color: #DCDEE3;
            --primary: #0F2B4F;
            --primary-hover: #091E3B;
            --primary-light: #1A3F6A;
            --success: #1F7B4D;
            --success-hover: #16633D;
            --danger: #B32A2A;
            --danger-hover: #8F2121;
            --warning: #A65A0E;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 10px;
            --sidebar-width: 250px;
            --sidebar-collapsed: 72px;
            --header-height: 64px;
            --mobile-nav-height: 64px;
        }

        /* Dark mode – fond anthracite #111827 */
        .dark {
            --bg-page: #111827;
            --bg-primary: #111827;
            --bg-secondary: #1F2937;
            --bg-card: #1A1D23;
            --bg-navbar: #111827;
            --bg-footer: #111827;
            --text-primary: #F3F4F6;
            --text-secondary: #9CA3AF;
            --text-tertiary: #6B7280;
            --border-color: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
        }

        /* ============================================================
           LAYOUT PRINCIPAL – Footer toujours en bas
           ============================================================ */
        .app-container {
            display: flex;
            min-height: 100vh;
            height: 100%;
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.25s ease-in-out, width 0.25s ease-in-out;
        }

        .main-content {
            flex: 1 0 auto;
            padding: 1rem;
        }

        .main-footer {
            flex-shrink: 0;
            background: var(--bg-footer);
            border-top: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            margin-top: auto;
        }

        /* ============================================================
           BOUTONS – Visibles et cohérents
           ============================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            text-decoration: none;
            white-space: nowrap;
            min-height: 40px;
            line-height: 1.2;
        }

        .btn:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .btn-primary {
            background: var(--primary);
            color: #FFFFFF;
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-success {
            background: var(--success);
            color: #FFFFFF;
            border-color: var(--success);
        }
        .btn-success:hover {
            background: var(--success-hover);
            border-color: var(--success-hover);
        }

        .btn-danger {
            background: var(--danger);
            color: #FFFFFF;
            border-color: var(--danger);
        }
        .btn-danger:hover {
            background: var(--danger-hover);
            border-color: var(--danger-hover);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border-color: var(--border-color);
        }
        .btn-outline:hover {
            background: var(--bg-secondary);
        }

        .btn-outline-primary {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            color: #FFFFFF;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            min-height: 32px;
        }

        .btn-lg {
            padding: 0.75rem 1.75rem;
            font-size: 1rem;
            min-height: 48px;
        }

        .btn-block {
            width: 100%;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 50%;
        }

        .btn-icon.btn-sm {
            width: 32px;
            height: 32px;
        }

        /* ============================================================
           SIDEBAR – Liens sobres
           ============================================================ */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            transition: background 0.15s ease, color 0.15s ease;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .sidebar-link svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            min-width: 1.25rem;
            color: var(--text-tertiary);
            transition: color 0.15s ease;
        }

        .sidebar-link .label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .sidebar-link:hover svg {
            color: var(--text-primary);
        }

        .sidebar-link.active {
            background: var(--primary);
            color: #FFFFFF;
        }

        .sidebar-link.active svg {
            color: #FFFFFF;
        }

        .sidebar-link.danger {
            color: var(--danger);
        }
        .sidebar-link.danger:hover {
            background: rgba(179, 42, 42, 0.08);
            color: var(--danger);
        }
        .sidebar-link.danger.active {
            background: var(--danger);
            color: #FFFFFF;
        }
        .sidebar-link.danger.active svg {
            color: #FFFFFF;
        }

        .sidebar-section {
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-tertiary);
            padding: 0.75rem 0.75rem 0.375rem;
            margin-top: 0.5rem;
        }

        /* ============================================================
           MOBILE BOTTOM NAV
           ============================================================ */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: var(--bg-navbar);
            border-top: 1px solid var(--border-color);
            display: none;
            padding: 0.25rem 0 env(safe-area-inset-bottom, 0.25rem) 0;
            height: var(--mobile-nav-height);
        }

        .mobile-bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0;
            border-radius: var(--radius-md);
            transition: color 0.15s ease;
            color: var(--text-secondary);
            text-decoration: none;
            flex: 1;
            position: relative;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-bottom-nav .nav-item svg {
            width: 24px;
            height: 24px;
        }

        .mobile-bottom-nav .nav-item span {
            font-size: 10px;
            margin-top: 1px;
            font-weight: 500;
        }

        .mobile-bottom-nav .nav-item.active {
            color: var(--primary);
        }

        .mobile-bottom-nav .nav-item .badge-count {
            position: absolute;
            top: 0;
            right: 50%;
            transform: translateX(calc(50% + 14px));
            background: var(--danger);
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--bg-navbar);
        }

        /* ============================================================
           SCROLLBAR – Discrète
           ============================================================ */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        /* ============================================================
           CONFIRMATION DIALOG
           ============================================================ */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .confirm-dialog {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            max-width: 420px;
            width: 90%;
            border: 1px solid var(--border-color);
            transform: scale(0.95);
            transition: transform 0.25s ease;
        }
        .confirm-overlay.active .confirm-dialog {
            transform: scale(1);
        }
        .confirm-dialog .icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
        }
        .confirm-dialog .icon.danger {
            background: #FDE8E8;
            color: var(--danger);
        }
        .confirm-dialog .icon.warning {
            background: #FEF1E6;
            color: var(--warning);
        }
        .confirm-dialog .icon.success {
            background: #E6F4EC;
            color: var(--success);
        }
        .confirm-dialog .icon svg {
            width: 28px;
            height: 28px;
        }
        .confirm-dialog h3 {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 0.375rem;
        }
        .confirm-dialog p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }
        .confirm-dialog .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        /* ============================================================
           TOAST
           ============================================================ */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 400px;
            width: 100%;
        }
        @media (max-width: 640px) {
            .toast-container {
                bottom: calc(var(--mobile-nav-height) + 1rem);
                right: 1rem;
                left: 1rem;
                max-width: none;
            }
        }
        .toast-item {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: #FFFFFF;
            font-weight: 500;
            font-size: 0.875rem;
            animation: toastIn 0.3s ease forwards;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateX(100%);
            opacity: 0;
        }
        .toast-item.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast-item.success { background: var(--success); }
        .toast-item.error { background: var(--danger); }
        .toast-item.warning { background: var(--warning); }
        .toast-item.info { background: var(--primary-light); }
        .toast-item .toast-icon {
            flex-shrink: 0;
            width: 1.25rem;
            height: 1.25rem;
        }
        .toast-item .toast-close {
            margin-left: auto;
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: background 0.15s;
            flex-shrink: 0;
        }
        .toast-item .toast-close:hover {
            background: rgba(255,255,255,0.12);
        }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* ============================================================
           FOOTER LINKS
           ============================================================ */
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .footer-links a:hover {
            color: var(--text-primary);
            text-decoration: underline;
        }
        .footer-separator {
            color: var(--text-tertiary);
        }

        /* ============================================================
           CARD – Style épuré
           ============================================================ */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 1.25rem;
            transition: border-color 0.15s ease;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        /* ============================================================
           FORMULAIRE
           ============================================================ */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-size: 0.813rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }
        .form-control {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 43, 79, 0.1);
        }
        .form-control::placeholder {
            color: var(--text-tertiary);
        }
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%237A7A82' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2.25rem;
        }
        .form-control-sm {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
        }

        /* ============================================================
           TABLE
           ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .table thead {
            background: var(--bg-secondary);
        }
        .table th {
            padding: 0.625rem 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border-color);
        }
        .table td {
            padding: 0.625rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background: var(--bg-secondary);
        }

        /* ============================================================
           BADGE
           ============================================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.688rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .badge-success { background: #E6F4EC; color: var(--success); }
        .badge-danger { background: #FDE8E8; color: var(--danger); }
        .badge-warning { background: #FEF1E6; color: var(--warning); }
        .badge-info { background: #E6EEF6; color: var(--primary); }
        .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 767px) {
            .mobile-bottom-nav {
                display: flex;
            }
            .main-content {
                padding-bottom: calc(var(--mobile-nav-height) + 1rem) !important;
            }
            .main-footer {
                padding-bottom: calc(var(--mobile-nav-height) + 1rem) !important;
            }
            .confirm-dialog {
                padding: 1.25rem;
                max-width: 95%;
            }
            .confirm-dialog .icon {
                width: 2.5rem;
                height: 2.5rem;
            }
            .confirm-dialog .icon svg {
                width: 24px;
                height: 24px;
            }
            .confirm-dialog h3 {
                font-size: 1rem;
            }
            .confirm-dialog p {
                font-size: 0.813rem;
            }
            .confirm-dialog .actions .btn {
                padding: 0.375rem 1rem;
                font-size: 0.75rem;
                min-width: 70px;
            }
            .main-content {
                padding: 0.75rem;
            }
            .main-footer {
                padding: 0.5rem 0.75rem;
            }
            .main-footer .footer-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.25rem;
            }
            .footer-separator {
                display: inline;
            }
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.813rem;
                min-height: 36px;
            }
            .btn-lg {
                padding: 0.625rem 1.25rem;
                font-size: 0.875rem;
                min-height: 42px;
            }
            .card {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem;
            }
            .main-footer {
                padding: 0.375rem 0.5rem;
                font-size: 0.7rem;
            }
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
                min-height: 32px;
            }
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.688rem;
                min-height: 28px;
            }
            .card {
                padding: 0.75rem;
            }
        }
    </style>
</head>
