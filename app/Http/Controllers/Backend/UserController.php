<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMute;
use Error;
use Exception;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class UserController extends Controller
{
    /**
     * @param User $user
     *
     * @return bool
     * @throws Error
     */
    public static function deleteUserAccount(User $user): bool {
        SettingsController::deleteProfilePicture(user: $user);

        DatabaseNotification::where([
                                        'notifiable_id'   => $user->id,
                                        'notifiable_type' => get_class($user)
                                    ])->delete();

        if ($user->delete()) {
            return true;
        }
        throw new Error();
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
