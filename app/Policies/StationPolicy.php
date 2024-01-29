<?php

namespace App\Policies;

use App\Models\Station;
use App\Models\User;

class StationPolicy
{

    public function create(User $user): bool {
        return $user->can('create stations');
    }

    public function update(User $user, Station $station): bool {
        return $user->can('update stations');
    }

    public function delete(User $user, Station $station): bool {
        return $user->can('delete stations');
    }
}
