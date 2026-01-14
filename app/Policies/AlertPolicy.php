<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('alerts.manage');
    }

    public function update(User $user, Alert $alert): bool
    {
        return $user->can('alerts.manage');
    }

    public function delete(User $user, Alert $alert): bool
    {
        return $user->can('alerts.manage');
    }
}
