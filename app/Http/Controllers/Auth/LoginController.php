<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Backend\Auth\LoginController as BackendLoginController;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected string $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request): RedirectResponse {
        $validated = $request->validate(['login'    => ['required', 'max:255'],
                                         'password' => ['required', 'min:8'],
                                         'remember' => ['nullable', 'in:1']]);

        if (BackendLoginController::login($validated['login'], $validated['password'], $request->remember)) {
            return redirect()->intended($this->redirectPath());
        }

        return redirect()->back()
                         ->withInput()
                         ->withErrors([
                                          'login_error' => __('error.login'),
                                      ]);
    }

    protected function authenticated(Request $request, $user): void {
        $user->update(['last_login' => Carbon::now()->toIso8601String()]);
    }
}
