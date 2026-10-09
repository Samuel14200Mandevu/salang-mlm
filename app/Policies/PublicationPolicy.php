<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\PublicationMedia;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Publication $publication): bool
    {
        if ($user->hasSupervisionAccess()) {
            return true;
        }

        return $publication->is_published;
    }

    public function create(User $user): bool
    {
        return $user->hasSupervisionAccess();
    }

    public function update(User $user, Publication $publication): bool
    {
        return $user->hasSupervisionAccess();
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $user->hasSupervisionAccess();
    }

    public function deleteMedia(User $user, PublicationMedia $media): bool
    {
        return $user->hasSupervisionAccess();
    }
}
