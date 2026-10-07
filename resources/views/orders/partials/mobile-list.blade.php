<ul class="orders-mobile-feed__list" id="ordersMobileList">
    @forelse($orders ?? [] as $order)
        <li>
            <a href="{{ route('orders.show', $order) }}"
               class="orders-mobile-row member-fintech-tx"
               data-status="{{ $order->status }}"
               data-date="{{ $order->created_at->format('Y-m-d') }}">
                <span class="member-fintech-tx__icon orders-mobile-row__icon">#</span>
                <div class="member-fintech-tx__body">
                    <p class="member-fintech-tx__title">#{{ $order->order_number }}</p>
                    <p class="member-fintech-tx__meta">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                        · {{ $order->items->count() }} article(s)
                    </p>
                </div>
                <div class="orders-mobile-row__aside">
                    <span class="member-fintech-tx__amount is-muted">${{ number_format($order->total, 2) }}</span>
                    @include('orders.partials.status-label', ['status' => $order->status])
                </div>
            </a>
        </li>
    @empty
        <li class="orders-mobile-feed__empty">
            <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <p class="font-semibold text-[var(--text-primary)]">Aucune commande</p>
            <p class="text-sm text-[var(--text-secondary)] mt-1">Commencez à magasiner pour passer votre première commande</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm mt-3 inline-flex w-auto">Voir la boutique</a>
        </li>
    @endforelse
</ul>
