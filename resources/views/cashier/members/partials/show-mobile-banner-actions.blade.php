{{-- Actions compactes dans le bandeau titre (mobile) — même pattern que admin --}}
<div class="admin-title-banner__tools admin-title-banner__tools--member-show">
    <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm btn-round-icon" title="Retour" aria-label="Retour">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <a href="{{ route('cashier.pv.dashboard', ['member_id' => $member->id]) }}" class="btn btn-success btn-sm btn-round-icon" title="Consulter les PV" aria-label="Consulter les PV">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
    </a>
    <a href="{{ route('cashier.members.orders', $member->id) }}" class="btn btn-primary btn-sm btn-round-icon" title="Commandes" aria-label="Commandes">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
        </svg>
    </a>
    <button type="button" id="btnPrintAdhesionMobile" class="btn btn-success btn-sm btn-round-icon" title="Adhésion PDF" aria-label="Adhésion PDF">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 21h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9V3H6v6z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12v4h8v-4"/>
        </svg>
    </button>
</div>
