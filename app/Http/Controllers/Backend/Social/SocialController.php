<?php

namespace App\Http\Controllers\Backend\Social;

use App\Http\Controllers\Controller;
use App\Models\User;

abstract class SocialController extends Controller
{

    public static function getDisplayName(\Laravel\Socialite\Contracts\User $socialiteUser): string {
        if (trim($socialiteUser->getName()) == '') {
            return $socialiteUser->getNickname();
        }
        return $socialiteUser->getName();
    }

    public static function getUniqueUsername(string $username): string {
        $existingUser = User::where('username', $username)->first();
        $errorCount   = 0;
        while ($errorCount < 10 && $existingUser !== null) {
            $username     = $username . rand(1, 10);
            $existingUser = User::where('username', $username)->first();
            $errorCount++;
        }
        return $username;
    }
}
