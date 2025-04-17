<?php

namespace App\Policies;

use App\Models\HafasOperator;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperatorPolicy
{

    public function viewAny(User $user): bool {
        return $user->hasRole('admin');
    }

    public function update(User $user, HafasOperator $hafasOperator): bool {
        return $user->hasRole('admin');
    }

    public function delete(User $user, HafasOperator $hafasOperator): bool {
        return $user->hasRole('admin');
    }
}
