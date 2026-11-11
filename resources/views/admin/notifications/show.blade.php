@extends('admin.layouts.app')

@section('title', $presenter->title())

@section('content')
@php
    $dayLabel = $notification->created_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
    $timeLabel = $notification->created_at->format('H:i');
    $detailRows = $presenter->detailRows();
@endphp
<div class="notifications-page notifications-detail admin-notifications-detail"
     x-data="{ menuOpen: false }"
     @keydown.escape.window="menuOpen = false">

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
                    aria-controls="adminNotificationDetailMenu"
                    aria-label="Options">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="5" cy="12" r="1.75"/>
                    <circle cx="12" cy="12" r="1.75"/>
                    <circle cx="19" cy="12" r="1.75"/>
                </svg>
            </button>
            <div id="adminNotificationDetailMenu"
                 class="notifications-thread-bar__dropdown"
                 x-show="menuOpen"
                 x-cloak
                 x-transition
                 role="menu">
                @if($presenter->actionUrl())
                    <a href="{{ $presenter->actionUrl() }}" class="notifications-thread-bar__dropdown-item" role="menuitem" @click="menuOpen = false">
                        Ouvrir l’élément lié
                    </a>
                @endif
                <a href="{{ route('admin.notifications.index') }}" class="notifications-thread-bar__dropdown-item" role="menuitem" @click="menuOpen = false">
                    Retour à la liste
                </a>
                <form action="{{ route('admin.notifications.destroy', $notification->id) }}"
                      method="POST"
                      role="none"
                      onsubmit="return confirm('Supprimer cette notification ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="notifications-thread-bar__dropdown-item notifications-thread-bar__dropdown-item--danger" role="menuitem">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="notifications-detail-desktop-actions">
        <a href="{{ route('admin.notifications.index') }}" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
            ← Retour aux notifications
        </a>
        <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST"
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
                <div class="question-chat-avatar question-chat-avatar--sm" aria-hidden="true">S</div>
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
                        {{ $timeLabel }} · {{ $notification->created_at->diffForHumans() }}
                    </time>
                </div>
            </div>

            @if($presenter->actionUrl())
                <div class="notifications-thread__cta notifications-thread__cta--desktop">
                    <a href="{{ $presenter->actionUrl() }}" class="btn btn-primary btn-sm">Ouvrir l’élément lié</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
