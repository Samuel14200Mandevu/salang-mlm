{{-- resources/views/commissions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Mes commissions')

@section('content')
@php
    $selectedType = $selectedType ?? '';
    $bonusByType = $bonusByType ?? [];
    $bonusMonth = $bonusMonth ?? '';
    $bonusMonthLabel = $bonusMonthLabel ?? '';
@endphp
<div class="commissions-page space-y-4 sm:space-y-6">

    <div class="commissions-page-header-desktop member-list-header animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mes Commissions</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Suivez vos gains en détail
                <span id="resultCount" class="text-xs text-[var(--text-tertiary)]">· {{ $commissions->total() ?? 0 }} commissions</span>
            </p>
        </div>
        <div class="member-header-actions">
            <div class="header-search member-desktop-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           id="searchInputDesktop"
                           class="search-input"
                           placeholder="Rechercher…"
                           autocomplete="off"
                           aria-label="Rechercher une commission">
                </div>
            </div>

            <div class="commissions-header-tools">
                <div class="supervision-filter-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="btn btn-outline btn-sm btn-icon commissions-header-icon-btn"
                            id="commissionsFilterBtn"
                            title="Filtrer"
                            :aria-expanded="open"
                            aria-haspopup="true"
                            aria-label="Filtrer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span id="commissionsFilterLabel" class="sr-only">Filtrer</span>
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition
                         @click.outside="open = false"
                         class="users-filter-menu users-filter-panel">
                        <button type="button" class="users-filter-menu__item is-active" data-commission-filter="">
                            <span>Toutes</span>
                        </button>
                        <button type="button" class="users-filter-menu__item" data-commission-filter="pending">
                            <span>En attente</span>
                        </button>
                        <button type="button" class="users-filter-menu__item" data-commission-filter="paid">
                            <span>En portefeuille</span>
                        </button>
                    </div>
                </div>

                <a href="{{ route('commissions.dashboard') }}"
                   class="btn btn-outline btn-sm btn-icon commissions-header-icon-btn"
                   title="Tableau de bord"
                   aria-label="Tableau de bord">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </a>
                <a href="{{ route('commissions.levels') }}"
                   class="btn btn-outline btn-sm btn-icon commissions-header-icon-btn"
                   title="Par niveau"
                   aria-label="Par niveau">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </a>
                <a href="{{ route('commissions.export-pdf', request()->all()) }}"
                   class="btn btn-sm btn-icon commissions-header-icon-btn btn-pdf-icon"
                   title="Exporter PDF"
                   aria-label="Exporter PDF"
                   target="_blank"
                   rel="noopener">
                    <x-pdf-icon size="lg" />
                </a>
            </div>
        </div>
    </div>

    @include('commissions.partials.catalog-head', compact('stats', 'commissions'))

    <div class="commissions-stats stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total gagne</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <a href="{{ route('withdrawal.index') }}" class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2 block no-underline hover:opacity-95">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payé</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['paid'] ?? 0, 2) }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)] mt-0.5">Déjà retiré</p>
        </a>
        <a href="{{ route('wallet.index') }}" class="card-stats border-l-4 border-sky-500 animate-fadeInUp delay-3 block no-underline hover:opacity-95">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Portefeuille</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-sky-500">${{ number_format($stats['wallet_balance'] ?? 0, 2) }}</p>
        </a>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['total_count'] ?? 0 }}</p>
        </div>
    </div>

    @if($selectedType === '')
        @include('commissions.partials.type-hub')
        <p class="commissions-hub-hint">Touchez un bonus (y compris POS cash) pour voir le détail</p>
    @else
        @php
            $selectedLabel = $bonusByType[$selectedType]['label'] ?? ucfirst($selectedType);
        @endphp
        <div class="commissions-detail-panel">
            <a href="{{ route('commissions.index', array_filter(['period' => ($bonusMonth ?? null) ?: null])) }}" class="commissions-detail-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Mes bonus</span>
            </a>
        </div>

        <p class="commissions-history-label">{{ $selectedLabel }}</p>

        <div class="commissions-mobile-feed commissions-mobile-feed--detail animate-fadeInUp delay-6" data-salang-page-swipe>
            @include('commissions.partials.mobile-list', ['commissions' => $commissions])
        </div>

        @if($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
            <div class="commissions-pagination commissions-pagination--mobile mt-2" id="paginationContainerMobile">
                <p class="commissions-pagination__meta" aria-live="polite">
                    Page {{ $commissions->currentPage() }} / {{ $commissions->lastPage() }}
                    · {{ number_format($commissions->total(), 0, ',', ' ') }} lignes
                </p>
                <x-salang-pagination :paginator="$commissions" />
            </div>
        @endif

        <div class="commissions-table-card card animate-fadeInUp delay-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Détail {{ $selectedLabel }}</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs" id="totalBadge">{{ $commissions->total() ?? 0 }} commissions</span>
            </div>

            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-xs sm:text-sm">Date</th>
                            <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Source</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Détail</th>
                            <th class="text-xs sm:text-sm text-right">Montant</th>
                            <th class="text-xs sm:text-sm">Statut</th>
                        </tr>
                    </thead>
                    <tbody id="commissionsTable">
                        @forelse($commissions ?? [] as $commission)
                            <tr class="commission-row clickable-row"
                                data-type="{{ $commission->type }}"
                                data-status="{{ $commission->status }}"
                                data-date="{{ $commission->created_at->format('Y-m-d') }}"
                                onclick="openCommissionDetails({{ $commission->id }})">
                                <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                    {{ $commission->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="hidden sm:table-cell">
                                    <span class="type-badge type-badge-{{ $commission->type }}">
                                        {{ ucfirst($commission->type) }}
                                    </span>
                                </td>
                                <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                    @if(in_array($commission->type, ['indirect', 'leadership', 'sponsor'], true) && $commission->fromUser)
                                        <span class="text-xs font-medium text-[var(--text-primary)]">{{ $commission->fromUser->name }}</span>
                                    @elseif($commission->package_id)
                                        <span class="item-badge item-badge-package">Package</span>
                                        <span class="text-xs">{{ $commission->package->name ?? 'N/A' }}</span>
                                    @elseif($commission->product_id)
                                        <span class="item-badge item-badge-product">Produit</span>
                                        <span class="text-xs">{{ $commission->product->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-[var(--text-tertiary)] text-xs">-</span>
                                    @endif
                                </td>
                                <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden lg:table-cell">
                                    @if(in_array($commission->type, ['indirect', 'leadership'], true) && $commission->fromUser)
                                        Source : {{ Str::limit($commission->fromUser->name, 28) }}
                                    @else
                                        {{ Str::limit($commission->description ?? '-', 30) }}
                                    @endif
                                </td>
                                <td class="text-right font-bold text-green-500 text-sm sm:text-base">
                                    +${{ number_format($commission->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                        {{ $commission->status == 'paid' ? 'En portefeuille' : 'En attente' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                    <p class="text-sm text-[var(--text-tertiary)]">Les commissions apparaîtront lorsque vous developperez votre reseau</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
            <div class="commissions-pagination commissions-pagination--desktop mt-3 sm:mt-4" id="paginationContainer">
                <x-salang-pagination :paginator="$commissions" />
            </div>
        @endif
    @endif

    @if($selectedType === '')
        @php
            $typeStatOrder = [
                'direct' => 'primary',
                'indirect' => 'warning',
                'leadership' => 'success',
                'cash_pos' => 'sky',
            ];
            $typeStatTotal = 0.0;
            $typeStatCount = 0;
            foreach ($typeStatOrder as $typeKey => $_color) {
                $typeStatTotal += (float) ($bonusByType[$typeKey]['total'] ?? 0);
                $typeStatCount += (int) ($bonusByType[$typeKey]['count'] ?? 0);
            }
        @endphp
        <div class="commissions-type-stats grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-7">
            @foreach($typeStatOrder as $typeKey => $color)
                @php
                    $row = $bonusByType[$typeKey] ?? [
                        'label' => $typeKey === 'cash_pos' ? 'Profit POS' : ucfirst((string) $typeKey),
                        'total' => 0,
                        'count' => 0,
                    ];
                    $rowCount = (int) ($row['count'] ?? 0);
                    $rowMeta = $rowCount.' commission(s)';
                    if ($typeKey === 'cash_pos') {
                        $rowMeta .= ' · cash inclus';
                    }
                @endphp
                <a
                    href="{{ route('commissions.index', array_filter(['type' => $typeKey, 'period' => ($bonusMonth ?? null) ?: null])) }}"
                    class="card-stats border-l-4 border-{{ $color }}-500 block no-underline hover:opacity-95"
                >
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $row['label'] }}</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-{{ $color }}-500">
                        ${{ number_format((float) ($row['total'] ?? 0), 2) }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $rowMeta }}</p>
                </a>
            @endforeach
            <div class="card-stats card-stats--total border-l-4 border-purple-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">
                    ${{ number_format($typeStatTotal, 2) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $typeStatCount }} commission(s)</p>
            </div>
        </div>
    @endif

</div>

<!-- ===== MODAL DETAILS ===== -->
<div id="commissionModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeCommissionModal()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div id="modalContent">
            <!-- Contenu injecte par JavaScript -->
        </div>
    </div>
</div>

@push('scripts')
<script>
// Donnees des types de commission avec leurs informations de calcul
const commissionTypes = {
    sponsor: {
        label: 'Sponsor Bonus',
        description: 'Commission de parrainage directe',
        formula: 'Prix du package ou produit × 30%',
        icon: 'S'
    },
    direct: {
        label: 'Bonus Direct',
        description: 'Bonus sur vos achats personnels : PV du mois × taux MLM de votre grade',
        formula: 'PV personnels du mois × taux MLM du grade',
        icon: 'D'
    },
    cash_pos: {
        label: 'Profit POS',
        description: 'Profit sur les ventes caisse (y compris payé cash)',
        formula: 'Montant POS × taux applicable',
        icon: 'P'
    },
    indirect: {
        label: 'Commission Indirecte',
        description: 'Commission sur les niveaux inferieurs',
        formula: 'PV total × (Taux du sponsor - Taux du filleul)',
        icon: 'I'
    },
    leadership: {
        label: 'Leadership Bonus',
        description: 'Bonus de leadership sur le reseau',
        formula: 'PV total × Taux de leadership',
        icon: 'L'
    },
    retail: {
        label: 'Retail Bonus',
        description: 'Bonus sur les ventes au detail',
        formula: 'Montant de la vente × 25%',
        icon: 'R'
    }
};

function openCommissionDetails(commissionId) {
    fetch('/commissions/' + commissionId + '/json')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Erreur lors du chargement');
            }
            return response.json();
        })
        .then(function(data) {
            displayCommissionDetails(data);
        })
        .catch(function(error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des details', 'error');
        });
}

function displayCommissionDetails(commission) {
    const modal = document.getElementById('commissionModal');
    const content = document.getElementById('modalContent');
    
    const typeInfo = commissionTypes[commission.type] || commissionTypes.direct;
    
    const statusClass = commission.status === 'paid' ? 'success' : 'warning';
    const statusLabel = commission.status === 'paid' ? 'En portefeuille' : 'En attente';
    
    // Récupérer les informations de l'item (package ou produit)
    const itemType = commission.item_type || 'unknown';
    const itemName = commission.item_name || commission.package_name || commission.product_name || 'N/A';
    const itemPV = commission.pv || 0;
    const itemPrice = commission.price || 0;
    const quantity = commission.quantity || 1;
    
    // Déterminer le badge de l'item
    let itemBadgeClass = '';
    let itemIcon = '';
    if (commission.package_id) {
        itemBadgeClass = 'item-badge-package';
        itemIcon = 'Package';
    } else if (commission.product_id) {
        itemBadgeClass = 'item-badge-product';
        itemIcon = 'Produit';
    }
    
    // Afficher le bon type d'item dans le modal
    let itemDisplay = '';
    if (commission.package_id || commission.product_id) {
        itemDisplay = `
            <div class="detail-item" style="grid-column: span 2;">
                <div class="label">Item</div>
                <div class="value">
                    <span class="item-badge ${itemBadgeClass}">${itemIcon}</span>
                    <span style="font-weight:600;">${itemName}</span>
                    ${quantity > 1 ? `× ${quantity}` : ''}
                </div>
            </div>
        `;
    }
    
    // Afficher le prix et le PV seulement s'ils sont > 0
    let priceDisplay = '';
    if (itemPrice > 0) {
        priceDisplay = `
            <div class="detail-item">
                <div class="label">Prix de l'item</div>
                <div class="value">${itemPrice}$</div>
            </div>
        `;
    }
    
    let pvDisplay = '';
    const displayPv = (commission.type === 'direct' && commission.pv_used > 0)
        ? commission.pv_used
        : itemPV;
    if (displayPv > 0) {
        const pvLabel = commission.type === 'direct' ? 'PV personnels du mois' : 'PV de l\'item';
        pvDisplay = `
            <div class="detail-item">
                <div class="label">${pvLabel}</div>
                <div class="value primary">${displayPv} PV</div>
            </div>
        `;
    }
    
    // Construire la formule de calcul avec les vraies valeurs
    let calculationHtml = '';
    if (commission.type === 'sponsor') {
        const priceValue = itemPrice > 0 ? itemPrice : 'Prix';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul du Sponsor Bonus</div>
                <div class="calc-formula">${priceValue}$ × 30%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPrice > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le prix de l'item</div>` : ''}
            </div>
        `;
    } else if (commission.type === 'direct') {
        const personalPv = (commission.pv_used > 0 ? commission.pv_used : itemPV) || 0;
        const pvValue = personalPv > 0 ? personalPv : 'PV perso';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul du Bonus Direct</div>
                <div class="calc-formula">${pvValue} PV personnels × ${pct}% (taux grade)</div>
                <div class="calc-result">= $${commission.amount}</div>
                <div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Sur vos achats personnels, selon le taux MLM de votre grade</div>
            </div>
        `;
    } else if (commission.type === 'indirect') {
        const pvValue = itemPV > 0 ? itemPV : 'PV';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul de la Commission Indirecte</div>
                <div class="calc-formula">${pvValue} PV × ${pct}%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPV > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le PV de l'item</div>` : ''}
            </div>
        `;
    } else if (commission.type === 'leadership') {
        const pvValue = itemPV > 0 ? itemPV : 'PV';
        const pct = commission.percentage || 'N/A';
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul du Leadership Bonus</div>
                <div class="calc-formula">${pvValue} PV × ${pct}%</div>
                <div class="calc-result">= $${commission.amount}</div>
                ${itemPV > 0 ? `<div style="font-size:0.65rem;color:var(--text-tertiary);margin-top:0.25rem;">Basé sur le PV de l'item</div>` : ''}
            </div>
        `;
    } else {
        calculationHtml = `
            <div class="calculation-box">
                <div class="calc-label">Calcul</div>
                <div class="calc-formula">${commission.formula || 'Montant : ' + commission.amount + '$'}</div>
                <div class="calc-result">= $${commission.amount}</div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <div class="flex items-center gap-2 mb-1">
            <span class="type-badge type-badge-${commission.type}" style="font-size:0.8rem;padding:0.25rem 0.75rem;">
                ${typeInfo.icon} ${typeInfo.label}
            </span>
            <span class="badge badge-${statusClass}" style="font-size:0.7rem;">
                ${statusLabel}
            </span>
        </div>
        <h2 class="modal-title">${typeInfo.description}</h2>
        <p class="modal-subtitle">Commission #${commission.id} • ${commission.created_at}</p>
        
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">Montant</div>
                    <div class="value success">+ $${commission.amount}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Pourcentage</div>
                    <div class="value primary">${commission.percentage || 'N/A'}%</div>
                </div>
                <div class="detail-item">
                    <div class="label">Source</div>
                    <div class="value">${commission.from_user || 'Systeme'}</div>
                </div>
                ${pvDisplay}
                ${itemDisplay}
                ${priceDisplay}
                <div class="detail-item">
                    <div class="label">Periode</div>
                    <div class="value">${commission.period || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Statut</div>
                    <div class="value ${statusClass}">${statusLabel}</div>
                </div>
            </div>
            
            ${calculationHtml}
            
            <div style="margin-top:0.5rem;padding:0.5rem 0.75rem;background:var(--bg-secondary);border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                <p style="font-size:0.65rem;color:var(--text-tertiary);">Description</p>
                <p style="font-size:0.8rem;color:var(--text-secondary);">${commission.description || 'Aucune description'}</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button onclick="closeCommissionModal()" class="btn btn-primary btn-sm">Fermer</button>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCommissionModal() {
    const modal = document.getElementById('commissionModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Fermer le modal en cliquant sur l'overlay
document.getElementById('commissionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCommissionModal();
    }
});

// Fermer avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCommissionModal();
    }
});

// ============================================================
// RECHERCHE EN TEMPS RÉEL - UNIQUEMENT LA RECHERCHE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var searchToggle = document.getElementById('commissionsSearchToggle');
    var searchPanel = document.getElementById('commissionsSearchPanel');

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function() {
            var open = searchPanel.classList.toggle('is-open');
            searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            searchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (open) {
                var input = document.getElementById('searchInput');
                if (input) input.focus();
            }
        });
    }

    var searchInput = document.getElementById('searchInput');
    var searchInputDesktop = document.getElementById('searchInputDesktop');
    var tableRows = document.querySelectorAll('#commissionsTable tr.commission-row');
    var mobileRows = document.querySelectorAll('.commissions-mobile-row');
    var resultCount = document.getElementById('resultCount');
    var resultCountMobile = document.getElementById('resultCountMobile');
    var totalBadge = document.getElementById('totalBadge');
    var filterLabel = document.getElementById('commissionsFilterLabel');
    var filterBtn = document.getElementById('commissionsFilterBtn');
    var totalRows = tableRows.length || mobileRows.length;
    var statusFilter = '';

    function getSearchValue() {
        if (searchInput && searchInput.offsetParent !== null) {
            return searchInput.value.trim().toLowerCase();
        }
        if (searchInputDesktop) {
            return searchInputDesktop.value.trim().toLowerCase();
        }
        return '';
    }

    function syncSearchInputs(value) {
        if (searchInput) searchInput.value = value;
        if (searchInputDesktop) searchInputDesktop.value = value;
    }

    function updateCounters(visibleCount, search) {
        var label = (search === '' && !statusFilter)
            ? '· ' + totalRows + ' commissions'
            : '· ' + visibleCount + ' résultat(s) sur ' + totalRows;

        if (resultCount) resultCount.textContent = label;
        if (resultCountMobile) resultCountMobile.textContent = label.replace(/^·\s*/, '');
        if (totalBadge) {
            totalBadge.textContent = (search === '' && !statusFilter)
                ? totalRows + ' commissions'
                : visibleCount + ' / ' + totalRows;
        }
    }

    function filterRows(sourceInput) {
        var search = sourceInput ? sourceInput.value.trim().toLowerCase() : getSearchValue();
        if (sourceInput) {
            syncSearchInputs(sourceInput.value);
        }
        var visibleCount = 0;

        var countRows = window.matchMedia('(max-width: 768px)').matches && mobileRows.length
            ? mobileRows
            : (tableRows.length ? tableRows : mobileRows);

        function applyRow(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = (row.dataset.status || '').toLowerCase();
            var show = (search === '' || text.includes(search))
                && (!statusFilter || rowStatus === statusFilter);
            row.style.display = show ? '' : 'none';
            return show;
        }

        tableRows.forEach(function(row) { applyRow(row); });

        mobileRows.forEach(function(row) {
            var show = applyRow(row);
            var item = row.closest('li');
            if (item) item.style.display = show ? '' : 'none';
        });

        countRows.forEach(function(row) {
            if (row.style.display !== 'none') visibleCount++;
        });

        updateCounters(visibleCount, search);
    }

    if (searchInput) searchInput.addEventListener('input', function() { filterRows(searchInput); });
    if (searchInputDesktop) searchInputDesktop.addEventListener('input', function() { filterRows(searchInputDesktop); });

    document.querySelectorAll('[data-commission-filter]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            statusFilter = btn.getAttribute('data-commission-filter') || '';
            document.querySelectorAll('[data-commission-filter]').forEach(function (el) {
                el.classList.toggle('is-active', el === btn);
            });
            if (filterLabel) {
                filterLabel.textContent = statusFilter
                    ? (btn.textContent || '').trim()
                    : 'Filtrer';
            }
            if (filterBtn) {
                filterBtn.classList.toggle('border-[var(--primary)]', !!statusFilter);
                filterBtn.classList.toggle('text-[var(--primary)]', !!statusFilter);
            }
            filterRows();
        });
    });
});

function showToast(message, type) {
    type = type || 'success';
    document.querySelectorAll('.custom-toast').forEach(function(el) { el.remove(); });
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50';
    toast.style.animation = 'slideUp 0.3s ease forwards';
    toast.style.fontSize = '0.875rem';
    
    if (type === 'success') {
        toast.style.background = '#22c55e';
    } else if (type === 'error') {
        toast.style.background = '#ef4444';
    } else if (type === 'warning') {
        toast.style.background = '#f59e0b';
    } else {
        toast.style.background = '#5ab638';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}
</script>
@endpush
@endsection