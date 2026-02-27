<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager as Image;

abstract class SettingsController extends Controller
{
    /**
     * @throws RateLimitExceededException if the user has exceeded the rate limit for sending verification emails
     */
    public static function updateSettings(array $fields, ?User $user = null): Authenticatable|null|User
    {
        if ($user === null) {
            $user = auth()->user();
        }

        // todo: remove, once new mail endpoint is implemented everywhere
        if (in_array('email', $fields) && $fields['email'] !== $user->email) {
            self::updateMail($fields['email'], $user);
        }
        unset($fields['email']);

        if (array_key_exists('experimental', $fields)) {
            if ($fields['experimental'] && !$user->hasRole('open-beta')) {
                auth()->user()->assignRole('open-beta');
            } elseif (!$fields['experimental'] && $user->hasRole('open-beta')) {
                auth()->user()->removeRole('open-beta');
            }
        }

        if (array_key_exists('profileLinks', $fields)) {
            self::updateProfileLinks($fields['profileLinks'], $user);
            unset($fields['profileLinks']);
        }

        // map api fields to model fields for update
        // this is necessary because the API uses different field names than the model
        // don't add your field if your api field is identical to the model field
        $mappings = [
            'displayName' => 'name',
            'friendCheckin' => 'friend_checkin',
            'privateProfile' => 'private_profile',
            'likesEnabled' => 'likes_enabled',
            'pointsEnabled' => 'points_enabled',
            'preventIndex' => 'prevent_index',
            'privacyHideDays' => 'privacy_hide_days',
            'defaultStatusVisibility' => 'default_status_visibility',
            'mapProvider' => 'mapprovider',
            'dataProvider' => 'data_provider',
        ];
        foreach ($mappings as $apiField => $modelField) {
            if (array_key_exists($apiField, $fields)) {
                $fields[$modelField] = $fields[$apiField];
                unset($fields[$apiField]);
            }
        }

        $user->update($fields);

        if (array_key_exists('mastodonVisibility', $fields)) {
            $user->socialProfile->update(['mastodon_visibility' => $fields['mastodonVisibility']]);
        }

        return $user;
    }

    public static function updateMail(string $newMail, User $user): User
    {
        // todo: #4459 implement notification on mail change

        $user->email = $newMail;
        $user->email_verified_at = null;
        $user->save();
        $user->sendEmailVerificationNotification();

        return $user;
    }

    private static function updateProfileLinks(array $profileLinks, User $user): void
    {
        $user->profileLinks()->delete();
        foreach ($profileLinks as $link) {

            $user->profileLinks()->create([
                'name' => $link['name'],
                'url' => $link['url'],
            ]);
        }
    }

    public static function deleteProfilePicture(User $user): bool
    {
        if ($user->avatar !== null) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
            $user->update(['avatar' => null]);

            return true;
        }

        return false;
    }

    public static function updateProfilePicture(string $avatar): bool
    {
        $filename = strtr(':userId_:time.png', [':userId' => Auth::user()->id, ':time' => time()]);

        (new Image(new Driver()))->read($avatar)
            ->resize(400, 400)
            ->save(public_path('/uploads/avatars/' . $filename));

        if (auth()->user()->avatar) {
            File::delete(public_path('/uploads/avatars/' . auth()->user()->avatar));
        }

        auth()->user()->update([
            'avatar' => $filename,
        ]);

        return true;
    }
}
