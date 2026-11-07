@extends('layouts.app')

@section('title', 'Supervision IT')

@push('styles')
<style>
/* Accueil IT desktop — mêmes primitives que le dashboard admin */
.it-manager-shell .it-dash .stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem 1.25rem;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
    text-decoration: none !important;
    display: block;
    color: inherit;
}
.it-manager-shell .it-dash .stat-card:hover {
    box-shadow: var(--shadow-md);
}
.it-manager-shell .it-dash .stat-card:active {
    transform: scale(0.98);
}
/* Annule .member-app .stat-card .stat-icon { position:absolute } */
.it-manager-shell .it-dash .stat-card {
    position: relative;
}
.it-manager-shell .it-dash .stat-card .stat-icon,
.it-manager-shell .it-dash .quick-action .quick-icon {
    position: static !important;
    inset: auto !important;
    opacity: 1 !important; /* annule .member-app .stat-card .stat-icon { opacity: 0.12 } */
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.it-manager-shell .it-dash .stat-card .stat-icon svg,
.it-manager-shell .it-dash .quick-action .quick-icon svg {
    width: 1.25rem;
    height: 1.25rem;
    opacity: 1 !important;
    color: inherit;
    stroke: currentColor !important;
    fill: none;
}
.it-manager-shell .it-dash .stat-icon-primary {
    background: rgba(30, 93, 173, 0.14) !important;
    color: #1E5DAD !important;
}
.it-manager-shell .it-dash .stat-icon-success {
    background: rgba(28, 126, 74, 0.14) !important;
    color: #1C7E4A !important;
}
.it-manager-shell .it-dash .stat-icon-info {
    background: rgba(37, 99, 235, 0.14) !important;
    color: #2563EB !important;
}
.it-manager-shell .it-dash .stat-icon-warning {
    background: rgba(245, 166, 35, 0.18) !important;
    color: #B54708 !important;
}
.it-manager-shell .it-dash .stat-icon-purple {
    background: rgba(124, 58, 237, 0.14) !important;
    color: #7C3AED !important;
}
.it-manager-shell .it-dash .stat-card .stat-value {
    font-size: 1.375rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 0.125rem;
}
.it-manager-shell .it-dash .stat-card .stat-label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-dash .stat-card .stat-detail {
    font-size: 0.625rem;
    color: var(--text-tertiary);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.25rem;
}
.it-manager-shell .it-dash .stat-card .stat-detail .value-success { color: #1C7E4A; font-weight: 600; }
.it-manager-shell .it-dash .stat-card .stat-detail .value-danger { color: #B91C1C; font-weight: 600; }
.it-manager-shell .it-dash .stat-card .stat-detail .value-warning { color: #B54708; font-weight: 600; }

.it-manager-shell .it-dash .quick-action {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    text-align: center;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    text-decoration: none !important;
    display: block;
}
.it-manager-shell .it-dash .quick-action:hover {
    box-shadow: var(--shadow-md);
}
.it-manager-shell .it-dash .quick-action:active {
    transform: scale(0.97);
}
.it-manager-shell .it-dash .quick-action .quick-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    margin: 0 auto 0.375rem;
}
.it-manager-shell .it-dash .quick-action .quick-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-primary);
}

.it-manager-shell .it-dash .activity-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 6px;
    transition: background 0.15s ease;
    border: 1px solid transparent;
    text-decoration: none !important;
    color: inherit;
}
.it-manager-shell .it-dash .activity-item:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}
.it-manager-shell .it-dash .activity-item .activity-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.688rem;
    color: white;
    flex-shrink: 0;
    background: var(--primary-blue);
}
.it-manager-shell .it-dash .activity-item .activity-avatar.is-amber {
    background: #B54708;
}
.it-manager-shell .it-dash .activity-item .activity-content {
    flex: 1;
    min-width: 0;
}
.it-manager-shell .it-dash .activity-item .activity-title {
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.it-manager-shell .it-dash .activity-item .activity-subtitle {
    font-size: 0.688rem;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.it-manager-shell .it-dash .activity-item .activity-time {
    font-size: 0.625rem;
    color: var(--text-tertiary);
    flex-shrink: 0;
}

.it-manager-shell .it-dash .card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.it-manager-shell .it-dash .badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.it-manager-shell .it-dash .badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.it-manager-shell .it-dash .badge-warning { background: rgba(245, 166, 35, 0.14); color: #B54708; border-color: rgba(245, 166, 35, 0.2); }
.it-manager-shell .it-dash .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

@keyframes it-dash-fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.it-manager-shell .it-dash .animate-fadeInUp { animation: it-dash-fadeInUp 0.3s ease forwards; }
.it-manager-shell .it-dash .delay-1 { animation-delay: 0.05s; }
.it-manager-shell .it-dash .delay-2 { animation-delay: 0.1s; }
.it-manager-shell .it-dash .delay-3 { animation-delay: 0.15s; }
.it-manager-shell .it-dash .delay-4 { animation-delay: 0.2s; }
.it-manager-shell .it-dash .delay-5 { animation-delay: 0.25s; }
.it-manager-shell .it-dash .delay-6 { animation-delay: 0.3s; }
.it-manager-shell .it-dash .delay-7 { animation-delay: 0.35s; }
</style>
@endpush

@section('content')
@php
    $hour = (int) now()->format('G');
    if ($hour >= 5 && $hour < 12) {
        $salutation = 'Bonjour';
    } elseif ($hour >= 12 && $hour < 18) {
        $salutation = 'Bon après-midi';
    } elseif ($hour >= 18 && $hour < 22) {
        $salutation = 'Bonsoir';
    } else {
        $salutation = 'Bonne nuit';
    }
    $userName = auth()->user()?->name ?? 'Responsable IT';
    $attention = (int) $stats['messages_new'] + (int) $stats['questions_open'] + (int) $stats['register_pending_adhesion'];
@endphp

{{-- ========== Desktop : structure alignée sur admin ========== --}}
<div class="it-dash it-desktop-only space-y-4">
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
            Tableau de bord IT
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            {{ $salutation }}, <span class="font-semibold text-[var(--primary-blue)]">{{ $userName }}</span>
            <span class="text-[var(--text-tertiary)] text-xs ml-1">({{ date('H:i') }})</span>
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <a href="{{ route('site-management.contacts.index', ['status' => 'new']) }}" class="stat-card animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Messages</p>
                    <p class="stat-value text-[var(--primary-blue)]">{{ number_format($stats['messages_new']) }}</p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Nouveaux <span class="value-warning">{{ number_format($stats['messages_new']) }}</span></span>
                <span>Total <span class="value-success">{{ number_format($stats['messages_total']) }}</span></span>
            </div>
        </a>

        <a href="{{ route('questions.index', ['status' => 'open']) }}" class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Questions</p>
                    <p class="stat-value text-[var(--ui-stat-success)]">{{ number_format($stats['questions_open']) }}</p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Ouvertes <span class="value-warning">{{ number_format($stats['questions_open']) }}</span></span>
                <span>Total <span class="value-success">{{ number_format($stats['questions_total']) }}</span></span>
            </div>
        </a>

        <a href="{{ route('it-manager.follow-up.index', ['filter' => 'adhesion']) }}" class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Sans adhésion</p>
                    <p class="stat-value text-[var(--ui-stat-warning)]">{{ number_format($stats['register_pending_adhesion']) }}</p>
                </div>
                <div class="stat-icon stat-icon-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>14 derniers jours</span>
                <span>À traiter <span class="value-warning">{{ number_format($stats['register_pending_adhesion']) }}</span></span>
            </div>
        </a>

        <a href="{{ route('it-manager.activity-register.show') }}" class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Registre du jour</p>
                    <p class="stat-value text-[var(--ui-stat-info)]">{{ number_format($stats['register_today']) }}</p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Participants aujourd’hui</span>
                <span>Pubs <span class="value-success">{{ number_format($stats['publications_published']) }}</span></span>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
        <div class="card animate-fadeInUp delay-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Messages du site
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentMessages->count() }}
                </span>
            </div>

            <div class="space-y-1 max-h-56 overflow-y-auto">
                @forelse ($recentMessages as $msg)
                    <a href="{{ route('site-management.contacts.show', $msg) }}" class="activity-item">
                        <div class="activity-avatar">{{ strtoupper(mb_substr($msg->name, 0, 1)) }}</div>
                        <div class="activity-content">
                            <p class="activity-title">{{ $msg->name }}</p>
                            <p class="activity-subtitle">{{ $msg->subject }}</p>
                            <p class="activity-time">{{ $msg->created_at?->diffForHumans() }}</p>
                        </div>
                        <span @class([
                            'badge',
                            'badge-warning' => $msg->status === 'new',
                            'badge-neutral' => $msg->status !== 'new',
                        ])>{{ $msg->statusLabel() }}</span>
                    </a>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-3 text-sm">
                        Aucun message
                    </p>
                @endforelse
            </div>

            <div class="mt-2 text-right">
                <a href="{{ route('site-management.contacts.index') }}"
                   class="text-xs text-[var(--primary-blue)] hover:underline font-medium transition">
                    Voir tous →
                </a>
            </div>
        </div>

        <div class="card animate-fadeInUp delay-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Questions membres
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentQuestions->count() }}
                </span>
            </div>

            <div class="space-y-1 max-h-56 overflow-y-auto">
                @forelse ($recentQuestions as $conversation)
                    <a href="{{ route('questions.show', $conversation) }}" class="activity-item">
                        <div class="activity-avatar is-amber">
                            {{ strtoupper(mb_substr($conversation->member?->name ?? 'M', 0, 1)) }}
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">{{ $conversation->member?->name ?? 'Membre' }}</p>
                            <p class="activity-subtitle">
                                {{ \Illuminate\Support\Str::limit($conversation->latestMessage?->body ?? '—', 72) }}
                            </p>
                            <p class="activity-time">
                                {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span @class([
                            'badge',
                            'badge-warning' => $conversation->status === 'open',
                            'badge-success' => $conversation->status !== 'open',
                        ])>{{ $conversation->status === 'open' ? 'Ouverte' : 'Résolue' }}</span>
                    </a>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-3 text-sm">
                        Aucune question
                    </p>
                @endforelse
            </div>

            <div class="mt-2 text-right">
                <a href="{{ route('questions.index') }}"
                   class="text-xs text-[var(--primary-blue)] hover:underline font-medium transition">
                    Voir toutes →
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-7">
        <a href="{{ route('it-manager.activity-register.show') }}" class="quick-action">
            <div class="quick-icon stat-icon-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <p class="quick-label">Ouvrir le registre</p>
        </a>

        <a href="{{ route('it-manager.follow-up.index') }}" class="quick-action">
            <div class="quick-icon stat-icon-warning">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <p class="quick-label">Suivi participants</p>
        </a>

        <a href="{{ route('site-management.contacts.index') }}" class="quick-action">
            <div class="quick-icon stat-icon-info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="quick-label">Messages du site</p>
        </a>

        <a href="{{ route('it-manager.announcements.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <p class="quick-label">Nouvelle annonce</p>
        </a>
    </div>
</div>

{{-- ========== Mobile : hero premium ========== --}}
<div class="it-home it-mobile-only">
    <header class="it-home-hero">
        <div class="it-home-hero__glow" aria-hidden="true"></div>
        <div class="it-home-hero__glow it-home-hero__glow--accent" aria-hidden="true"></div>
        <div class="it-home-hero__mesh" aria-hidden="true"></div>
        <div class="it-home-hero__orbit" aria-hidden="true"></div>
        <svg class="it-home-hero__spark" viewBox="0 0 120 48" fill="none" aria-hidden="true">
            <polyline points="2,36 22,28 38,32 58,14 78,22 98,8 118,16"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <div class="it-home-hero__top">
            <div class="it-home-hero__copy min-w-0">
                <p class="it-home-hero__eyebrow">
                    <span class="it-home-hero__pill">Supervision</span>
                    <span class="it-home-hero__date">{{ now()->translatedFormat('l d F') }}</span>
                </p>
                <h1 class="it-home-hero__title">
                    <span class="it-home-hero__salute">{{ $salutation }},</span>
                    <span class="it-home-hero__name">{{ \Illuminate\Support\Str::before($userName, ' ') }}</span>
                </h1>
                <p class="it-home-hero__sub">Pilotage registre, messages site et assistance membres.</p>
            </div>
            <span class="it-home-hero__badge" title="Éléments à traiter">
                <span class="it-home-hero__badge-pulse" aria-hidden="true"></span>
                <span class="it-home-hero__badge-value">{{ $attention }}</span>
            </span>
        </div>

        <div class="it-home-hero__spotlight">
            <a href="{{ route('site-management.contacts.index', ['status' => 'new']) }}" class="it-home-spot">
                <p class="it-home-spot__value">{{ $stats['messages_new'] }}</p>
                <p class="it-home-spot__label">Messages</p>
            </a>
            <a href="{{ route('questions.index', ['status' => 'open']) }}" class="it-home-spot">
                <p class="it-home-spot__value">{{ $stats['questions_open'] }}</p>
                <p class="it-home-spot__label">Questions</p>
            </a>
            <a href="{{ route('it-manager.follow-up.index', ['filter' => 'adhesion']) }}" class="it-home-spot">
                <p class="it-home-spot__value">{{ $stats['register_pending_adhesion'] }}</p>
                <p class="it-home-spot__label">Sans adhésion</p>
            </a>
        </div>
    </header>

    <nav class="it-home-actions" aria-label="Actions rapides">
        <a href="{{ route('it-manager.activity-register.show') }}" class="it-home-actions__item is-primary">
            <span class="it-home-actions__icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </span>
            <span class="it-home-actions__label">Registre</span>
        </a>
        <a href="{{ route('it-manager.follow-up.index') }}" class="it-home-actions__item">
            <span class="it-home-actions__icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </span>
            <span class="it-home-actions__label">Suivi</span>
        </a>
        <a href="{{ route('site-management.contacts.index') }}" class="it-home-actions__item">
            <span class="it-home-actions__icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </span>
            <span class="it-home-actions__label">Messages</span>
        </a>
        <a href="{{ route('it-manager.announcements.create') }}" class="it-home-actions__item">
            <span class="it-home-actions__icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </span>
            <span class="it-home-actions__label">Annonce</span>
        </a>
    </nav>

    <div class="it-home-kpis">
        <a href="{{ route('it-manager.activity-register.show') }}" class="it-home-kpi">
            <p class="it-home-kpi__value">{{ $stats['register_today'] }}</p>
            <p class="it-home-kpi__label">Registre du jour</p>
            <p class="it-home-kpi__meta">{{ $stats['register_pending_adhesion'] }} sans adhésion (14 j)</p>
        </a>
        <a href="{{ route('publications.index') }}" class="it-home-kpi">
            <p class="it-home-kpi__value">{{ $stats['publications_published'] }}</p>
            <p class="it-home-kpi__label">Publications</p>
            <p class="it-home-kpi__meta">{{ $stats['publications'] }} au total</p>
        </a>
        <a href="{{ route('site-management.contacts.index') }}" class="it-home-kpi">
            <p class="it-home-kpi__value">{{ $stats['messages_total'] }}</p>
            <p class="it-home-kpi__label">Msgs site</p>
            <p class="it-home-kpi__meta">Historique complet</p>
        </a>
        <a href="{{ route('questions.index') }}" class="it-home-kpi">
            <p class="it-home-kpi__value">{{ $stats['questions_total'] }}</p>
            <p class="it-home-kpi__label">Conversations</p>
            <p class="it-home-kpi__meta">{{ $stats['questions_open'] }} ouvertes</p>
        </a>
    </div>

    <section class="it-home-panel">
        <header class="it-home-panel__head">
            <h2 class="it-home-panel__title">Messages du site</h2>
            <a href="{{ route('site-management.contacts.index') }}" class="it-home-panel__link">Tout voir</a>
        </header>
        <ul class="it-home-feed">
            @forelse ($recentMessages as $msg)
                <li>
                    <a href="{{ route('site-management.contacts.show', $msg) }}" class="it-home-feed__item">
                        <span class="it-home-feed__avatar" aria-hidden="true">{{ strtoupper(mb_substr($msg->name, 0, 1)) }}</span>
                        <span class="it-home-feed__body min-w-0">
                            <span class="it-home-feed__row">
                                <span class="it-home-feed__name truncate">{{ $msg->name }}</span>
                                <span @class([
                                    'it-home-feed__status',
                                    'is-new' => $msg->status === 'new',
                                ])>{{ $msg->statusLabel() }}</span>
                            </span>
                            <span class="it-home-feed__subject truncate">{{ $msg->subject }}</span>
                            <span class="it-home-feed__time">{{ $msg->created_at?->diffForHumans() }}</span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="it-home-feed__empty">Aucun message pour le moment.</li>
            @endforelse
        </ul>
    </section>

    <section class="it-home-panel">
        <header class="it-home-panel__head">
            <h2 class="it-home-panel__title">Questions membres</h2>
            <a href="{{ route('questions.index') }}" class="it-home-panel__link">Tout voir</a>
        </header>
        <ul class="it-home-feed">
            @forelse ($recentQuestions as $conversation)
                <li>
                    <a href="{{ route('questions.show', $conversation) }}" class="it-home-feed__item">
                        <span class="it-home-feed__avatar is-amber" aria-hidden="true">
                            {{ strtoupper(mb_substr($conversation->member?->name ?? 'M', 0, 1)) }}
                        </span>
                        <span class="it-home-feed__body min-w-0">
                            <span class="it-home-feed__row">
                                <span class="it-home-feed__name truncate">{{ $conversation->member?->name ?? 'Membre' }}</span>
                                <span @class([
                                    'it-home-feed__status',
                                    'is-open' => $conversation->status === 'open',
                                ])>{{ $conversation->status === 'open' ? 'Ouverte' : 'Résolue' }}</span>
                            </span>
                            <span class="it-home-feed__subject truncate">
                                {{ \Illuminate\Support\Str::limit($conversation->latestMessage?->body ?? '—', 72) }}
                            </span>
                            <span class="it-home-feed__time">
                                {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at?->diffForHumans() }}
                            </span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="it-home-feed__empty">Aucune question pour le moment.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
