@auth
    @php
        $drawerUser = Auth::user()->loadMissing(['package']);
        $rawRankLevel = $drawerUser->getAttributes()['rank_level'] ?? null;
        $drawerRankModel = null;
        $drawerRankLevel = 1;

        if ($rawRankLevel !== null && $rawRankLevel !== '') {
            $drawerRankLevel = max(0, min(9, (int) $rawRankLevel));
            $drawerRankModel = \App\Models\Rank::query()
                ->where('level', $drawerRankLevel)
                ->where('is_active', true)
                ->first();
        }

        if (!$drawerRankModel) {
            $drawerRankModel = $drawerUser->rankObject;
            if ($drawerRankModel) {
                $drawerRankLevel = max(0, min(9, (int) ($drawerRankModel->level ?? $drawerRankLevel)));
            }
        }

        $drawerRankName = $drawerRankModel?->name ?? \App\Support\MlmRank::label($drawerRankLevel);
        $drawerCode = $drawerUser->sponsor_id ?: ('#' . $drawerUser->id);
        $drawerSubtitle = $drawerUser->package?->name;
        $drawerInitials = collect(preg_split('/\s+/u', trim($drawerUser->name), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->join('');
        if ($drawerInitials === '') {
            $drawerInitials = 'M';
        }
    @endphp
    <div class="sidebar-mobile-drawer-head lg:hidden">
        <button type="button"
                class="sidebar-mobile-drawer-head__close"
                @click="sidebarOpen = false"
                aria-label="Fermer le menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="sidebar-mobile-drawer-head__avatar">
            @if($drawerUser->avatar && file_exists(public_path('storage/avatars/' . $drawerUser->avatar)))
                <img src="{{ asset('storage/avatars/' . $drawerUser->avatar) }}" alt="" class="sidebar-mobile-drawer-head__photo">
            @else
                <span class="sidebar-mobile-drawer-head__initials">{{ $drawerInitials }}</span>
            @endif
        </div>

        <p class="sidebar-mobile-drawer-head__name">{{ $drawerUser->name }}</p>

        <p class="sidebar-mobile-drawer-head__grade">{{ $drawerRankName }}</p>

        <p class="sidebar-mobile-drawer-head__code">
            Code&nbsp;: <strong>{{ $drawerCode }}</strong>
        </p>

        @if($drawerSubtitle)
            <p class="sidebar-mobile-drawer-head__meta">{{ $drawerSubtitle }}</p>
        @endif
    </div>
@endauth
