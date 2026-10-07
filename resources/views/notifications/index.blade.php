@extends('layouts.app')

@section('title', 'Notifications')



@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Notifications</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $unreadCount ?? 0 }} non lue(s)
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    <!-- Liste des notifications -->
    <div class="card animate-fadeInUp delay-1">
        @if($notifications->count() > 0)
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-xs sm:text-sm w-12">Type</th>
                            <th class="text-xs sm:text-sm">Message</th>
                            <th class="text-xs sm:text-sm hidden sm:table-cell">Date</th>
                            <th class="text-xs sm:text-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            <tr class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                                <td>
                                    <div class="notification-icon 
                                        @if(isset($notification->data['type']) && $notification->data['type'] == 'success') success
                                        @elseif(isset($notification->data['type']) && $notification->data['type'] == 'warning') warning
                                        @elseif(isset($notification->data['type']) && $notification->data['type'] == 'danger') danger
                                        @else info @endif">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(isset($notification->data['type']) && $notification->data['type'] == 'success')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif(isset($notification->data['type']) && $notification->data['type'] == 'warning')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            @elseif(isset($notification->data['type']) && $notification->data['type'] == 'danger')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            @endif
                                        </svg>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p class="font-medium text-[var(--text-primary)] text-sm">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <span class="text-[10px] sm:text-xs text-[var(--text-tertiary)] sm:hidden">
                                            {{ $notification->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                    {{ $notification->created_at->format('d/m/Y H:i') }}
                                    <br>
                                    <span class="text-[10px] text-[var(--text-tertiary)]">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" title="Marquer comme lu">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="hidden sm:inline">Marquer lu</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-success text-[10px] sm:text-xs">Lu</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="mt-3 sm:mt-4">
                    <x-salang-pagination :paginator="$notifications" />
                </div>
            @endif
        @else
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucune notification</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">
                    Vous n'avez pas encore de notifications.
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3 sm:mt-4">
                    Retour à l'accueil
                </a>
            </div>
        @endif
    </div>
</div>
@endsection