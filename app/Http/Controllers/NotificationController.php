<?php

namespace App\Http\Controllers;

use App\Notifications\ActionFeedbackNotification;
use App\Support\NotificationPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->query('status', 'all');
        $category = $request->query('category', 'all');
        $q = trim((string) $request->query('q', ''));

        if (! in_array($status, ['all', 'unread', 'read'], true)) {
            $status = 'all';
        }
        if (! array_key_exists($category, NotificationPresenter::CATEGORIES)) {
            $category = 'all';
        }

        $query = $user->notifications()->orderByDesc('created_at');

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where('data', 'like', $like);
        }

        $notifications = $query->paginate(20)->withQueryString();

        if ($category !== 'all') {
            $notifications->setCollection(
                $notifications->getCollection()->filter(function ($notification) use ($category) {
                    return NotificationPresenter::make($notification)->category() === $category;
                })->values()
            );
        }

        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'totalCount' => $totalCount,
            'status' => $status,
            'category' => $category,
            'q' => $q,
            'categories' => NotificationPresenter::CATEGORIES,
        ]);
    }

    public function show(string $id): View
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
            $notification->refresh();
        }

        $presenter = NotificationPresenter::make($notification);

        return view('notifications.show', [
            'notification' => $notification,
            'presenter' => $presenter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Marquer comme lues les confirmations d'action (cloche).
     */
    public function markActionFeedbackRead(Request $request)
    {
        $user = Auth::user();

        $user->unreadNotifications()
            ->where('type', ActionFeedbackNotification::class)
            ->get()
            ->each
            ->markAsRead();

        return response()->json([
            'success' => true,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification supprimée.');
    }

    public function destroyAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Toutes les notifications ont été supprimées.');
    }
}
