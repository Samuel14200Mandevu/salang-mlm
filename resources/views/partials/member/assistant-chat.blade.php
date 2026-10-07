@php
    $chatId = $chatId ?? 'assistantChat';
    $chatClass = $chatClass ?? '';
@endphp
<div
    class="member-assistant-chat {{ $chatClass }}"
    data-assistant-chat
    data-search-url="{{ route('profile.assistant.search') }}"
    data-contact-url="{{ route('contact') }}"
    id="{{ $chatId }}"
>
    <div class="member-assistant-chat__head">
        <span class="member-assistant-chat__status" aria-hidden="true"></span>
        <div class="member-assistant-chat__head-text">
            <p class="member-assistant-chat__name">Assistant Salang</p>
            <p class="member-assistant-chat__meta">FAQ · <a href="{{ route('contact') }}">Support</a></p>
        </div>
        @if(!empty($showExpand))
            <a href="{{ route('profile.assistant') }}" class="member-assistant-chat__expand" aria-label="Ouvrir en plein écran" title="Plein écran">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            </a>
        @endif
        @if(!empty($showFabHide))
            @include('profile.partials.assistant-fab-toggle', [
                'toggleId' => 'assistantFabToggleWidget',
                'compact' => true,
            ])
        @endif
        @if(!empty($showClose))
            <button type="button" class="member-assistant-chat__close" data-assistant-panel-close aria-label="Fermer">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>

    <div class="member-assistant-chat__thread" data-assistant-thread role="log" aria-live="polite" aria-relevant="additions">
        <div class="member-assistant-msg member-assistant-msg--bot" data-assistant-msg="bot">
            <div class="member-assistant-msg__avatar" aria-hidden="true">S</div>
            <div class="member-assistant-msg__bubble">
                <p class="member-assistant-msg__title">{{ $welcomeReply['title'] }}</p>
                <p class="member-assistant-msg__text">{{ $welcomeReply['body'] }}</p>
            </div>
        </div>
    </div>

    <div class="member-assistant-chat__chips" data-assistant-prompts>
        @foreach($quickPrompts as $prompt)
            <button type="button" class="member-assistant-prompt" data-assistant-prompt="{{ $prompt }}">{{ $prompt }}</button>
        @endforeach
    </div>

    <form class="member-assistant-composer" data-assistant-form autocomplete="off">
        <label class="sr-only" for="{{ $chatId }}_message">Votre message</label>
        <input
            type="text"
            id="{{ $chatId }}_message"
            class="member-assistant-composer__input"
            placeholder="Écrivez votre message…"
            maxlength="{{ config('member-assistant.max_query_length', 200) }}"
            data-assistant-input
        >
        <button type="submit" class="member-assistant-composer__send" aria-label="Envoyer">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>
</div>
