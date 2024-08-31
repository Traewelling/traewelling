<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Backend\Auth\LoginController as BackendLoginController;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function login(Request $request): Response {
        $validator = Validator::make($request->all(), [
            'login'    => ['required', 'max:255'],
            'password' => ['required', 'min:8'],
            'remember' => ['nullable',],
        ]);

        if ($validator->fails()) {
            return redirect(route('login'))
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if (BackendLoginController::login($validated['login'], $validated['password'], isset($validated['remember']))) {
            return redirect()->intended($this->redirectPath());
        }

        $this->incrementLoginAttempts($request);

        return redirect()->route('login')
                         ->withInput()
                         ->withErrors([
                                          'login_error' => __('error.login'),
                                      ]);
    }

    public function username(): string {
        return 'login';
    }
}
