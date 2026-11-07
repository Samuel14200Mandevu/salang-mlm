<?php

namespace App\Support;

use App\Helpers\NotificationHelper;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationPresenter
{
    public const CATEGORIES = [
        'all' => 'Toutes',
        'announcement' => 'Annonces',
        'finance' => 'Finances',
        'account' => 'Compte',
        'network' => 'Réseau',
        'orders' => 'Commandes',
        'other' => 'Autres',
    ];

    public function __construct(protected DatabaseNotification $notification) {}

    public static function make(DatabaseNotification $notification): self
    {
        return new self($notification);
    }

    public function data(): array
    {
        return is_array($this->notification->data) ? $this->notification->data : [];
    }

    public function type(): string
    {
        return (string) ($this->data()['type'] ?? class_basename((string) $this->notification->type));
    }

    public function title(): string
    {
        $data = $this->data();

        return (string) ($data['title']
            ?? NotificationHelper::getNotificationPrefix($this->type())
            ?? 'Notification');
    }

    public function message(): string
    {
        return NotificationHelper::getNotificationMessage($this->data());
    }

    public function category(): string
    {
        $data = $this->data();
        $type = $this->type();
        $category = (string) ($data['category'] ?? '');

        if ($category !== '' && array_key_exists($category, self::CATEGORIES)) {
            return $category;
        }

        if (Str::contains($type, ['staff_announcement', 'announcement'])) {
            return 'announcement';
        }
        if (Str::contains($type, ['commission', 'payment', 'withdrawal', 'wallet', 'deposit'])) {
            return 'finance';
        }
        if (Str::contains($type, ['kyc', 'activation', 'welcome', 'password', 'action_feedback'])) {
            return 'account';
        }
        if (Str::contains($type, ['downline', 'rank', 'network'])) {
            return 'network';
        }
        if (Str::contains($type, ['package', 'order', 'purchase'])) {
            return 'orders';
        }

        return 'other';
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category()] ?? self::CATEGORIES['other'];
    }

    public function actionUrl(): ?string
    {
        $data = $this->data();
        $url = $data['url'] ?? $data['action_url'] ?? null;

        if (! is_string($url) || $url === '' || $url === '#') {
            return null;
        }

        return $url;
    }

    public function isUnread(): bool
    {
        return $this->notification->read_at === null;
    }

    public function tone(): string
    {
        $data = $this->data();
        $type = $this->type();
        $ui = (string) ($data['ui_type'] ?? $data['tone'] ?? '');

        if ($ui === 'danger' || Str::contains($type, ['reject', 'failed'])) {
            return 'danger';
        }
        if ($ui === 'warning') {
            return 'warning';
        }
        if ($ui === 'success' || Str::contains($type, ['paid', 'approved', 'verified', 'welcome', 'received'])) {
            return 'success';
        }

        return 'info';
    }

    /**
     * Champs lisibles pour la page détail (hors métadonnées internes).
     *
     * @return list<array{label: string, value: string}>
     */
    public function detailRows(): array
    {
        $data = $this->data();
        $hidden = [
            'type', 'category', 'title', 'message', 'url', 'action_url',
            'ui_type', 'tone', 'action', 'icon',
        ];

        $labels = [
            'visitor_name' => 'Visiteur',
            'visitor_email' => 'Email',
            'subject' => 'Sujet',
            'contact_message_id' => 'N° message',
            'member_name' => 'Membre',
            'member_email' => 'Email membre',
            'member_id' => 'ID membre',
            'amount' => 'Montant',
            'method' => 'Méthode',
            'transaction_id' => 'Transaction',
            'provider' => 'Fournisseur',
            'failure_count' => 'Échecs',
            'new_rank' => 'Nouveau rang',
            'package_name' => 'Package',
            'price' => 'Prix',
            'commission_type' => 'Type de commission',
            'downline_name' => 'Filleul',
            'level' => 'Niveau',
            'reason' => 'Motif',
            'sponsor_name' => 'Parrain',
        ];

        $rows = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $hidden, true)) {
                continue;
            }
            if (is_array($value) || is_object($value) || $value === null || $value === '') {
                continue;
            }

            $display = is_bool($value)
                ? ($value ? 'Oui' : 'Non')
                : (string) $value;

            if (in_array($key, ['amount', 'price'], true) && is_numeric($value)) {
                $display = NotificationHelper::formatAmount((float) $value);
            }

            $rows[] = [
                'label' => $labels[$key] ?? Str::of($key)->replace('_', ' ')->title()->toString(),
                'value' => $display,
            ];
        }

        return $rows;
    }
}
