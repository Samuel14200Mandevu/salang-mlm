@php
    $role = old('role', $user->roles->first()?->name ?? 'user');
    $isStaff = in_array($role, ['admin', 'it_manager', 'cashier'], true);
    $listUrl = $isStaff ? route('admin.cashiers.index') : route('admin.users');
    $listLabel = $isStaff ? 'Employés' : 'Membres';
@endphp
<a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    Détail
</a>
<a href="{{ $listUrl }}"
   class="btn btn-outline btn-sm"
   data-edit-list-link
   data-members-url="{{ route('admin.users') }}"
   data-staff-url="{{ route('admin.cashiers.index') }}"
   data-members-label="Membres"
   data-staff-label="Employés">
    <span data-edit-list-label>{{ $listLabel }}</span>
</a>
