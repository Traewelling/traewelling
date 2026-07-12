<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Format;
use Intervention\Image\ImageManager as Image;

class ProfilePictureService
{
    public function getUrlForUserId(int $userId): string
    {
        $user = User::where('id', $userId)->first();

        return $this->getUrl(user: $user);
    }

    public function getUrl(User $user): string
    {
        // Avatar is not found or user is blocked -> show default avatar
        if ($user->avatar === null || Gate::denies('view', $user)) {
            return route('profile.picture', ['username' => $user->username]);
        }

        return url('/uploads/avatars/' . $user->avatar);
    }

    public function generateProfilePicture(User $user): array
    {
        $publicPath = public_path('/uploads/avatars/' . $user->avatar);

        if ($user->avatar === null
            || !file_exists($publicPath)
            || Gate::denies('view', $user) // e.g. blocked users always get a default picture
        ) {
            return [
                'picture' => $this->generateDefaultAvatar($user),
                'extension' => 'png',
            ];
        }

        try {
            $ext = pathinfo($publicPath, PATHINFO_EXTENSION);
            $picture = File::get($publicPath);

            return [
                'picture' => $picture,
                'extension' => $ext,
            ];
        } catch (Exception $exception) {
            report($exception);

            return [
                'picture' => $this->generateDefaultAvatar($user),
                'extension' => 'png',
            ];
        }
    }

    public function generateBackgroundHash(string $username): string
    {
        $hash = 0;
        $usernameLength = strlen($username);
        for ($i = 0; $i < $usernameLength; $i++) {
            $securedHash = ord(substr($username, $i, 1)) + (($hash << 5) - $hash);
            if ($securedHash <= 0) {
                break;
            }
            $hash = $securedHash;
        }

        return str_pad(dechex($hash & 0x00FFFFFF), 6, '0');
    }

    public function update(User $user, string $avatar): bool
    {
        $filename = strtr(':userId_:time.png', [':userId' => $user->id, ':time' => time()]);

        try {
            $image = Image::usingDriver(new Driver())->decode($avatar);
        } catch (DecoderException) {
            // unsupported format (e.g. SVG) or corrupt image data
            return false;
        }

        $image->resize(400, 400)
            ->save(public_path('/uploads/avatars/' . $filename));

        if ($user->avatar) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
        }

        $user->update(['avatar' => $filename]);

        return true;
    }

    public function delete(User $user): bool
    {
        if ($user->avatar === null) {
            return false;
        }

        File::delete(public_path('/uploads/avatars/' . $user->avatar));
        $user->update(['avatar' => null]);

        return true;
    }

    private function generateDefaultAvatar(User $user): string
    {
        $hex = $this->generateBackgroundHash($user->username);

        return Image::usingDriver(new Driver())->createImage(512, 512)
            ->fill($hex)
            ->insert(public_path('/img/user.png'))
            ->encodeUsingFormat(Format::PNG)
            ->toString();
    }
}
