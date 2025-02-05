<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct() {
        $this->middleware('guest');
    }

    public function sendResetLinkEmail(Request $request): JsonResponse|RedirectResponse {
        // prevent new registered users from sending password reset email instantly (spam protection)
        $user = User::where('email', $request->email)->first();
        if ($user !== null && $user->created_at->diffInMinutes() < 60) {
            return $this->sendResetLinkFailedResponse($request, Password::RESET_THROTTLED);
        }

        // rate limit: 1 attempt per 60 minutes (link is valid for 60 minutes)
        $throttleKey = CacheKey::getPasswordResetThrottleKey($user);
        if (cache()->has($throttleKey)) {
            return $this->sendResetLinkFailedResponse($request, Password::RESET_THROTTLED);
        }
        cache()->put($throttleKey, true, 60 * 60);

        // continue with the default password reset email sending process
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
