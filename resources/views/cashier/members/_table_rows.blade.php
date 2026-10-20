@forelse($members as $member)
    @php
        $rankLevel = (int) ($member->rank_level ?? 0);
        $totalCash = (float) ($member->pos_cash_paid_total ?? 0);
    @endphp
    <tr class="member-row cursor-pointer"
        onclick="window.location='{{ route('cashier.members.show', $member) }}'">
        <td class="admin-td-compact" data-label="">
            <div class="flex items-center gap-2">
                <div class="avatar-sm">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                    <div class="text-xs text-[var(--text-secondary)] truncate">{{ $member->email }}</div>
                    @if($member->phone)
                        <div class="text-xs text-[var(--text-secondary)]">{{ $member->phone }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td class="font-mono text-xs sm:text-sm" data-label="Code">
            {{ $member->sponsor_id ?? '—' }}
        </td>
        <td data-label="Grade">
            <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">
                {{ \App\Support\MlmRank::label($rankLevel) }}
            </span>
        </td>
        <td class="font-mono text-xs sm:text-sm" data-label="Code parrain">
            @if($codeParrain = $member->parrainSponsorCode())
                {{ $codeParrain }}
            @else
                <span class="text-[var(--text-tertiary)]">—</span>
            @endif
        </td>
        <td class="font-mono text-sm tabular-nums" data-label="PV">
            {{ number_format((float) ($member->pv_balance ?? 0), 0, ',', ' ') }}
        </td>
        <td data-label="Statut">
            @if($member->is_active)
                <span class="badge badge-success">Actif</span>
            @else
                <span class="badge badge-danger">Inactif</span>
            @endif
        </td>
        <td data-label="Total CASH">
            @if($totalCash > 0)
                <span class="badge badge-commission">${{ number_format($totalCash, 2) }}</span>
            @else
                <span class="text-xs text-[var(--text-tertiary)]">Aucune</span>
            @endif
        </td>
        <td class="text-center admin-td-compact" data-label="">
            <div class="flex items-center justify-end gap-1 mt-1">
                <a href="{{ route('cashier.members.commissions', $member->id) }}"
                   class="btn btn-success btn-sm member-row-action" title="Commissions POS"
                   aria-label="Commissions POS"
                   onclick="event.stopPropagation();">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402 2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.members.orders', $member->id) }}"
                   class="btn btn-warning btn-sm member-row-action" title="Commandes"
                   aria-label="Commandes"
                   onclick="event.stopPropagation();">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr class="admin-mobile-empty-row">
        <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base admin-td-compact" data-label="">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-base font-medium text-[var(--text-primary)]">Aucun membre</p>
            <p class="text-sm text-[var(--text-tertiary)]">Aucun membre trouvé</p>
        </td>
    </tr>
@endforelse
