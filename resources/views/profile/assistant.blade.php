@extends('layouts.app')

@section('title', 'Assistant Salang')

@section('content')
@php
    use App\Services\MemberAssistantService;
    $assistantService = app(MemberAssistantService::class);
    $welcomeReply = $assistantService->welcomeReply(auth()->user());
    $quickPrompts = config('member-assistant.quick_prompts', []);
@endphp
<div class="profile-page profile-page--account profile-subpage profile-assistant-page">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Assistant</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Chat d’aide sur l’application membre</p>
    </div>

    @include('profile.partials.account-subpage-header', [
        'title' => 'Assistant',
        'sub' => 'En ligne · FAQ',
        'backUrl' => route('profile.help'),
        'showAssistantFabSwitch' => true,
    ])

    <div class="profile-subpage-body member-assistant">
        @include('partials.member.assistant-chat', [
            'chatId' => 'assistantChatPage',
            'chatClass' => 'member-assistant-chat--page card',
            'welcomeReply' => $welcomeReply,
            'quickPrompts' => $quickPrompts,
            'showExpand' => false,
            'showClose' => false,
        ])
    </div>
</div>
@endsection
