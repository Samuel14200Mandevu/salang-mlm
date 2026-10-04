<div class="card animate-fadeInUp delay-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Progression vers le prochain grade</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                Actuel: <span class="font-bold text-primary-500">{{ $currentRankName ?? 'Distributeur' }}</span>
                @if(isset($rankProgress['next']) && $rankProgress['next'] != 'Maximum Level')
                    -> Prochain: <span class="font-bold text-purple-500">{{ $rankProgress['next'] }}</span>
                @endif
            </p>
        </div>
        <span class="text-sm font-bold text-primary-500">{{ number_format($rankProgress['progress'] ?? 0, 1) }}%</span>
    </div>

    <div class="dashboard-rank-progress-bar">
        <div class="fill" style="width: {{ $rankProgress['progress'] ?? 0 }}%;"></div>
    </div>

    <div class="flex justify-between text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
        <span>{{ number_format($pvCumul ?? 0) }} PV (Cumule)</span>
        <span>{{ number_format($rankProgress['next_pv'] ?? 0) }} PV</span>
    </div>

    @if(($rankProgress['pv_needed'] ?? 0) > 0)
        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">
            Encore <span class="font-bold">{{ number_format($rankProgress['pv_needed']) }} PV</span> pour atteindre le grade suivant
        </p>
    @endif
</div>
