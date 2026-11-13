@php
    $memberRows = $members ?? collect();
@endphp
<div class="admin-user-list md:hidden" id="adminCommissionsMobileList">
@forelse($memberRows as $row)
    @php
        $detailUrl = route('admin.commissions.member', array_filter(
            array_merge(
                ['user' => $row->user_id],
                request()->only(['search', 'type', 'status', 'period', 'band'])
            ),
            fn ($v) => $v !== null && $v !== ''
        ));
        $n = $memberRows->firstItem() ? ($memberRows->firstItem() + $loop->index) : $loop->iteration;
    @endphp
    <a href="{{ $detailUrl }}" class="admin-entity-card">
        <div class="admin-entity-card__top">
            <div class="min-w-0">
                <div class="admin-entity-card__name">{{ $n }}. {{ $row->name }}</div>
                <div class="admin-entity-card__sub font-mono">{{ $row->code }} · G{{ $row->grade }}</div>
            </div>
            @php
                $bonusTone = $row->bonus_tone ?? 'neutral';
                $bonusClass = match ($bonusTone) {
                    'wallet' => 'bonus-total-wallet',
                    'pending' => 'bonus-total-pending',
                    default => 'bonus-total-neutral',
                };
                $bonusTitle = match ($bonusTone) {
                    'wallet' => 'En portefeuille',
                    'pending' => 'En attente',
                    default => 'Bonus total',
                };
            @endphp
            <span class="text-sm font-bold shrink-0 {{ $bonusClass }}" title="{{ $bonusTitle }}">{{ number_format($row->total, 2) }} $</span>
        </div>
        <div class="admin-entity-card__meta flex flex-wrap gap-x-2 gap-y-1 text-xs text-[var(--text-secondary)]">
            <span>Parrain {{ $row->parrain_code }}</span>
            <span>{{ $row->parrain_name ?? '—' }}</span>
            <span>PV {{ $row->pv === null ? '—' : number_format($row->pv, 0) }}</span>
            <span class="amount-paid">Payé {{ number_format($row->paid ?? 0, 2) }} $</span>
            <span class="amount-reste">Reste {{ number_format($row->reste ?? 0, 2) }} $</span>
        </div>
    </a>
@empty
    <div class="admin-empty-state">
        <p>Aucune commission</p>
    </div>
@endforelse
</div>
