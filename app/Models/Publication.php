<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    public const TYPE_EVENT = 'event';

    public const TYPE_PROMOTION = 'promotion';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'start_date',
        'end_date',
        'is_published',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medias(): HasMany
    {
        return $this->hasMany(PublicationMedia::class)->orderBy('order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeEvents(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EVENT);
    }

    public function scopePromotions(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PROMOTION);
    }

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->published()
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            });
    }
}
