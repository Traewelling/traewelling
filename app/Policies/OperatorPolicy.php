<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Operator $operator): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Operator $operator): bool
    {
        return $user->hasRole('admin');
    }
}
