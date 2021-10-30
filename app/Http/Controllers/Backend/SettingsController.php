<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public static function updateSettings(array $fields, User $user = null): Authenticatable|null|User {
        if ($user === null) {
            $user = auth()->user();
        }
        $user->update($fields);

        return $user;
    }

    public static function deleteProfilePicture(): bool {
        $user = auth()->user();

        if ($user?->avatar !== null) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
            $user->update(['avatar' => null]);
            return true;
        }

        return false;
    }
}
