@php
    $bonusByType = $bonusByType ?? [];
    $bonusMonths = $bonusMonths ?? [];
    $bonusMonth = $bonusMonth ?? '';
    $activityPeriod = $activityPeriod ?? 'week';
    $bonusFilterActive = filled($bonusMonth);
    $bonusFilterLabel = ($bonusMonths[$bonusMonth] ?? null) ?: 'Filtrer';
@endphp
@if(!empty($bonusByType))
<div class="wallet-bonus-desktop animate-fadeInUp delay-3 space-y-4" data-wallet-bonus-desktop>
    <div class="card p-4 sm:p-5">
        <div class="wallet-bonus-desktop__head mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base shrink-0">Détail des bonus (payés)</h3>

            <div class="wallet-bonus-desktop__tools member-header-actions">
                <div class="header-search member-desktop-search">
                    <div class="search-wrapper">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               id="walletBonusSearchDesktop"
                               class="search-input"
                               placeholder="Rechercher un bonus…"
                               autocomplete="off"
                               aria-label="Rechercher un type de bonus">
                    </div>
                </div>

                @if(!empty($bonusMonths))
                    <div class="supervision-filter-wrap relative shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button"
                                @click="open = !open"
                                class="btn btn-outline btn-sm btn-icon {{ $bonusFilterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                                :aria-expanded="open"
                                aria-haspopup="true"
                                title="{{ $bonusFilterLabel }}"
                                aria-label="{{ $bonusFilterLabel }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </button>
                        <div x-show="open"
                             x-cloak
                             x-transition
                             @click.outside="open = false"
                             class="users-filter-menu users-filter-panel"
                             role="menu"
                             aria-label="Mois des bonus">
                            @foreach($bonusMonths as $value => $label)
                                <a href="{{ route('wallet.index', ['period' => $activityPeriod, 'bonus_month' => $value]) }}"
                                   class="users-filter-menu__item {{ $bonusMonth === $value ? 'is-active' : '' }}"
                                   role="menuitem">
                                    <span>{{ $label }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3" id="walletBonusDesktopGrid">
            @foreach($bonusByType as $key => $row)
                <div class="wallet-bonus-desktop__stat border border-[var(--border-light)] rounded-xl p-3"
                     data-bonus-key="{{ $key }}"
                     data-bonus-search="{{ strtolower(($row['label'] ?? '').' '.($row['short'] ?? '').' '.$key) }}">
                    <p class="text-[10px] sm:text-xs font-semibold uppercase text-[var(--text-secondary)]">{{ $row['label'] }}</p>
                    <p class="text-lg sm:text-xl font-bold text-primary-500 mt-1">${{ number_format($row['total'], 2) }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-0.5">{{ $row['short'] }}</p>
                    @if(($row['count'] ?? 0) > 0)
                        <p class="text-[10px] text-[var(--text-secondary)] mt-1">{{ $row['count'] }} paiement(s)</p>
                    @endif
                </div>
            @endforeach
        </div>
        <p id="walletBonusDesktopEmpty" class="hidden text-sm text-[var(--text-secondary)] text-center py-6">
            Aucun bonus ne correspond à votre recherche.
        </p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-wallet-bonus-desktop]');
    if (!root) return;

    const input = document.getElementById('walletBonusSearchDesktop');
    const grid = document.getElementById('walletBonusDesktopGrid');
    const empty = document.getElementById('walletBonusDesktopEmpty');
    if (!input || !grid) return;

    const cards = Array.from(grid.querySelectorAll('[data-bonus-search]'));

    function applySearch() {
        const q = (input.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const hay = card.getAttribute('data-bonus-search') || '';
            const show = !q || hay.indexOf(q) !== -1;
            card.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        if (empty) {
            empty.classList.toggle('hidden', visible > 0);
        }
        grid.classList.toggle('hidden', visible === 0);
    }

    input.addEventListener('input', applySearch);
});
</script>
@endpush
@endif
