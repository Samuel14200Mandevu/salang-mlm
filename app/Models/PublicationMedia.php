<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PublicationMedia extends Model
{
    protected $table = 'publication_medias';

    public const TYPE_PHOTO = 'photo';

    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'publication_id',
        'type',
        'path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'size',
        'duration',
        'order',
    ];

    protected $casts = [
        'size' => 'integer',
        'duration' => 'integer',
        'order' => 'integer',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function scopePhotos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PHOTO);
    }

    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        if ($this->type === self::TYPE_VIDEO) {
            return asset('images/video-placeholder.jpg');
        }

        return null;
    }
}
