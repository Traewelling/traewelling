<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\StatusVisibility;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Resources\UserProfileSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends ResponseController
{
    public function getProfileSettings(): UserProfileSettingsResource {
        return new UserProfileSettingsResource(auth()->user());
    }

    public function updateSettings(Request $request): UserProfileSettingsResource {
        $validated = $request->validate([
                                            'username'                  => ['required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/'],
                                            'name'                      => ['required', 'string', 'max:50'],
                                            'private_profile'           => ['boolean', 'nullable'],
                                            'prevent_index'             => ['boolean', 'nullable'],
                                            'always_dbl'                => ['boolean', 'nullable'],
                                            'default_status_visibility' => [
                                                Rule::in(StatusVisibility::getList()),
                                                'nullable'
                                            ]
                                        ]);

        return new UserProfileSettingsResource(BackendSettingsController::updateSettings($validated));
    }

    public function deleteProfilePicture(): JsonResponse {
        if (BackendSettingsController::deleteProfilePicture()) {
            return $this->sendv1Response('', 204);
        }

        return $this->sendError('', 400);
    }

    public function uploadProfilePicture(Request $request): JsonResponse {
        if (BackendSettingsController::updateProfilePicture($request->input('image'))) {
            return $this->sendv1Response('', 204);
        }
        return $this->sendError('', 400);
    }
}
