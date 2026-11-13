@extends('admin.layouts.app')

@section('title', 'Notifications')

@push('styles')
<style>
.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 11.5rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    padding: 0.35rem;
    z-index: 40;
}
.users-filter-menu a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    border-radius: 0.45rem;
    font-size: 0.8125rem;
    color: var(--text-primary);
    text-decoration: none;
}
.users-filter-menu a:hover { background: var(--bg-secondary); }

/* Un seul bouton « Tout supprimer » visible selon l’écran */
.notif-destroy-desktop-only { display: none !important; }
.notif-destroy-mobile-only { display: inline-flex !important; }
@media (min-width: 768px) {
    .notif-destroy-mobile-only { display: none !important; }
    .notif-destroy-desktop-only { display: inline-flex !important; }
}
.users-filter-menu a.is-active {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 600;
}
.users-filter-menu .filter-count {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-tertiary);
}
.users-filter-menu a.is-active .filter-count { color: var(--primary); }
</style>
@endpush

@section('content')
@php
    use App\Support\NotificationPresenter;
    $filtersActive = ($status ?? 'all') !== 'all';
    $searchOpen = filled($q ?? null);
    $filterButtonLabel = match ($status ?? 'all') {
        'unread' => 'Non lues',
        'read' => 'Lues',
        default => 'Filtrer',
    };
    $subtitle = ($unreadCount ?? 0).' non lue(s) · '.($totalCount ?? $notifications->total()).' alerte(s) · '
        .(($pendingConsultations ?? 0) + ($pendingReports ?? 0)).' validation(s)';
    $filterUrl = function (?string $statusValue = null) use ($q) {
        return route('admin.notifications.index', array_filter([
            'status' => $statusValue && $statusValue !== 'all' ? $statusValue : null,
            'q' => filled($q ?? null) ? $q : null,
        ], fn ($v) => $v !== null && $v !== ''));
    };
@endphp

<div class="notifications-page admin-notifications-page space-y-4 sm:space-y-6">

    {{-- Mobile : bandeau Notifications + icônes --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>Notifications</h1>
                <p>{{ $subtitle }}</p>
            </div>
            <div class="admin-title-banner__tools admin-title-banner__tools--notifications-index">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminNotifSearchPanel"
                        aria-expanded="{{ $searchOpen ? 'true' : 'false' }}"
                        aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="admin-banner-icon-btn{{ $filtersActive ? ' is-active' : '' }}"
                            :aria-expanded="open"
                            aria-label="{{ $filterButtonLabel }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu">
                        <a href="{{ $filterUrl(null) }}" class="{{ ! $filtersActive ? 'is-active' : '' }}">
                            <span>Toutes</span>
                        </a>
                        <a href="{{ $filterUrl('unread') }}" class="{{ ($status ?? '') === 'unread' ? 'is-active' : '' }}">
                            <span>Non lues</span>
                            <span class="filter-count">{{ $unreadCount ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl('read') }}" class="{{ ($status ?? '') === 'read' ? 'is-active' : '' }}">
                            <span>Lues</span>
                        </a>
                    </div>
                </div>

                @if(($totalCount ?? 0) > 0)
                    <form action="{{ route('admin.notifications.destroy-all') }}"
                          method="POST"
                          class="inline-flex notifications-destroy-all-form notif-destroy-mobile-only">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-outline btn-sm btn-round-icon notifications-destroy-all-btn"
                                title="Tout supprimer"
                                aria-label="Tout supprimer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div id="adminNotifSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="search-wrapper admin-shop-search">
                @if(($status ?? 'all') !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
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
            </form>
        </div>
    </div>

    {{-- Desktop : titre + recherche + filtre + supprimer --}}
    <div class="admin-page-header page-header header-with-search hidden md:flex animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Notifications</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">{{ $subtitle }}</p>
        </div>
        <div class="header-right admin-page-header-actions">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="header-search admin-search-full">
                @if(($status ?? 'all') !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
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

            <div class="relative shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm btn-round-icon {{ $filtersActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        title="{{ $filterButtonLabel }}"
                        aria-label="{{ $filterButtonLabel }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu">
                    <a href="{{ $filterUrl(null) }}" class="{{ ! $filtersActive ? 'is-active' : '' }}">
                        <span>Toutes</span>
                    </a>
                    <a href="{{ $filterUrl('unread') }}" class="{{ ($status ?? '') === 'unread' ? 'is-active' : '' }}">
                        <span>Non lues</span>
                        <span class="filter-count">{{ $unreadCount ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl('read') }}" class="{{ ($status ?? '') === 'read' ? 'is-active' : '' }}">
                        <span>Lues</span>
                    </a>
                </div>
            </div>

            @if(($totalCount ?? 0) > 0)
                <form action="{{ route('admin.notifications.destroy-all') }}"
                      method="POST"
                      class="inline-flex notifications-destroy-all-form notif-destroy-desktop-only">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            class="btn btn-outline btn-sm btn-round-icon notifications-destroy-all-btn"
                            title="Tout supprimer"
                            aria-label="Tout supprimer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Type 1 : validations métier --}}
    <section class="admin-notif-validations animate-fadeInUp" aria-labelledby="admin-notif-validations-title">
        <div class="flex items-center justify-between gap-2 mb-2">
            <h2 id="admin-notif-validations-title" class="text-sm font-bold uppercase tracking-wider text-[var(--text-tertiary)]">
                Validations en attente
            </h2>
            <span class="text-xs text-[var(--text-secondary)]">
                {{ ($pendingConsultations ?? 0) + ($pendingReports ?? 0) }}
            </span>
        </div>
        <div class="admin-notif-validations__grid">
            <a href="{{ route('admin.consultations.index', ['status' => 'pending']) }}"
               class="admin-notif-validation-card {{ ($pendingConsultations ?? 0) > 0 ? 'is-urgent' : '' }}">
                <div class="admin-notif-validation-card__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="admin-notif-validation-card__title">Consultations</p>
                    <p class="admin-notif-validation-card__hint">Fiches envoyées par les caissiers</p>
                </div>
                <span class="admin-notif-validation-card__count">{{ $pendingConsultations ?? 0 }}</span>
            </a>
            <a href="{{ route('cashier.reports.index', ['status' => 'submitted']) }}"
               class="admin-notif-validation-card {{ ($pendingReports ?? 0) > 0 ? 'is-urgent' : '' }}">
                <div class="admin-notif-validation-card__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="admin-notif-validation-card__title">Rapports caisse</p>
                    <p class="admin-notif-validation-card__hint">Rapports journaliers soumis</p>
                </div>
                <span class="admin-notif-validation-card__count">{{ $pendingReports ?? 0 }}</span>
            </a>
        </div>
    </section>

    {{-- Type 2 : alertes / activité --}}
    <section class="animate-fadeInUp delay-1" aria-labelledby="admin-notif-alerts-title">
        <div class="flex items-center justify-between gap-2 mb-2">
            <h2 id="admin-notif-alerts-title" class="text-sm font-bold uppercase tracking-wider text-[var(--text-tertiary)]">
                Alertes & activité
            </h2>
        </div>

        <div class="notifications-feed">
            @forelse($notifications as $notification)
                @php $p = NotificationPresenter::make($notification); @endphp
                <a href="{{ route('admin.notifications.show', $notification->id) }}"
                   class="notifications-feed__item notifications-feed__item--{{ $p->tone() }} {{ $p->isUnread() ? 'is-unread' : '' }}">
                    <span class="notifications-feed__dot" aria-hidden="true"></span>
                    <div class="notifications-feed__body">
                        <div class="notifications-feed__meta">
                            <span class="notifications-feed__cat">{{ $p->categoryLabel() }}</span>
                            <time datetime="{{ $notification->created_at->toIso8601String() }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>
                        <h3 class="notifications-feed__title">{{ $p->title() }}</h3>
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
                    <h3 class="text-lg font-semibold text-[var(--text-primary)]">Aucune alerte</h3>
                    <p class="mt-1 text-sm text-[var(--text-secondary)]">
                        @if(filled($q ?? null) || $filtersActive)
                            Aucun résultat pour ces filtres.
                        @else
                            Pas encore de notification d’alerte ou d’activité.
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
    </section>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function openDestroyAllDialog(form) {
        if (!form || typeof window.showConfirmDialog !== 'function') return;

        window.showConfirmDialog({
            type: 'danger',
            title: 'Supprimer toutes les alertes',
            message: 'Cette action est définitive. Toutes les notifications seront effacées.',
            confirmText: 'Tout supprimer',
            onConfirm: function () {
                if (typeof window.closeConfirmDialog === 'function') {
                    window.closeConfirmDialog();
                }
                form.submit();
            },
            form: form
        });
    }

    document.querySelectorAll('.notifications-destroy-all-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openDestroyAllDialog(btn.closest('form'));
        });
    });

    document.querySelectorAll('.notifications-destroy-all-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            openDestroyAllDialog(form);
        });
    });
});
</script>
@endpush
@endsection
