@extends('cashier.layouts.app')

@push('styles')
<style>
    .member-select {
        max-height: 300px;
        overflow-y: auto;
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
        background: #0F2B4F;
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
        border-color: #0F2B4F;
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
        border-color: #0F2B4F;
        color: #0F2B4F;
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

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Distribuer des PV</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Répartissez vos Points de Volume dans votre réseau</p>
        </div>
        <a href="{{ route('cashier.pv.dashboard') }}" class="btn btn-outline btn-sm">Retour</a>
    </div>

    {{-- SOLDE --}}
    <div class="card flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-sm text-[var(--text-secondary)]">PV disponibles à distribuer</p>
            <p class="text-2xl font-bold text-[#0F2B4F]">{{ number_format($balance?->available_pv ?? 0) }} PV</p>
            @if($balance?->available_bv > 0)
                <p class="text-xs text-[var(--text-tertiary)]">BV: {{ number_format($balance?->available_bv) }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-sm text-[var(--text-secondary)]">PV total</p>
            <p class="text-xl font-bold text-[var(--text-primary)]">{{ number_format($balance?->total_pv ?? 0) }} PV</p>
        </div>
    </div>

    {{-- FORMULAIRE --}}
    <form action="{{ route('cashier.pv.distribute.post') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Sélection --}}
            <div class="lg:col-span-1 card">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                    Sélectionner un membre
                    <span class="text-xs text-[var(--text-secondary)] font-normal">(cliquez pour ajouter)</span>
                </h3>

                <div class="member-select space-y-1">
                    @if(empty($networkMembers))
                        <p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Aucun membre dans votre réseau</p>
                    @else
                        @foreach($networkMembers as $member)
                            <div class="member-item" data-id="{{ $member['id'] }}" onclick="addMember(this)">
                                <div class="avatar">{{ substr($member['name'], 0, 1) }}</div>
                                <div class="info">
                                    <div class="name">{{ $member['name'] }}</div>
                                    <div class="detail">Niveau {{ $member['level'] }} • {{ $member['phone'] ?? 'N/A' }}</div>
                                </div>
                                <div class="pv-info">{{ $member['pv_balance'] }} PV</div>
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
const maxAvailablePv = {{ $balance?->available_pv ?? 0 }};

function addMember(element) {
    const id = element.dataset.id;
    const name = element.querySelector('.info .name').textContent;
    const level = element.querySelector('.info .detail').textContent.trim();

    if (distributions.some(d => d.id == id)) {
        alert('Ce membre est déjà dans la liste');
        return;
    }

    distributions.push({ id, name, level, pv_amount: 0, bv_amount: 0, notes: '' });
    updateDistributionList();
    updateSubmitButton();
}

function removeMember(id) {
    distributions = distributions.filter(d => d.id != id);
    updateDistributionList();
    updateSubmitButton();
}

function updateDistributionList() {
    const container = document.getElementById('distributionList');

    if (distributions.length === 0) {
        container.innerHTML = `<p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Sélectionnez un membre pour commencer</p>`;
        return;
    }

    let html = '';
    distributions.forEach((dist, index) => {
        html += `
            <div class="dist-item">
                <div class="flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm">${dist.name}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${dist.level}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" class="input-sm" style="width:100px;"
                           value="${dist.pv_amount || 0}" min="0" max="${maxAvailablePv}"
                           onchange="updatePv(${index}, this.value)" placeholder="PV">
                    <input type="text" class="input-sm" style="width:120px;"
                           value="${dist.notes || ''}" onchange="updateNotes(${index}, this.value)" placeholder="Notes">
                    <button type="button" class="remove-btn" onclick="removeMember(${dist.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <input type="hidden" name="distributions[${index}][user_id]" value="${dist.id}">
            <input type="hidden" name="distributions[${index}][pv_amount]" value="${dist.pv_amount}">
            <input type="hidden" name="distributions[${index}][notes]" value="${dist.notes || ''}">
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
    const memberId = new URLSearchParams(window.location.search).get('member_id');
    if (memberId) {
        const el = document.querySelector(`.member-item[data-id="${memberId}"]`);
        if (el) { addMember(el); el.classList.add('selected'); }
    }
});
</script>
@endpush
@endsection