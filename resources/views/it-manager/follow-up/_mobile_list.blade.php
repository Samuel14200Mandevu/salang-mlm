@forelse ($entries as $entry)
    <a href="{{ route('it-manager.follow-up.show', array_filter(['entry' => $entry, 'filter' => $filter, 'member_code' => $memberCode ?: null])) }}"
       class="follow-up-mobile-card block rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-xs text-[var(--text-muted)] tabular-nums">{{ $entry->sheet?->activity_date?->format('d/m/Y') }}</p>
                <p class="mt-0.5 font-semibold text-[var(--text-primary)] truncate">{{ $entry->displayName() }}</p>
                <p class="text-xs text-[var(--text-secondary)] truncate">
                    Parrain · {{ $entry->member?->name ?: '—' }}
                    @if($entry->member_code)
                        <span class="font-mono text-[var(--text-muted)]">({{ $entry->member_code }})</span>
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 mt-1 flex-shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
@empty
    <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-10 text-center text-sm text-[var(--text-muted)]">
        Aucun invité pour ce filtre.
    </div>
@endforelse
