<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Backend\User\ProfilePictureController as ProfilePictureBackend;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;

class ProfilePictureController extends Controller
{

    public function generateProfilePicture($username): Response {
        $user = User::where('username', $username)->firstOrFail();

        $profilePicture = ProfilePictureBackend::generateProfilePicture($user);

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/' . $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }
}
