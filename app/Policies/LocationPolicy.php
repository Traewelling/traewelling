<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool {
        return $user->role >= 5;
    }

    public function update(User $user, Location $location): bool {
        return $user->role >= 5;
    }

    public function delete(User $user, Location $location): bool {
        return $user->role >= 5;
    }
}
