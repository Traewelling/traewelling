<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\IcsController as BackendIcsController;
use App\Http\Resources\IcsEntryResource;
use App\Models\IcsToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IcsController extends Controller
{
    public function createIcsToken(Request $request): JsonResponse {
        $validated = $request->validate(['name' => ['required', 'max:255']]);

        $icsToken = BackendIcsController::createIcsToken(user: auth()->user(), name: $validated['name']);

        return $this->sendResponse(
            data: route('ics', [
                    'user_id' => $icsToken->user_id,
                    'token'   => $icsToken->token,
                    'limit'   => 10000,
                    'from'    => '2010-01-01',
                    'until'   => '2030-12-31'
                ]),
            code: 201,
        );
    }

    public function revokeIcsToken(Request $request): JsonResponse {
        $validated = $request->validate(['tokenId' => ['required', 'numeric']]);

        try {
            BackendIcsController::revokeIcsToken(user: auth()->user(), tokenId: $validated['tokenId']);
            return $this->sendResponse(null, 204);
        } catch (ModelNotFoundException) {
            return $this->sendError();
        }
    }

    public function getIcsTokens(): AnonymousResourceCollection {
        return IcsEntryResource::collection(IcsToken::where('user_id', auth()->user()->id)->get());
    }
}
