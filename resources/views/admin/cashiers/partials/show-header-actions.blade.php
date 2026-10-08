<a href="{{ route('admin.users.edit', $cashier->id) }}" class="btn btn-primary btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Modifier
</a>
<a href="{{ route('admin.cashiers.index') }}" class="btn btn-outline btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour
</a>
<form action="{{ route('admin.users.toggle-status', $cashier->id) }}" method="POST" class="inline">
    @csrf
    @method('POST')
    <button type="submit" class="btn {{ $cashier->is_active ? 'btn-warning' : 'btn-success' }} btn-sm sm:btn-md"
            onclick="return confirm('Confirmer le changement de statut ?')">
        @if($cashier->is_active)
            Désactiver
        @else
            Activer
        @endif
    </button>
</form>
@if(auth()->id() != $cashier->id)
    <form action="{{ route('admin.users.destroy', $cashier->id) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm sm:btn-md"
                onclick="return confirm('Supprimer définitivement ce caissier ?')">
            Supprimer
        </button>
    </form>
@endif
