<ul class="commissions-levels-earnings__list">
    @foreach(collect($levels ?? [])->except('total') as $key => $level)
        @if(! is_numeric($key))
            @continue
        @endif
        <li>
            @if($key <= 4)
                <button type="button"
                        class="commissions-levels-earnings-row member-fintech-tx"
                        onclick="openLevelModal({{ (int) $key }})">
                    <span class="member-fintech-tx__icon">{{ $level['icon'] ?? $key }}</span>
                    <div class="member-fintech-tx__body">
                        <p class="member-fintech-tx__title">{{ $level['label'] ?? 'Niveau '.$key }}</p>
                        <p class="member-fintech-tx__meta">{{ $level['count'] ?? 0 }} commission(s) payée(s)</p>
                    </div>
                    <span class="member-fintech-tx__amount is-credit">${{ number_format($level['amount'] ?? 0, 2) }}</span>
                </button>
            @else
                <div class="commissions-levels-earnings-row member-fintech-tx is-static">
                    <span class="member-fintech-tx__icon">{{ $level['icon'] ?? $key }}</span>
                    <div class="member-fintech-tx__body">
                        <p class="member-fintech-tx__title">{{ $level['label'] ?? 'Niveau '.$key }}</p>
                        <p class="member-fintech-tx__meta">{{ $level['count'] ?? 0 }} commission(s) payée(s)</p>
                    </div>
                    <span class="member-fintech-tx__amount is-credit">${{ number_format($level['amount'] ?? 0, 2) }}</span>
                </div>
            @endif
        </li>
    @endforeach
</ul>
