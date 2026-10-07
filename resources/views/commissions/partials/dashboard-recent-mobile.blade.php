<ul class="commissions-dashboard-feed__list">
    @forelse($recent as $commission)
        <li>
            <a href="{{ route('commissions.show', $commission) }}" class="commissions-dashboard-feed-row member-fintech-tx">
                <span class="member-fintech-tx__icon">{{ strtoupper(substr($commission->type ?? 'C', 0, 1)) }}</span>
                <div class="member-fintech-tx__body">
                    <p class="member-fintech-tx__title">{{ ucfirst($commission->type) }}</p>
                    <p class="member-fintech-tx__meta">{{ $commission->fromUser?->name ?? 'Système' }} · {{ $commission->created_at->diffForHumans() }}</p>
                </div>
                <span class="member-fintech-tx__amount is-credit">+${{ number_format($commission->amount, 2) }}</span>
            </a>
        </li>
    @empty
        <li class="commissions-dashboard-feed__empty">Aucune commission récente</li>
    @endforelse
</ul>
