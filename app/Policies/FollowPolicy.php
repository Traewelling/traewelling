<?php

namespace App\Policies;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FollowPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Follow $follow): bool {
        return $user->id == $follow->follow_id;
    }
}
