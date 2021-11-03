<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\SessionController as SessionBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SessionController extends ResponseController
{
    public function index(): AnonymousResourceCollection {
        return SessionBackend::index(user: auth()->user());
    }

    public function deleteAllSessions(): JsonResponse {
        SessionBackend::deleteAllSessionsFor(user: auth()->user());

        return $this->sendv1Response(null, 204);
    }
}
