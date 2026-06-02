<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\Mastodon\NoAvatarException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Requests\UpdateProfileInformationRequest;
use App\Http\Resources\UserProfileSettingsResource;
use App\Services\Mastodon\AvatarImportService;
use App\Services\ProfilePictureService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class SettingsController extends Controller
{
    public function __construct(
        private readonly ProfilePictureService $profilePictureService,
        private readonly AvatarImportService $avatarImportService,
    ) {}

    #[OA\Get(
        path: '/settings/profile',
        operationId: 'getProfileSettings',
        description: 'Get the current user\'s profile settings',
        summary: 'Get the current user\'s profile settings',
        security: [['passport' => ['read-settings']], ['token' => []]],
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserProfileSettingsResource',
                            type: 'object',
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
                required: ['email'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'mail@example.com',
                    ),
                    new OA\Property(
                        property: 'password',
                        description: 'Required only if the account already has a password set.',
                        type: 'string',
                        format: 'password',
                        example: 'thisisnotasecurepassword123',
                        nullable: true,
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
                    required: ['data'],
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
        $userHasPassword = auth()->user()->password !== null;

        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => [Rule::requiredIf($userHasPassword), 'nullable', 'string'],
        ]);

        if ($userHasPassword && !Hash::check($validated['password'], auth()->user()->password)) {
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
        description: 'Update the current user\'s profile settings',
        summary: 'Update the current user\'s profile settings',
        security: [['passport' => ['write-settings']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileInformationRequest'),
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserProfileSettingsResource',
                            type: 'object',
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

    #[OA\Put(
        path: '/settings/password',
        operationId: 'updatePassword',
        description: 'Change the current user\'s password.',
        summary: 'Change password',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'currentPassword',
                        description: 'Current password (required if the account has a password set)',
                        type: 'string',
                        format: 'password',
                    ),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ],
            ),
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: UserProfileSettingsResource::class)],
                ),
            ),
            new OA\Response(response: 400, description: self::OA_DESC_BAD_REQUEST),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
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
                    required: ['message'],
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
        if ($this->profilePictureService->delete(user: auth()->user())) {
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
                    required: ['message'],
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
        if ($this->profilePictureService->update(auth()->user(), $request->input('image'))) {
            return $this->sendResponse(['message' => __('settings.saved')]);
        }

        return $this->sendError('', 400);
    }

    #[OA\Post(
        path: '/settings/profile-picture/mastodon',
        operationId: 'importProfilePictureFromMastodon',
        description: 'Import the profile picture from the connected Mastodon account',
        summary: 'Import profile picture from Mastodon',
        security: [new OA\SecurityScheme(
            securityScheme: 'passport',
            type: 'oauth2',
        )],
        tags: ['Settings'],
        responses: [
            new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT),
            new OA\Response(response: 400, description: 'Bad Request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No Mastodon account connected'),
        ]
    )]
    public function importProfilePictureFromMastodon(): JsonResponse
    {
        $user = auth()->user();

        if ($user->can('disallow-social-interaction')) {
            return response()->json(null, 403);
        }

        try {
            $this->avatarImportService->importFromMastodon($user);
        } catch (NotConnectedException) {
            return $this->sendError(__('controller.social.delete-never-connected'), 404);
        } catch (NoAvatarException) {
            return $this->sendError(__('settings.mastodon.no-profile-picture'), 400);
        } catch (GuzzleException $e) {
            report($e);

            return $this->sendError(__('messages.exception.general'), 400);
        }

        return $this->sendResponse(null, 204);
    }
}
