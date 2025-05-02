<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{

    public function create(User $user): bool {
        return $user->can('create-events');
    }

    public function update(User $user, Event $event): bool {
        return $user->can('update-events');
    }

    public function delete(User $user, Event $event): bool {
        return $user->can('delete-events');
    }
}
