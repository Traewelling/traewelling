<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-events');
    }

    public function create(User $user): bool
    {
        return $user->can('create-events');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can('update-events');
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can('delete-events');
    }

    public function viewAnySuggestion(User $user): bool
    {
        return $user->can('accept-events') || $user->can('deny-events');
    }

    public function acceptSuggestion(User $user, EventSuggestion $suggestion): bool
    {
        if (!$user->can('accept-events')) {
            return false;
        }

        // Users may not accept their own suggestions unless they are admins.
        return $suggestion->user_id !== $user->id || $user->hasRole('admin');
    }

    public function denySuggestion(User $user): bool
    {
        return $user->can('deny-events');
    }
}
