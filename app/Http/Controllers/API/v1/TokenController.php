<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\User\TokenController as BackendTokenController;
use App\Http\Resources\TokenResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TokenController extends Controller
{
    public function index(): AnonymousResourceCollection {
        return TokenResource::collection(BackendTokenController::index(user: auth()->user()));
    }

    public function revokeToken(Request $request): JsonResponse {
        $validated = $request->validate(['tokenId' => ['required', 'exists:oauth_access_tokens,id']]);

        try {
            BackendTokenController::revokeToken($validated['tokenId'], auth()->user());
            return $this->sendResponse(null, 204);
        } catch (AuthorizationException) {
            return $this->sendError(null, 403);
        }
    }

    public function revokeAllTokens(): JsonResponse {
        BackendTokenController::revokeAllTokens(user: auth()->user());
        return $this->sendResponse(null, 204);
    }
}
