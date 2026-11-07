<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\StaffAnnouncementNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function create(): View
    {
        $membersCount = $this->membersQuery()->count();

        return view('it-manager.announcements.create', compact('membersCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'url', 'max:500'],
        ]);

        $sent = 0;
        $title = $data['title'];
        $message = $data['message'];
        $url = $data['url'] ?? null;

        try {
            set_time_limit(300);

            $this->membersQuery()
                ->select(['id'])
                ->orderBy('id')
                ->chunkById(150, function ($members) use ($title, $message, $url, &$sent) {
                    Notification::send(
                        $members,
                        new StaffAnnouncementNotification($title, $message, $url)
                    );
                    $sent += $members->count();
                });
        } catch (\Throwable $e) {
            Log::error('Envoi annonce IT échoué', [
                'sent' => $sent,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Envoi interrompu après '.$sent.' membre(s) : '.$e->getMessage());
        }

        return redirect()
            ->route('it-manager.announcements.create')
            ->with('success', 'Annonce envoyée dans la cloche de '.$sent.' membre(s).');
    }

    protected function membersQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'it_manager', 'cashier']));
    }
}
