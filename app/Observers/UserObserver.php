<?php

namespace App\Observers;

use App\Models\User;

/**
 * UserObserver - VERSION VIDE (le modèle User gère tout dans booted()).
 *
 * Les événements created/updated/deleted sont gérés dans le modèle User
 * qui dispatche RecalculateAfterPVImport.
 */
class UserObserver
{
    public function created(User $user): void
    {
        // Géré par User::booted()
    }

    public function updated(User $user): void
    {
        // Géré par User::booted()
    }

    public function saved(User $user): void
    {
        // Géré par User::booted()
    }

    public function deleted(User $user): void
    {
        // Géré par User::booted()
    }
}