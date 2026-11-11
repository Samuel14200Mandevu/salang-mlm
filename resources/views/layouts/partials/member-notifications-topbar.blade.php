@auth
@php
    $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<div class="relative member-mobile-topbar__notifications">
    <a href="{{ route('notifications.index') }}"
       class="member-mobile-topbar__notify p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative inline-flex"
       aria-label="Notifications{{ $unreadCount > 0 ? ' ('.$unreadCount.' non lue'.($unreadCount > 1 ? 's' : '').')' : '' }}">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-primary-500 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
</div>
@endauth
