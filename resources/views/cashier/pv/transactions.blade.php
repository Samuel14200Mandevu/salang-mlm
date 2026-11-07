@extends('cashier.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card, #FCFCFD);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
        padding: 1.25rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.813rem;
        transition: background 0.15s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary, #1A1A1E);
        border: 1.5px solid var(--border-color, #DCDEE3);
    }
    .btn-outline:hover {
        background: var(--bg-hover, #E8EAEE);
        border-color: #184f94;
        color: #184f94;
    }

    .table-wrap { overflow-x: auto; }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }
    .table thead th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary, #7A7A82);
        border-bottom: 1px solid var(--border-color, #DCDEE3);
        white-space: nowrap;
    }
    .table tbody td {
        padding: 0.625rem 0.75rem;
        border-bottom: 1px solid var(--border-light, #E8EAEE);
        color: var(--text-primary, #1A1A1E);
        vertical-align: middle;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr.tx-row {
        cursor: pointer;
        transition: background 0.12s ease;
    }
    .table tbody tr.tx-row:hover {
        background: var(--bg-hover, #E8EAEE);
    }

    .badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(31, 123, 77, 0.12); color: #1F7B4D; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #B32A2A; }
    .badge-info { background: rgba(37, 99, 235, 0.12); color: #2563EB; }
    .badge-warning { background: rgba(166, 90, 14, 0.12); color: #A65A0E; }

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
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 6px;
        background: var(--bg-card, #FCFCFD);
        color: var(--text-primary, #1A1A1E);
        outline: none;
    }
    .pv-hist-search input:focus { border-color: #184f94; }
    .pv-hist-search-msg {
        font-size: 0.7rem;
        min-height: 1rem;
        margin-top: 0.35rem;
    }
    .pv-hist-search-msg.is-error { color: #B32A2A; }
    .pv-hist-search-msg.is-ok { color: #1F7B4D; }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .pv-hist-search { max-width: none; }
    }
</style>
@endpush

@section('title', 'Historique PV')

@section('content')
<div class="space-y-4 sm:space-y-6">

    @php
        $pvQuery = !empty($viewingMember) && $subject ? ['member_id' => $subject->id] : [];
        $isGlobalHistory = empty($viewingMember);
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Historique des transactions</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                @if(!empty($viewingMember) && $subject)
                    Mouvements PV de {{ $subject->name }} (code {{ $subject->sponsor_id ?? 'N/A' }})
                @else
                    Tous les mouvements PV des membres
                @endif
                · Cliquez une ligne pour le détail
            </p>
        </div>
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
    </div>

    @if(!empty($viewingMember) && $subject)
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cashier.members.show', $subject->id) }}" class="btn btn-outline btn-sm">Fiche membre</a>
            <a href="{{ route('cashier.pv.dashboard', $pvQuery) }}" class="btn btn-outline btn-sm">Gestion PV</a>
            <a href="{{ route('cashier.pv.transactions') }}" class="btn btn-outline btn-sm">Tout l’historique</a>
        </div>
    @endif

    <div class="card">
        @if($transactions->isEmpty())
            <p class="text-[var(--text-tertiary)] text-sm py-8 text-center">Aucune transaction</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @if($isGlobalHistory)
                                <th>Membre</th>
                            @endif
                            <th>Type</th>
                            <th class="text-right">PV</th>
                            <th>Source</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $detailUrl = route('cashier.pv.transactions.show', array_merge(
                                    ['transaction' => $transaction->id],
                                    $pvQuery
                                ));
                            @endphp
                            <tr class="tx-row" onclick="window.location.href=@json($detailUrl)" tabindex="0"
                                onkeydown="if(event.key==='Enter'){window.location.href=@json($detailUrl)}">
                                <td class="text-xs text-[var(--text-tertiary)] whitespace-nowrap">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                @if($isGlobalHistory)
                                    <td class="text-sm">
                                        <span class="font-medium text-[var(--text-primary)]">{{ $transaction->user?->name ?? 'N/A' }}</span>
                                        <span class="block text-xs text-[var(--text-tertiary)]">{{ $transaction->user?->sponsor_id ?? '' }}</span>
                                    </td>
                                @endif
                                <td>
                                    @php
                                        $typeLabels = [
                                            'credit' => ['label' => 'Crédit', 'class' => 'badge-success'],
                                            'debit' => ['label' => 'Débit', 'class' => 'badge-danger'],
                                            'allocation' => ['label' => 'Allocation', 'class' => 'badge-info'],
                                            'bonus' => ['label' => 'Bonus', 'class' => 'badge-warning'],
                                        ];
                                        $type = $typeLabels[$transaction->type] ?? ['label' => ucfirst($transaction->type), 'class' => 'badge-info'];
                                    @endphp
                                    <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
                                </td>
                                <td class="text-right font-semibold {{ in_array($transaction->type, ['credit', 'bonus'], true) ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }}">
                                    {{ in_array($transaction->type, ['credit', 'bonus'], true) ? '+' : '-' }}
                                    {{ $transaction->pv_amount }} PV
                                    @if($transaction->bv_amount)
                                        <span class="text-xs text-[var(--text-tertiary)]">({{ $transaction->bv_amount }} BV)</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sourceLabel = match ($transaction->source) {
                                            'migration' => 'Import initial',
                                            'distribution' => 'Distribution',
                                            'sale' => 'Vente',
                                            default => ucfirst(str_replace('_', ' ', (string) $transaction->source)),
                                        };
                                    @endphp
                                    <span class="text-xs text-[var(--text-secondary)]">{{ $sourceLabel }}</span>
                                </td>
                                <td class="text-xs text-[var(--text-secondary)] max-w-xs truncate" title="{{ $transaction->description }}">
                                    {{ $transaction->description }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="mt-3">
                    <x-salang-pagination :paginator="$transactions" />
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
const findMemberUrl = @json(route('cashier.pv.find-member'));
let histTimer = null;
let histSeq = 0;

function setHistMsg(text, type) {
    const el = document.getElementById('pvHistSearchMsg');
    if (!el) return;
    el.textContent = text || '';
    el.classList.remove('is-error', 'is-ok');
    if (type) el.classList.add(type === 'error' ? 'is-error' : 'is-ok');
}

async function searchHistMember() {
    const input = document.getElementById('pvHistMemberCode');
    const code = (input?.value || '').trim();
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
    const input = document.getElementById('pvHistMemberCode');
    input?.addEventListener('input', () => {
        clearTimeout(histTimer);
        histTimer = setTimeout(searchHistMember, 350);
    });
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(histTimer);
            searchHistMember();
        }
    });
});
</script>
@endpush
@endsection
