@php
    $series = collect($performanceChart ?? []);
    $hasPerformance = $series->isNotEmpty();
    $performancePvTotal = (float) $series->sum('pv');
    $performancePvPeak = (float) ($series->max('pv') ?? 0);
    $performanceChartPayload = $series->map(fn ($row) => [
        'month' => (string) ($row['month_short'] ?? $row['month'] ?? ''),
        'pv' => (float) ($row['pv'] ?? 0),
        'commission' => (float) ($row['commission'] ?? 0),
    ])->values();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-5 member-dash-lower">
    <div class="card member-dashboard-performance-card member-dash-panel member-performance-chart-card">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-2">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Performance</h3>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5">
                    PV crédités · 6 mois · {{ now()->format('Y') }}
                </p>
            </div>
            @if($hasPerformance)
                <div class="member-performance-chart-meta">
                    <span class="meta-total"
                          id="memberPerformanceTotalCount"
                          data-count-to="{{ (int) round($performancePvTotal) }}">0</span>
                    <span class="meta-label">
                        PV total · pic
                        <span id="memberPerformancePeakCount" data-count-to="{{ (int) round($performancePvPeak) }}">0</span>
                    </span>
                </div>
            @endif
        </div>

        <div class="member-performance-chart-shell">
            @if($hasPerformance)
                <canvas id="memberPerformanceChart" aria-label="Graphique de performance mensuelle"></canvas>
            @else
                <div class="w-full h-full flex items-center justify-center text-[var(--text-secondary)] text-sm">
                    Aucune performance enregistrée sur cette période.
                </div>
            @endif
        </div>
    </div>

    <div class="card member-dash-panel member-dash-activity-panel">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Activités récentes</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $recentActivities->count() ?? 0 }}</span>
        </div>

        <div class="space-y-1 max-h-56 overflow-y-auto custom-scrollbar">
            @forelse($recentActivities ?? [] as $activity)
                <div class="member-dash-activity">
                    <div class="member-dash-activity__avatar">
                        {{ substr($activity->fromUser?->name ?? 'S', 0, 1) }}
                    </div>
                    <div class="member-dash-activity__body">
                        <p class="member-dash-activity__title">
                            {{ $activity->fromUser?->name ?? 'Système' }}
                            <span class="text-[var(--text-secondary)] font-normal">{{ $activity->type_label ?? $activity->type ?? 'action' }}</span>
                        </p>
                        <p class="member-dash-activity__time">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                    @if($activity->amount)
                        <span class="member-dash-activity__amount">+${{ number_format($activity->amount, 2) }}</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-8 text-sm">Aucune activité récente</p>
            @endforelse
        </div>

        <div class="mt-2 text-right">
            <a href="{{ route('commissions.index') }}" class="text-xs text-primary-500 hover:underline font-medium">
                Voir les commissions →
            </a>
        </div>
    </div>
</div>
