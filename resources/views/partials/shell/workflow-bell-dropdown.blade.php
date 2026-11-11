@php
    use App\Notifications\ActionFeedbackNotification;

    $bell = $workflowBell ?? [];
    $prefix = $bell['idPrefix'] ?? 'workflow';
    $panelTitle = $bell['panelTitle'] ?? 'Notifications';
    $indexUrl = $bell['indexUrl'] ?? route('notifications.index');

    $actionUnread = 0;
    $inboxUnread = 0;
    if (auth()->check() && auth()->user()) {
        $actionUnread = auth()->user()?->unreadNotifications()
            ->where('type', ActionFeedbackNotification::class)
            ->count() ?? 0;
        $inboxUnread = auth()->user()?->unreadNotifications()
            ->where('type', '!=', ActionFeedbackNotification::class)
            ->count() ?? 0;
    }
    $bellUnread = (int) $actionUnread + (int) $inboxUnread;
@endphp

<div class="relative">
    <a href="{{ $indexUrl }}"
       id="{{ $prefix }}BellBtn"
       class="relative p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors inline-flex"
       title="{{ $panelTitle }}"
       aria-label="{{ $panelTitle }}{{ $bellUnread > 0 ? ' ('.$bellUnread.' non lue'.($bellUnread > 1 ? 's' : '').')' : '' }}">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        <span id="{{ $prefix }}HeaderDot"
              class="notification-dot workflow-notify-accent"
              @if($bellUnread <= 0) style="display:none;" @endif
              aria-hidden="true"></span>
        <span id="{{ $prefix }}HeaderBadge"
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
        var btn = document.getElementById(@json($prefix.'BellBtn'));
        if (btn) {
            btn.classList.add('action-feedback-bell-pulse');
            setTimeout(function () { btn.classList.remove('action-feedback-bell-pulse'); }, 1800);
        }
    })();
    </script>
@endif
