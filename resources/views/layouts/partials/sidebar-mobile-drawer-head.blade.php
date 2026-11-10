@auth
    @php
        $drawerUser = Auth::user()?->loadMissing(['package']);
        $drawerVariant = $drawerVariant ?? (
            ($drawerUser?->usesItManagerShell() ?? false)
                ? 'it'
                : 'member'
        );
        $isStaffDrawer = in_array($drawerVariant, ['admin', 'cashier', 'it'], true);
    @endphp
    @if($drawerUser)
    @php
        $drawerAvatarUrl = null;
        if ($drawerUser->avatar && file_exists(public_path('storage/avatars/' . $drawerUser->avatar))) {
            $drawerAvatarUrl = asset('storage/avatars/' . $drawerUser->avatar);
        }

        $drawerInitials = collect(preg_split('/\s+/u', trim($drawerUser->name), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->join('');
        if ($drawerInitials === '') {
            $drawerInitials = 'U';
        }

        $drawerRankModel = null;
        $drawerRankLevel = 1;
        $drawerRankName = null;
        $drawerRankImageUrl = null;
        $drawerCode = null;
        $drawerSubtitle = null;

        if ($drawerVariant === 'member') {
            $rawRankLevel = $drawerUser->getAttributes()['rank_level'] ?? null;

            if ($rawRankLevel !== null && $rawRankLevel !== '') {
                $drawerRankLevel = max(0, min(9, (int) $rawRankLevel));
                $drawerRankModel = \App\Models\Rank::query()
                    ->where('level', $drawerRankLevel)
                    ->where('is_active', true)
                    ->first();
            }

            if (! $drawerRankModel) {
                $drawerRankModel = $drawerUser->rankObject;
                if ($drawerRankModel) {
                    $drawerRankLevel = max(0, min(9, (int) ($drawerRankModel->level ?? $drawerRankLevel)));
                }
            }

            $drawerRankName = $drawerRankModel?->name ?? \App\Support\MlmRank::label($drawerRankLevel);
            $drawerRankImageUrl = \App\Support\MlmRank::imageUrl($drawerRankLevel);
            $drawerCode = $drawerUser->sponsor_id ?: ('#' . $drawerUser->id);
            $drawerSubtitle = $drawerUser->package?->name;
        } elseif ($drawerVariant === 'admin') {
            $drawerRankName = 'Administrateur';
            $drawerSubtitle = $drawerUser->email;
        } elseif ($drawerVariant === 'cashier') {
            $drawerRankName = 'Caissier';
            $drawerSubtitle = $drawerUser->email;
        } else {
            $drawerRankName = $drawerUser->hasRole('admin') && ! $drawerUser->isItManagerOnly()
                ? 'Admin · Supervision IT'
                : 'Responsable IT';
        }

        $headModifier = ($drawerVariant === 'member' && $drawerRankImageUrl)
            ? ' has-grade-badge'
            : ' has-profile-photo';
    @endphp
    <div class="sidebar-mobile-drawer-head md:hidden{{ $headModifier }}">
        <span class="sidebar-mobile-drawer-head__glow" aria-hidden="true"></span>
        <span class="sidebar-mobile-drawer-head__orbit" aria-hidden="true"></span>

        <button type="button"
                class="sidebar-mobile-drawer-head__close"
                @click="typeof closeSidebar === 'function' ? closeSidebar() : (sidebarOpen = false)"
                aria-label="Fermer le menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="sidebar-mobile-drawer-head__badge">
            @if($isStaffDrawer || ! $drawerRankImageUrl)
                <div class="sidebar-mobile-drawer-head__avatar" aria-hidden="true">
                    @if($drawerAvatarUrl)
                        <img src="{{ $drawerAvatarUrl }}"
                             alt=""
                             class="sidebar-mobile-drawer-head__photo"
                             width="96"
                             height="96"
                             loading="eager"
                             decoding="async">
                    @else
                        <span class="sidebar-mobile-drawer-head__initials">{{ $drawerInitials }}</span>
                    @endif
                </div>
            @else
                <span class="sidebar-mobile-drawer-head__badge-glow" aria-hidden="true"></span>
                <img
                    src="{{ $drawerRankImageUrl }}"
                    alt="{{ $drawerRankName }}"
                    class="sidebar-mobile-drawer-head__badge-img"
                    width="148"
                    height="185"
                    loading="eager"
                    decoding="async"
                >
            @endif
        </div>

        <p class="sidebar-mobile-drawer-head__grade">{{ $drawerRankName }}</p>
        <p class="sidebar-mobile-drawer-head__name">{{ $drawerUser->name }}</p>

        @if($drawerVariant === 'member')
            <p class="sidebar-mobile-drawer-head__code">
                Code&nbsp;: <strong>{{ $drawerCode }}</strong>
            </p>

            @if($drawerSubtitle)
                <p class="sidebar-mobile-drawer-head__meta">{{ $drawerSubtitle }}</p>
            @endif
        @elseif($drawerSubtitle)
            <p class="sidebar-mobile-drawer-head__meta">{{ $drawerSubtitle }}</p>
        @endif
    </div>
    @endif
@endauth
