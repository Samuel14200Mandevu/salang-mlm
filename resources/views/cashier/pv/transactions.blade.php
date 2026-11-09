@extends('cashier.layouts.app')

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }

    .pv-hist-search {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        min-width: min(100%, 16rem);
        max-width: 18rem;
        flex: 1 1 16rem;
    }
    .pv-hist-search input {
        width: 100%;
        padding: 0.45rem 0.75rem;
        font-size: 0.8125rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        color: var(--text-primary);
        outline: none;
    }
    .pv-hist-search input:focus { border-color: var(--primary); }
    .pv-hist-search-msg {
        font-size: 0.7rem;
        min-height: 1rem;
        margin-top: 0.35rem;
    }
    .pv-hist-search-msg.is-error { color: var(--danger, #b32a2a); }
    .pv-hist-search-msg.is-ok { color: #16a34a; }

    /* Liste — même panel que Historique ventes POS / Dépenses */
    .pv-history-panel {
        display: flex;
        flex-direction: column;
        min-height: 14rem;
        max-height: min(42vh, 22rem);
    }
    .pv-history-panel .table-wrap {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
    }
    .pv-history-panel .table-wrap tbody td[colspan] {
        height: 8rem;
        vertical-align: middle;
    }
</style>
@endpush

@section('title', 'Historique PV')

@section('content')
@php
    $pvQuery = !empty($viewingMember) && $subject ? ['member_id' => $subject->id] : [];
    $isGlobalHistory = empty($viewingMember);
    $colCount = $isGlobalHistory ? 6 : 5;
    $historySubtitle = ($transactions->total() ?? 0).' transaction(s)'
        .(!empty($viewingMember) && $subject
            ? ' · '.$subject->name.' ('.($subject->sponsor_id ?? 'N/A').')'
            : ' · Tous les membres');
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Historique des transactions</h1>
                <p class="page-subtitle">{!! $historySubtitle !!}</p>
            </div>
            <div class="admin-title-banner__tools">
                @if(!empty($viewingMember) && $subject)
                    <a href="{{ route('cashier.pv.dashboard', $pvQuery) }}"
                       class="btn btn-outline btn-sm btn-round-icon"
                       title="Gestion PV"
                       aria-label="Gestion PV">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
        <div class="admin-search-panel is-open" aria-hidden="false">
            <div class="pv-hist-search w-full max-w-none">
                <input type="text"
                       id="pvHistMemberCodeMobile"
                       class="search-input"
                       inputmode="numeric"
                       autocomplete="off"
                       placeholder="Code membre…"
                       aria-label="Rechercher un membre par code"
                       value="{{ !empty($viewingMember) && $subject ? ($subject->sponsor_id ?? '') : '' }}">
                <p id="pvHistSearchMsgMobile" class="pv-hist-search-msg" role="status" aria-live="polite"></p>
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Historique des transactions
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {!! $historySubtitle !!}
            </p>
        </div>
        <div class="flex flex-wrap items-start gap-2 justify-end">
            <div class="pv-hist-search">
                <input type="text"
                       id="pvHistMemberCode"
                       inputmode="numeric"
                       autocomplete="off"
                       placeholder="Code membre…"
                       aria-label="Rechercher un membre par code"
                       value="{{ !empty($viewingMember) && $subject ? ($subject->sponsor_id ?? '') : '' }}">
                <p id="pvHistSearchMsg" class="pv-hist-search-msg" role="status" aria-live="polite"></p>
            </div>
            @if(!empty($viewingMember) && $subject)
                <a href="{{ route('cashier.members.show', $subject->id) }}" class="btn btn-outline btn-sm">Fiche membre</a>
                <a href="{{ route('cashier.pv.dashboard', $pvQuery) }}" class="btn btn-outline btn-sm">Gestion PV</a>
                <a href="{{ route('cashier.pv.transactions') }}" class="btn btn-outline btn-sm">Tout l’historique</a>
            @endif
        </div>
    </div>

    {{-- LISTE (div comme Historique ventes POS / Dépenses) --}}
    <div class="card p-3 sm:p-4 pv-history-panel">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 shrink-0">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des transactions</h3>
            <span class="text-xs text-[var(--text-tertiary)]">Cliquez une ligne pour le détail</span>
        </div>

        <div class="table-wrap">
            <table class="table" id="pvTransactionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        @if($isGlobalHistory)
                            <th>Membre</th>
                        @endif
                        <th>Type</th>
                        <th>PV</th>
                        <th class="hidden sm:table-cell">Source</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($transactions as $transaction)
                        @php
                            $detailUrl = route('cashier.pv.transactions.show', array_merge(
                                ['transaction' => $transaction->id],
                                $pvQuery
                            ));
                            $typeLabels = [
                                'credit' => ['label' => 'Crédit', 'class' => 'badge-success'],
                                'debit' => ['label' => 'Débit', 'class' => 'badge-danger'],
                                'allocation' => ['label' => 'Allocation', 'class' => 'badge-info'],
                                'bonus' => ['label' => 'Bonus', 'class' => 'badge-warning'],
                            ];
                            $type = $typeLabels[$transaction->type] ?? ['label' => ucfirst((string) $transaction->type), 'class' => 'badge-info'];
                            $sourceLabel = match ($transaction->source) {
                                'migration' => 'Import initial',
                                'distribution' => 'Distribution',
                                'sale' => 'Vente',
                                default => ucfirst(str_replace('_', ' ', (string) $transaction->source)),
                            };
                            $isCredit = in_array($transaction->type, ['credit', 'bonus'], true);
                        @endphp
                        <tr class="cursor-pointer"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="text-xs text-[var(--text-tertiary)] whitespace-nowrap">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            @if($isGlobalHistory)
                                <td class="text-sm font-semibold text-[var(--text-primary)]">
                                    @if(!empty($transaction->user?->sponsor_id)){{ $transaction->user->sponsor_id }} : @endif{{ $transaction->user?->name ?? 'N/A' }}
                                </td>
                            @endif
                            <td>
                                <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
                            </td>
                            <td class="font-semibold {{ $isCredit ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format((float) $transaction->pv_amount) }} PV
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $sourceLabel }}
                            </td>
                            <td class="text-xs text-[var(--text-secondary)] max-w-xs truncate" title="{{ $transaction->description }}">
                                {{ $transaction->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colCount }}" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune transaction</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">L'historique des mouvements PV apparaîtra ici</p>
                                @if(!empty($viewingMember) && $subject)
                                    <a href="{{ route('cashier.pv.dashboard', $pvQuery) }}" class="btn btn-outline btn-sm mt-3">Retour Gestion PV</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-3 shrink-0">
            @if($transactions->hasPages())
                <x-salang-pagination :paginator="$transactions" />
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
const findMemberUrl = @json(route('cashier.pv.find-member'));
let histTimer = null;
let histSeq = 0;

function setHistMsg(text, type) {
    ['pvHistSearchMsg', 'pvHistSearchMsgMobile'].forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = text || '';
        el.classList.remove('is-error', 'is-ok');
        if (type) el.classList.add(type === 'error' ? 'is-error' : 'is-ok');
    });
}

function syncHistInputs(source) {
    const value = source?.value || '';
    ['pvHistMemberCode', 'pvHistMemberCodeMobile'].forEach((id) => {
        const el = document.getElementById(id);
        if (el && el !== source) el.value = value;
    });
}

async function searchHistMember(source) {
    const code = (source?.value || '').trim();
    if (!code) { setHistMsg('', null); return; }
    if (code.length < 3) { setHistMsg('Continuez de saisir le code…', null); return; }

    const seq = ++histSeq;
    setHistMsg('Recherche…', null);

    try {
        const response = await fetch(`${findMemberUrl}?code=${encodeURIComponent(code)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        if (seq !== histSeq) return;

        if (!data.ok || !data.member) {
            setHistMsg(data.message || 'Membre introuvable.', 'error');
            return;
        }

        setHistMsg(`${data.member.name} — ouverture…`, 'ok');
        window.location.href = @json(route('cashier.pv.dashboard')) + '?member_id=' + encodeURIComponent(data.member.id);
    } catch (e) {
        if (seq === histSeq) setHistMsg('Erreur de recherche.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    ['pvHistMemberCode', 'pvHistMemberCodeMobile'].forEach((id) => {
        const input = document.getElementById(id);
        input?.addEventListener('input', () => {
            syncHistInputs(input);
            clearTimeout(histTimer);
            histTimer = setTimeout(() => searchHistMember(input), 350);
        });
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(histTimer);
                searchHistMember(input);
            }
        });
    });
});
</script>
@endpush
@endsection
