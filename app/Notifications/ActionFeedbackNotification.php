<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Confirmation d'action (ajouter / modifier / supprimer) affichée dans la cloche.
 */
class ActionFeedbackNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $tone = 'success',
        public ?string $title = null,
        public ?string $action = null,
        public ?string $url = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tone = in_array($this->tone, ['success', 'danger', 'warning', 'info'], true)
            ? $this->tone
            : 'info';

        return [
            'type' => 'action_feedback',
            'tone' => $tone,
            'category' => 'action',
            'action' => $this->action ?? $this->guessAction($this->message),
            'title' => $this->title ?? $this->defaultTitle($tone),
            'message' => $this->message,
            'url' => $this->url,
            'ui_type' => $tone,
        ];
    }

    private function defaultTitle(string $tone): string
    {
        return match ($tone) {
            'success' => 'Action réussie',
            'danger' => 'Erreur',
            'warning' => 'Attention',
            default => 'Information',
        };
    }

    private function guessAction(string $message): string
    {
        $m = mb_strtolower($message);

        if (str_contains($m, 'supprim')) {
            return 'delete';
        }
        if (str_contains($m, 'modifi') || str_contains($m, 'mis à jour') || str_contains($m, 'mise à jour') || str_contains($m, 'enregistr')) {
            return 'update';
        }
        if (str_contains($m, 'ajout') || str_contains($m, 'créé') || str_contains($m, 'cree') || str_contains($m, 'validé') || str_contains($m, 'adhésion') || str_contains($m, 'adhesion')) {
            return 'create';
        }

        return 'info';
    }
}
