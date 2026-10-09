<div class="notifications-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner notifications-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Compte</p>
            <p class="shop-catalog-banner__title">Notifications</p>
            <p class="shop-catalog-banner__sub">
                {{ $unreadCount ?? 0 }} non lue(s) · {{ $notifications->total() }} notification(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            @if(($unreadCount ?? 0) > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="shop-banner-icon-btn" aria-label="Tout marquer comme lu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </form>
            @endif
            <a href="{{ route('services.index') }}" class="shop-banner-icon-btn" aria-label="Retour aux services">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </div>
</div>
