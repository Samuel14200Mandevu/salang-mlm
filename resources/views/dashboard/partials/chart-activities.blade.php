<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-5">
    <div class="card member-dashboard-performance-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
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
