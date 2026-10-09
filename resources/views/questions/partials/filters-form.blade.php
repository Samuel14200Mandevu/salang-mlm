@php($statusFieldId = $filterFormId ?? 'questions-status-filter')
<form method="get" action="{{ route('questions.index') }}" class="questions-filters-form flex flex-wrap sm:flex-nowrap gap-3 items-end">
    <div class="w-full sm:w-auto sm:min-w-[11rem]">
        <label for="{{ $statusFieldId }}" class="text-xs text-[var(--text-secondary)]">Statut</label>
        <select name="status" id="{{ $statusFieldId }}" class="input text-sm mt-1 w-full sm:w-auto sm:min-w-[11rem]">
            <option value="">Tous</option>
            <option value="open" @selected($statusFilter === 'open')>Ouvertes</option>
            <option value="resolved" @selected($statusFilter === 'resolved')>Résolues</option>
        </select>
    </div>
    <button type="submit" class="btn btn-outline btn-sm w-full sm:w-auto shrink-0">Appliquer</button>
</form>
