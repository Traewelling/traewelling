<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

abstract class SettingsController extends Controller
{
    /** @todo Implement privacy_hide_days */
    public static function updateSettings(array $fields, User $user = null): Authenticatable|null|User {
        if ($user === null) {
            $user = auth()->user();
        }

        if (in_array('email', $fields, true) && $fields['email'] !== $user->email) {
            $fields['email_verified_at'] = null;
            $fields['email']             = strtolower($fields['email']);
            try {
                $user->sendEmailVerificationNotification();
            } catch (RateLimitExceededException) {
                //do nothing... the user can resend the mail later
            }
        }

        $user->update($fields);

        return $user;
    }

    public static function deleteProfilePicture(User $user): bool {
        if ($user?->avatar !== null) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
            $user->update(['avatar' => null]);
            return true;
        }
        return false;
    }

    public static function updateProfilePicture(string $avatar): bool {
        $filename = strtr(':userId_:time.png', [':userId' => Auth::user()->id, ':time' => time()]);

        Image::make($avatar)->resize(300, 300)
             ->save(public_path('/uploads/avatars/' . $filename));

        if (auth()->user()->avatar) {
            File::delete(public_path('/uploads/avatars/' . auth()->user()->avatar));
        }

        auth()->user()->update([
                                   'avatar' => $filename
                               ]);

        return true;
    }
}
