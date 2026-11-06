@extends('layouts.app')

@section('title', 'Annonces')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Annonce aux membres</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">
        Notification dans la cloche — {{ number_format($membersCount) }} membre(s) actif(s).
    </p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">{{ session('error') }}</div>
@endif
<p class="text-xs text-[var(--text-muted)] mb-4">
    Chaque membre actif reçoit l’annonce dans sa <strong>cloche de notifications</strong> (badge + liste).
</p>

<form method="POST" action="{{ route('it-manager.announcements.store') }}" class="max-w-xl space-y-3 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
    @csrf
    <div>
        <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Titre *</label>
        <input type="text" name="title" value="{{ old('title') }}" required maxlength="120"
               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
        @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Message *</label>
        <textarea name="message" rows="4" required maxlength="1000"
                  class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ old('message') }}</textarea>
        @error('message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Lien (optionnel)</label>
        <input type="url" name="url" value="{{ old('url') }}" placeholder="https://"
               class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
    </div>
    <button type="submit" class="btn btn-primary"
            onclick="return confirm('Envoyer cette annonce à tous les membres ?')">
        Envoyer
    </button>
</form>
@endsection
