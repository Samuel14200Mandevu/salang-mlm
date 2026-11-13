@if($selectedPeriod)
<a href="{{ url('/admin/pv/commission-history/' . $selectedPeriod . '/pdf?user_id=' . $userId) }}" class="btn btn-outline btn-sm">
    <x-pdf-icon />
    Rapport Détaillé
</a>
<a href="{{ route('admin.pv.commission-history.global-pdf', $selectedPeriod) }}" class="btn btn-outline btn-sm" target="_blank">
    <x-pdf-icon />
    Rapport Global
</a>
@endif
<a href="{{ route('admin.pv.import.index') }}" class="btn btn-outline btn-sm">
    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour
</a>
