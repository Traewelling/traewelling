<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\StatusVisibility;
use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Resources\UserProfileSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function getProfileSettings(): UserProfileSettingsResource {
        return new UserProfileSettingsResource(auth()->user());
    }

    /**
     * @throws ValidationException
     */
    public function updateMail(Request $request): UserProfileSettingsResource|JsonResponse {
        $validated = $request->validate(['email'    => ['required',
                                                        'string',
                                                        'email:rfc,dns',
                                                        'max:255',
                                                        'unique:users'],
                                         'password' => ['required', 'string']
                                        ]);
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            throw ValidationException::withMessages([__('auth.password')]);
        }
        unset($validated['password']);

        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateSettings($validated));
        } catch (RateLimitExceededException) {
            return $this->sendv1Error(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    public function resendMail(): void {
        try {
            auth()->user()->sendEmailVerificationNotification();
            $this->sendv1Response('', 204);
        } catch (RateLimitExceededException) {
            $this->sendv1Error(error: __('email.verification.too-many-requests'), code: 429);
        }
    }

    public function updatePassword(Request $request): UserProfileSettingsResource|JsonResponse {
        $userHasPassword = auth()->user()->password !== null;

        $validated = $request->validate([
                                            'currentPassword' => [Rule::requiredIf($userHasPassword)],
                                            'password'        => ['required', 'string', 'min:8', 'confirmed']
                                        ]);

        if ($userHasPassword && !Hash::check($validated['currentPassword'], auth()->user()->password)) {
            throw ValidationException::withMessages([__('controller.user.password-wrong')]);
        }

        $validated['password'] = Hash::make($validated['password']);

        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateSettings($validated));
        } catch (RateLimitExceededException) {
            return $this->sendv1Error(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    public function updateSettings(Request $request): UserProfileSettingsResource|JsonResponse {
        $validated = $request->validate([
                                            'username'                  => ['required',
                                                                            'string',
                                                                            'max:25',
                                                                            'regex:/^[a-zA-Z0-9_]*$/'],
                                            'name'                      => ['required', 'string', 'max:50'],
                                            'private_profile'           => ['boolean', 'nullable'],
                                            'prevent_index'             => ['boolean', 'nullable'],
                                            'privacy_hide_days'         => ['integer', 'nullable', 'gte:1'],
                                            'always_dbl'                => ['boolean', 'nullable'],
                                            'default_status_visibility' => [
                                                'nullable',
                                                new Enum(StatusVisibility::class),
                                            ]
                                        ]);

        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateSettings($validated));
        } catch (RateLimitExceededException) {
            return $this->sendv1Error(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    public function deleteProfilePicture(): JsonResponse {
        if (BackendSettingsController::deleteProfilePicture(user: auth()->user())) {
            return $this->sendv1Response('', 204);
        }

        return $this->sendv1Error('', 400);
    }

    public function uploadProfilePicture(Request $request): JsonResponse {
        if (BackendSettingsController::updateProfilePicture($request->input('image'))) {
            return $this->sendv1Response('', 204);
        }
        return $this->sendv1Error('', 400);
    }
}
