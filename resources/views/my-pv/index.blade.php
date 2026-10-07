@extends('layouts.app')

@section('title', 'Historique PV')

@php
    $activePeriod = request('period');
    $activePeriodLabel = null;
    if ($activePeriod && preg_match('/^(\d{4})-(\d{1,2})/', (string) $activePeriod, $activeParts)) {
        $activePeriodLabel = str_pad($activeParts[2], 2, '0', STR_PAD_LEFT) . '/' . $activeParts[1];
    }
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 my-pv-page">
    <div class="member-page-intro my-pv-page-intro-desktop animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Historique des PV</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Vos points de volume enregistrés par période</p>
    </div>

    <div class="my-pv-mobile-banner animate-fadeInUp">
        <div class="my-pv-mobile-banner__stack" data-my-pv-picker>
            <div class="shop-catalog-banner my-pv-catalog-banner">
                <div class="shop-catalog-banner__text">
                    <p class="shop-catalog-banner__eyebrow">Points volume</p>
                    <p class="shop-catalog-banner__title">Historique PV</p>
                    @if($activePeriodLabel)
                        <p class="shop-catalog-banner__sub">Période · {{ $activePeriodLabel }}</p>
                    @else
                        <p class="shop-catalog-banner__sub">Personnel · équipe · mensuel</p>
                    @endif
                </div>
                <div class="shop-catalog-banner__tools">
                    @include('my-pv.partials.period-picker', ['pickerVariant' => 'banner', 'activePeriod' => $activePeriod])
                </div>
            </div>
            @include('my-pv.partials.period-panel', [
                'activePeriod' => $activePeriod,
                'panelClass' => 'my-pv-period-panel--banner-inline',
            ])
        </div>
    </div>

    <div class="my-pv-stats stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV personnel</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ number_format($totals['personal'], 1, ',', ' ') }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV équipe</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ number_format($totals['team'], 1, ',', ' ') }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV mensuel</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ number_format($totals['monthly'], 1, ',', ' ') }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total historique</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ number_format($totals['history_sum'], 1, ',', ' ') }}</p>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-2 my-pv-movements-card">
        <div class="my-pv-movements-head">
            <div class="my-pv-movements-head__left">
                <h2 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Mouvements PV</h2>
                @if($activePeriodLabel)
                    <p class="my-pv-movements-head__filter">Période · {{ $activePeriodLabel }}</p>
                @endif
                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $entries->total() }} entrée(s)</span>
            </div>

            <div class="my-pv-period-picker-wrap my-pv-period-picker-wrap--desktop">
                @include('my-pv.partials.period-picker', ['pickerVariant' => 'card', 'activePeriod' => $activePeriod])
            </div>
        </div>

        <div class="table-wrap my-pv-movements-desktop">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mois / année</th>
                        <th>Type</th>
                        <th class="text-right">Montant</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $monthYear = '—';
                            if ($entry->period && preg_match('/^(\d{4})-(\d{1,2})/', (string) $entry->period, $parts)) {
                                $monthYear = str_pad($parts[2], 2, '0', STR_PAD_LEFT) . '/' . $parts[1];
                            } elseif ($entry->date) {
                                $monthYear = $entry->date->format('m/Y');
                            } elseif ($entry->period) {
                                $monthYear = $entry->period;
                            }
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $monthYear }}</td>
                            <td>
                                <span class="badge badge-sm badge-info">{{ $entry->type_label }}</span>
                            </td>
                            <td class="text-right font-semibold text-primary-500">
                                +{{ number_format($entry->amount, 1, ',', ' ') }} PV
                            </td>
                            <td class="text-[var(--text-secondary)] text-sm">{{ $entry->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-[var(--text-secondary)] py-8">Aucun mouvement PV enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="my-pv-movements-mobile space-y-2">
            @forelse($entries as $entry)
                @php
                    $monthYearMobile = '—';
                    if ($entry->period && preg_match('/^(\d{4})-(\d{1,2})/', (string) $entry->period, $parts)) {
                        $monthYearMobile = str_pad($parts[2], 2, '0', STR_PAD_LEFT) . '/' . $parts[1];
                    } elseif ($entry->date) {
                        $monthYearMobile = $entry->date->format('m/Y');
                    } elseif ($entry->period) {
                        $monthYearMobile = $entry->period;
                    }
                @endphp
                <article class="rounded-lg border border-[var(--border-light)] p-3 bg-[var(--bg-secondary)]/40">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $monthYearMobile }}</p>
                            <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ $entry->type_label }}</p>
                        </div>
                        <p class="text-sm font-bold text-primary-500 shrink-0">+{{ number_format($entry->amount, 1, ',', ' ') }}</p>
                    </div>
                    @if($entry->notes)
                        <p class="text-xs text-[var(--text-secondary)] mt-2">{{ $entry->notes }}</p>
                    @endif
                </article>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-6 text-sm">Aucun mouvement PV enregistré.</p>
            @endforelse
        </div>

        @if($entries->hasPages())
            <div class="mt-4">
                <x-salang-pagination :paginator="$entries" />
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pickers = document.querySelectorAll('[data-my-pv-picker]');
    if (!pickers.length) {
        return;
    }

    function closeAll(except) {
        pickers.forEach(function (picker) {
            if (except && picker === except) {
                return;
            }
            var toggle = picker.querySelector('[data-my-pv-period-toggle]');
            var panel = picker.querySelector('[data-my-pv-period-panel]');
            if (!toggle || !panel) {
                return;
            }
            panel.classList.remove('is-open');
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');
        });
    }

    pickers.forEach(function (picker) {
        var toggle = picker.querySelector('[data-my-pv-period-toggle]');
        var panel = picker.querySelector('[data-my-pv-period-panel]');
        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var willOpen = !panel.classList.contains('is-open');
            closeAll(picker);
            panel.classList.toggle('is-open', willOpen);
            toggle.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            panel.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
        });

        panel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeAll(null);
            });
        });
    });

    document.addEventListener('click', function () {
        closeAll(null);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll(null);
        }
    });
});
</script>
@endpush
