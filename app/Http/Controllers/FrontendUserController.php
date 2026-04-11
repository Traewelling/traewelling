<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\UserController as UserControllerAlias;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendUserController extends Controller
{
    public function getProfilePage(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        return view('profile.profile', [
            'user' => $user,
        ]);
    }

    public function searchUser(Request $request): Renderable|RedirectResponse
    {
        try {
            $users = UserControllerAlias::searchUser($request['searchQuery']);
            if ($users->count() === 1) {
                return redirect()->route('profile', ['username' => $users->first()->username]);
            }

            return view('search', [
                'users' => $users,
            ]);
        } catch (HttpException|InvalidArgumentException) {
            // abort(400) is triggered.
            return redirect()->back()->with('error', __('error.bad-request'));
        }
    }
}
