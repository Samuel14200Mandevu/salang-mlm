<div class="admin-user-list md:hidden" id="adminCommissionsMobileList">
@forelse($commissions as $commission)
    @php
        $typeLabels = [
            'direct' => 'Direct',
            'indirect' => 'Indirect',
            'leadership' => 'Leadership',
            'retail' => 'Retail',
            'global' => 'Global',
            'binary' => 'Binaire',
        ];
        $typeClass = 'type-badge-' . $commission->type;
        $typeLabel = $typeLabels[$commission->type] ?? ucfirst(str_replace('_', ' ', $commission->type));
        $statusLabels = [
            'paid' => 'Payé',
            'pending' => 'En attente',
            'cancelled' => 'Annulé',
        ];
        $statusClasses = [
            'paid' => 'badge-success',
            'pending' => 'badge-warning',
            'cancelled' => 'badge-danger',
        ];
    @endphp
    <a href="{{ route('admin.commissions.show', $commission->id) }}" class="admin-entity-card">
        <div class="admin-entity-card__top">
            <div class="min-w-0">
                <div class="admin-entity-card__name">{{ $commission->user?->name ?? 'N/A' }}</div>
                <div class="admin-entity-card__sub">{{ $commission->fromUser?->name ?? 'Système' }}</div>
            </div>
            <span class="amount-positive text-sm font-bold shrink-0">+{{ number_format($commission->amount, 2) }} $</span>
        </div>
        <div class="admin-entity-card__meta">
            <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
            <span class="badge {{ $statusClasses[$commission->status] ?? 'badge-warning' }}">
                {{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}
            </span>
            @if($commission->period)
                <span class="text-xs text-[var(--text-secondary)]">{{ $commission->period }}</span>
            @endif
        </div>
    </a>
@empty
    <div class="admin-empty-state">
        <p>Aucune commission</p>
    </div>
@endforelse
</div>
