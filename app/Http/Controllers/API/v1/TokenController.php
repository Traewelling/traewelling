<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\TokenController as BackendTokenController;
use App\Http\Resources\TokenResource;
use Illuminate\Http\Request;

class TokenController extends ResponseController
{
    public function index() {
        return TokenResource::collection(BackendTokenController::index(user: auth()->user()));
    }

    public function revokeToken(Request $request) {
        $validated = $request->validate(['tokenId' => ['required', 'exists:oauth_access_tokens,id']]);

        try {
            BackendTokenController::revokeToken($validated['tokenId'], auth()->user());
            return $this->sendv1Response(null, 204);
        } catch (PermissionException) {
            return $this->sendError(null, 403);
        }
    }

    public function revokeAllTokens() {
        BackendTokenController::revokeAllTokens(user: auth()->user());
        return $this->sendv1Response(null, 204);
    }
}
