<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-5">
    <div class="card lg:col-span-2 member-dashboard-performance-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Performance</h3>
            <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV crédités et commissions · 6 mois</span>
        </div>

        @php
            $series = $performanceChart ?? [];
            $hasPerformance = collect($series)->contains(
                fn ($row) => ((float) ($row['pv'] ?? 0)) > 0 || ((float) ($row['commission'] ?? 0)) > 0
            );
        @endphp

        @if($hasPerformance)
            <div class="member-fintech-performance__legend mb-2" aria-hidden="true">
                <span class="member-fintech-performance__key is-pv">PV crédités</span>
                <span class="member-fintech-performance__key is-comm">Commissions</span>
            </div>
            @include('dashboard.partials.fintech-performance-chart', ['series' => $series])
        @else
            <p class="text-center text-[var(--text-secondary)] py-8 text-sm">Aucune performance enregistrée sur cette période.</p>
        @endif
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Activites recentes</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $recentActivities->count() ?? 0 }}</span>
        </div>

        <div class="space-y-2 max-h-48 sm:max-h-60 overflow-y-auto custom-scrollbar">
            @forelse($recentActivities ?? [] as $activity)
                <div class="dashboard-activity-item">
                    <div class="avatar avatar-sm avatar-gradient flex-shrink-0">
                        {{ substr($activity->fromUser?->name ?? 'S', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm text-[var(--text-primary)] truncate">
                            <span class="font-semibold">{{ $activity->fromUser?->name ?? 'Systeme' }}</span>
                            <span class="text-[var(--text-secondary)]">{{ $activity->type_label ?? $activity->type ?? 'action' }}</span>
                        </p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                    @if($activity->amount)
                        <span class="text-xs sm:text-sm font-bold text-green-500 flex-shrink-0">+${{ number_format($activity->amount, 2) }}</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-8 text-sm">Aucune activite recente</p>
            @endforelse
        </div>
    </div>
</div>
