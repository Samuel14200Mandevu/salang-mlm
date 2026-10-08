@php
    use Illuminate\Support\Str;

    $topbarNotifications = auth()->user()->notifications()->limit(50)->get();

    $notifyCategory = static function (array $data): string {
        $type = (string) ($data['type'] ?? '');
        if (Str::contains($type, ['commission', 'payment', 'withdrawal', 'wallet'])) {
            return 'finance';
        }
        if (Str::contains($type, ['kyc', 'activation', 'welcome', 'password'])) {
            return 'account';
        }
        if (Str::contains($type, ['downline', 'rank', 'network'])) {
            return 'network';
        }
        if (Str::contains($type, ['package', 'order', 'purchase'])) {
            return 'orders';
        }
        return 'other';
    };

    $notifyTone = static function (array $data): string {
        $type = (string) ($data['type'] ?? '');
        $ui = (string) ($data['ui_type'] ?? $data['severity'] ?? '');
        if ($ui === 'danger' || Str::contains($type, ['reject', 'failed'])) {
            return 'orange';
        }
        if ($ui === 'warning') {
            return 'orange';
        }
        if (Str::contains($type, ['kyc', 'activation', 'welcome', 'password'])) {
            return 'teal';
        }
        if (Str::contains($type, ['rank', 'downline'])) {
            return 'green';
        }
        return 'blue';
    };

    $categoryOptions = [
        'all' => 'Toutes les catégories',
        'finance' => 'Finances & commissions',
        'account' => 'Compte & sécurité',
        'network' => 'Réseau & rang',
        'orders' => 'Achats & packages',
        'other' => 'Autres',
    ];
@endphp

<div
    class="relative member-mobile-topbar__notifications"
    x-data="{
        dropdownOpen: false,
        sheetOpen: false,
        unreadCount: {{ auth()->user()->unreadNotifications()->count() }},
        tab: 'all',
        category: 'all',
        isMobileViewport() { return window.innerWidth < 768; },
        openPanel() {
            if (this.isMobileViewport()) {
                this.sheetOpen = true;
                document.body.classList.add('member-notify-sheet-open');
            } else {
                this.dropdownOpen = !this.dropdownOpen;
            }
        },
        closeSheet() {
            this.sheetOpen = false;
            document.body.classList.remove('member-notify-sheet-open');
        },
        matches(itemUnread, itemCategory) {
            if (this.tab === 'unread' && !itemUnread) return false;
            if (this.category !== 'all' && this.category !== itemCategory) return false;
            return true;
        }
    }"
    @keydown.escape.window="dropdownOpen = false; closeSheet()"
>
    <button type="button"
            @click="openPanel()"
            class="member-mobile-topbar__notify p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative"
            aria-label="Notifications"
            :aria-expanded="dropdownOpen || sheetOpen">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-cloak
              x-show="unreadCount > 0"
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-primary-500 rounded-full"></span>
    </button>

    {{-- Desktop : menu déroulant --}}
    <div x-cloak
         x-show="dropdownOpen"
         @click.away="dropdownOpen = false"
         class="member-notify-dropdown hidden md:block absolute right-0 mt-2 w-80 lg:w-96 py-2 max-h-[80vh] overflow-y-auto z-50 bg-[var(--bg-card)] rounded-lg shadow-sm border border-[var(--border-color)]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="px-4 py-2 border-b border-[var(--border-color)] flex justify-between items-center">
            <h4 class="font-semibold text-sm text-[var(--text-primary)]">Notifications</h4>
            <a href="{{ route('notifications.index') }}" class="text-xs text-primary-500 hover:text-primary-600 transition font-medium">Voir tout</a>
        </div>
        <div class="divide-y divide-[var(--border-color)]" id="notificationList">
            @forelse($topbarNotifications->take(8) as $notification)
                @php $data = $notification->data ?? []; @endphp
                <div class="px-4 py-3 hover:bg-[var(--bg-secondary)] transition notification-item" data-id="{{ $notification->id }}">
                    <p class="text-sm font-medium text-[var(--text-primary)]">{{ $data['title'] ?? 'Notification' }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $data['message'] ?? '' }}</p>
                    <p class="text-xs text-[var(--text-tertiary)] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="px-4 py-4 text-center text-[var(--text-secondary)] text-sm">Aucune notification</div>
            @endforelse
        </div>
        <div class="px-4 py-2 border-t border-[var(--border-color)] text-center">
            <button type="button"
                    @click="window.markAllAsRead(() => { unreadCount = 0 })"
                    class="text-xs text-primary-500 hover:text-primary-600 transition font-medium hover:underline cursor-pointer">
                Tout marquer comme lu
            </button>
        </div>
    </div>

    {{-- Mobile : panneau plein écran --}}
    <template x-teleport="body">
        <div x-cloak
             x-show="sheetOpen"
             class="member-notify-sheet md:hidden"
             role="dialog"
             aria-modal="true"
             aria-labelledby="memberNotifySheetTitle">
            <div class="member-notify-sheet__backdrop" @click="closeSheet()" aria-hidden="true"></div>
            <div class="member-notify-sheet__panel"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-full opacity-0">
                <header class="member-notify-sheet__head">
                    <div class="member-notify-sheet__head-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <h2 id="memberNotifySheetTitle">Notifications</h2>
                    </div>
                    <button type="button" class="member-notify-sheet__close" @click="closeSheet()" aria-label="Fermer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="member-notify-sheet__filters">
                    <div class="member-notify-sheet__tabs" role="tablist">
                        <button type="button"
                                role="tab"
                                class="member-notify-sheet__tab"
                                :class="{ 'is-active': tab === 'all' }"
                                @click="tab = 'all'">Toutes</button>
                        <button type="button"
                                role="tab"
                                class="member-notify-sheet__tab"
                                :class="{ 'is-active': tab === 'unread' }"
                                @click="tab = 'unread'">Non lues</button>
                    </div>
                    <label class="member-notify-sheet__category">
                        <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <select x-model="category" class="member-notify-sheet__select" aria-label="Filtrer par catégorie">
                            @foreach($categoryOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 shrink-0 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </label>
                </div>

                <div class="member-notify-sheet__list">
                    @forelse($topbarNotifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $tone = $notifyTone($data);
                            $cat = $notifyCategory($data);
                            $isUnread = $notification->read_at === null;
                            $url = $data['url'] ?? null;
                            $title = $data['title'] ?? 'Notification';
                        @endphp
                        <article class="member-notify-card member-notify-card--{{ $tone }} {{ $isUnread ? 'is-unread' : '' }}"
                                 data-id="{{ $notification->id }}"
                                 x-show="matches({{ $isUnread ? 'true' : 'false' }}, '{{ $cat }}')"
                                 x-cloak>
                            <div class="member-notify-card__icon" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="member-notify-card__body">
                                <h3 class="member-notify-card__title">{{ $title }}</h3>
                                @if(! empty($data['message']))
                                    <p class="member-notify-card__message">{{ $data['message'] }}</p>
                                @endif
                                <div class="member-notify-card__foot">
                                    @if($url)
                                        <a href="{{ $url }}" class="member-notify-card__link" @click="closeSheet()">Ouvrir ›</a>
                                    @else
                                        <a href="{{ route('notifications.index') }}" class="member-notify-card__link" @click="closeSheet()">Voir tout ›</a>
                                    @endif
                                    <time class="member-notify-card__time" datetime="{{ $notification->created_at->toIso8601String() }}">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </time>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="member-notify-sheet__empty">
                            <p>Aucune notification pour le moment.</p>
                        </div>
                    @endforelse
                </div>

                <footer class="member-notify-sheet__foot">
                    <button type="button"
                            class="member-notify-sheet__mark-all"
                            @click="window.markAllAsRead(() => { unreadCount = 0 })">
                        Tout marquer comme lu
                    </button>
                    <a href="{{ route('notifications.index') }}" class="member-notify-sheet__all-link" @click="closeSheet()">
                        Centre de notifications
                    </a>
                </footer>
            </div>
        </div>
    </template>
</div>
