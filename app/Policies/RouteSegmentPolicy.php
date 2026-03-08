<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RouteSegment;
use App\Models\User;

class RouteSegmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, RouteSegment $routeSegment): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, RouteSegment $routeSegment): bool
    {
        return $user->hasRole('admin');
    }
}
