<?php

namespace App\Policies;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FollowPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user) {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Follow $follow
     * @return mixed
     */
    public function view(User $user, Follow $follow) {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user) {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Follow $follow
     * @return mixed
     */
    public function update(User $user, Follow $follow) {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Follow $follow
     * @return mixed
     */
    public function delete(User $user, Follow $follow): bool {
        return $user->id == $follow->follow_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Follow $follow
     * @return mixed
     */
    public function restore(User $user, Follow $follow) {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Follow $follow
     * @return mixed
     */
    public function forceDelete(User $user, Follow $follow) {
        //
    }
}
