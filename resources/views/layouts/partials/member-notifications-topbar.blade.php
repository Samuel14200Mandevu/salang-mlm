{{-- Cloche membre : comme admin → ouvre la page notifications --}}
@auth
@php
    $actionFeedbackType = \App\Notifications\ActionFeedbackNotification::class;
    $actionUnread = auth()->user()?->unreadNotifications()
        ->where('type', $actionFeedbackType)
        ->count() ?? 0;
    $inboxUnread = auth()->user()?->unreadNotifications()
        ->where('type', '!=', $actionFeedbackType)
        ->count() ?? 0;
    $bellUnread = (int) $actionUnread + (int) $inboxUnread;
@endphp

<div class="relative member-mobile-topbar__notifications">
    <a href="{{ route('notifications.index') }}"
       id="memberBellBtn"
       class="member-mobile-topbar__notify p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative inline-flex"
       title="Notifications"
       aria-label="Notifications{{ $bellUnread > 0 ? ' ('.$bellUnread.' non lue'.($bellUnread > 1 ? 's' : '').')' : '' }}">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        <span id="memberHeaderDot"
              class="notification-dot workflow-notify-accent"
              @if($bellUnread <= 0) style="display:none;" @endif
              aria-hidden="true"></span>
        <span id="memberHeaderBadge"
              class="workflow-notify-accent absolute -top-0.5 -right-0.5 text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 items-center justify-center px-1 text-white {{ $bellUnread > 0 ? 'flex' : 'hidden' }}"
              @if($bellUnread > 0) style="display:flex;" @else style="display:none;" @endif
              data-notif-unread="{{ $bellUnread }}">
            {{ $bellUnread > 0 ? ($bellUnread > 99 ? '99+' : $bellUnread) : '0' }}
        </span>
    </a>
</div>

@if(session('_action_feedback_pulse'))
    <script>
    (function () {
        document.body.classList.add('has-action-feedback-notify');
        var btn = document.getElementById('memberBellBtn');
        if (btn) {
            btn.classList.add('action-feedback-bell-pulse');
            setTimeout(function () { btn.classList.remove('action-feedback-bell-pulse'); }, 1800);
        }
    })();
    </script>
@endif
@endauth
