<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

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

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetLinkEmail(Request $request): JsonResponse|RedirectResponse
    {
        $this->validateEmail($request);

        $context = ['ip' => $request->ip(), 'email' => $request->string('email')->toString()];

        $user = User::where('email', $request->email)->first();
        if (
            ($user !== null && $user->created_at->diffInMinutes() < 60)
            || ($user !== null && $user->email_verified_at === null && $user->created_at->diffInDays() < 7)
        ) {
            // prevent new registered users from sending password reset email instantly
            Log::notice('password.reset: blocked for new/unverified account', $context);

            return $this->sendResetLinkFailedResponse($request, Password::RESET_THROTTLED);
        }

        // rate limit: 1 attempt per 60 minutes (link is valid for 60 minutes)
        // Use email hash as fallback key for non-existent accounts to throttle per-email
        $throttleKey = CacheKey::getPasswordResetThrottleKey($user, $request->string('email')->toString());
        if (cache()->has($throttleKey)) {
            Log::notice('password.reset: throttled', $context);

            return $this->sendResetLinkFailedResponse($request, Password::RESET_THROTTLED);
        }
        cache()->put($throttleKey, true, 60 * 60);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        if ($response === Password::RESET_LINK_SENT) {
            Log::info('password.reset: link sent', $context);
        } elseif ($response === Password::INVALID_USER) {
            Log::info('password.reset: no account found, no email sent', $context);
        }

        return $response === Password::RESET_LINK_SENT || $response === Password::INVALID_USER
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Return a consistent success-like response to avoid leaking whether an account exists.
     */
    protected function sendResetLinkResponse(Request $request, string $response): JsonResponse|RedirectResponse
    {
        $message = trans('passwords.sent_if_exists');

        return $request->wantsJson()
            ? new JsonResponse(['message' => $message], 200)
            : back()->with('status', $message);
    }

    /**
     * Get the response for a failed password reset link.
     * Note: INVALID_USER is handled in sendResetLinkResponse to avoid user enumeration.
     */
    protected function sendResetLinkFailedResponse(Request $request, string $response): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}
