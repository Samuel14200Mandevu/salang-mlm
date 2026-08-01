@extends('layouts.app')

@push('styles')
<style>
    .notification-item {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid transparent;
    }
    .notification-item:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
        transform: translateX(4px);
    }
    .notification-item.unread {
        background: rgba(59, 130, 246, 0.04);
        border-left: 3px solid #3b82f6;
    }
    .notification-item.unread:hover {
        background: rgba(59, 130, 246, 0.08);
    }
    
    .notification-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .notification-icon.info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .notification-icon.success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .notification-icon.warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .notification-icon.danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    .empty-state svg {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: var(--text-tertiary);
    }
    
    @media (max-width: 640px) {
        .notification-item { padding: 0.75rem !important; }
        .notification-icon { width: 2rem; height: 2rem; }
        .notification-icon svg { width: 1rem; height: 1rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .card { padding: 0.875rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.875rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
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
                    {{ $notifications->links() }}
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