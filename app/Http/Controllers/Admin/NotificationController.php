<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierReport;
use App\Models\Consultation;
use App\Support\NotificationPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->query('status', 'all');
        $q = trim((string) $request->query('q', ''));

        if (! in_array($status, ['all', 'unread', 'read'], true)) {
            $status = 'all';
        }

        $query = $user->notifications()->orderByDesc('created_at');

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($q !== '') {
            $query->where('data', 'like', '%'.$q.'%');
        }

        $notifications = $query->paginate(20)->withQueryString();
        $pendingConsultations = Consultation::query()->where('status', 'pending')->count();
        $pendingReports = CashierReport::query()->where('status', 'submitted')->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'totalCount' => $user->notifications()->count(),
            'status' => $status,
            'q' => $q,
            'pendingConsultations' => $pendingConsultations,
            'pendingReports' => $pendingReports,
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

        return view('admin.notifications.show', [
            'notification' => $notification,
            'presenter' => NotificationPresenter::make($notification),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification supprimée.');
    }

    public function destroyAll(): RedirectResponse
    {
        Auth::user()->notifications()->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Toutes les notifications ont été supprimées.');
    }
}
