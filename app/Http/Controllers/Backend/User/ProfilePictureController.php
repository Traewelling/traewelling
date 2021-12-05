<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;

abstract class ProfilePictureController extends Controller
{

    public static function getUrl(User $user): string {
        if ($user->avatar === null) {
            //Return default route to generate users avatar with matching color
            return route('account.showProfilePicture', ['username' => $user->username]);
        }
        return url('/uploads/avatars/' . $user->avatar);
    }
}
