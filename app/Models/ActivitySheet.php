<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivitySheet extends Model
{
    protected $fillable = [
        'activity_date',
        'title',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ActivityEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function findForDate(\DateTimeInterface|string $date): ?self
    {
        $day = \Illuminate\Support\Carbon::parse($date)->toDateString();

        return static::query()->whereDate('activity_date', $day)->first();
    }

    public static function forDate(\DateTimeInterface|string $date, ?User $creator = null): self
    {
        $day = \Illuminate\Support\Carbon::parse($date)->toDateString();

        return static::query()->firstOrCreate(
            ['activity_date' => $day],
            [
                'title' => 'Registre du '.$day,
                'created_by' => $creator?->id,
            ]
        );
    }

    /** Feuille non persistée pour l’affichage d’un jour encore vide. */
    public static function draftForDate(\DateTimeInterface|string $date): self
    {
        $day = \Illuminate\Support\Carbon::parse($date)->toDateString();
        $sheet = new static([
            'activity_date' => $day,
            'title' => 'Registre du '.$day,
        ]);
        $sheet->setRelation('entries', collect());

        return $sheet;
    }
}
