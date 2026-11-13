@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
@php
    use App\Support\NotificationPresenter;
    $filtersActive = ($status ?? 'all') !== 'all' || ($category ?? 'all') !== 'all';
    $filterButtonLabel = match (true) {
        ($status ?? 'all') === 'unread' => 'Non lues',
        ($status ?? 'all') === 'read' => 'Lues',
        ($category ?? 'all') !== 'all' => ($categories[$category] ?? 'Filtrer'),
        default => 'Filtrer',
    };
@endphp
<div class="notifications-page space-y-4 sm:space-y-6">

    @include('notifications.partials.catalog-head', [
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'totalCount' => $totalCount ?? $notifications->total(),
        'q' => $q ?? '',
        'status' => $status ?? 'all',
        'category' => $category ?? 'all',
        'categories' => $categories ?? NotificationPresenter::CATEGORIES,
    ])

    {{-- Desktop — titre + recherche + Filtrer (comme admin membres) --}}
    <div class="notifications-page-header-desktop animate-fadeInUp">
        <div class="member-page-intro">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Centre de notifications</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $unreadCount ?? 0 }} non lue(s) · {{ $totalCount ?? $notifications->total() }} au total
                @if(filled($q ?? null))
                    <span class="text-xs text-[var(--text-tertiary)]">· « {{ $q }} »</span>
                @endif
            </p>
        </div>
        <div class="supervision-header-actions notifications-header-actions">
            <form method="GET" action="{{ route('notifications.index') }}" class="header-search notifications-desktop-search">
                @if(($status ?? 'all') !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if(($category ?? 'all') !== 'all')
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           name="q"
                           class="search-input"
                           placeholder="Rechercher une notification"
                           autocomplete="off"
                           value="{{ $q ?? '' }}"
                           aria-label="Rechercher une notification">
                </div>
            </form>

            <div class="supervision-filter-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm {{ $filtersActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>{{ $filterButtonLabel }}</span>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition
                     @click.outside="open = false"
                     class="users-filter-menu users-filter-panel">
                    <form method="GET" action="{{ route('notifications.index') }}" class="space-y-2">
                        @if(filled($q ?? null))
                            <input type="hidden" name="q" value="{{ $q }}">
                        @endif
                        <div>
                            <label for="notif-status-desktop">État</label>
                            <select id="notif-status-desktop" name="status" class="form-control">
                                <option value="all" @selected(($status ?? 'all') === 'all')>Toutes</option>
                                <option value="unread" @selected(($status ?? '') === 'unread')>Non lues</option>
                                <option value="read" @selected(($status ?? '') === 'read')>Lues</option>
                            </select>
                        </div>
                        <div>
                            <label for="notif-category-desktop">Catégorie</label>
                            <select id="notif-category-desktop" name="category" class="form-control">
                                @foreach(($categories ?? NotificationPresenter::CATEGORIES) as $key => $label)
                                    <option value="{{ $key }}" @selected(($category ?? 'all') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm">Effacer</a>
                        </div>
                    </form>
                </div>
            </div>

            @if(($totalCount ?? 0) > 0)
                <form action="{{ route('notifications.destroy-all') }}"
                      method="POST"
                      class="inline"
                      id="notificationsDestroyAllForm">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            id="notificationsDestroyAllBtn"
                            class="btn btn-outline btn-sm">
                        Tout supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="notifications-page-flash p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <div class="notifications-feed animate-fadeInUp delay-1">
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
                    @if(filled($q ?? null) || $filtersActive)
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var head = document.getElementById('notificationsCatalogHead');
    var searchToggle = document.getElementById('notificationsSearchToggle');
    var searchPanel = document.getElementById('notificationsSearchPanel');
    var filterToggle = document.getElementById('notificationsFilterToggle');
    var filterPanel = document.getElementById('notificationsFiltersPanel');

    function setPanel(panel, toggle, open) {
        if (!panel || !toggle) return;
        panel.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function () {
            var open = !searchPanel.classList.contains('is-open');
            setPanel(searchPanel, searchToggle, open);
            if (open) {
                setPanel(filterPanel, filterToggle, false);
                var input = searchPanel.querySelector('input[type="search"]');
                if (input) input.focus();
            }
            if (head) head.classList.toggle('is-filters-open', open || (filterPanel && filterPanel.classList.contains('is-open')));
        });
    }

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function () {
            var open = !filterPanel.classList.contains('is-open');
            setPanel(filterPanel, filterToggle, open);
            if (open) setPanel(searchPanel, searchToggle, false);
            if (head) head.classList.toggle('is-filters-open', open || (searchPanel && searchPanel.classList.contains('is-open')));
        });
    }

    var destroyAllForm = document.getElementById('notificationsDestroyAllForm');
    var destroyAllBtn = document.getElementById('notificationsDestroyAllBtn');

    function openDestroyAllDialog() {
        if (!destroyAllForm) return;

        if (typeof window.showConfirmDialog !== 'function') {
            console.error('showConfirmDialog indisponible');
            return;
        }

        window.showConfirmDialog({
            type: 'danger',
            title: 'Supprimer toutes les notifications',
            message: 'Cette action est définitive. Toutes vos notifications seront effacées.',
            confirmText: 'Tout supprimer',
            onConfirm: function () {
                if (typeof window.closeConfirmDialog === 'function') {
                    window.closeConfirmDialog();
                }
                destroyAllForm.submit();
            },
            form: destroyAllForm
        });
    }

    if (destroyAllBtn) {
        destroyAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openDestroyAllDialog();
        });
    }

    if (destroyAllForm) {
        destroyAllForm.addEventListener('submit', function (e) {
            e.preventDefault();
            openDestroyAllDialog();
        });
    }
});
</script>
@endpush
@endsection
