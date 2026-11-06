{{-- Liste mobile (cartes) — sync AJAX via #cashierMemberMobileList --}}
<div id="cashierMemberMobileList" class="md:hidden space-y-3">
    @forelse($members as $member)
        @php
            $rankLevel = (int) ($member->rank_level ?? 0);
            $phone = ($member->phone && $member->phone !== 'N/A') ? $member->phone : null;
            $subtitleParts = array_filter([
                $member->email ?? null,
                $phone,
                $member->sponsor_id ? ('Code ' . $member->sponsor_id) : null,
            ]);
            $totalCash = (float) ($member->pos_cash_paid_total ?? 0);
        @endphp
        {{-- div + onclick (pas :href) pour éviter <a> imbriqués avec les actions --}}
        <x-ui.list-card
            :title="$member->name"
            :subtitle="implode(' · ', $subtitleParts)"
            :amount="'$' . number_format($totalCash, 2)"
            class="cursor-pointer"
            role="link"
            tabindex="0"
            onclick="window.location='{{ route('cashier.members.show', $member) }}'"
            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ route('cashier.members.show', $member) }}';}"
        >
            <x-slot:status>
                <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">
                    {{ \App\Support\MlmRank::label($rankLevel) }}
                </span>
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ml-1',
                    'text-[var(--success)] bg-[var(--success-light)]' => $member->is_active,
                    'text-[var(--danger)] bg-[var(--danger-light)]' => ! $member->is_active,
                ])>{{ $member->is_active ? 'Actif' : 'Inactif' }}</span>
            </x-slot:status>
            <x-slot:actions>
                <a href="{{ route('cashier.members.commissions', $member->id) }}"
                   class="btn btn-success btn-sm"
                   title="Commissions POS"
                   aria-label="Commissions POS"
                   onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.members.orders', $member->id) }}"
                   class="btn btn-warning btn-sm"
                   title="Commandes"
                   aria-label="Commandes"
                   onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </a>
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucun membre"
            :description="request()->filled('search') ? 'Aucun résultat pour ces filtres.' : 'Aucun membre trouvé.'"
        >
            <x-slot:icon>👥</x-slot:icon>
        </x-ui.empty-state>
    @endforelse
</div>
