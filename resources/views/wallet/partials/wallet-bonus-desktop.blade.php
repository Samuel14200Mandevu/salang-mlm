@php
    $bonusByType = $bonusByType ?? [];
    $careerQualificationPrizes = $careerQualificationPrizes ?? [];
@endphp
@if(!empty($bonusByType) || !empty($careerQualificationPrizes))
<div class="wallet-bonus-desktop animate-fadeInUp delay-3 space-y-4">
    @if(!empty($bonusByType))
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
    @endif

    @if(!empty($careerQualificationPrizes))
        <div class="card p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Bonus carrière · qualification</h3>
                <a href="{{ route('services.index') }}" class="text-xs text-primary-500 hover:underline">Voir dans Services →</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach($careerQualificationPrizes as $prize)
                    <div class="wallet-bonus-desktop__career rounded-xl border overflow-hidden {{ $prize['unlocked'] ? 'border-primary-500/40 ring-1 ring-primary-500/20' : 'border-[var(--border-light)] opacity-90' }}">
                        <div class="aspect-[4/3] bg-[var(--bg-subtle)] relative">
                            @if($prize['image_url'])
                                <img src="{{ $prize['image_url'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="flex items-center justify-center h-full text-primary-500 font-bold text-2xl">15 $</div>
                            @endif
                            @if($prize['unlocked'])
                                <span class="absolute top-1.5 right-1.5 badge badge-success text-[9px]">Atteint</span>
                            @endif
                        </div>
                        <div class="p-2 text-center">
                            <p class="text-[10px] font-semibold text-[var(--text-secondary)]">Niv. {{ $prize['level'] }}</p>
                            <p class="text-xs font-medium text-[var(--text-primary)] truncate" title="{{ $prize['rank_name'] }}">{{ $prize['rank_name'] }}</p>
                            <p class="text-[10px] text-primary-600 font-semibold mt-0.5">{{ $prize['reward'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endif
