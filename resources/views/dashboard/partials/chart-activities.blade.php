<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-5">
    <div class="card lg:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Evolution des gains</h3>
            <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ now()->format('Y') }}</span>
        </div>

        <div class="h-40 sm:h-48 flex items-end gap-1 sm:gap-2">
            @php
                $max = max(array_column($monthlyData ?? [], 'amount') ?: [1]);
            @endphp
            @forelse($monthlyData ?? [] as $data)
                @php
                    $height = ($data['amount'] / max($max, 1)) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group relative">
                    <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500 transition-all duration-300"
                         style="height: {{ max(8, $height) }}%;">
                        <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                    </div>
                    <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">
                        {{ substr($data['month'] ?? '', 0, 3) }}
                    </span>
                </div>
            @empty
                <div class="w-full text-center text-[var(--text-secondary)] py-8">Aucune donnee disponible</div>
            @endforelse
        </div>
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
