<div class="member-fintech-rail-wrap member-fintech-animate member-fintech-animate--d3">
    <p class="member-fintech-section-label">Synthèse</p>
    <div class="member-fintech-rail" tabindex="0">
        <div class="member-fintech-rail__track">
            <article class="member-fintech-tile is-highlight">
                <span class="member-fintech-tile__label">Commissions</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">{{ number_format($commissionsCount ?? 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Transactions</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">{{ number_format($transactionsCount ?? 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Commandes</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">{{ number_format($ordersCount ?? 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Retiré</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">${{ number_format($totalWithdrawn ?? 0, 0) }}</span>
            </article>
        </div>
    </div>
</div>
