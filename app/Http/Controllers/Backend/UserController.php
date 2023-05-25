<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\UserAlreadyBlockedException;
use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotBlockedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Like;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;
use Exception;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

abstract class UserController extends Controller
{
    /**
     * @param User $user
     *
     * @return bool|null
     */
    public static function deleteUserAccount(User $user): ?bool {
        SettingsController::deleteProfilePicture(user: $user);

        DatabaseNotification::where([
                                        'notifiable_id'   => $user->id,
                                        'notifiable_type' => get_class($user)
                                    ])->delete();

        return $user->delete();
    }

    /**
     * @param User $user
     * @param User $userToBeBlocked
     *
     * @return bool
     * @throws UserAlreadyBlockedException
     * @throws InvalidArgumentException
     */
    public static function blockUser(User $user, User $userToBeBlocked): bool {
        if ($user->blockedUsers->contains('id', $userToBeBlocked->id)) {
            throw new UserAlreadyBlockedException();
        }
        if ($user->is($userToBeBlocked)) {
            throw new InvalidArgumentException();
        }
        try {
            Follow::where('user_id', $userToBeBlocked->id)->where('follow_id', $user->id)->delete();
            Follow::where('user_id', $user->id)->where('follow_id', $userToBeBlocked->id)->delete();

            Like::where('user_id', $user->id)->whereIn('status_id', $userToBeBlocked->statuses()->select('id'))->delete();
            Like::where('user_id', $userToBeBlocked->id)->whereIn('status_id', $user->statuses()->select('id'))->delete();

            UserBlock::create([
                                  'user_id'    => $user->id,
                                  'blocked_id' => $userToBeBlocked->id
                              ]);
            $user->load('blockedUsers');
            return true;
        } catch (Exception $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * @param User $user
     * @param User $userToBeUnblocked
     *
     * @return bool
     * @throws UserNotBlockedException
     */
    public static function unblockUser(User $user, User $userToBeUnblocked): bool {
        if (!$user->blockedUsers->contains('id', $userToBeUnblocked->id)) {
            throw new UserNotBlockedException();
        }

        $queryCount = UserBlock::where('user_id', $user->id)->where('blocked_id', $userToBeUnblocked->id)->delete();
        $user->load('blockedUsers');
        return $queryCount === 1;
    }

    /**
     * @param User $user
     * @param User $userToBeMuted
     *
     * @return bool
     * @throws UserAlreadyMutedException
     * @throws InvalidArgumentException
     */
    public static function muteUser(User $user, User $userToBeMuted): bool {
        if ($user->mutedUsers->contains('id', $userToBeMuted->id)) {
            throw new UserAlreadyMutedException();
        }
        if ($user->is($userToBeMuted)) {
            throw new InvalidArgumentException();
        }
        try {
            UserMute::create([
                                 'user_id'  => $user->id,
                                 'muted_id' => $userToBeMuted->id
                             ]);
            $user->load('mutedUsers');
            return true;
        } catch (Exception $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * @param User $user
     * @param User $userToBeUnmuted
     *
     * @return bool
     * @throws UserNotMutedException
     */
    public static function unmuteUser(User $user, User $userToBeUnmuted): bool {
        if (!$user->mutedUsers->contains('id', $userToBeUnmuted->id)) {
            throw new UserNotMutedException();
        }

        $queryCount = UserMute::where('user_id', $user->id)->where('muted_id', $userToBeUnmuted->id)->delete();
        $user->load('mutedUsers');
        return $queryCount === 1;
    }

    /**
     * @param string|null $searchQuery
     *
     * @return Paginator
     * @throws InvalidArgumentException
     */
    public static function searchUser(?string $searchQuery): Paginator {
        $validator = Validator::make(['searchQuery' => $searchQuery], ['searchQuery' => ['required', 'alpha_num']]);
        if ($validator->fails()) {
            throw new InvalidArgumentException();
        }

        return User::where(
            'name', 'like', "%{$searchQuery}%"
        )->orWhere(
            'username', 'like', "%{$searchQuery}%"
        )->simplePaginate(10);
    }
}
