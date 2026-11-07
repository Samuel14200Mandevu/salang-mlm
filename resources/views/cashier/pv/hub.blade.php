@extends('cashier.layouts.app')

@section('title', 'Gestion des PV')

@push('styles')
<style>
    .pv-hub-card {
        background: var(--bg-card, #FCFCFD);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
        padding: 1.5rem;
        max-width: 32rem;
    }
    .pv-hub-card input {
        width: 100%;
        padding: 0.55rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 6px;
        background: var(--bg-card, #FCFCFD);
        outline: none;
    }
    .pv-hub-card input:focus { border-color: #184f94; }
    .pv-hub-msg { font-size: 0.75rem; min-height: 1.1rem; margin-top: 0.5rem; }
    .pv-hub-msg.is-error { color: #B32A2A; }
    .pv-hub-msg.is-ok { color: #1F7B4D; }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Gestion des PV</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            Recherchez un membre par code pour gérer son portefeuille et ses distributions
        </p>
    </div>

    <div class="pv-hub-card">
        <label for="pvHubMemberCode" class="block text-sm font-semibold text-[var(--text-primary)] mb-2">
            Code membre
        </label>
        <input type="text"
               id="pvHubMemberCode"
               inputmode="numeric"
               autocomplete="off"
               placeholder="Saisir le code membre…">
        <p id="pvHubMsg" class="pv-hub-msg" role="status" aria-live="polite"></p>
        <p class="text-xs text-[var(--text-tertiary)] mt-3">
            Ou ouvrez un membre depuis
            <a href="{{ route('cashier.members') }}" class="text-[var(--primary)] hover:underline">Membres</a>
            puis « Consulter les PV ».
        </p>
    </div>
</div>

@push('scripts')
<script>
const findMemberUrl = @json(route('cashier.pv.find-member'));
let hubTimer = null;
let hubSeq = 0;

function setHubMsg(text, type) {
    const el = document.getElementById('pvHubMsg');
    if (!el) return;
    el.textContent = text || '';
    el.classList.remove('is-error', 'is-ok');
    if (type) el.classList.add(type === 'error' ? 'is-error' : 'is-ok');
}

async function searchHubMember() {
    const input = document.getElementById('pvHubMemberCode');
    const code = (input?.value || '').trim();
    if (!code) { setHubMsg('', null); return; }
    if (code.length < 3) { setHubMsg('Continuez de saisir le code…', null); return; }

    const seq = ++hubSeq;
    setHubMsg('Recherche…', null);

    try {
        const response = await fetch(`${findMemberUrl}?code=${encodeURIComponent(code)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        if (seq !== hubSeq) return;

        if (!data.ok || !data.member) {
            setHubMsg(data.message || 'Membre introuvable.', 'error');
            return;
        }

        setHubMsg(`${data.member.name} trouvé — ouverture…`, 'ok');
        window.location.href = data.url;
    } catch (e) {
        if (seq === hubSeq) setHubMsg('Erreur de recherche.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('pvHubMemberCode');
    input?.focus();
    input?.addEventListener('input', () => {
        clearTimeout(hubTimer);
        hubTimer = setTimeout(searchHubMember, 350);
    });
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(hubTimer);
            searchHubMember();
        }
    });
});
</script>
@endpush
@endsection
