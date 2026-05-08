<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class TripPolicy
{
    public function adminViewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
