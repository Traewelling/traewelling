<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class SettingsController extends Controller
{
    public static function updateSettings(array $fields, User $user = null): Authenticatable|null|User {
        if ($user === null) {
            $user = auth()->user();
        }
        $user->update($fields);

        return $user;
    }
}
