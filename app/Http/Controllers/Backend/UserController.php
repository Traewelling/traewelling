<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMute;
use Exception;
use InvalidArgumentException;

class UserController extends Controller
{

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
}
