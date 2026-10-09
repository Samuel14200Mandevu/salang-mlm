<?php

namespace App\Observers;

use App\Models\PublicationMedia;
use App\Services\PublicationMediaStorage;

class PublicationMediaObserver
{
    public function __construct(
        protected PublicationMediaStorage $mediaStorage
    ) {}

    public function deleted(PublicationMedia $media): void
    {
        $this->mediaStorage->deleteFiles($media);
    }
}
