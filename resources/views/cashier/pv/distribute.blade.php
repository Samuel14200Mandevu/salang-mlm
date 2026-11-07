@extends('cashier.layouts.app')

@push('styles')
<style>
    .member-select {
        max-height: 300px;
        overflow-y: auto;
    }

    .pv-code-search {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    .pv-code-search input {
        flex: 1;
        min-width: 0;
        padding: 0.45rem 0.65rem;
        font-size: 0.8125rem;
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 6px;
        background: var(--bg-card, #FCFCFD);
        color: var(--text-primary, #1A1A1E);
        outline: none;
    }
    .pv-code-search input:focus {
        border-color: #184f94;
    }
    .pv-code-search-msg {
        font-size: 0.75rem;
        margin: 0 0 0.75rem;
        min-height: 1.1rem;
    }
    .pv-code-search-msg.is-error { color: #B32A2A; }
    .pv-code-search-msg.is-ok { color: #1F7B4D; }
    .pv-code-search-hint {
        font-size: 0.7rem;
        color: var(--text-tertiary, #7A7A82);
        margin: -0.35rem 0 0.75rem;
    }

    .member-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: background 0.15s ease;
        cursor: pointer;
    }
    .member-item:hover {
        background: var(--bg-hover, #E8EAEE);
    }
    .member-item.selected {
        background: rgba(31, 123, 77, 0.08);
        border: 1px solid rgba(31, 123, 77, 0.20);
    }

    .member-item .avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        background: #184f94;
        color: #fff;
        flex-shrink: 0;
    }

    .member-item .info {
        flex: 1;
    }
    .member-item .info .name {
        font-weight: 600;
        color: var(--text-primary, #1A1A1E);
        font-size: 0.875rem;
    }
    .member-item .info .detail {
        font-size: 0.7rem;
        color: var(--text-secondary, #4A4A52);
    }

    .member-item .pv-info {
        font-size: 0.7rem;
        font-weight: 600;
        color: #2563EB;
        background: rgba(37, 99, 235, 0.08);
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
    }

    .dist-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-secondary, #EEF0F3);
        border-radius: 6px;
        border: 1px solid var(--border-color, #DCDEE3);
        margin-bottom: 0.5rem;
    }

    .dist-item .remove-btn {
        color: #B32A2A;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 50%;
        transition: background 0.15s ease;
        background: none;
        border: none;
    }
    .dist-item .remove-btn:hover {
        background: rgba(179, 42, 42, 0.10);
    }

    .input-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 4px;
        background: var(--bg-card, #FCFCFD);
        color: var(--text-primary, #1A1A1E);
        outline: none;
    }
    .input-sm:focus {
        border-color: #184f94;
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

    .btn-success {
        background: #1F7B4D;
        color: #fff;
    }
    .btn-success:hover {
        background: #16633D;
    }
    .btn-success:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

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

    .card {
        background: var(--bg-card, #FCFCFD);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
        padding: 1.25rem;
    }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .dist-item { flex-wrap: wrap; }
        .dist-item .flex-1 { width: 100%; }
        .member-item { padding: 0.375rem 0.5rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', 'Distribuer des PV')

@section('content')
<div class="space-y-4 sm:space-y-6">

    @php
        $pvQuery = !empty($viewingMember) ? ['member_id' => $subject->id] : [];
    @endphp

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Distribuer des PV</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                @if(!empty($viewingMember))
                    Portefeuille de {{ $subject->name }} — saisie caisse après proposition verbale
                @else
                    Répartissez les Points de Volume dans le réseau
                @endif
            </p>
        </div>
        <a href="{{ route('cashier.pv.dashboard', $pvQuery) }}" class="btn btn-outline btn-sm">Retour</a>
    </div>

    <p class="text-xs text-[var(--text-secondary)]">
        Période {{ $mlmPeriodValue ?? '' }} ({{ $mlmPeriodDates['start']->format('d/m') }} → {{ $mlmPeriodDates['end']->format('d/m/Y') }}).
        @if(!empty($isMlmClosureDay))
            <span class="font-semibold text-amber-800">Jour 7 : privilégiez la distribution aujourd’hui.</span>
        @endif
    </p>

    {{-- SOLDE --}}
    <div class="card flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-sm text-[var(--text-secondary)]">PV disponibles à distribuer</p>
            <p class="text-2xl font-bold text-[var(--ui-stat-primary)]">{{ number_format($memberPv['available_pv'] ?? 0) }} PV</p>
            @if(($memberPv['available_bv'] ?? 0) > 0)
                <p class="text-xs text-[var(--text-tertiary)]">BV: {{ number_format($memberPv['available_bv']) }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-sm text-[var(--text-secondary)]">PV Personnel (grade)</p>
            <p class="text-xl font-bold text-[var(--text-primary)]">{{ number_format($memberPv['cumulative_pv'] ?? 0) }} PV</p>
            <p class="text-xs text-[var(--text-tertiary)]">Portefeuille: {{ number_format($memberPv['wallet_total_pv'] ?? 0) }} PV</p>
        </div>
    </div>

    {{-- FORMULAIRE --}}
    <form action="{{ route('cashier.pv.distribute.post') }}" method="POST">
        @csrf
        @if(!empty($viewingMember))
            <input type="hidden" name="member_id" value="{{ $subject->id }}">
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Sélection --}}
            <div class="lg:col-span-1 card">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-2">
                    Ajouter un membre du réseau
                </h3>
                <p class="pv-code-search-hint">
                    Recherchez par code membre pour distribuer à n’importe quel niveau du réseau.
                </p>

                <div class="pv-code-search">
                    <input type="text"
                           id="networkMemberCode"
                           inputmode="numeric"
                           autocomplete="off"
                           placeholder="Saisir le code membre…"
                           aria-label="Code membre">
                </div>
                <p id="networkSearchMsg" class="pv-code-search-msg" role="status" aria-live="polite"></p>

                <h4 class="text-xs font-semibold text-[var(--text-secondary)] uppercase tracking-wide mb-2">
                    Aperçu niveaux 1–2
                    <span class="font-normal normal-case">({{ count($networkMembers) }})</span>
                </h4>

                <div class="member-select space-y-1">
                    @if(empty($networkMembers))
                        <p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Aucun filleul proche — utilisez la recherche par code</p>
                    @else
                        @foreach($networkMembers as $member)
                            <div class="member-item"
                                 data-id="{{ $member['id'] }}"
                                 data-name="{{ $member['name'] }}"
                                 data-level="{{ $member['level'] }}"
                                 data-code="{{ $member['sponsor_id'] ?? '' }}"
                                 onclick="addMemberFromElement(this)">
                                <div class="avatar">{{ substr($member['name'], 0, 1) }}</div>
                                <div class="info">
                                    <div class="name">{{ $member['name'] }}</div>
                                    <div class="detail">Niveau {{ $member['level'] }} • {{ $member['sponsor_id'] ?? 'N/A' }}</div>
                                </div>
                                <div class="pv-info">{{ number_format($member['pv_balance']) }} PV</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Liste de distribution --}}
            <div class="lg:col-span-2 card">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                    Distribution
                    <span class="text-xs text-[var(--text-secondary)] font-normal">(définissez les montants)</span>
                </h3>

                <div id="distributionList" class="distribution-list">
                    <p class="text-[var(--text-tertiary)] text-sm py-4 text-center" id="emptyMessage">
                        Sélectionnez un membre pour commencer
                    </p>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                    <button type="submit" class="btn btn-success btn-sm" id="submitBtn" disabled>
                        Valider la distribution
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let distributions = [];
const maxAvailablePv = {{ $memberPv['available_pv'] ?? 0 }};
const searchMemberUrl = @json(route('cashier.pv.search-member'));
const subjectMemberId = @json(!empty($viewingMember) ? $subject->id : null);

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function setSearchMessage(text, type) {
    const el = document.getElementById('networkSearchMsg');
    if (!el) return;
    el.textContent = text || '';
    el.classList.remove('is-error', 'is-ok');
    if (type) el.classList.add(type === 'error' ? 'is-error' : 'is-ok');
}

function addMemberFromData(member) {
    const id = String(member.id);
    if (distributions.some(d => String(d.id) === id)) {
        setSearchMessage('Ce membre est déjà dans la liste de distribution.', 'error');
        return false;
    }

    const code = member.sponsor_id || member.code || '';
    const levelLabel = `Niveau ${member.level}${code ? ' • ' + code : ''}`;

    distributions.push({
        id,
        name: member.name,
        level: levelLabel,
        pv_amount: 0,
        bv_amount: 0,
        notes: '',
    });

    updateDistributionList();
    updateSubmitButton();
    return true;
}

function addMemberFromElement(element) {
    addMemberFromData({
        id: element.dataset.id,
        name: element.dataset.name,
        level: element.dataset.level,
        sponsor_id: element.dataset.code,
    });
    element.classList.add('selected');
}

let searchTimer = null;
let searchSeq = 0;
let lastAddedCode = '';

async function searchNetworkMember(opts = {}) {
    const autoAdd = opts.autoAdd !== false;
    const input = document.getElementById('networkMemberCode');
    const code = (input?.value || '').trim();

    if (!code) {
        setSearchMessage('', null);
        return;
    }

    if (code.length < 3) {
        setSearchMessage('Continuez de saisir le code…', null);
        return;
    }

    const params = new URLSearchParams({ code });
    if (subjectMemberId) params.set('member_id', String(subjectMemberId));

    const seq = ++searchSeq;
    setSearchMessage('Recherche…', null);

    try {
        const response = await fetch(`${searchMemberUrl}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        if (seq !== searchSeq) return;

        if (!data.ok || !data.member) {
            setSearchMessage(data.message || 'Membre introuvable ou hors réseau.', 'error');
            return;
        }

        if (!autoAdd) {
            setSearchMessage(`${data.member.name} (niveau ${data.member.level})`, 'ok');
            return;
        }

        if (lastAddedCode === code && distributions.some(d => String(d.id) === String(data.member.id))) {
            setSearchMessage(`${data.member.name} déjà dans la liste.`, 'ok');
            return;
        }

        if (addMemberFromData(data.member)) {
            lastAddedCode = code;
            setSearchMessage(
                `${data.member.name} (niveau ${data.member.level}) ajouté.`,
                'ok'
            );
            input.value = '';
            lastAddedCode = '';
        }
    } catch (e) {
        if (seq !== searchSeq) return;
        setSearchMessage('Erreur de recherche. Réessayez.', 'error');
    }
}

function scheduleNetworkSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => searchNetworkMember({ autoAdd: true }), 350);
}

function removeMember(id) {
    distributions = distributions.filter(d => String(d.id) !== String(id));
    updateDistributionList();
    updateSubmitButton();
}

function updateDistributionList() {
    const container = document.getElementById('distributionList');

    if (distributions.length === 0) {
        container.innerHTML = `<p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Recherchez un membre par code pour commencer</p>`;
        return;
    }

    let html = '';
    distributions.forEach((dist, index) => {
        html += `
            <div class="dist-item">
                <div class="flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm">${escapeHtml(dist.name)}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${escapeHtml(dist.level)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" class="input-sm" style="width:100px;"
                           value="${dist.pv_amount || 0}" min="0" max="${maxAvailablePv}"
                           onchange="updatePv(${index}, this.value)" placeholder="PV">
                    <input type="text" class="input-sm" style="width:120px;"
                           value="${escapeHtml(dist.notes || '')}" onchange="updateNotes(${index}, this.value)" placeholder="Notes">
                    <button type="button" class="remove-btn" onclick="removeMember(${dist.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <input type="hidden" name="distributions[${index}][user_id]" value="${dist.id}">
            <input type="hidden" name="distributions[${index}][pv_amount]" value="${dist.pv_amount}">
            <input type="hidden" name="distributions[${index}][notes]" value="${escapeHtml(dist.notes || '')}">
        `;
    });

    container.innerHTML = html;
}

function updatePv(index, value) {
    const pv = parseInt(value) || 0;
    distributions[index].pv_amount = pv;

    const totalPv = distributions.reduce((sum, d) => sum + d.pv_amount, 0);
    if (totalPv > maxAvailablePv) {
        alert(`Total (${totalPv} PV) dépasse les PV disponibles (${maxAvailablePv})`);
        distributions[index].pv_amount = 0;
        updateDistributionList();
    }
    updateSubmitButton();
}

function updateNotes(index, value) {
    distributions[index].notes = value;
    updateDistributionList();
}

function updateSubmitButton() {
    const btn = document.getElementById('submitBtn');
    const totalPv = distributions.reduce((sum, d) => sum + d.pv_amount, 0);

    if (distributions.length > 0 && totalPv > 0 && totalPv <= maxAvailablePv) {
        btn.disabled = false;
        btn.textContent = `Valider (${totalPv} PV)`;
    } else {
        btn.disabled = true;
        btn.textContent = 'Valider la distribution';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('networkMemberCode');
    codeInput?.addEventListener('input', scheduleNetworkSearch);
    codeInput?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimer);
            searchNetworkMember({ autoAdd: true });
        }
    });

    const params = new URLSearchParams(window.location.search);
    const recipientId = params.get('recipient_id');
    const recipientCode = params.get('recipient_code');

    if (recipientId) {
        const el = document.querySelector(`.member-item[data-id="${recipientId}"]`);
        if (el) {
            addMemberFromElement(el);
            return;
        }
    }

    if (recipientCode) {
        if (codeInput) codeInput.value = recipientCode;
        searchNetworkMember({ autoAdd: true });
    }
});
</script>
@endpush
@endsection