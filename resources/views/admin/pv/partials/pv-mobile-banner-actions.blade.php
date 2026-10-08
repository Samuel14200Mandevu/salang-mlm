<div class="admin-title-banner__tools admin-title-banner__tools--pv-show">
    <a href="{{ url('/admin/pv/commission-history?user_id=' . $user->id) }}" class="btn btn-success btn-sm btn-round-icon" title="Commissions historiques" aria-label="Commissions historiques">
        <svg class="icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </a>
    <a href="{{ url('/admin/pv/import?user_id=' . $user->id) }}" class="btn btn-success btn-sm btn-round-icon" title="Importer des PV" aria-label="Importer des PV">
        <svg class="icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
    </a>
    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm btn-round-icon" title="Retour fiche membre" aria-label="Retour fiche membre">
        <svg class="icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
</div>
