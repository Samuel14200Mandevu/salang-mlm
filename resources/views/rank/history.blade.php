{{-- resources/views/rank/history.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .rank-history-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.875rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    .rank-history-item:hover {
        background: var(--bg-hover);
    }
    .rank-history-item.promotion {
        border-left-color: #22c55e;
    }
    .rank-history-item.demotion {
        border-left-color: #ef4444;
    }
    .rank-history-item.update {
        border-left-color: #3b82f6;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    
    @media (max-width: 640px) {
        .rank-history-item {
            flex-wrap: wrap;
            padding: 0.5rem;
        }
        .rank-history-item .rank-badge {
            font-size: 0.55rem;
            padding: 0.125rem 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Historique des grades</h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    Toutes vos promotions et r&eacute;trogradations
                </p>
            </div>
            <a href="{{ route('rank.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-3 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total</p>
            <p class="text-lg sm:text-2xl font-bold text-[var(--text-primary)]">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Promotions</p>
            <p class="text-lg sm:text-2xl font-bold text-green-500">{{ $stats['promotions'] ?? 0 }}</p>
        </div>
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">R&eacute;trogradations</p>
            <p class="text-lg sm:text-2xl font-bold text-red-500">{{ $stats['demotions'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Historique -->
    <div class="rank-card animate-fadeInUp delay-2">
        <div class="space-y-2">
            @forelse($history ?? [] as $item)
                @php
                    $oldLevel = $item->oldRank?->level ?? 0;
                    $newLevel = $item->newRank?->level ?? 0;
                    $type = $newLevel > $oldLevel ? 'promotion' : ($newLevel < $oldLevel ? 'demotion' : 'update');
                    $typeLabel = $newLevel > $oldLevel ? 'Promotion' : ($newLevel < $oldLevel ? 'R&eacute;trogradation' : 'Mise &agrave; jour');
                    $badgeClass = $type === 'promotion' ? 'badge-success' : ($type === 'demotion' ? 'badge-danger' : 'badge-info');
                @endphp
                <div class="rank-history-item {{ $type }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                            <span class="text-xs sm:text-sm font-medium text-[var(--text-primary)]">
                                {{ $item->old_rank_name ?? 'Distributeur' }}
                            </span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-primary-500">
                                {{ $item->new_rank_name ?? 'Distributeur' }}
                            </span>
                            <span class="badge {{ $badgeClass }} text-[8px] sm:text-[10px]">
                                {{ $typeLabel }}
                            </span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                            <span class="mx-1">•</span>
                            PV: {{ number_format($item->pv_at_time ?? 0) }}
                            <span class="mx-1">•</span>
                            {{ $item->notes ?? '' }}
                        </p>
                    </div>
                    <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] flex-shrink-0">
                        {{ $item->created_at->diffForHumans() }}
                    </span>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-8 text-sm">
                    Aucun historique de grade
                </p>
            @endforelse
        </div>

        @if($history->hasPages())
            <div class="mt-4">
                {{ $history->links() }}
            </div>
        @endif
    </div>

</div>
@endsection