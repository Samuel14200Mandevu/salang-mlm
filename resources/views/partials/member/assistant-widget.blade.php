@php
    use App\Services\MemberAssistantService;
    $assistantService = app(MemberAssistantService::class);
    $welcomeReply = $assistantService->welcomeReply(auth()->user());
    $quickPrompts = config('member-assistant.quick_prompts', []);
@endphp
<div
    class="member-assistant-fab-root"
    id="memberAssistantFabRoot"
    data-assistant-fab-root
    aria-hidden="false"
>
    <div class="member-assistant-fab-panel" data-assistant-fab-panel hidden>
        @include('partials.member.assistant-chat', [
            'chatId' => 'assistantChatWidget',
            'chatClass' => 'member-assistant-chat--widget',
            'welcomeReply' => $welcomeReply,
            'quickPrompts' => $quickPrompts,
            'showClose' => true,
            'showExpand' => true,
            'showFabHide' => true,
        ])
    </div>

    <button
        type="button"
        class="member-assistant-fab"
        data-assistant-fab
        aria-label="Ouvrir l’assistant Salang"
        aria-expanded="false"
        aria-controls="assistantChatWidget"
    >
        <span class="member-assistant-fab__glow" aria-hidden="true"></span>
        <span class="member-assistant-fab__surface" aria-hidden="true">
            <svg class="member-assistant-fab__svg member-assistant-fab__svg--open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="salangFabIconGrad" x1="4" y1="4" x2="20" y2="20" gradientUnits="userSpaceOnUse">
                        <stop stop-color="var(--color-primary-600, #1d4ed8)"/>
                        <stop offset="1" stop-color="var(--color-accent-500, #eab308)"/>
                    </linearGradient>
                </defs>
                <path
                    fill="url(#salangFabIconGrad)"
                    d="M9.5 2.5a1 1 0 0 1 .894.553l1.044 2.09 2.09 1.044a1 1 0 0 1 0 1.788l-2.09 1.044-1.044 2.09a1 1 0 0 1-1.788 0l-1.044-2.09-2.09-1.044a1 1 0 0 1 0-1.788l2.09-1.044 1.044-2.09A1 1 0 0 1 9.5 2.5Z"
                />
                <path
                    fill="url(#salangFabIconGrad)"
                    fill-opacity="0.85"
                    d="M16.5 6.5a.75.75 0 0 1 .67.415l.783 1.567 1.567.783a.75.75 0 0 1 0 1.338l-1.567.783-.783 1.567a.75.75 0 0 1-1.338 0l-.783-1.567-1.567-.783a.75.75 0 0 1 0-1.338l1.567-.783.783-1.567a.75.75 0 0 1 .668-.415Z"
                />
                <path
                    fill="url(#salangFabIconGrad)"
                    d="M7 10.5A4.5 4.5 0 0 1 11.5 6h.25c2.21 0 4.125 1.37 4.898 3.312a3.25 3.25 0 0 1 .352 6.088v2.35a1.25 1.25 0 0 1-1.25 1.25h-.75a1.25 1.25 0 0 1-1.25-1.25v-.25H11.5A4.5 4.5 0 0 1 7 14V10.5Z"
                />
                <circle cx="10.25" cy="12.25" r="0.65" fill="#fff"/>
                <circle cx="13.75" cy="12.25" r="0.65" fill="#fff"/>
                <path stroke="#fff" stroke-width="0.9" stroke-linecap="round" d="M10.5 14.35c.55.45 1.2.65 1.9.65s1.35-.2 1.9-.65"/>
            </svg>
            <svg class="member-assistant-fab__svg member-assistant-fab__svg--close" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path stroke="currentColor" stroke-width="2.25" stroke-linecap="round" d="M7 7l10 10M17 7L7 17"/>
            </svg>
        </span>
        <span class="member-assistant-fab__status" aria-hidden="true"></span>
    </button>
</div>
