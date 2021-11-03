<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function deleteUserAccount(Request $request): RedirectResponse {
        $request->validate(['confirmation' => ['required', Rule::in([auth()->user()->username])]]);

        try {
            BackendUserController::deleteUserAccount(user: auth()->user());
            return redirect()->route('static.welcome');
        } catch (Error) {
            return back()->with('error', __('messages.exception.general'));
        }
    }
}
