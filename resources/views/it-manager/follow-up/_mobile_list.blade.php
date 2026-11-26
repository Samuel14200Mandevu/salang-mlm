@forelse ($groups as $index => $group)
    @php
        $member = $members->get($group->member_id);
        $date = \Illuminate\Support\Carbon::parse($group->activity_date)->toDateString();
        $detailUrl = route('it-manager.follow-up.show', array_filter([
            'member' => $group->member_id,
            'date' => $date,
            'filter' => $filter,
            'member_code' => ($memberCode ?? null) ?: null,
        ]));
        $rowNumber = $groups->firstItem() + $index;
        $formationCount = (int) ($group->formation_count ?? 0);
        $adhesionCount = (int) ($group->adhesion_count ?? 0);
        $consultationCount = (int) ($group->consultation_count ?? 0);
        $purchaseCount = (int) ($group->purchase_count ?? 0);
    @endphp
    <a href="{{ $detailUrl }}"
       class="follow-up-mobile-card block rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-xs text-[var(--text-muted)]">
                    <span class="tabular-nums">N° {{ $rowNumber }}</span>
                    <span aria-hidden="true">·</span>
                    <span class="tabular-nums">{{ \Illuminate\Support\Carbon::parse($group->activity_date)->format('d/m/Y') }}</span>
                </div>
                <p class="mt-0.5 font-semibold text-[var(--text-primary)] truncate">
                    {{ $member?->name ?: '—' }}
                </p>
                <p class="text-xs text-[var(--text-secondary)]">
                    {{ (int) $group->invites_count }} invité{{ (int) $group->invites_count > 1 ? 's' : '' }}
                    <span class="font-mono text-[var(--text-muted)]">({{ $group->member_code ?: ($member?->sponsor_id ?: '—') }})</span>
                </p>
                <p class="mt-1.5 text-[0.6875rem] uppercase tracking-wide text-[var(--text-muted)] tabular-nums">
                    Form {{ $formationCount }}
                    · Adh {{ $adhesionCount }}
                    · Cons {{ $consultationCount }}
                    · Achat {{ $purchaseCount }}
                </p>
            </div>
            <svg class="w-4 h-4 mt-1 flex-shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
@empty
    <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-10 text-center text-sm text-[var(--text-muted)]">
        Aucun groupe pour ce filtre.
    </div>
@endforelse
