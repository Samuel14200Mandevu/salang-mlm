<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active', 'supervision']);
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();

        $messages = ContactMessage::query()
            ->when(in_array($status, [ContactMessage::STATUS_NEW, ContactMessage::STATUS_READ, ContactMessage::STATUS_ARCHIVED], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('subject', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => ContactMessage::query()->count(),
            'new' => ContactMessage::query()->where('status', ContactMessage::STATUS_NEW)->count(),
            'read' => ContactMessage::query()->where('status', ContactMessage::STATUS_READ)->count(),
            'archived' => ContactMessage::query()->where('status', ContactMessage::STATUS_ARCHIVED)->count(),
        ];

        return view('site-management.contacts.index', compact('messages', 'counts', 'status', 'q'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->isNew()) {
            $contactMessage->markRead(auth()->user());
        }

        $contactMessage->loadMissing('handler');

        return view('site-management.contacts.show', [
            'message' => $contactMessage,
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,archived'],
        ]);

        if ($data['status'] === ContactMessage::STATUS_ARCHIVED) {
            $contactMessage->archive(auth()->user());
        } elseif ($data['status'] === ContactMessage::STATUS_READ) {
            $contactMessage->markRead(auth()->user());
        } else {
            $contactMessage->forceFill([
                'status' => ContactMessage::STATUS_NEW,
                'read_at' => null,
            ])->save();
        }

        return back()->with('success', 'Statut du message mis à jour.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('site-management.contacts.index')
            ->with('success', 'Message supprimé.');
    }
}
