<?php

namespace App\Policies;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Controllers\Backend\User\BlockController;
use App\Http\Controllers\Backend\User\FollowController;
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
     */
    public function view(?User $user, User $model): Response {
        if ($user === null) {
            return $model->private_profile ? Response::deny(__('profile.private-profile-text')) : Response::allow();
        }
        if ($user->is($model)) {
            return Response::allow();
        }
        if ($model->private_profile && !$model->followers->contains('user_id', $user->id)) {
            return Response::deny(__('profile.private-profile-text'));
        }
        if ($user->mutedUsers->contains('id', $model->id)) {
            return Response::deny(__('user.muted.heading'));
        }
        if (BlockController::isBlocked($model, $user)) {
            return Response::deny(__('profile.youre-blocked-text'));
        }
        if (BlockController::isBlocked($user, $model)) {
            return Response::deny(__('user.blocked.heading'));
        }
        return Response::allow();
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
        return $user->id === $model->id || $user->hasRole('admin');
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

    /**
     * Check if user can check in another user
     *
     * @param User $user
     * @param User $userToCheckin
     *
     * @return bool
     */
    public function checkin(User $user, User $userToCheckin): bool {
        if ($user->is($userToCheckin)) {
            return true;
        }
        if ($userToCheckin->friend_checkin === FriendCheckinSetting::FORBIDDEN) {
            return false;
        }
        if ($userToCheckin->friend_checkin === FriendCheckinSetting::FRIENDS) {
            return FollowController::isFollowingEachOther($user, $userToCheckin);
        }
        if ($userToCheckin->friend_checkin === FriendCheckinSetting::LIST) {
            return $userToCheckin->trustedUsers->contains('trusted_id', $user->id);
        }
        return false;
    }
}
