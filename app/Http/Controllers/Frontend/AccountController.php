<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\File;

class AccountController extends Controller
{
    public function deleteUserAccount(Request $request): RedirectResponse {
        $request->validate([
                               'confirmation' => ['required', 'regex:(delete ' . auth()->user()->username . ')']
                           ]);

        $user = auth()->user();

        if ($user->avatar != 'user.jpg') {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
        }

        DatabaseNotification::where([
                                        'notifiable_id'   => $user->id,
                                        'notifiable_type' => get_class($user)
                                    ])->delete();


        if ($user->delete()) {
            return redirect()->route('static.welcome');
        }
        return back()->with('error', __('messages.exception.general'));
    }
}
