<?php

namespace App\Policies;

use App\Models\QuestionConversation;
use App\Models\User;

class QuestionConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, QuestionConversation $conversation): bool
    {
        return $user->hasSupervisionAccess() || $conversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, QuestionConversation $conversation): bool
    {
        return $user->hasRole('admin');
    }

    public function answer(User $user, QuestionConversation $conversation): bool
    {
        return $user->hasSupervisionAccess();
    }

    public function message(User $user, QuestionConversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }
}
