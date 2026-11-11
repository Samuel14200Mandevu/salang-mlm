@extends('layouts.app')

@section('title', $presenter->title())

@section('content')
@php
    $dayLabel = $notification->created_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
    $timeLabel = $notification->created_at->format('H:i');
    $detailRows = $presenter->detailRows();
@endphp
<div class="notifications-page notifications-detail"
     x-data="{ menuOpen: false }"
     @keydown.escape.window="menuOpen = false">

    {{-- Mobile — barre type fil SMS : identité à gauche, ⋯ à droite --}}
    <header class="notifications-thread-bar">
        <div class="notifications-thread-bar__identity">
            <span class="notifications-thread-bar__avatar" aria-hidden="true">S</span>
            <div class="min-w-0">
                <p class="notifications-thread-bar__name">Salang Group</p>
                <p class="notifications-thread-bar__sub">{{ $presenter->categoryLabel() }} · {{ $timeLabel }}</p>
            </div>
        </div>

        <div class="notifications-thread-bar__menu" @click.outside="menuOpen = false">
            <button type="button"
                    class="notifications-thread-bar__icon-btn"
                    @click="menuOpen = !menuOpen"
                    :aria-expanded="menuOpen.toString()"
                    aria-haspopup="menu"
                    aria-controls="notificationDetailMenu"
                    aria-label="Options">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="5" cy="12" r="1.75"/>
                    <circle cx="12" cy="12" r="1.75"/>
                    <circle cx="19" cy="12" r="1.75"/>
                </svg>
            </button>
            <div id="notificationDetailMenu"
                 class="notifications-thread-bar__dropdown"
                 x-show="menuOpen"
                 x-cloak
                 x-transition
                 role="menu"
                 aria-label="Options de la notification">
                @if($presenter->actionUrl())
                    <a href="{{ $presenter->actionUrl() }}" class="notifications-thread-bar__dropdown-item" role="menuitem" @click="menuOpen = false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Ouvrir l’élément lié
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="notifications-thread-bar__dropdown-item" role="menuitem" @click="menuOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
                <form action="{{ route('notifications.destroy', $notification->id) }}"
                      method="POST"
                      role="none"
                      onsubmit="return confirm('Supprimer cette notification ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="notifications-thread-bar__dropdown-item notifications-thread-bar__dropdown-item--danger" role="menuitem">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Desktop — actions classiques --}}
    <div class="notifications-detail-desktop-actions">
        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
            ← Retour aux notifications
        </a>
        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
              onsubmit="return confirm('Supprimer cette notification ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline btn-sm text-red-600 border-red-200 hover:bg-red-50">Supprimer</button>
        </form>
    </div>

    <div class="notifications-thread" role="log" aria-live="polite">
        <div class="notifications-thread__messages">
            <div class="question-chat-day">
                <span>{{ $dayLabel }}</span>
            </div>

            <div class="question-chat-row question-chat-row--incoming">
                <div class="question-chat-avatar question-chat-avatar--sm question-chat-avatar--support" aria-hidden="true">S</div>
                <div class="question-chat-bubble-group">
                    <p class="question-chat-bubble-meta">Salang Group</p>
                    <div class="question-chat-bubble notifications-detail-bubble">
                        <p class="question-chat-bubble__subject">{{ $presenter->title() }}</p>
                        <div class="question-chat-bubble__text">{{ $presenter->message() }}</div>

                        @if(count($detailRows) > 0)
                            <dl class="notifications-detail-bubble__rows">
                                @foreach($detailRows as $row)
                                    <div class="notifications-detail-bubble__row">
                                        <dt>{{ $row['label'] }}</dt>
                                        <dd>{{ $row['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                    <time class="question-chat-bubble-time" datetime="{{ $notification->created_at->toIso8601String() }}">
                        {{ $timeLabel }}
                        · {{ $notification->created_at->diffForHumans() }}
                    </time>
                </div>
            </div>

            @if($presenter->actionUrl())
                <div class="notifications-thread__cta notifications-thread__cta--desktop">
                    <a href="{{ $presenter->actionUrl() }}" class="btn btn-primary btn-sm">
                        Ouvrir l’élément lié
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
