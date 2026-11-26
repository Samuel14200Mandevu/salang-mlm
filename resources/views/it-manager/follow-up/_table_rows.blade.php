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
    @endphp
    <tr class="border-t border-[var(--border-light)] hover:bg-[var(--bg-secondary)]/50 cursor-pointer"
        onclick="window.location.href=@js($detailUrl)">
        <td class="px-3 py-2.5 tabular-nums text-[var(--text-muted)]">{{ $rowNumber }}</td>
        <td class="px-3 py-2.5 whitespace-nowrap tabular-nums">{{ \Illuminate\Support\Carbon::parse($group->activity_date)->format('d/m/Y') }}</td>
        <td class="px-3 py-2.5 tabular-nums font-semibold text-[var(--text-primary)]">{{ (int) $group->invites_count }}</td>
        <td class="px-3 py-2.5">
            <p class="font-medium text-[var(--text-primary)]">{{ $member?->name ?: '—' }}</p>
            <p class="text-xs font-mono text-[var(--text-muted)]">{{ $group->member_code ?: ($member?->sponsor_id ?: '—') }}</p>
        </td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) $group->formation_count }}/{{ (int) $group->invites_count }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) $group->adhesion_count }}/{{ (int) $group->invites_count }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) $group->consultation_count }}/{{ (int) $group->invites_count }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) $group->purchase_count }}/{{ (int) $group->invites_count }}</td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">Aucun groupe pour ce filtre.</td>
    </tr>
@endforelse
