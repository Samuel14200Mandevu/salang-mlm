<button type="button" class="btn btn-warning btn-sm" onclick="openRecalculateModal()">
    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    Recalculer
</button>
@if($selectedPeriod)
<a href="{{ url('/admin/pv/commission-history/' . $selectedPeriod . '/pdf?user_id=' . $userId) }}" class="btn btn-primary btn-sm">
    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Rapport Détaillé
</a>
<a href="{{ route('admin.pv.commission-history.global-pdf', $selectedPeriod) }}" class="btn btn-danger btn-sm" target="_blank">
    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Rapport Global
</a>
@endif
<a href="{{ route('admin.pv.import.index') }}" class="btn btn-outline btn-sm">
    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour
</a>
