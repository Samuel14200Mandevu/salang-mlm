@forelse ($entries as $entry)
    <tr class="border-t border-[var(--border-light)]">
        <td class="px-3 py-2.5 whitespace-nowrap">{{ $entry->sheet?->activity_date?->format('d/m/Y') }}</td>
        <td class="px-3 py-2.5">
            <a href="{{ route('it-manager.follow-up.show', array_filter(['entry' => $entry, 'filter' => $filter, 'member_code' => $memberCode ?: null])) }}"
               class="font-medium text-[var(--text-primary)] hover:text-primary-600">{{ $entry->displayName() }}</a>
            <p class="text-xs text-[var(--text-muted)]">{{ $entry->participant_phone }}</p>
        </td>
        <td class="px-3 py-2.5">
            <p>{{ $entry->member?->name }}</p>
            <p class="text-xs font-mono text-[var(--text-muted)]">{{ $entry->member_code }}</p>
        </td>
        <td class="px-3 py-2.5 text-center">{{ $entry->formation_done ? '✓' : '·' }}</td>
        <td class="px-3 py-2.5 text-center">{{ $entry->adhesion_done ? '✓' : '·' }}</td>
        <td class="px-3 py-2.5 text-center">{{ $entry->consultation_done ? '✓' : '·' }}</td>
        <td class="px-3 py-2.5 text-center">{{ $entry->purchase_done ? '✓' : '·' }}</td>
        <td class="px-3 py-2.5 text-right">
            <a href="{{ route('it-manager.activity-register.show', ['date' => $entry->sheet?->activity_date?->toDateString()]) }}"
               class="text-sm font-semibold text-primary-600">Registre</a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">Aucun invité pour ce filtre.</td>
    </tr>
@endforelse
