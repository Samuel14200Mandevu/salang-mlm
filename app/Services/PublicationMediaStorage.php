<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\PublicationMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class PublicationMediaStorage
{
    public function storeMany(Publication $publication, array $files): void
    {
        $order = (int) $publication->medias()->max('order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $order++;
            $this->storeOne($publication, $file, $order);
        }
    }

    public function storeOne(Publication $publication, UploadedFile $file, int $order): PublicationMedia
    {
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $type = str_starts_with((string) $mime, 'video/')
            ? PublicationMedia::TYPE_VIDEO
            : PublicationMedia::TYPE_PHOTO;

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = 'publications/'.$publication->id;
        $path = $file->storeAs($directory, $filename, 'public');

        $thumbnailPath = null;
        $duration = null;

        if ($type === PublicationMedia::TYPE_VIDEO) {
            $absolutePath = Storage::disk('public')->path($path);
            $thumbnailPath = $this->generateVideoThumbnail($publication->id, $absolutePath);
            $duration = $this->probeVideoDuration($absolutePath);
        }

        return PublicationMedia::create([
            'publication_id' => $publication->id,
            'type' => $type,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'duration' => $duration,
            'order' => $order,
        ]);
    }

    public function deleteFiles(PublicationMedia $media): void
    {
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }
        if ($media->thumbnail_path) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }
    }

    protected function generateVideoThumbnail(int $publicationId, string $absoluteVideoPath): ?string
    {
        $ffmpeg = config('media.ffmpeg.bin', 'ffmpeg');
        $thumbName = Str::uuid()->toString().'.jpg';
        $relativeThumb = 'publications/'.$publicationId.'/thumbnails/'.$thumbName;
        $absoluteThumb = Storage::disk('public')->path($relativeThumb);

        if (! is_dir(dirname($absoluteThumb))) {
            mkdir(dirname($absoluteThumb), 0755, true);
        }

        try {
            $process = new Process([
                $ffmpeg,
                '-y',
                '-ss',
                '00:00:01',
                '-i',
                $absoluteVideoPath,
                '-frames:v',
                '1',
                '-q:v',
                '2',
                $absoluteThumb,
            ]);
            $process->setTimeout(60);
            $process->run();

            if ($process->isSuccessful() && is_file($absoluteThumb)) {
                return $relativeThumb;
            }
        } catch (\Throwable $e) {
            Log::warning('Publication video thumbnail failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    protected function probeVideoDuration(string $absoluteVideoPath): ?int
    {
        $ffprobe = config('media.ffmpeg.ffprobe', 'ffprobe');

        try {
            $process = new Process([
                $ffprobe,
                '-v',
                'error',
                '-show_entries',
                'format=duration',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $absoluteVideoPath,
            ]);
            $process->setTimeout(30);
            $process->run();

            if ($process->isSuccessful()) {
                $seconds = (int) round((float) trim($process->getOutput()));

                return $seconds > 0 ? $seconds : null;
            }
        } catch (\Throwable $e) {
            Log::debug('Publication video duration probe skipped', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
