<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityEntry extends Model
{
    public const INVITE_FORMATION = 'formation';

    public const INVITE_OTHER = 'autre';

    protected $fillable = [
        'activity_sheet_id',
        'member_id',
        'member_code',
        'participant_name',
        'participant_nom',
        'participant_postnom',
        'participant_prenom',
        'participant_phone',
        'participant_birth_date',
        'participant_gender',
        'participant_address',
        'participant_email',
        'participant_profession',
        'participant_identity_number',
        'sort_order',
        'invite_type',
        'formation_done',
        'adhesion_done',
        'adhesion_user_id',
        'adhesion_at',
        'consultation_done',
        'consultation_id',
        'consultation_at',
        'purchase_done',
        'order_id',
        'purchases_amount',
        'net_sales_amount',
        'validated_amount',
        'purchase_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'participant_birth_date' => 'date',
            'formation_done' => 'boolean',
            'adhesion_done' => 'boolean',
            'consultation_done' => 'boolean',
            'purchase_done' => 'boolean',
            'adhesion_at' => 'datetime',
            'consultation_at' => 'datetime',
            'purchase_at' => 'datetime',
            'purchases_amount' => 'decimal:2',
            'net_sales_amount' => 'decimal:2',
            'validated_amount' => 'decimal:2',
        ];
    }

    public function genderLabel(): string
    {
        return match ($this->participant_gender) {
            'male' => 'Masculin',
            'female' => 'Féminin',
            default => '—',
        };
    }

    public static function composeFullName(?string $nom, ?string $postnom, ?string $prenom): string
    {
        return trim(collect([$nom, $postnom, $prenom])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(' '));
    }

    public function displayName(): string
    {
        $composed = static::composeFullName(
            $this->participant_nom,
            $this->participant_postnom,
            $this->participant_prenom,
        );

        return $composed !== '' ? $composed : (string) $this->participant_name;
    }

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(ActivitySheet::class, 'activity_sheet_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function adhesionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adhesion_user_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePendingAdhesion(Builder $query): Builder
    {
        return $query->where('adhesion_done', false);
    }

    public function inviteLabel(): string
    {
        return match ($this->invite_type) {
            self::INVITE_FORMATION => 'Formation',
            default => 'Autre',
        };
    }

    public function normalizedParticipantName(): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $this->participant_name) ?? ''));
    }
}
