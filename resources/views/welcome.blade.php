<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salang Group - Health Care International</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --primary-500: #e0b430;
            --primary-600: #d1a728;
            --gradient-primary: linear-gradient(135deg, #5ab638 0%, #3d8a2a 100%);
            --radius-lg: 12px;
            --radius-md: 8px;
        }
        
        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: #f8fafc;
            color: #1a202c;
        }
        
        .dark body {
            background: #0f172a;
            color: #e2e8f0;
        }
        
        /* Variables pour le thème */
        .dark {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --bg-navbar: #0f172a;
            --bg-footer: #0f172a;
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --text-tertiary: #64748b;
            --border-color: #334155;
            --border-light: #1e293b;
            --bg-hover: #334155;
            --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        :root:not(.dark) {
            --bg-primary: #f8fafc;
            --bg-secondary: #f1f5f9;
            --bg-card: #ffffff;
            --bg-navbar: #ffffff;
            --bg-footer: #f1f5f9;
            --text-primary: #1a202c;
            --text-secondary: #475569;
            --text-tertiary: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --bg-hover: #f1f5f9;
            --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        /* ========== HERO ========== */
        .welcome-hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .welcome-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('{{ asset("images/site.png") }}') center center / cover no-repeat;
            z-index: 0;
        }
        
        .welcome-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1;
        }
        
        .dark .welcome-hero::after {
            background: rgba(0, 0, 0, 0.7);
        }
        
        .welcome-hero .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 4rem 1.5rem;
        }
        
        .hero-badge {
            display: inline-block;
            padding: 0.25rem 1rem;
            border-radius: 9999px;
            background: rgba(90, 182, 56, 0.2);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border: 1px solid rgba(90, 182, 56, 0.3);
            margin-bottom: 1rem;
            backdrop-filter: blur(4px);
        }
        .hero-badge svg {
            display: inline-block;
            width: 0.875rem;
            height: 0.875rem;
            margin-right: 0.375rem;
            vertical-align: middle;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.1;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }
        .hero-title .highlight {
            background: linear-gradient(135deg, #5ab638 0%, #3d8a2a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 700;
            color: #d4a72b;
            margin-top: 0.5rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .hero-description {
            font-size: 1rem;
            color: rgba(255,255,255,0.85);
            max-width: 600px;
            margin: 1rem auto 0;
            line-height: 1.6;
            text-shadow: 0 1px 10px rgba(0,0,0,0.3);
        }
        .hero-description .highlight-text { color: #5ab638; font-weight: 600; }
        .hero-description .small-text { font-size: 0.8rem; opacity: 0.7; display: block; margin-top: 0.25rem; }
        
        .btn-hero {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-hero-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 24px rgba(90, 182, 56, 0.4);
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 40px rgba(90, 182, 56, 0.5);
        }
        .btn-hero-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(4px);
        }
        .btn-hero-secondary:hover {
            border-color: #5ab638;
            background: rgba(90, 182, 56, 0.2);
            transform: translateY(-3px);
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 500px;
            margin: 2rem auto 0;
        }
        .hero-stats .stat-item { text-align: center; }
        .hero-stats .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #5ab638;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .hero-stats .stat-label {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* ========== SECTIONS ========== */
        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            position: relative;
            display: inline-block;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.25rem;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }
        
        .badge-section {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(90, 182, 56, 0.12);
            color: var(--primary-500);
            border: 1px solid rgba(90, 182, 56, 0.2);
        }
        
        .icon-wrapper {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            flex-shrink: 0;
        }
        .icon-wrapper svg { width: 1.5rem; height: 1.5rem; }
        .icon-health { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
        .icon-prosperity { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .icon-freedom { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .icon-natural { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
        .icon-globe { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .icon-company { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
        .icon-primary { background: rgba(90, 182, 56, 0.12); color: #5ab638; }
        
        .value-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .value-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-500);
        }
        
        .step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            font-weight: 800;
            font-size: 1rem;
            margin: 0 auto 0.75rem;
        }
        
        .rank-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-500);
        }
        .rank-card:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-hover);
        }
        .rank-card .rank-level {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
        }
        .rank-card .rank-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .rank-card .rank-detail {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        
        .commission-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.813rem;
        }
        .commission-table th {
            background: var(--bg-secondary);
            padding: 0.625rem 0.75rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
        }
        .commission-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
        }
        .commission-table tr:hover td { background: var(--bg-hover); }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.813rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
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
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        /* ========== IMAGES ========== */
        .image-wrapper {
            width: 100%;
            border-radius: var(--radius-lg);
            overflow: hidden;
            position: relative;
        }
        
        .image-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            transition: transform 0.5s ease, filter 0.5s ease;
        }
        
        .image-wrapper:hover img {
            transform: scale(1.03);
        }
        
        /* Images avec ratio fixe pour les cartes */
        .image-cover {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            border-radius: var(--radius-lg);
        }
        
        .image-cover-square {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: var(--radius-lg);
        }
        
        .image-cover-portrait {
            width: 100%;
            aspect-ratio: 3/4;
            object-fit: cover;
            border-radius: var(--radius-lg);
        }
        
        /* Placeholder pour images manquantes */
        .image-placeholder {
            width: 100%;
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--bg-secondary);
            border: 2px dashed var(--border-color);
            transition: all 0.3s ease;
            min-height: 120px;
        }
        .image-placeholder img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }
        .image-placeholder:hover img {
            transform: scale(1.02);
        }
        .image-placeholder .placeholder-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 150px;
            color: var(--text-tertiary);
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            padding: 1.5rem;
            flex-direction: column;
            gap: 0.25rem;
        }
        .image-placeholder .placeholder-text small {
            font-weight: 400;
            font-size: 0.7rem;
            opacity: 0.7;
        }
        
        .ranks-diagram-container {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1rem;
            border: 1px solid var(--border-color);
        }
        
        /* ========== LOGO ========== */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .logo-container:hover {
            transform: scale(1.02);
        }
        
        .logo-image {
            height: 48px;
            width: auto;
            object-fit: contain;
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        .dark .logo-image {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        .logo-text .brand-name {
            font-size: 1.125rem;
            font-weight: 900;
            background: linear-gradient(135deg, #5ab638 0%, #3d8a2a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        .logo-text .brand-sub {
            font-size: 0.5rem;
            font-weight: 700;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        .dark .logo-text .brand-sub {
            color: #94a3b8;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 640px) {
            .hero-title { font-size: 1.75rem; }
            .hero-subtitle { font-size: 1rem; }
            .hero-description { font-size: 0.875rem; }
            .hero-stats { gap: 1rem; }
            .hero-stats .stat-value { font-size: 1.5rem; }
            .section-title { font-size: 1.25rem; }
            .value-card { padding: 1rem; }
            .icon-wrapper { width: 3rem; height: 3rem; }
            .icon-wrapper svg { width: 1.25rem; height: 1.25rem; }
            .commission-table { font-size: 0.7rem; }
            .commission-table th, .commission-table td { padding: 0.375rem 0.5rem; }
            .rank-card { padding: 0.75rem 1rem; }
            .rank-card .rank-name { font-size: 0.875rem; }
            .btn-hero { padding: 0.625rem 1.25rem; font-size: 0.875rem; }
            .feature-grid { grid-template-columns: 1fr; }
            .welcome-hero .hero-content { padding: 3rem 1rem; }
            .welcome-hero { min-height: auto; padding: 2rem 0; }
            .logo-image { height: 36px; }
            .logo-text .brand-name { font-size: 0.875rem; }
            .logo-text .brand-sub { font-size: 0.4rem; }
            .image-cover { aspect-ratio: 4/3; }
            .image-cover-square { aspect-ratio: 1/1; }
            .image-cover-portrait { aspect-ratio: 2/3; }
        }
        
        @media (min-width: 641px) and (max-width: 1024px) {
            .hero-title { font-size: 3rem; }
            .logo-image { height: 44px; }
        }
        
        @media (min-width: 1025px) {
            .logo-image { height: 56px; }
        }
        
        /* Utilitaires */
        .max-w-7xl { max-width: 80rem; margin: 0 auto; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .text-center { text-align: center; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mt-8 { margin-top: 2rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-8 { gap: 2rem; }
        .p-2 { padding: 0.5rem; }
        .p-3 { padding: 0.75rem; }
        .p-4 { padding: 1rem; }
        .p-8 { padding: 2rem; }
        .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
        .py-16 { padding-top: 4rem; padding-bottom: 4rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: 1fr; }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .hidden { display: none; }
        .inline-flex { display: inline-flex; }
        .w-full { width: 100%; }
        .w-auto { width: auto; }
        .h-auto { height: auto; }
        .h-10 { height: 2.5rem; }
        .h-12 { height: 3rem; }
        .h-14 { height: 3.5rem; }
        .h-48 { height: 12rem; }
        .object-cover { object-fit: cover; }
        .rounded-lg { border-radius: var(--radius-lg); }
        .rounded-full { border-radius: 9999px; }
        .border { border: 1px solid var(--border-color); }
        .border-2 { border-width: 2px; }
        .border-t { border-top: 1px solid var(--border-color); }
        .border-b { border-bottom: 1px solid var(--border-color); }
        .border-l-4 { border-left-width: 4px; }
        .bg-primary-500\/5 { background: rgba(90, 182, 56, 0.05); }
        .bg-primary-500\/10 { background: rgba(90, 182, 56, 0.1); }
        .bg-primary-500\/20 { background: rgba(90, 182, 56, 0.2); }
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
        .from-primary-500\/10 { --tw-gradient-from: rgba(90, 182, 56, 0.1); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, transparent); }
        .to-transparent { --tw-gradient-to: transparent; }
        .shadow-lg { box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .sticky { position: sticky; }
        .top-0 { top: 0; }
        .z-50 { z-index: 50; }
        .backdrop-blur-sm { backdrop-filter: blur(8px); }
        .bg-opacity-80 { background-opacity: 0.8; }
        .transition { transition: all 0.3s ease; }
        .transition-colors { transition-property: color, background-color, border-color; transition-duration: 0.3s; }
        .transition-transform { transition-property: transform; transition-duration: 0.3s; }
        .hover\:scale-105:hover { transform: scale(1.05); }
        .hover\:bg-primary-500\/20:hover { background: rgba(90, 182, 56, 0.2); }
        .hover\:bg-\[var\(--bg-secondary\)\]:hover { background: var(--bg-secondary); }
        .hover\:text-primary-500:hover { color: #c5a025; }
        .overflow-x-auto { overflow-x: auto; }
        .overflow-hidden { overflow: hidden; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .inset-0 { inset: 0; }
        .-top-2 { top: -0.5rem; }
        .-right-2 { right: -0.5rem; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-base { font-size: 1rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-3xl { font-size: 1.875rem; }
        .text-\[8px\] { font-size: 8px; }
        .text-\[10px\] { font-size: 10px; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }
        .tracking-widest { letter-spacing: 0.1em; }
        .tracking-wider { letter-spacing: 0.05em; }
        .uppercase { text-transform: uppercase; }
        .italic { font-style: italic; }
        .leading-relaxed { line-height: 1.625; }
        .text-primary-500 { color: #d5ae2b; }
        .text-\[var\(--text-primary\)\] { color: var(--text-primary); }
        .text-\[var\(--text-secondary\)\] { color: var(--text-secondary); }
        .text-\[var\(--text-tertiary\)\] { color: var(--text-tertiary); }
        .bg-\[var\(--bg-primary\)\] { background: var(--bg-primary); }
        .bg-\[var\(--bg-secondary\)\] { background: var(--bg-secondary); }
        .bg-\[var\(--bg-card\)\] { background: var(--bg-card); }
        .bg-\[var\(--bg-navbar\)\] { background: var(--bg-navbar); }
        .bg-\[var\(--bg-footer\)\] { background: var(--bg-footer); }
        .border-\[var\(--border-color\)\] { border-color: var(--border-color); }
        .border-primary-500\/20 { border-color: rgba(90, 182, 56, 0.2); }
        .border-primary-500\/30 { border-color: rgba(90, 182, 56, 0.3); }
        .border-gray-400 { border-color: #9ca3af; }
        .border-blue-400 { border-color: #60a5fa; }
        .border-yellow-400 { border-color: #fbbf24; }
        .border-orange-400 { border-color: #fb923c; }
        .border-green-400 { border-color: #34d399; }
        .border-teal-400 { border-color: #2dd4bf; }
        .border-purple-400 { border-color: #a78bfa; }
        .border-indigo-400 { border-color: #818cf8; }
        .border-pink-400 { border-color: #f472b6; }
        .text-white { color: #ffffff; }
        .bg-primary-500 { background: #d3ad32; }
        
        /* Responsive grid */
        @media (min-width: 640px) {
            .sm\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
            .sm\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            .sm\:grid-cols-5 { grid-template-columns: repeat(5, 1fr); }
            .sm\:flex-row { flex-direction: row; }
            .sm\:hidden { display: none; }
            .sm\:block { display: block; }
            .sm\:inline-flex { display: inline-flex; }
            .sm\:gap-3 { gap: 0.75rem; }
            .sm\:gap-4 { gap: 1rem; }
            .sm\:gap-6 { gap: 1.5rem; }
            .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            .sm\:py-16 { padding-top: 4rem; padding-bottom: 4rem; }
            .sm\:text-base { font-size: 1rem; }
            .sm\:text-lg { font-size: 1.125rem; }
            .sm\:text-3xl { font-size: 1.875rem; }
            .sm\:h-12 { height: 3rem; }
            .sm\:h-16 { height: 4rem; }
        }
        
        @media (min-width: 768px) {
            .md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
            .md\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            .md\:h-16 { height: 4rem; }
        }
        
        @media (min-width: 1024px) {
            .lg\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
            .lg\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; }
        }
        
        /* Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        
        /* Variables Tailwind-like pour le thème */
        .bg-\[var\(--bg-primary\)\] { background-color: var(--bg-primary); }
        .bg-\[var\(--bg-secondary\)\] { background-color: var(--bg-secondary); }
        .bg-\[var\(--bg-card\)\] { background-color: var(--bg-card); }
        .bg-\[var\(--bg-navbar\)\] { background-color: var(--bg-navbar); }
        .bg-\[var\(--bg-footer\)\] { background-color: var(--bg-footer); }
        .text-\[var\(--text-primary\)\] { color: var(--text-primary); }
        .text-\[var\(--text-secondary\)\] { color: var(--text-secondary); }
        .text-\[var\(--text-tertiary\)\] { color: var(--text-tertiary); }
        .border-\[var\(--border-color\)\] { border-color: var(--border-color); }
        .border-\[var\(--border-light\)\] { border-color: var(--border-light); }
        .bg-\[var\(--bg-hover\)\] { background-color: var(--bg-hover); }
        .shadow-\[var\(--shadow-hover\)\] { box-shadow: var(--shadow-hover); }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)]">
    
    <!-- ========== NAVIGATION ========== -->
    <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-50 backdrop-blur-sm bg-opacity-80">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                
                <!-- LOGO -->
                <a href="/" class="logo-container">
                    <img src="{{ asset('images/salang_logo.png') }}" 
                         alt="Salang Group - Health Care International" 
                         class="logo-image"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'text-2xl font-bold text-primary-500\' style=\'font-size:1.5rem;\'>SG</span>'">
                    <div class="logo-text hidden sm:block">
                        <span class="brand-sub">Health Care International</span>
                    </div>
                </a>
                
                <!-- ACTIONS -->
                <div class="flex items-center gap-1.5 sm:gap-3">
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" 
                            class="p-1.5 sm:p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors" 
                            aria-label="Changer de theme">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                        </svg>
                    </button>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm hidden xs:inline-flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex xs:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                            <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs sm:text-sm text-[var(--text-secondary)] hover:text-primary-500 transition font-medium hidden xs:inline">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden xs:inline-flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Adhérer
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex xs:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                            <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex xs:hidden p-2 rounded-lg bg-primary-500/10 hover:bg-primary-500/20 transition-colors">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- ========== HERO ========== -->
    <section class="welcome-hero">
        <div class="hero-content">
            <div class="max-w-7xl mx-auto text-center">
                
                <span class="hero-badge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Complementary & Alternative Medicine
                </span>
                
                <h1 class="hero-title">
                    SALANG GROUP
                    <br>
                    <span class="highlight">HEALTH CARE INTERNATIONAL</span>
                </h1>
                
                <p class="hero-subtitle">Your health our priority</p>
                
                <p class="hero-description">
                    Produits <span class="highlight-text">100% naturels</span> — Médecine alternative et complémentaire
                    <span class="small-text">Fondé en 2015 par Dr Lou Jiancheng Salang</span>
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 mt-8">
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Adhérer dès aujourd'hui — 30$
                    </a>
                    <a href="#how-it-works" class="btn-hero btn-hero-secondary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        En savoir plus
                    </a>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <p class="stat-value">500+</p>
                        <p class="stat-label">Membres actifs</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value">$2M+</p>
                        <p class="stat-label">Commissions versées</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value">50+</p>
                        <p class="stat-label">Pays représentés</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== QUI SOMMES-NOUS ========== -->
    <section class="py-12 sm:py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">À propos</span><br>
                <h2 class="section-title mt-3">Qui sommes-nous ?</h2>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed">
                        Fondé en <strong class="text-[var(--text-primary)]">2015</strong> par <strong class="text-[var(--text-primary)]">Dr Lou Jiancheng Salang</strong>, 
                        Salang Group est une entreprise internationale spécialisée dans la production de 
                        produits de santé <strong class="text-primary-500">100% naturels</strong>.
                    </p>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed">
                        <strong class="text-[var(--text-primary)]">Notre siège</strong> est basé à Hong Kong, au cœur de l'innovation asiatique, 
                        et notre mission est simple mais puissante : 
                        <span class="text-primary-500 font-medium">Améliorer la vie des gens à travers des solutions de santé naturelle, 
                        tout en leur offrant une voie vers la liberté financière.</span>
                    </p>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="bg-[var(--bg-card)] p-3 rounded-lg border border-[var(--border-color)] text-center">
                            <div class="icon-wrapper icon-natural mx-auto mb-1">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <p class="text-[10px] text-[var(--text-secondary)]">Produits 100% naturels</p>
                        </div>
                        <div class="bg-[var(--bg-card)] p-3 rounded-lg border border-[var(--border-color)] text-center">
                            <div class="icon-wrapper icon-globe mx-auto mb-1">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-[10px] text-[var(--text-secondary)]">Présence internationale</p>
                        </div>
                    </div>
                </div>
                
                <!-- IMAGE : ÉQUIPE -->
                <div class="grid grid-cols-1 gap-4">
                    <div class="image-wrapper">
                        <img src="{{ asset('images/team.png') }}" 
                             alt="Équipe Salang Group - Siège à Hong Kong" 
                             class="image-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\'><span>📸 Équipe Salang</span><small>Ajoutez team.jpg dans resources/images/</small></div></div>'">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="value-card">
                            <div class="icon-wrapper icon-health mx-auto">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-[var(--text-primary)]">Santé</h3>
                            <p class="text-xs text-[var(--text-secondary)]">Médecine alternative et prévention</p>
                        </div>
                        <div class="value-card">
                            <div class="icon-wrapper icon-prosperity mx-auto">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-[var(--text-primary)]">Prospérité</h3>
                            <p class="text-xs text-[var(--text-secondary)]">Revenu régulier et croissant</p>
                        </div>
                        <div class="value-card">
                            <div class="icon-wrapper icon-freedom mx-auto">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-[var(--text-primary)]">Liberté</h3>
                            <p class="text-xs text-[var(--text-secondary)]">Revenus passifs, indépendance et choix de vie</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== POURQUOI SALANG ========== -->
    <section class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Pourquoi nous choisir ?</span><br>
                <h2 class="section-title mt-3">Pourquoi Salang ?</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Carte 1 -->
                <div class="value-card text-left">
                    <div class="image-wrapper mb-3">
                        <img src="{{ asset('images/natural-products.jpeg') }}" 
                             alt="Produits naturels Salang" 
                             class="image-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\' style=\'min-height:180px;\'><span>🌿 Produits naturels</span><small>Ajoutez natural-products.jpg</small></div></div>'">
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="icon-wrapper icon-natural" style="width: 2.5rem; height: 2.5rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[var(--text-primary)]">Produits naturels de haute qualité</h3>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">Basés sur la médecine alternative et complémentaire</p>
                        </div>
                    </div>
                </div>

                <!-- Carte 2 -->
                <div class="value-card text-left">
                    <div class="image-wrapper mb-3">
                        <img src="{{ asset('images/holistic-health.jpeg') }}" 
                             alt="Approche holistique Salang" 
                             class="image-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\' style=\'min-height:180px;\'><span>🧘 Approche holistique</span><small>Ajoutez holistic-health.jpg</small></div></div>'">
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="icon-wrapper icon-primary" style="width: 2.5rem; height: 2.5rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[var(--text-primary)]">Approche holistique</h3>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">Renforcer le corps, prévenir les maladies, équilibre santé optimal</p>
                        </div>
                    </div>
                </div>

                <!-- Carte 3 -->
                <div class="value-card text-left">
                    <div class="image-wrapper mb-3">
                        <img src="{{ asset('images/global-growth.jpg') }}" 
                             alt="Croissance internationale Salang Group" 
                             class="image-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\' style=\'min-height:180px;\'><span>🌍 Croissance internationale</span><small>Ajoutez global-growth.jpg</small></div></div>'">
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="icon-wrapper icon-company" style="width: 2.5rem; height: 2.5rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[var(--text-primary)]">Entreprise solide et transparente</h3>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">En pleine croissance à l'international</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== ADHÉRER À SALANG ========== -->
    <section class="py-12 sm:py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Opportunité en or</span><br>
                <h2 class="section-title mt-3">Adhérer à Salang</h2>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
                    Avec seulement <strong class="text-primary-500 text-lg">30 $</strong> d'adhésion, vous ouvrez la porte à trois grandes richesses
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="value-card">
                    <div class="icon-wrapper icon-health mx-auto">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--text-primary)]">La Santé</h3>
                    <p class="text-sm text-[var(--text-secondary)] mt-2">
                        Bénéficiez de nos produits exclusifs, issus de la médecine naturelle 
                        et des traditions asiatiques millénaires
                    </p>
                </div>
                <div class="value-card border-2 border-primary-500/30 relative">
                    <span class="absolute -top-2 -right-2 bg-primary-500 text-white text-[8px] font-bold px-2 py-0.5 rounded-full">POPULAIRE</span>
                    <div class="icon-wrapper icon-prosperity mx-auto">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--text-primary)]">La Prospérité</h3>
                    <p class="text-sm text-[var(--text-secondary)] mt-2">
                        Gagnez un revenu régulier et croissant en recommandant Salang autour de vous
                    </p>
                </div>
                <div class="value-card">
                    <div class="icon-wrapper icon-freedom mx-auto">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--text-primary)]">La Liberté</h3>
                    <p class="text-sm text-[var(--text-secondary)] mt-2">
                        Développez votre réseau et construisez un système de revenus passifs
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== COMMENT ÇA FONCTIONNE ========== -->
    <section id="how-it-works" class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Simple et accessible</span><br>
                <h2 class="section-title mt-3">Comment ça fonctionne ?</h2>
            </div>
            
            <!-- IMAGE : SCHÉMA FONCTIONNEMENT -->
            <div class="image-wrapper mb-8">
                <img src="{{ asset('images/how-it-works.png') }}" 
                     alt="Schéma du fonctionnement Salang - 5 étapes pour réussir" 
                     class="w-full max-w-4xl mx-auto rounded-lg"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\'><span>📊 Schéma du fonctionnement</span><small>Ajoutez how-it-works.png</small></div></div>'">
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="step-number">1</div>
                    <h4 class="font-bold text-sm text-[var(--text-primary)]">Adhérez</h4>
                    <p class="text-xs text-[var(--text-secondary)]">Pour 30 $</p>
                </div>
                <div class="text-center">
                    <div class="step-number">2</div>
                    <h4 class="font-bold text-sm text-[var(--text-primary)]">Utilisez</h4>
                    <p class="text-xs text-[var(--text-secondary)]">Découvrez nos produits naturels</p>
                </div>
                <div class="text-center">
                    <div class="step-number">3</div>
                    <h4 class="font-bold text-sm text-[var(--text-primary)]">Partagez</h4>
                    <p class="text-xs text-[var(--text-secondary)]">L'opportunité autour de vous</p>
                </div>
                <div class="text-center">
                    <div class="step-number">4</div>
                    <h4 class="font-bold text-sm text-[var(--text-primary)]">Gagnez</h4>
                    <p class="text-xs text-[var(--text-secondary)]">Des commissions sur chaque adhésion</p>
                </div>
                <div class="text-center">
                    <div class="step-number">5</div>
                    <h4 class="font-bold text-sm text-[var(--text-primary)]">Développez</h4>
                    <p class="text-xs text-[var(--text-secondary)]">Votre réseau et vos revenus</p>
                </div>
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-sm text-[var(--text-secondary)] max-w-2xl mx-auto">
                    <span class="font-semibold text-[var(--text-primary)]">Salang est bien plus qu'une entreprise :</span> 
                    c'est une communauté engagée pour une vie meilleure. 
                    Vous ne vendez pas simplement des produits, vous partagez un mode de vie 
                    basé sur la prévention, la liberté et l'abondance.
                </p>
            </div>
        </div>
    </section>

    <!-- ========== COMMENCER À GAGNER ========== -->
    <section class="py-12 sm:py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Commencer à gagner</span><br>
                <h2 class="section-title mt-3">Vos revenus avec Salang</h2>
                <p class="text-sm text-[var(--text-secondary)] mt-2">
                    La meilleure façon de réussir est de commencer maintenant. Votre rêve serait réalisé.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rank-card">
                    <div class="rank-level">Bonus</div>
                    <div class="rank-name">Bénéfice de détail</div>
                    <div class="rank-detail">25% du prix de chaque produit que vous achetez</div>
                </div>
                <div class="rank-card">
                    <div class="rank-level">Bonus</div>
                    <div class="rank-name">Bonus Direct</div>
                    <div class="rank-detail">Paiement obtenu à partir de l'achat personnel de chaque distributeur (PBV)</div>
                </div>
                <div class="rank-card">
                    <div class="rank-level">Bonus</div>
                    <div class="rank-name">Bonus Indirect</div>
                    <div class="rank-detail">De 2% à 39% selon le niveau entre vous et les descendants</div>
                </div>
                <div class="rank-card">
                    <div class="rank-level">Bonus</div>
                    <div class="rank-name">Bonus de Leadership</div>
                    <div class="rank-detail">À partir du 5e niveau, pourcentage variable selon votre niveau</div>
                </div>
                <div class="rank-card">
                    <div class="rank-level">Récompense</div>
                    <div class="rank-name">Prix Supplémentaire</div>
                    <div class="rank-detail">Manager → Woofer, Directeur Principal → LCD TV, Saphire Manager → Petite voiture, Blue Diamond → Grande voiture, Diamond Pearl → House</div>
                </div>
                <div class="rank-card">
                    <div class="rank-level">Bonus</div>
                    <div class="rank-name">Bonus d'Encouragement</div>
                    <div class="rank-detail">À partir de la qualité Rubis, mondial sauf le Diamant 1</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== GRADES ET QUALITÉS ========== -->
    <section class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Évolution</span><br>
                <h2 class="section-title mt-3">Différentes qualités et exigences</h2>
                <br><p class="text-xs text-[var(--text-secondary)] mt-1">PV: achats cumulés, 1PV = 1 $ | BV: le mois d'achat cumulé</p>
            </div>
            
            <!-- IMAGE : ORGANIGRAMME DES GRADES -->
            <div class="ranks-diagram-container mb-8">
                <div class="image-wrapper">
                    <img src="{{ asset('images/ranks-diagram.png') }}" 
                         alt="Organigramme des grades Salang - Distributeur à Diamond Pearl" 
                         class="w-full max-w-5xl mx-auto rounded-lg"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\'><span>📈 Organigramme des grades</span><small>Ajoutez ranks-diagram.png</small></div></div>'">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rank-card border-l-4 border-gray-400">
                    <span class="rank-level">Niveau 1</span>
                    <div class="rank-name">1. Distributeur</div>
                    <div class="rank-detail">Dépensez 30 $, devenez membre de Salang Company</div>
                </div>
                <div class="rank-card border-l-4 border-blue-400">
                    <span class="rank-level">Niveau 2</span>
                    <div class="rank-name">2. Qualification</div>
                    <div class="rank-detail">Achat unique ou cumulatif ≥ 100 BV</div>
                </div>
                <div class="rank-card border-l-4 border-yellow-400">
                    <span class="rank-level">Niveau 3</span>
                    <div class="rank-name">3. Cumul Directeur</div>
                    <div class="rank-detail">Achat unique ou cumulatif ≥ 200 BV • BV * 22% + bonus indirect</div>
                </div>
                <div class="rank-card border-l-4 border-orange-400">
                    <span class="rank-level">Niveau 4</span>
                    <div class="rank-name">4. Directeur</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 filleuls Manager, PV groupe ≥ 1000 PV<br>
                        • 2 filleuls Manager, PV groupe ≥ 2200 PV<br>
                        • PV personnelle ≥ 1000 PV<br>
                        <strong>Bénéfice:</strong> BV * 26% + bonus indirect
                    </div>
                </div>
                <div class="rank-card border-l-4 border-green-400">
                    <span class="rank-level">Niveau 5</span>
                    <div class="rank-name">5. Manager Senior</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 filleuls Manager, PV groupe ≥ 3800 PV<br>
                        • 2 filleuls Manager, PV groupe ≥ 7800 PV<br>
                        • 2 Manager + 4 Directeurs, PV groupe ≥ 3800 PV<br>
                        • 1 Manager + 6 Directeurs, PV groupe ≥ 3800 PV<br>
                        <strong>Bénéfice:</strong> BV * 30% + bonus indirect/leadership
                    </div>
                </div>
                <div class="rank-card border-l-4 border-teal-400">
                    <span class="rank-level">Niveau 6</span>
                    <div class="rank-name">6. Directeur de l'Envolée</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 Manager Senior, PV groupe ≥ 16000 PV<br>
                        • 2 Manager Senior, PV groupe ≥ 35000 PV<br>
                        • 2 Manager Senior + 6 Manager, PV groupe ≥ 16000 PV<br>
                        • 1 Manager Senior + 6 Manager, PV groupe ≥ 16000 PV<br>
                        <strong>Bénéfice:</strong> BV * 34% + bonus indirect/leadership
                    </div>
                </div>
                <div class="rank-card border-l-4 border-purple-400">
                    <span class="rank-level">Niveau 7</span>
                    <div class="rank-name">7. Saphire Manager</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 Directeur Envolée, PV groupe ≥ 73000 PV<br>
                        • 2 Directeur Envolée, PV groupe ≥ 145000 PV<br>
                        • 2 Directeur Envolée + 4 Manager Senior, PV groupe ≥ 73000 PV<br>
                        • 1 Directeur Envolée + 6 Manager Senior, PV groupe ≥ 73000 PV<br>
                        <strong>Bénéfice:</strong> BV * 40% + bonus indirect/leadership
                    </div>
                </div>
                <div class="rank-card border-l-4 border-indigo-400">
                    <span class="rank-level">Niveau 8</span>
                    <div class="rank-name">8. Diamant Bleu</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 Saphire Manager, PV groupe ≥ 280000 PV<br>
                        • 2 Saphire Manager, PV groupe ≥ 580000 PV<br>
                        • 2 Saphire + 4 Directeur Envolée, PV groupe ≥ 280000 PV<br>
                        • 1 Saphire + 6 Directeur Envolée, PV groupe ≥ 280000 PV<br>
                        <strong>Bénéfice:</strong> BV * 43% + bonus indirect/leadership
                    </div>
                </div>
                <div class="rank-card border-l-4 border-pink-400">
                    <span class="rank-level">Niveau 9</span>
                    <div class="rank-name">9. Diamond Pearl</div>
                    <div class="rank-detail text-xs">
                        <strong>Options:</strong><br>
                        • 3 Diamant Bleu, PV groupe ≥ 400000 PV<br>
                        • 2 Diamant Bleu, PV groupe ≥ 780000 PV<br>
                        • 2 Diamant Bleu + 4 Saphire, PV groupe ≥ 400000 PV<br>
                        • 1 Diamant Bleu + 6 Saphire, PV groupe ≥ 400000 PV<br>
                        <strong>Bénéfice:</strong> BV * 45% + bonus indirect/leadership
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TABLEAUX DES BONUS ========== -->
    <section class="py-12 sm:py-16 bg-[var(--bg-secondary)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <span class="badge-section">Structure des bonus</span><br>
                <h2 class="section-title mt-3">Pourcentages de bonus</h2>
            </div>
            
            <!-- IMAGE : GRAPHIQUE DES BONUS -->
            <div class="image-wrapper mb-6">
                <img src="{{ asset('images/bonus-chart.png') }}" 
                     alt="Graphique des pourcentages de bonus Salang" 
                     class="w-full max-w-4xl mx-auto rounded-lg"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-placeholder\'><div class=\'placeholder-text\'><span>📊 Graphique des bonus</span><small>Ajoutez bonus-chart.png</small></div></div>'">
            </div>
            
            <div class="overflow-x-auto">
                <table class="commission-table">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Bonus direct</th>
                            <th>Bonus indirect</th>
                            <th>Leadership</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cumul Directeur</strong></td>
                            <td>22%</td>
                            <td>4% - 20%</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>Directeur</strong></td>
                            <td>26%</td>
                            <td>4% - 20%</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>Manager Senior</strong></td>
                            <td>30%</td>
                            <td>4% - 24%</td>
                            <td>0.5%</td>
                        </tr>
                        <tr>
                            <td><strong>Directeur de l'Envolée</strong></td>
                            <td>34%</td>
                            <td>4% - 28%</td>
                            <td>1.1%</td>
                        </tr>
                        <tr>
                            <td><strong>Saphire Manager</strong></td>
                            <td>40%</td>
                            <td>6% - 34%</td>
                            <td>1.8%</td>
                        </tr>
                        <tr>
                            <td><strong>Diamant Bleu</strong></td>
                            <td>43%</td>
                            <td>3% - 37%</td>
                            <td>2.6%</td>
                        </tr>
                        <tr>
                            <td><strong>Diamond Pearl</strong></td>
                            <td>45%</td>
                            <td>2% - 39%</td>
                            <td>3.5%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 text-center text-xs text-[var(--text-secondary)]">
                <p>* Conditions de PV personnel et PV groupe applicables pour chaque niveau</p>
            </div>
        </div>
    </section>

    <!-- ========== POURQUOI VOUS ET MAINTENANT ========== -->
    <section class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <span class="badge-section">Rejoignez-nous</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">
                        Pourquoi vous et <br>pourquoi maintenant ?
                    </h2>
                    <div class="space-y-3 mt-4">
                        <div class="flex items-start gap-3">
                            <div class="icon-wrapper icon-health" style="width: 2.5rem; height: 2.5rem;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">Vous méritez une santé optimale</p>
                                <p class="text-sm text-[var(--text-secondary)]">Produits naturels pour votre bien-être</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="icon-wrapper icon-prosperity" style="width: 2.5rem; height: 2.5rem;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">Vous méritez un revenu qui grandit avec vous</p>
                                <p class="text-sm text-[var(--text-secondary)]">Commencez dès maintenant à bâtir votre avenir</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="icon-wrapper icon-freedom" style="width: 2.5rem; height: 2.5rem;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">Vous méritez de construire votre avenir</p>
                                <p class="text-sm text-[var(--text-secondary)]">À votre rythme, en toute liberté</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-primary-500/5 border border-primary-500/20 rounded-lg">
                        <p class="text-sm text-[var(--text-secondary)] italic">
                            "Santé, Prospérité, Liberté : c'est la promesse de Salang Group."
                        </p>
                        <p class="text-xs text-primary-500 font-medium mt-1">Dr Franck KOWAVING</p>
                    </div>
                </div>
                
                <!-- IMAGE : TÉMOIGNAGE -->
                <div class="bg-gradient-to-br from-primary-500/10 to-transparent p-8 rounded-2xl border border-primary-500/20">
                    <div class="text-center">
                        <div class="icon-wrapper icon-primary mx-auto">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-[var(--text-primary)]">Avec seulement 30 $</h3>
                        <p class="text-[var(--text-secondary)] mt-2">
                            Vous pouvez transformer votre santé, votre situation financière,<br>
                            et même inspirer les autres à faire de même.
                        </p>
                        <a href="{{ route('register') }}" class="btn-hero btn-hero-primary mt-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Commencez maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="bg-[var(--bg-footer)] border-t border-[var(--border-color)] py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/salang_logo.png') }}" 
                         alt="Salang Group - Logo" 
                         class="h-10 sm:h-12 w-auto"
                         onerror="this.style.display='none'">
                    <div>
                        <span class="font-bold text-[var(--text-primary)] text-sm sm:text-base">Salang Group</span>
                        <span class="block text-[8px] text-[var(--text-tertiary)] uppercase tracking-widest">Health Care International</span>
                    </div>
                </div>
                
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] text-center">
                    &copy; {{ date('Y') }} Salang Group. Tous droits réservés.
                </p>
                
                <div class="flex gap-3 sm:gap-4 text-xs sm:text-sm text-[var(--text-secondary)]">
    <a href="{{ route('legal.mentions') }}" class="hover:text-primary-500 transition">
        Mentions légales
    </a>
    <a href="{{ route('legal.privacy') }}" class="hover:text-primary-500 transition">
        Confidentialité
    </a>
    <a href="{{ route('legal.terms') }}" class="hover:text-primary-500 transition hidden xs:inline">
        Conditions générales
    </a>
</div>
            </div>
            <div class="mt-2 text-center text-[10px] text-[var(--text-tertiary)]">
                <span>Complementary & Alternative Medicine — Your health our priority</span>
            </div>
        </div>
    </footer>

    @livewireScripts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Theme toggle
        (function() {
            'use strict';
            
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
            
            function initTheme() {
                var toggle = document.getElementById('theme-toggle');
                var icon = document.getElementById('theme-icon');
                
                if (!toggle) return;
                
                function setTheme(theme) {
                    if (theme === 'dark') {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                    updateIcon();
                }
                
                function updateIcon() {
                    if (!icon) return;
                    if (document.documentElement.classList.contains('dark')) {
                        icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
                    } else {
                        icon.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
                    }
                }
                
                var newToggle = toggle.cloneNode(true);
                toggle.parentNode.replaceChild(newToggle, toggle);
                
                newToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (document.documentElement.classList.contains('dark')) {
                        setTheme('light');
                    } else {
                        setTheme('dark');
                    }
                });
                
                setTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTheme);
            } else {
                initTheme();
            }
        })();
    </script>
</body>
</html>