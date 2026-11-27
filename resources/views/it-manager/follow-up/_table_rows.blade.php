@forelse ($groups as $index => $group)
    @php
        $member = $members->get($group->member_id);
        $detailUrl = route('it-manager.follow-up.show', array_filter([
            'member' => $group->member_id,
            'filter' => $filter,
            'member_code' => ($memberCode ?? null) ?: null,
        ]));
        $rowNumber = $groups->firstItem() + $index;
        $formationCount = (int) ($group->formation_count ?? 0);
        $adhesionCount = (int) ($group->adhesion_count ?? 0);
        $consultationCount = (int) ($group->consultation_count ?? 0);
        $purchaseCount = (int) ($group->purchase_count ?? 0);
    @endphp
    <tr class="border-t border-[var(--border-light)] hover:bg-[var(--bg-secondary)]/50 cursor-pointer"
        onclick="window.location.href=@js($detailUrl)">
        <td class="px-3 py-2.5 tabular-nums text-[var(--text-muted)]">{{ $rowNumber }}</td>
        <td class="px-3 py-2.5 tabular-nums font-semibold text-[var(--text-primary)]">{{ (int) $group->invites_count }}</td>
        <td class="px-3 py-2.5">
            <p class="font-medium text-[var(--text-primary)]">{{ $member?->name ?: '—' }}</p>
            <p class="text-xs font-mono text-[var(--text-muted)]">{{ $group->member_code ?: ($member?->sponsor_id ?: '—') }}</p>
        </td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ $formationCount }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ $adhesionCount }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ $consultationCount }}</td>
        <td class="px-3 py-2.5 text-center tabular-nums">{{ $purchaseCount }}</td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">Aucun membre pour ce filtre.</td>
    </tr>
@endforelse
