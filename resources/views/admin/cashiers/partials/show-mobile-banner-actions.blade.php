<div class="admin-title-banner__tools admin-title-banner__tools--cashiers-show">
    <a href="{{ route('admin.cashiers.index') }}" class="btn btn-outline btn-sm btn-round-icon" title="Retour à la liste" aria-label="Retour à la liste">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <a href="{{ route('admin.users.edit', $cashier->id) }}" class="btn btn-primary btn-sm btn-round-icon" title="Modifier" aria-label="Modifier">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </a>
    <button type="button"
            class="btn {{ $cashier->is_active ? 'btn-warning' : 'btn-success' }} btn-sm btn-round-icon"
            title="{{ $cashier->is_active ? 'Désactiver' : 'Activer' }}"
            aria-label="{{ $cashier->is_active ? 'Désactiver' : 'Activer' }}"
            onclick="document.getElementById('cashierToggleForm')?.requestSubmit()">
        @if($cashier->is_active)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
            </svg>
        @else
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        @endif
    </button>
    @if(auth()->id() != $cashier->id)
        <button type="button"
                class="btn btn-danger btn-sm btn-round-icon"
                title="Supprimer"
                aria-label="Supprimer"
                onclick="document.getElementById('cashierDeleteForm')?.requestSubmit()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    @endif
</div>
