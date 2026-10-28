@extends('layouts.app')

@section('title', 'Message — '.$message->subject)

@section('content')
<div class="mb-4">
    <a href="{{ route('site-management.contacts.index') }}" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">← Retour aux messages</a>
</div>

<div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-5 sm:p-6 max-w-3xl">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <span class="inline-flex rounded-md px-2 py-0.5 text-[0.625rem] font-semibold
                {{ $message->status === 'new' ? 'bg-sky-100 text-sky-800' : ($message->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800') }}">
                {{ $message->statusLabel() }}
            </span>
            <h1 class="mt-2 text-xl font-bold text-[var(--text-primary)]">{{ $message->subject }}</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">
                {{ $message->name }} ·
                <a href="mailto:{{ $message->email }}" class="underline">{{ $message->email }}</a>
                @if ($message->phone)
                    · <a href="tel:{{ preg_replace('/\s+/', '', $message->phone) }}">{{ $message->phone }}</a>
                @endif
            </p>
            <p class="mt-1 text-xs text-[var(--text-muted)]">
                Reçu le {{ $message->created_at?->format('d/m/Y à H:i') }}
                @if ($message->handler)
                    · traité par {{ $message->handler->name }}
                @endif
            </p>
        </div>
    </div>

    <div class="mt-6 rounded-lg bg-[var(--bg-secondary)] p-4 text-sm text-[var(--text-primary)] leading-relaxed whitespace-pre-wrap">{{ $message->message }}</div>

    <div class="mt-6 flex flex-col sm:flex-row flex-wrap gap-2">
        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}" class="btn btn-primary">Répondre par email</a>

        <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="read">
            <button type="submit" class="btn btn-secondary">Marquer lu</button>
        </form>

        <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="archived">
            <button type="submit" class="btn btn-secondary">Archiver</button>
        </form>

        <form method="POST" action="{{ route('site-management.contacts.destroy', $message) }}"
              onsubmit="return confirm('Supprimer définitivement ce message ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </div>
</div>
@endsection
