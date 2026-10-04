<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Salang MLM')</title>
    
    <!-- ============================================ -->
    <!-- PWA HEAD - GÉRÉ PAR LE PACKAGE              -->
    <!-- ============================================ -->
    @if(class_exists('PwaKit'))
        {!! PwaKit::head() !!}
    @endif
    
    <!-- ============================================ -->
    <!-- MÉTADONNÉES PWA MANUELLES (SÉCURITÉ)        -->
    <!-- ============================================ -->
    <meta name="theme-color" content="#5ab638">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    
    <!-- ============================================ -->
    <!-- FAVICONS ET ICÔNES (COMPATIBILITÉ)          -->
    <!-- ============================================ -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- ============================================ -->
    <!-- FONTS ET STYLES                              -->
    <!-- ============================================ -->
    @stack('styles')

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ===== SCROLLBAR ===== */
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

        /* ===== TOAST CUSTOM ===== */
        .custom-toast {
            animation: slideUp 0.3s ease forwards;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== CONFIRMATION DIALOG ===== */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }
        .confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .confirm-dialog {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        .confirm-overlay.active .confirm-dialog {
            transform: scale(1) translateY(0);
        }
        .confirm-dialog .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
        }
        .confirm-dialog .icon.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        .confirm-dialog .icon.danger {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        .confirm-dialog .icon.success {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }
        .confirm-dialog h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .confirm-dialog p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .confirm-dialog .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        .confirm-dialog .actions .btn {
            padding: 0.5rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            min-width: 100px;
        }
        .confirm-dialog .actions .btn-cancel {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .confirm-dialog .actions .btn-cancel:hover {
            background: var(--bg-hover);
        }
        .confirm-dialog .actions .btn-confirm {
            background: #ef4444;
            color: white;
        }
        .confirm-dialog .actions .btn-confirm:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .confirm-dialog .actions .btn-confirm.success {
            background: #22c55e;
        }
        .confirm-dialog .actions .btn-confirm.success:hover {
            background: #16a34a;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        @media (max-width: 767px) {
            .confirm-dialog {
                padding: 1.5rem;
                max-width: 95%;
            }
            .confirm-dialog .icon {
                width: 48px;
                height: 48px;
                font-size: 1.5rem;
            }
            .confirm-dialog h3 {
                font-size: 1rem;
            }
            .confirm-dialog p {
                font-size: 0.813rem;
            }
            .confirm-dialog .actions .btn {
                padding: 0.375rem 1rem;
                font-size: 0.813rem;
                min-width: 80px;
            }
        }
    </style>
</head>
