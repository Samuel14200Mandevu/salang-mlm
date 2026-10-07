@php
    $bell = $workflowBell ?? [];
    $prefix = $bell['idPrefix'] ?? 'workflow';
    $consultationsUrl = $bell['consultationsUrl'] ?? '#';
    $reportsUrl = $bell['reportsUrl'] ?? '#';
    $consultationsLabel = $bell['consultationsLabel'] ?? 'Consultations';
    $reportsLabel = $bell['reportsLabel'] ?? 'Rapports';
    $panelTitle = $bell['panelTitle'] ?? 'Notifications';
    $emptyText = $bell['emptyText'] ?? 'Aucune notification pour le moment.';
    $urgentLabel = $bell['urgentLabel'] ?? 'Ouvrir la priorité';
@endphp

<div class="relative" x-data="{ workflowOpen: false }" @keydown.escape.window="workflowOpen = false">
    <button type="button"
            id="{{ $prefix }}BellBtn"
            @click="workflowOpen = !workflowOpen"
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
    </button>

    <div x-show="workflowOpen"
         x-cloak
         @click.away="workflowOpen = false"
         id="{{ $prefix }}Dropdown"
         class="workflow-notify-panel absolute right-0 mt-2 w-72 sm:w-80 bg-[var(--bg-card)] rounded-lg shadow-lg border border-[var(--border-color)] z-50 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         style="display: none;">

        <div class="px-4 py-3 border-b border-[var(--border-color)] flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $panelTitle }}</p>
            <span id="{{ $prefix }}HeaderBadgeMirror" class="workflow-notify-total-pill hidden">0</span>
        </div>

        <p id="{{ $prefix }}DropdownEmpty"
           class="px-4 py-4 text-sm text-[var(--text-secondary)] hidden">{{ $emptyText }}</p>

        <div id="{{ $prefix }}DropdownItems" class="py-1">
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
