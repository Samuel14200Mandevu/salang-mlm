<ul class="commissions-dashboard-feed__list">
    @forelse($topReferrals as $index => $referral)
        <li class="commissions-dashboard-feed-row member-fintech-tx is-static">
            <span class="member-fintech-tx__icon">{{ $index + 1 }}</span>
            <div class="member-fintech-tx__body">
                <p class="member-fintech-tx__title">{{ $referral->fromUser?->name ?? 'N/A' }}</p>
                <p class="member-fintech-tx__meta">{{ $referral->count }} commission(s)</p>
            </div>
            <span class="member-fintech-tx__amount is-muted">${{ number_format($referral->total, 2) }}</span>
        </li>
    @empty
        <li class="commissions-dashboard-feed__empty">Aucune donnée</li>
    @endforelse
</ul>
