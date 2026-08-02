<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enum\TripSource;
use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function adminViewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Users may only edit manual trips they created themselves. Admins may edit any trip.
     */
    public function update(User $user, Trip $trip): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->can('disallow-manual-trips')) {
            return false;
        }

        return $trip->source === TripSource::USER && $trip->user_id === $user->id;
    }

    public function view(User $user, Trip $trip): bool
    {
        return $this->update($user, $trip);
    }

    /**
     * Any trip may be copied, except manual trips of other users.
     */
    public function copy(User $user, Trip $trip): bool
    {
        if ($user->can('disallow-manual-trips')) {
            return false;
        }

        return $trip->source !== TripSource::USER || $trip->user_id === $user->id;
    }
}
