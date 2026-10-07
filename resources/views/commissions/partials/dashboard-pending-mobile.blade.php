<ul class="commissions-dashboard-feed__list">
    @foreach($pending as $commission)
        <li>
            <a href="{{ route('commissions.show', $commission) }}" class="commissions-dashboard-feed-row member-fintech-tx">
                <span class="member-fintech-tx__icon">!</span>
                <div class="member-fintech-tx__body">
                    <p class="member-fintech-tx__title">{{ ucfirst($commission->type) }}</p>
                    <p class="member-fintech-tx__meta">{{ $commission->fromUser?->name ?? 'Système' }} · {{ $commission->created_at->format('d/m/Y') }}</p>
                </div>
                <span class="member-fintech-tx__amount is-muted">${{ number_format($commission->amount, 2) }}</span>
            </a>
        </li>
    @endforeach
</ul>
