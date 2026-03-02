<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Requests\UpdateProfileInformationRequest;
use App\Http\Resources\UserProfileSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class SettingsController extends Controller
{
    #[OA\Get(
        path: '/settings/profile',
        operationId: 'getProfileSettings',
        tags: ['Settings'],
        summary: 'Get the current user\'s profile settings',
        description: 'Get the current user\'s profile settings',
        security: [['passport' => ['read-settings']], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/UserProfileSettingsResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function getProfileSettings(): UserProfileSettingsResource
    {
        return new UserProfileSettingsResource(auth()->user());
    }

    /**
     * @throws ValidationException
     */
    #[OA\Put(
        path: '/settings/email',
        operationId: 'updateEmail',
        description: 'Update the current user\'s email address',
        summary: 'Update the current user\'s email address',
        security: [new OA\SecurityScheme(
            securityScheme: 'passport',
            type: 'oauth2',
        )],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'mail@example.com',
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'thisisnotasecurepassword123',
                    ),
                ]
            )
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserProfileSettingsResource',
                            type: 'object',
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Unprocessable Entity'),
        ],
    )]
    public function updateMail(Request $request): UserProfileSettingsResource|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users',
            ],
            'password' => ['required', 'string'],
        ]);
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            throw ValidationException::withMessages([__('auth.password')]);
        }
        unset($validated['password']);

        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateMail($validated['email'], auth()->user()));
        } catch (RateLimitExceededException) {
            return $this->sendError(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    #[OA\Put(
        path: '/settings/profile',
        operationId: 'updateProfileSettings',
        tags: ['Settings'],
        summary: 'Update the current user\'s profile settings',
        description: 'Update the current user\'s profile settings',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileInformationRequest'),
        ),
        security: [['passport' => ['write-settings']], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/UserProfileSettingsResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Unprocessable Entity'),
            new OA\Response(response: 400, description: 'Bad Request'),
        ],
    )]
    public function updateSettings(UpdateProfileInformationRequest $request): UserProfileSettingsResource|JsonResponse
    {
        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateSettings($request->validated()));
        } catch (RateLimitExceededException) {
            return $this->sendError(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    #[OA\Post(
        path: '/settings/email/verification',
        operationId: 'resendVerificationEmail',
        description: 'Resend verification email',
        summary: 'Resend verification email',
        security: [new OA\SecurityScheme(
            securityScheme: 'passport',
            type: 'oauth2',
        )],
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Success',
            ),
            new OA\Response(
                response: 429,
                description: 'Rate limit exceeded',
            ),
        ]
    )]
    public function resendMail(): JsonResponse
    {
        try {
            auth()->user()->sendEmailVerificationNotification();

            return $this->sendResponse(null, 201);
        } catch (RateLimitExceededException) {
            return $this->sendError(error: __('email.verification.too-many-requests'), code: 429);
        }
    }

    /**
     * @throws ValidationException
     */
    public function updatePassword(Request $request): UserProfileSettingsResource|JsonResponse
    {
        $userHasPassword = auth()->user()->password !== null;

        $validated = $request->validate([
            'currentPassword' => [Rule::requiredIf($userHasPassword)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($userHasPassword && !Hash::check($validated['currentPassword'], auth()->user()->password)) {
            throw ValidationException::withMessages([__('controller.user.password-wrong')]);
        }

        $validated['password'] = Hash::make($validated['password']);

        try {
            return new UserProfileSettingsResource(BackendSettingsController::updateSettings($validated));
        } catch (RateLimitExceededException) {
            return $this->sendError(error: __('email.verification.too-many-requests'), code: 400);
        }
    }

    #[OA\Delete(
        path: '/settings/profile-picture',
        operationId: 'deleteProfilePicture',
        description: 'Delete the current user\'s profile picture',
        summary: 'Delete the current user\'s profile picture',
        security: [new OA\SecurityScheme(
            securityScheme: 'passport',
            type: 'oauth2',
        )],
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Profile picture deleted successfully.',
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function deleteProfilePicture(): JsonResponse
    {
        if (BackendSettingsController::deleteProfilePicture(user: auth()->user())) {
            return $this->sendResponse(['message' => __('settings.profilePicture.deleted')]);
        }

        return $this->sendError(__('messages.exception.general'), 400);
    }

    #[OA\Post(
        path: '/settings/profile-picture',
        operationId: 'uploadProfilePicture',
        description: 'Upload a new profile picture for the current user',
        summary: 'Upload a new profile picture for the current user',
        security: [new OA\SecurityScheme(
            securityScheme: 'passport',
            type: 'oauth2',
        )],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'image',
                        type: 'string',
                        format: 'base64',
                        example: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...',
                    ),
                ]
            )
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Profile picture updated successfully.',
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        if (auth()->user()->can('disallow-social-interaction')) {
            return response()->json(null, 403);
        }
        if (BackendSettingsController::updateProfilePicture($request->input('image'))) {
            return $this->sendResponse(['message' => __('settings.saved')]);
        }

        return $this->sendError('', 400);
    }
}
