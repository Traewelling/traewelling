<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view the model.
     *
     * +---------+---------------+-----------+--------+
     * | Private | authenticated | following | result |
     * +---------+---------------+-----------+--------+
     * |       0 |             0 |         0 | 0      |
     * |       0 |             0 |         1 | 0      |
     * |       0 |             1 |         0 | 0      |
     * |       0 |             1 |         1 | 0      |
     * |       1 |             0 |         0 | 1      |
     * |       1 |             0 |         1 | -      |
     * |       1 |             1 |         0 | 1      |
     * |       1 |             1 |         1 | 0      |
     * +---------+---------------+-----------+--------+
     *
     * @param User|null $user
     * @param User      $model
     *
     * @return Response
     * @test check table above and test
     * @todo implement blocked and muted
     */
    public function view(?User $user, User $model): Response {
        if ($user->is($model)) {
            return Response::allow();
        }
        if ($model->private_profile && $user !== null && $model->followers->contains('id', $user->id)) {
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function update(User $user, User $model): bool {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function delete(User $user, User $model): bool {
        return $user->id === $model->id;
    }

}
