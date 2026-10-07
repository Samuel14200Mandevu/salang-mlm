@php
    $typeIcons = [
        'commission' => ['bg' => '#16a34a', 'letter' => 'C'],
        'deposit' => ['bg' => '#2563eb', 'letter' => 'D'],
        'withdrawal' => ['bg' => '#f59e0b', 'letter' => 'R'],
        'purchase' => ['bg' => '#7c3aed', 'letter' => 'A'],
        'refund' => ['bg' => '#0891b2', 'letter' => '↩'],
    ];
@endphp
<section class="member-fintech-panel member-fintech-feed member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">Historique</h2>
            <p class="member-fintech-panel__sub">Dernières opérations</p>
        </div>
        <span class="member-fintech-panel__badge">{{ $transactions->total() }} total</span>
    </div>

    @if($transactions->count() > 0)
        <ul class="member-fintech-feed__list">
            @foreach($transactions as $transaction)
                @php
                    $meta = $typeIcons[$transaction->type] ?? ['bg' => 'var(--color-primary-600)', 'letter' => strtoupper(substr($transaction->type, 0, 1))];
                    $isCredit = (float) $transaction->amount > 0;
                @endphp
                <li class="member-fintech-tx">
                    <span class="member-fintech-tx__icon" style="background: {{ $meta['bg'] }};">{{ $meta['letter'] }}</span>
                    <div class="member-fintech-tx__body">
                        <p class="member-fintech-tx__title">{{ $transaction->type_label }}</p>
                        <p class="member-fintech-tx__meta">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                            @if($transaction->description)
                                · {{ \Illuminate\Support\Str::limit($transaction->description, 28) }}
                            @endif
                        </p>
                    </div>
                    <span class="member-fintech-tx__amount {{ $isCredit ? 'is-credit' : 'is-debit' }}">
                        {{ $isCredit ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="member-fintech-feed__empty">Aucune transaction pour le moment.</p>
    @endif

    @if($transactions->hasPages())
        <div class="member-wallet-feed-pagination">
            <x-salang-pagination :paginator="$transactions" />
        </div>
    @endif
</section>
