<?php

namespace App\Http\Controllers\Backend\Social;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Laravel\Socialite\Contracts\User as SocialiteUser;

abstract class SocialController extends Controller
{

    public static function getDisplayName(SocialiteUser $socialiteUser): string {
        if (trim($socialiteUser->getName()) === '') {
            return $socialiteUser->getNickname();
        }
        return $socialiteUser->getName();
    }

    /**
     * @throws Exception
     */
    public static function getUniqueUsername(string $username): string {
        $existingUser = User::where('username', $username)->first();
        $errorCount   = 0;
        while ($errorCount < 10 && $existingUser !== null) {
            $username     .= random_int(1, 10);
            $existingUser = User::where('username', $username)->first();
            $errorCount++;
        }
        return $username;
    }
}
