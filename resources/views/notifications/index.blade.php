@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
@php
    use App\Support\NotificationPresenter;
@endphp
<div class="notifications-page space-y-4 sm:space-y-6">

    @include('notifications.partials.catalog-head', [
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'totalCount' => $totalCount ?? $notifications->total(),
    ])

    <div class="notifications-page-header-desktop flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Centre de notifications</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $unreadCount ?? 0 }} non lue(s) · {{ $totalCount ?? $notifications->total() }} au total
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(($unreadCount ?? 0) > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Tout marquer comme lu</button>
                </form>
            @endif
            @if(($totalCount ?? 0) > 0)
                <form action="{{ route('notifications.destroy-all') }}" method="POST" class="inline"
                      onsubmit="return confirm('Supprimer toutes les notifications ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm">Tout supprimer</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="notifications-page-flash p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('notifications.index') }}" class="notifications-filters card p-3 sm:p-4 animate-fadeInUp delay-1">
        <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3 sm:items-end">
            <div class="flex-1 min-w-[10rem]">
                <label for="notif-q" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Recherche</label>
                <input id="notif-q" type="search" name="q" value="{{ $q }}" placeholder="Titre, message…"
                       class="public-input w-full text-sm">
            </div>
            <div>
                <label for="notif-status" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">État</label>
                <select id="notif-status" name="status" class="public-input public-select text-sm">
                    <option value="all" @selected($status === 'all')>Toutes</option>
                    <option value="unread" @selected($status === 'unread')>Non lues</option>
                    <option value="read" @selected($status === 'read')>Lues</option>
                </select>
            </div>
            <div>
                <label for="notif-category" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Catégorie</label>
                <select id="notif-category" name="category" class="public-input public-select text-sm">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
            @if($q !== '' || $status !== 'all' || $category !== 'all')
                <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="notifications-feed animate-fadeInUp delay-2">
        @forelse($notifications as $notification)
            @php $p = NotificationPresenter::make($notification); @endphp
            <a href="{{ route('notifications.show', $notification->id) }}"
               class="notifications-feed__item notifications-feed__item--{{ $p->tone() }} {{ $p->isUnread() ? 'is-unread' : '' }}">
                <span class="notifications-feed__dot" aria-hidden="true"></span>
                <div class="notifications-feed__body">
                    <div class="notifications-feed__meta">
                        <span class="notifications-feed__cat">{{ $p->categoryLabel() }}</span>
                        <time datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </time>
                    </div>
                    <h2 class="notifications-feed__title">{{ $p->title() }}</h2>
                    <p class="notifications-feed__message">{{ $p->message() }}</p>
                </div>
                <span class="notifications-feed__chevron" aria-hidden="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @empty
            <div class="notifications-feed__empty card p-8 text-center">
                <svg class="mx-auto w-12 h-12 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-3 text-lg font-semibold text-[var(--text-primary)]">Aucune notification</h3>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                    @if($q !== '' || $status !== 'all' || $category !== 'all')
                        Aucun résultat pour ces filtres.
                    @else
                        Vous n’avez pas encore de notifications.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-2">
            <x-salang-pagination :paginator="$notifications" />
        </div>
    @endif
</div>
@endsection
