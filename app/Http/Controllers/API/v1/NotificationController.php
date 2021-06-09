<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\JsonResponse;

class NotificationController extends ResponseController
{
    public function count():JsonResponse {
        return $this->sendv1Response(NotificationBackend::count());
    }
}
