@extends('layouts.app')

@section('title', $presenter->title())

@section('content')
<div class="notifications-page notifications-detail space-y-4 sm:space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
            ← Retour aux notifications
        </a>
        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
              onsubmit="return confirm('Supprimer cette notification ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline btn-sm text-red-600 border-red-200 hover:bg-red-50">Supprimer</button>
        </form>
    </div>

    <article class="notifications-detail__card card p-5 sm:p-6 animate-fadeInUp">
        <div class="flex flex-wrap items-start gap-3 justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="notifications-detail__badge notifications-detail__badge--{{ $presenter->tone() }}">
                        {{ $presenter->categoryLabel() }}
                    </span>
                    @if($presenter->isUnread())
                        <span class="notifications-detail__badge notifications-detail__badge--unread">Non lue</span>
                    @else
                        <span class="notifications-detail__badge">Lue</span>
                    @endif
                </div>
                <h1 class="mt-3 text-xl sm:text-2xl font-bold text-[var(--text-primary)]">{{ $presenter->title() }}</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                    {{ $notification->created_at->format('d/m/Y à H:i') }}
                    · {{ $notification->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        <div class="notifications-detail__message mt-5">
            {{ $presenter->message() }}
        </div>

        @if(count($presenter->detailRows()) > 0)
            <dl class="notifications-detail__rows mt-6">
                @foreach($presenter->detailRows() as $row)
                    <div class="notifications-detail__row">
                        <dt>{{ $row['label'] }}</dt>
                        <dd>{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <div class="mt-6 flex flex-col sm:flex-row flex-wrap gap-2">
            @if($presenter->actionUrl())
                <a href="{{ $presenter->actionUrl() }}" class="btn btn-primary">
                    Ouvrir l’élément lié
                </a>
            @endif
            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </article>
</div>
@endsection
