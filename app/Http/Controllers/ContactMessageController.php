<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceivedNotification;
use App\Services\ContactSpamGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function __construct(
        protected ContactSpamGuard $spamGuard
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:filter', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', Rule::in(ContactSpamGuard::SUBJECTS)],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            // Honeypots : accepter toute valeur, le guard refuse silencieusement.
            'website' => ['nullable', 'string', 'max:255'],
            'company_url' => ['nullable', 'string', 'max:255'],
            'form_token' => ['required', 'string'],
        ], [
            'subject.in' => 'Veuillez choisir un sujet valide.',
            'message.min' => 'Le message doit contenir au moins :min caractères.',
            'form_token.required' => 'Formulaire expiré. Rechargez la page et réessayez.',
        ]);

        $inspection = $this->spamGuard->inspect($request, $data);

        // Réponse générique : ne pas enseigner aux bots pourquoi ça a échoué.
        if ($inspection['blocked']) {
            return redirect()
                ->route('contact')
                ->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.');
        }

        unset($data['website'], $data['company_url'], $data['form_token']);

        $contactMessage = ContactMessage::query()->create([
            ...$data,
            'status' => ContactMessage::STATUS_NEW,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->spamGuard->rememberSubmission($request, $data);
        $this->notifyStaff($contactMessage);

        return redirect()
            ->route('contact')
            ->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.');
    }

    protected function notifyStaff(ContactMessage $contactMessage): void
    {
        $recipients = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'it_manager']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new ContactMessageReceivedNotification($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Contact: notification staff échouée', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
