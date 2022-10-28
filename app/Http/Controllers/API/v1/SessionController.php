<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\User\SessionController as SessionBackend;
use App\Http\Resources\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SessionController extends Controller
{
    public function index(): AnonymousResourceCollection {
        return SessionResource::collection(SessionBackend::index(user: auth()->user()));
    }

    public function deleteAllSessions(): JsonResponse {
        SessionBackend::deleteAllSessionsFor(user: auth()->user());

        return $this->sendResponse(null, 204);
    }
}
