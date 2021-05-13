<?php


namespace App\Http\Controllers\API\v1;


use App\Http\Resources\StatusResource;
use App\Models\Status;

class StatusController
{
    public function enRoute() {
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
}