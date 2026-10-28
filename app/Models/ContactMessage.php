<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'handled_by',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function markRead(?User $user = null): void
    {
        if ($this->status === self::STATUS_ARCHIVED) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_READ,
            'read_at' => $this->read_at ?? now(),
            'handled_by' => $user?->id ?? $this->handled_by,
        ])->save();
    }

    public function archive(?User $user = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_ARCHIVED,
            'read_at' => $this->read_at ?? now(),
            'handled_by' => $user?->id ?? $this->handled_by,
        ])->save();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_READ => 'Lu',
            self::STATUS_ARCHIVED => 'Archivé',
            default => 'Nouveau',
        };
    }
}
