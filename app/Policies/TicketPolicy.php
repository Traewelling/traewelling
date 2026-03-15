<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('closed-beta');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id && $user->hasRole('closed-beta');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('closed-beta');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id && $user->hasRole('closed-beta');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id && $user->hasRole('closed-beta');
    }
}
