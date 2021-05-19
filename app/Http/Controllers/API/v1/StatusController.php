<?php


namespace App\Http\Controllers\API\v1;


use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\UserController;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\Status;
use App\Models\User;
use App\Http\Controllers\StatusController as StatusBackend;

class StatusController extends ResponseController
{
    public function enRoute() {
        return StatusResource::collection(StatusBackend::getActiveStatuses(null, false)['statuses']);

        return StatusResource::collection(Status::with([
                                                           'likes',
                                                           'user',
                                                           'trainCheckin.Origin',
                                                           'trainCheckin.Destination',
                                                           'trainCheckin.HafasTrip.getPolyLine',
                                                           'trainCheckin.HafasTrip.stopoversNEW.trainStation',
                                                           'event'
                                                       ])->paginate(10));
    }

    /**
     * Show single status
     * @param $id
     * @return StatusResource
     */
    public function show($id) {
        return new StatusResource(StatusBackend::getStatus($id));
    }
}