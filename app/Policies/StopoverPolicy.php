<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class StopoverPolicy
{
    public function create(User $user, Trip $trip): bool
    {
        return Gate::forUser($user)->allows('update', $trip);
    }

    public function update(User $user, Stopover $stopover): bool
    {
        return $stopover->trip !== null && Gate::forUser($user)->allows('update', $stopover->trip);
    }

    public function delete(User $user, Stopover $stopover): bool
    {
        return $user->hasRole('admin') || $this->update($user, $stopover);
    }
}
