@props([
    'user',
    'size' => 'md',
    'class' => '',
])

@php
    $name = $user?->name ?? '?';
    $initial = mb_strtoupper(mb_substr($name, 0, 1));
    $hasAvatar = $user?->avatar && file_exists(public_path('storage/avatars/'.$user->avatar));
@endphp

<div @class([
    'question-chat-avatar',
    'question-chat-avatar--'.$size,
    $class,
]) aria-hidden="true">
    @if($hasAvatar)
        <img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="">
    @else
        <span>{{ $initial }}</span>
    @endif
</div>
