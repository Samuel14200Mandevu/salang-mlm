<?php

namespace App\Notifications;

use App\Models\Publication;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PublicationPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const TYPE = 'publication_published';

    public const TTL_HOURS = 24;

    public function __construct(
        public Publication $publication,
        public ?CarbonInterface $expiresAt = null,
    ) {
        $this->expiresAt = $expiresAt ?? now()->addHours(self::TTL_HOURS);
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = $this->typeLabel();
        $url = route('publications.show', $this->publication);
        $description = trim((string) ($this->publication->description ?? ''));

        $mail = (new MailMessage)
            ->subject('Nouvelle publication — '.$this->publication->title)
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Une nouvelle publication est disponible dans votre espace Salang.')
            ->line('')
            ->line('**'.$typeLabel.'** · '.$this->publication->title);

        if ($description !== '') {
            $mail->line('')
                ->line(\Illuminate\Support\Str::limit($description, 280));
        }

        return $mail
            ->line('')
            ->action('Voir la publication', $url)
            ->line('')
            ->line('Cette alerte reste visible 24 h dans vos notifications.')
            ->salutation("L'équipe Salang");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => self::TYPE,
            'tone' => 'info',
            'category' => 'announcement',
            'action' => 'create',
            'title' => 'Nouvelle publication',
            'message' => $this->typeLabel().' · '.$this->publication->title,
            'url' => route('publications.show', $this->publication),
            'ui_type' => 'info',
            'publication_id' => $this->publication->id,
            'expires_at' => $this->expiresAt->toIso8601String(),
        ];
    }

    /**
     * @param  object{type?: string, data?: mixed, created_at?: mixed}  $notification
     */
    public static function isExpired(object $notification): bool
    {
        if (($notification->type ?? null) !== self::class) {
            return false;
        }

        $data = is_array($notification->data ?? null)
            ? $notification->data
            : (json_decode((string) ($notification->data ?? '{}'), true) ?: []);

        if (! empty($data['expires_at'])) {
            $expires = strtotime((string) $data['expires_at']);

            return $expires !== false && $expires <= time();
        }

        $created = strtotime((string) ($notification->created_at ?? ''));

        return $created !== false
            && $created < now()->subHours(self::TTL_HOURS)->getTimestamp();
    }

    protected function typeLabel(): string
    {
        return $this->publication->type === Publication::TYPE_PROMOTION
            ? 'Promotion'
            : 'Événement';
    }
}
