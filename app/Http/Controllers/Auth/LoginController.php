<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Backend\Auth\LoginController as BackendLoginController;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

    use AuthenticatesUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login.
     */
    protected string $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(): View
    {
        return view('vue.spa', [
            'title' => __('menu.login'),
            'robots' => 'noindex',
        ]);
    }

    public function login(Request $request): Response
    {
        $validated = $request->validate([
            'login' => ['required', 'max:255'],
            'password' => ['required', 'min:8'],
            'remember' => ['nullable'],
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if (BackendLoginController::login($validated['login'], $validated['password'], $request->boolean('remember'))) {
            $redirect = redirect()->intended($this->redirectPath());

            return $request->expectsJson()
                ? response()->json(['redirect' => $redirect->getTargetUrl()])
                : $redirect;
        }

        $this->incrementLoginAttempts($request);

        throw ValidationException::withMessages([
            'login_error' => __('error.login'),
        ]);
    }

    public function username(): string
    {
        return 'login';
    }
}
