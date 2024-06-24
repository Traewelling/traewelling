<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager as Image;
use Intervention\Image\Drivers\Gd\Driver;

abstract class ProfilePictureController extends Controller
{

    /**
     * To improve performance, you shouldn't use this method.
     * Better use the method `getUrl` when you have the user model.
     *
     * @param int $userId
     *
     * @return string
     */
    public static function getUrlForUserId(int $userId): string {
        $user = User::where('id', $userId)->first();
        return self::getUrl(user: $user);
    }

    public static function getUrl(User $user): string {
        // Avatar is not found or user is blocked -> show default avatar
        if ($user->avatar === null || Gate::denies('view', $user)) {
            //Return default route to generate users avatar with matching color
            return route('profile.picture', ['username' => $user->username]);
        }
        return url('/uploads/avatars/' . $user->avatar);
    }

    public static function generateProfilePicture(User $user): array {
        $publicPath = public_path('/uploads/avatars/' . $user->avatar);

        if ($user->avatar === null
            || !file_exists($publicPath)
            || Gate::denies('view', $user) // e.g. Blocked users always get a default picture
        ) {
            return [
                'picture'   => self::generateDefaultAvatar($user),
                'extension' => 'png'
            ];
        }

        try {
            $ext     = pathinfo($publicPath, PATHINFO_EXTENSION);
            $picture = File::get($publicPath);
            return [
                'picture'   => $picture,
                'extension' => $ext
            ];
        } catch (Exception $exception) {
            report($exception);
            return [
                'picture'   => self::generateDefaultAvatar($user),
                'extension' => 'png'
            ];
        }
    }

    public static function generateBackgroundHash(string $username): string {
        $hash           = 0;
        $usernameLength = strlen($username);
        for ($i = 0; $i < $usernameLength; $i++) {
            $securedHash = ord(substr($username, $i, 1)) + (($hash << 5) - $hash);
            if ($securedHash <= 0) {
                break;
            }
            $hash = $securedHash;
        }

        return str_pad(dechex($hash & 0x00FFFFFF), 6, "0");
    }

    /**
     * @param User $user
     *
     * @return string Encoded PNG Image
     */
    private static function generateDefaultAvatar(User $user): string {
        $hex = self::generateBackgroundHash($user->username);

        return (new Image(new Driver()))->create(512, 512)
                    ->fill($hex)
                    ->place(public_path('/img/user.png'))
                    ->encode(new PngEncoder())
                    ->toString();
    }
}
