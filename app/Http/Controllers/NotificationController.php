<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Notifications\ActionFeedbackNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
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
        
        return back();
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

        return back();
    }

    public function destroyAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}