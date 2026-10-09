@php($typeFieldId = $filterFormId ?? 'publications-type-filter')
<form method="get" class="publications-filters-form flex flex-wrap sm:flex-nowrap gap-3 items-end">
    <div class="w-full sm:w-auto sm:min-w-[11rem]">
        <label for="{{ $typeFieldId }}" class="text-xs text-[var(--text-secondary)]">Type</label>
        <select name="type" id="{{ $typeFieldId }}" class="input text-sm mt-1 w-full sm:w-auto sm:min-w-[11rem]">
            <option value="">Tous</option>
            <option value="event" @selected($typeFilter === 'event')>Événements</option>
            <option value="promotion" @selected($typeFilter === 'promotion')>Promotions</option>
        </select>
    </div>
    <label class="inline-flex items-center gap-2 text-sm text-[var(--text-secondary)] pb-2">
        <input type="checkbox" name="active" value="1" class="rounded border-[var(--border-color)]" @checked($activeFilter)>
        Actifs seulement
    </label>
    <button type="submit" class="btn btn-outline btn-sm w-full sm:w-auto shrink-0">Appliquer</button>
</form>
