<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\PublicationMedia;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CareerQualificationPublicationsSync
{
    public function sync(): void
    {
        $author = User::role('admin')->first()
            ?? User::role('it_manager')->first()
            ?? User::query()->first();

        if (! $author) {
            return;
        }

        foreach (config('career_qualification_prizes.levels', []) as $level => $data) {
            $pubMeta = $data['publication'] ?? null;
            if (! is_array($pubMeta) || empty($pubMeta['title'])) {
                continue;
            }

            $publication = Publication::query()->firstOrCreate(
                [
                    'title' => $pubMeta['title'],
                    'type' => Publication::TYPE_PROMOTION,
                ],
                [
                    'user_id' => $author->id,
                    'description' => $pubMeta['description'] ?? '',
                    'start_date' => null,
                    'end_date' => null,
                    'is_published' => true,
                ]
            );

            if ($publication->wasRecentlyCreated === false) {
                $publication->fill([
                    'description' => $pubMeta['description'] ?? $publication->description,
                    'is_published' => true,
                ]);
                if ($publication->isDirty()) {
                    $publication->save();
                }
            }

            $imageRelative = $data['image'] ?? null;
            if (! $imageRelative) {
                continue;
            }

            $source = public_path($imageRelative);
            if (! File::isFile($source)) {
                continue;
            }

            $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
            $storagePath = 'publications/career/level-'.(int) $level.'.'.$extension;
            $disk = Storage::disk('public');

            $shouldCopy = ! $disk->exists($storagePath);
            if (! $shouldCopy) {
                $sourceMtime = File::lastModified($source);
                $storedMtime = $disk->lastModified($storagePath);
                $shouldCopy = $sourceMtime > $storedMtime
                    || File::size($source) !== $disk->size($storagePath);
            }

            if ($shouldCopy) {
                $disk->put($storagePath, File::get($source));
            }

            $mediaPayload = [
                'type' => PublicationMedia::TYPE_PHOTO,
                'original_name' => basename($source),
                'mime_type' => File::mimeType($source) ?: 'image/jpeg',
                'size' => File::size($source),
                'order' => 0,
            ];

            $media = PublicationMedia::query()->firstOrCreate(
                [
                    'publication_id' => $publication->id,
                    'path' => $storagePath,
                ],
                $mediaPayload
            );

            if (! $media->wasRecentlyCreated && $shouldCopy) {
                $media->fill($mediaPayload);
                if ($media->isDirty()) {
                    $media->save();
                }
            }
        }
    }
}
