@extends('layouts.app')

@section('title', 'Message — '.$message->subject)

@section('content')
@php
    $initial = mb_strtoupper(mb_substr($message->name ?: '?', 0, 1));
    $dayLabel = $message->created_at
        ? $message->created_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY')
        : null;
    $backQuery = array_filter([
        'status' => ($status ?? null) ?: null,
        'q' => ($q ?? null) ?: null,
    ]);
@endphp

{{-- ========== Desktop : titre + stats + lecture (sans liste) ========== --}}
<div class="it-desktop-only">
    <div class="it-desktop-head mb-6">
        <div class="member-page-intro mb-0 min-w-0">
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">Messages du site officiel</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-1">Demandes des visiteurs — accessible admin &amp; responsable IT</p>
        </div>
    </div>

    <div class="follow-up-stat-grid mb-5">
        @foreach ([
            ['all', 'Tous', $counts['all'] ?? 0],
            ['new', 'Nouveaux', $counts['new'] ?? 0],
            ['read', 'Lus', $counts['read'] ?? 0],
            ['archived', 'Archivés', $counts['archived'] ?? 0],
        ] as [$key, $label, $count])
            <a href="{{ route('site-management.contacts.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => ($q ?? null) ?: null])) }}"
               class="rounded-lg border px-3 py-3 {{ (($status ?? '') === $key || ($key === 'all' && ($status ?? '') === '')) ? 'border-[var(--color-primary-600)] bg-[var(--bg-secondary)]' : 'border-[var(--border-color)] bg-[var(--bg-card)]' }}">
                <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]">{{ $count }}</p>
                <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">{{ $label }}</p>
            </a>
        @endforeach
    </div>

    <div class="contact-gmail contact-gmail--reader-only">
        <section class="contact-gmail__reader-pane">
            @include('site-management.contacts._desktop_reader', ['selectedMessage' => $message])
        </section>
    </div>
</div>

{{-- ========== Mobile WhatsApp ========== --}}
<div class="contact-chat-page it-mobile-only">
    <div class="contact-chat-topbar">
        <a href="{{ route('site-management.contacts.index', $backQuery) }}"
           class="contact-chat-topbar__back"
           aria-label="Retour aux messages">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span class="contact-convo-avatar contact-convo-avatar--sm" aria-hidden="true">{{ $initial }}</span>
        <div class="contact-chat-topbar__meta min-w-0">
            <p class="contact-chat-topbar__name truncate">{{ $message->name }}</p>
            <p class="contact-chat-topbar__sub truncate">{{ $message->email }}</p>
        </div>
        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}"
           class="contact-chat-topbar__action"
           title="Répondre par email"
           aria-label="Répondre par email">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </a>
    </div>

    <div class="contact-chat-shell">
        <div class="contact-chat-thread" role="log" aria-live="polite">
            @if ($dayLabel)
                <div class="contact-chat-day">
                    <span>{{ $dayLabel }}</span>
                </div>
            @endif

            <div class="contact-chat-row contact-chat-row--incoming">
                <div class="contact-chat-bubble-group">
                    <p class="contact-chat-bubble-meta">{{ $message->subject }}</p>
                    <div class="contact-chat-bubble">
                        <div class="contact-chat-bubble__text">{{ $message->message }}</div>
                    </div>
                    <time class="contact-chat-bubble-time" datetime="{{ $message->created_at?->toIso8601String() }}">
                        {{ $message->created_at?->format('H:i') }}
                    </time>
                </div>
            </div>

            @if ($message->phone)
                <div class="contact-chat-info">
                    Tél. <a href="tel:{{ preg_replace('/\s+/', '', $message->phone) }}">{{ $message->phone }}</a>
                </div>
            @endif
        </div>

        <div class="contact-chat-footer">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}"
               class="contact-chat-footer__primary">
                Répondre par email
            </a>
            <div class="contact-chat-footer__actions">
                <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="read">
                    <button type="submit" class="contact-chat-footer__btn">Lu</button>
                </form>
                <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="archived">
                    <button type="submit" class="contact-chat-footer__btn">Archiver</button>
                </form>
                <form method="POST" action="{{ route('site-management.contacts.destroy', $message) }}"
                      onsubmit="return confirm('Supprimer définitivement ce message ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="contact-chat-footer__btn contact-chat-footer__btn--danger">Suppr.</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
