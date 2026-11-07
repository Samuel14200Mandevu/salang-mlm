{{-- Liste mobile (cartes) — gardée en sync avec _table_rows via même boucle --}}
<div class="admin-user-list md:hidden" id="adminUserMobileList">
@forelse($users as $user)
    @php
        $roleName = $user->getRoleNames()->first() ?? 'user';
        $roleDisplay = 'Membre';
        if ($roleName === 'admin') {
            $roleDisplay = 'Admin';
        } elseif ($roleName === 'it_manager') {
            $roleDisplay = 'Responsable IT';
        } elseif ($roleName === 'cashier') {
            $roleDisplay = 'Caissier';
        } elseif ($roleName === 'caissier_principal') {
            $roleDisplay = 'Caiss. princ.';
        }
        $rankLevel = (int) ($user->rank_level ?? 0);
    @endphp
    <a href="{{ route('admin.users.show', $user) }}" class="admin-user-card">
        <div class="admin-user-card__main">
            <div class="admin-user-card__avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="admin-user-card__text">
                <span class="admin-user-card__name">{{ $user->name }}</span>
                <span class="admin-user-card__email">{{ $user->email }}</span>
            </div>
            <svg class="admin-user-card__chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
        <div class="admin-user-card__meta">
            @if($user->sponsor_id)
                <span class="admin-user-card__code">#{{ $user->sponsor_id }}</span>
            @endif
            <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">{{ \App\Support\MlmRank::label($rankLevel) }}</span>
            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                {{ $user->is_active ? 'Actif' : 'Inactif' }}
            </span>
            <span class="admin-user-card__role">{{ $roleDisplay }}</span>
        </div>
    </a>
@empty
    @php
        $emptyDescription = request()->filled('search')
            ? 'Aucun résultat ne correspond à votre recherche « ' . request('search') . ' ».'
            : (request()->filled('filter')
                ? 'Aucun résultat pour ces filtres.'
                : 'Commencez par ajouter votre premier membre.');
    @endphp
    <x-ui.empty-state
        title="Aucun membre"
        :description="$emptyDescription"
    >
        <x-slot:icon>👥</x-slot:icon>
    </x-ui.empty-state>
@endforelse
</div>
