<?php


namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Resources\StatusResource;
use App\Http\Controllers\StatusController as StatusBackend;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatusController extends ResponseController
{
    public function enRoute(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getActiveStatuses(null, false)['statuses']);
    }

    /**
     * Show single status
     * @param $id
     * @return StatusResource
     */
    public function show($id): StatusResource {
        return new StatusResource(StatusBackend::getStatus($id));
    }
}
