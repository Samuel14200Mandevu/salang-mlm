@extends('layouts.app')

@section('title', 'Supervision IT')

@section('content')
@php
    $hour = (int) now()->format('G');
    $salutation = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $userName = auth()->user()?->name ?? 'Responsable IT';
    $attention = (int) $stats['messages_new'] + (int) $stats['questions_open'] + (int) $stats['register_pending_adhesion'];
@endphp

<div class="it-home">
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
