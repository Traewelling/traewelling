<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfilePictureService;
use Illuminate\Http\Response;

class ProfilePictureController extends Controller
{
    public function __construct(private readonly ProfilePictureService $profilePictureService) {}

    public function generateProfilePicture($username): Response
    {
        $user = User::where('username', $username)->firstOrFail();

        $profilePicture = $this->profilePictureService->generateProfilePicture($user);

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/' . $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }
}
