<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RouteSegment;
use App\Models\User;

class RouteSegmentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: open up to contributors once route segment editing leaves experimental state
        return $user->hasRole('admin');
    }

    public function view(User $user, RouteSegment $routeSegment): bool
    {
        // TODO: open up to contributors once route segment editing leaves experimental state
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        // TODO: open up to contributors once route segment editing leaves experimental state
        return $user->hasRole('admin');
    }

    public function update(User $user, RouteSegment $routeSegment): bool
    {
        // TODO: open up to contributors once route segment editing leaves experimental state
        return $user->hasRole('admin');
    }

    public function delete(User $user, RouteSegment $routeSegment): bool
    {
        // TODO: open up to contributors once route segment editing leaves experimental state
        return $user->hasRole('admin');
    }
}
