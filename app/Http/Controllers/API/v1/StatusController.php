<?php


namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Resources\PolylineResource;
use App\Http\Resources\StatusResource;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\PolyLine;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
    public function show($id): StatusResource|Response {
        return new StatusResource(StatusBackend::getStatus($id));
    }

    /**
     * @param $parameters
     * @return AnonymousResourceCollection|JsonResponse
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
    public function getPolyline($parameters) {
        $ids      = explode(',', $parameters, 50);
        $mapLines = Status::whereIn('id', $ids)
                          ->with('trainCheckin.HafasTrip.getPolyLine')
                          ->get()
                          ->reject(function($status) {
                              return $status->user->userInvisibleToMe === true;
                          })
                          ->map(function($status) {
                              return ["id"               => (int) $status->id,
                                      "coordinatesArray" => $status->trainCheckin->getMapLines()
                              ];
                          });
        return $ids ? $this->sendv1Response($mapLines) : $this->sendError("");
    }
}
