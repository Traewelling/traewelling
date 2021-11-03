<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    protected function authenticated(Request $request, $user): void {
        $user->update(['last_login' => Carbon::now()->toIso8601String()]);
    }

    public function login(Request $request): RedirectResponse {
        $validated = $request->validate(['login' => 'required', 'password' => 'required', 'remember' => 'nullable|in:1']);

        $email       = $validated['login'];
        $password    = $validated['password'];
        $remember_me = $request->remember;

        $login_type = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$login_type => $email, 'password' => $password], $remember_me)) {
            //Auth successful here
            return redirect()->intended($this->redirectPath());
        }

        return redirect()->back()
                         ->withInput()
                         ->withErrors([
                                          'login_error' => 'These credentials do not match our records.',
                                      ]);
    }
}
