@if($dashboardLevel['show_leaders'] ?? false)
@php
    $objectiveTargets = match ($dashboardLevelNumber) {
        5 => ['downlines' => 20, 'pv_personal' => 5000],
        7 => ['downlines' => 30, 'pv_personal' => 12000],
        8 => ['downlines' => 35, 'pv_personal' => 20000],
        9 => ['downlines' => 50, 'pv_personal' => 30000],
        default => ['downlines' => 20, 'pv_personal' => 5000],
    };
@endphp

@if($dashboardLevelNumber === 3)
    <div class="card animate-fadeInUp delay-3">
        <h4 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Top Filleuls</h4>
        <div class="space-y-2">
            @forelse($topDownlines->take(5) as $index => $downline)
                <div class="dashboard-leader-row {{ $index < 3 ? 'highlight' : '' }}">
                    <span class="text-xs font-bold w-6 text-center">{{ $index + 1 }}</span>
                    <span class="flex-1 text-[var(--text-primary)]">{{ $downline->name }}</span>
                    <span class="text-sm text-[var(--text-secondary)]">{{ $downline->total_downlines ?? 0 }} filleuls</span>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4">Aucun filleul actif</p>
            @endforelse
        </div>
    </div>
@elseif($dashboardLevelNumber === 4)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-4">
        <div class="card">
            <h4 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Top 10 Filleuls</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                @forelse($topDownlines->take(10) as $index => $downline)
                    <div class="dashboard-leader-row">
                        <span class="text-xs font-bold w-6">{{ $index + 1 }}</span>
                        <span class="flex-1 text-[var(--text-primary)]">{{ $downline->name }}</span>
                        <span class="text-sm text-[var(--text-secondary)]">{{ $downline->total_downlines ?? 0 }} filleuls</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4">Aucun filleul actif</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <h4 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Performance</h4>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">Croissance grade</p>
                    <p class="text-2xl font-bold text-green-500">+{{ number_format($rankProgress['progress'] ?? 0, 1) }}%</p>
                </div>
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">PV total equipe</p>
                    <p class="text-2xl font-bold text-purple-500">{{ number_format($pvCumul ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">Commissions en attente</p>
                    <p class="text-2xl font-bold text-orange-500">${{ number_format($pendingCommission ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
@elseif($dashboardLevelNumber >= 5)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-4">
        <div class="card">
            <h4 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Top Leaders</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                @forelse($topDownlines->take(15) as $index => $downline)
                    <div class="dashboard-leader-row {{ $index < 3 ? 'highlight' : '' }}">
                        <span class="text-xs font-bold w-6">{{ $index + 1 }}</span>
                        <span class="flex-1 text-[var(--text-primary)]">{{ $downline->name }}</span>
                        <span class="text-sm text-[var(--text-secondary)]">{{ $downline->total_downlines ?? 0 }} filleuls</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4">Aucun leader disponible</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <h4 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Objectifs de progression</h4>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">Filleuls directs</span>
                        <span class="font-semibold">{{ $totalDownlines }} / {{ $objectiveTargets['downlines'] }}</span>
                    </div>
                    <div class="dashboard-objective-bar">
                        <div class="fill" style="width: {{ min(($totalDownlines / max($objectiveTargets['downlines'], 1)) * 100, 100) }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">PV mensuel</span>
                        <span class="font-semibold">{{ number_format($pvPersonnel ?? 0) }} / {{ number_format($objectiveTargets['pv_personal']) }}</span>
                    </div>
                    <div class="dashboard-objective-bar">
                        <div class="fill" style="width: {{ min((($pvPersonnel ?? 0) / max($objectiveTargets['pv_personal'], 1)) * 100, 100) }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">PV equipe</span>
                        <span class="font-semibold">{{ number_format($pvCumul ?? 0) }} / {{ number_format($rankProgress['next_pv'] ?? 0) }}</span>
                    </div>
                    <div class="dashboard-objective-bar">
                        <div class="fill" style="width: {{ min((($pvCumul ?? 0) / max($rankProgress['next_pv'] ?? 1, 1)) * 100, 100) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endif
