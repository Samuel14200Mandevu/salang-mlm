@php
    $bonusByType = $bonusByType ?? [];
@endphp
@if(!empty($bonusByType))
<div class="wallet-bonus-desktop animate-fadeInUp delay-3 space-y-4">
    <div class="card p-4 sm:p-5">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Détail des bonus (payés)</h3>
        <div class="grid grid-cols-2 gap-3">
            @foreach($bonusByType as $row)
                <div class="wallet-bonus-desktop__stat border border-[var(--border-light)] rounded-xl p-3">
                    <p class="text-[10px] sm:text-xs font-semibold uppercase text-[var(--text-secondary)]">{{ $row['label'] }}</p>
                    <p class="text-lg sm:text-xl font-bold text-primary-500 mt-1">${{ number_format($row['total'], 2) }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-0.5">{{ $row['short'] }}</p>
                    @if(($row['count'] ?? 0) > 0)
                        <p class="text-[10px] text-[var(--text-secondary)] mt-1">{{ $row['count'] }} paiement(s)</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
