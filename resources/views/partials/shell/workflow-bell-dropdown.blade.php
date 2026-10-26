@php
    use App\Notifications\ActionFeedbackNotification;
    use Illuminate\Support\Str;

    $bell = $workflowBell ?? [];
    $prefix = $bell['idPrefix'] ?? 'workflow';
    $consultationsUrl = $bell['consultationsUrl'] ?? '#';
    $reportsUrl = $bell['reportsUrl'] ?? '#';
    $consultationsLabel = $bell['consultationsLabel'] ?? 'Consultations';
    $reportsLabel = $bell['reportsLabel'] ?? 'Rapports';
    $panelTitle = $bell['panelTitle'] ?? 'Notifications';
    $emptyText = $bell['emptyText'] ?? 'Aucune notification pour le moment.';
    $urgentLabel = $bell['urgentLabel'] ?? 'Ouvrir la priorité';

    $actionNotifications = collect();
    $actionUnread = 0;
    if (auth()->check()) {
        $actionNotifications = auth()->user()->notifications()
            ->where('type', ActionFeedbackNotification::class)
            ->latest()
            ->limit(12)
            ->get();
        $actionUnread = auth()->user()->unreadNotifications()
            ->where('type', ActionFeedbackNotification::class)
            ->count();
    }

    $actionIcon = static function (?string $action, string $tone): string {
        return match ($action) {
            'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'update' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'create' => 'M12 4v16m8-8H4',
            default => match ($tone) {
                'danger' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                default => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            },
        };
    };
@endphp

<div class="relative"
     x-data="{
        workflowOpen: false,
        actionUnread: {{ (int) $actionUnread }},
        markActionFeedbackUrl: @js(route('notifications.mark-action-feedback-read')),
        csrf: @js(csrf_token()),
        async openBell() {
            this.workflowOpen = !this.workflowOpen;
            if (this.workflowOpen && this.actionUnread > 0) {
                try {
                    await fetch(this.markActionFeedbackUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    this.actionUnread = 0;
                    document.querySelectorAll('[data-action-feedback-item].is-unread').forEach(function (el) {
                        el.classList.remove('is-unread');
                    });
                    var pulseBadge = document.getElementById('{{ $prefix }}ActionBadge');
                    if (pulseBadge) {
                        pulseBadge.style.display = 'none';
                        pulseBadge.classList.add('hidden');
                    }
                } catch (e) {}
            }
        }
     }"
     @keydown.escape.window="workflowOpen = false">
    <button type="button"
            id="{{ $prefix }}BellBtn"
            @click="openBell()"
            class="relative p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
            :aria-expanded="workflowOpen"
            aria-haspopup="true"
            title="{{ $panelTitle }}"
            aria-label="{{ $panelTitle }}">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span id="{{ $prefix }}HeaderDot" class="notification-dot workflow-notify-accent" style="display:none;"></span>
        <span id="{{ $prefix }}HeaderBadge"
              class="workflow-notify-accent absolute -top-0.5 -right-0.5 text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 hidden"
              style="display:none;">0</span>
        <span id="{{ $prefix }}ActionBadge"
              x-show="actionUnread > 0"
              x-cloak
              x-text="actionUnread > 99 ? '99+' : actionUnread"
              class="action-feedback-badge absolute -top-0.5 -right-0.5 text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 {{ $actionUnread > 0 ? '' : 'hidden' }}"
              @if($actionUnread <= 0) style="display:none;" @endif>{{ $actionUnread > 99 ? '99+' : $actionUnread }}</span>
    </button>

    <div x-show="workflowOpen"
         x-cloak
         @click.away="workflowOpen = false"
         id="{{ $prefix }}Dropdown"
         class="workflow-notify-panel action-feedback-panel absolute right-0 mt-2 w-[min(22rem,calc(100vw-1.25rem))] sm:w-96 bg-[var(--bg-card)] rounded-xl shadow-xl border border-[var(--border-color)] z-50 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         style="display: none;">

        <div class="px-4 py-3 border-b border-[var(--border-color)] flex items-center justify-between gap-2 bg-[var(--bg-secondary)]/40">
            <div>
                <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $panelTitle }}</p>
                <p class="text-[11px] text-[var(--text-tertiary)]">Confirmations et alertes</p>
            </div>
            <span id="{{ $prefix }}HeaderBadgeMirror" class="workflow-notify-total-pill hidden">0</span>
        </div>

        {{-- Confirmations d'actions (ajouter / modifier / supprimer) --}}
        <div class="action-feedback-section">
            <div class="px-4 pt-3 pb-1.5 flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--text-tertiary)]">Activité récente</p>
                @if($actionNotifications->isNotEmpty())
                    <span class="text-[10px] text-[var(--text-tertiary)]">{{ $actionNotifications->count() }}</span>
                @endif
            </div>

            @forelse($actionNotifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $tone = $data['tone'] ?? 'info';
                    $action = $data['action'] ?? 'info';
                    $title = $data['title'] ?? 'Notification';
                    $message = $data['message'] ?? '';
                    $path = $actionIcon($action, $tone);
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div data-action-feedback-item
                     class="action-feedback-item tone-{{ $tone }} {{ $isUnread ? 'is-unread' : '' }}">
                    <span class="action-feedback-icon" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="action-feedback-title">{{ $title }}</p>
                            <time class="action-feedback-time shrink-0">{{ $notification->created_at?->diffForHumans(short: true) }}</time>
                        </div>
                        <p class="action-feedback-message">{{ Str::limit($message, 110) }}</p>
                    </div>
                </div>
            @empty
                <p class="px-4 py-3 text-sm text-[var(--text-secondary)]">{{ $emptyText }}</p>
            @endforelse
        </div>

        {{-- Workflow consultations / rapports --}}
        <div id="{{ $prefix }}DropdownItems" class="border-t border-[var(--border-color)] py-1">
            <p class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-[var(--text-tertiary)]">Suivi</p>
            <a href="{{ $consultationsUrl }}"
               id="{{ $prefix }}ConsultationRow"
               class="workflow-notify-row flex items-center gap-3 px-4 py-3 hover:bg-[var(--bg-secondary)] transition-colors">
                <span class="workflow-notify-row-icon text-[var(--primary)]" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-medium text-[var(--text-primary)]">{{ $consultationsLabel }}</span>
                    <span class="block text-xs text-[var(--text-secondary)] truncate">{{ $bell['consultationsHint'] ?? '' }}</span>
                </span>
                <span id="{{ $prefix }}DropdownConsultationCount" class="workflow-notify-count hidden">0</span>
            </a>

            <a href="{{ $reportsUrl }}"
               id="{{ $prefix }}ReportRow"
               class="workflow-notify-row flex items-center gap-3 px-4 py-3 hover:bg-[var(--bg-secondary)] transition-colors border-t border-[var(--border-color)]">
                <span class="workflow-notify-row-icon text-[var(--primary)]" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-medium text-[var(--text-primary)]">{{ $reportsLabel }}</span>
                    <span class="block text-xs text-[var(--text-secondary)] truncate">{{ $bell['reportsHint'] ?? '' }}</span>
                </span>
                <span id="{{ $prefix }}DropdownReportCount" class="workflow-notify-count hidden">0</span>
            </a>
        </div>

        <div id="{{ $prefix }}UrgentWrap" class="border-t border-[var(--border-color)] p-2 hidden">
            <a href="{{ $consultationsUrl }}"
               id="{{ $prefix }}UrgentLink"
               class="workflow-notify-urgent flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-md text-sm font-semibold text-white bg-[var(--primary)] hover:opacity-95 transition-opacity">
                {{ $urgentLabel }}
            </a>
        </div>
    </div>
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
