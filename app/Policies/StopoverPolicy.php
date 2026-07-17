<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Stopover;
use App\Models\User;

class StopoverPolicy
{
    public function delete(User $user, Stopover $stopover): bool
    {
        return $user->hasRole('admin');
    }
}
