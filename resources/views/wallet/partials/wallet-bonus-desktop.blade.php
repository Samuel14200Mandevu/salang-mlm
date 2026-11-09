@php
    $bonusByType = $bonusByType ?? [];
    $bonusMonths = $bonusMonths ?? [];
    $bonusMonth = $bonusMonth ?? '';
    $activityPeriod = $activityPeriod ?? 'week';
@endphp
@if(!empty($bonusByType))
<div class="wallet-bonus-desktop animate-fadeInUp delay-3 space-y-4">
    <div class="card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Détail des bonus (payés)</h3>
            @if(!empty($bonusMonths))
                <div class="inline-flex flex-wrap gap-2" role="group" aria-label="Mois des bonus">
                    @foreach($bonusMonths as $value => $label)
                        <a href="{{ route('wallet.index', ['period' => $activityPeriod, 'bonus_month' => $value]) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold border transition-colors
                                  {{ $bonusMonth === $value
                                      ? 'bg-primary-500 text-white border-primary-500'
                                      : 'bg-[var(--bg-card)] text-[var(--text-secondary)] border-[var(--border-color)] hover:border-primary-400 hover:text-primary-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
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
