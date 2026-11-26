@php
    $role = old('role', $user->roles->first()?->name ?? 'user');
    $isStaff = in_array($role, ['admin', 'it_manager', 'cashier'], true);
    $detailUrl = $isStaff
        ? route('admin.cashiers.show', $user->id)
        : route('admin.users.show', $user);
    $detailLabel = $isStaff ? 'Détail employé' : 'Détail membre';
    $listUrl = $isStaff ? route('admin.cashiers.index') : route('admin.users');
    $listLabel = $isStaff ? 'Employés' : 'Membres';
@endphp
<div class="admin-title-banner__tools admin-title-banner__tools--user-edit">
    <a href="{{ $detailUrl }}"
       class="btn btn-outline btn-sm btn-round-icon"
       title="{{ $detailLabel }}"
       aria-label="{{ $detailLabel }}"
       data-edit-detail-link
       data-members-url="{{ route('admin.users.show', $user) }}"
       data-staff-url="{{ route('admin.cashiers.show', $user->id) }}"
       data-members-label="Détail membre"
       data-staff-label="Détail employé">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </a>
    <a href="{{ $listUrl }}"
       class="btn btn-outline btn-sm btn-round-icon"
       title="{{ $listLabel }}"
       aria-label="Retour à {{ $listLabel }}"
       data-edit-list-link
       data-members-url="{{ route('admin.users') }}"
       data-staff-url="{{ route('admin.cashiers.index') }}"
       data-members-label="Membres"
       data-staff-label="Employés">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </a>
</div>
