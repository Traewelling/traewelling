<?php


namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Resources\PolylineResource;
use App\Http\Resources\StatusResource;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\StopoverResource;
use App\Models\HafasTrip;
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
     * @param int $id
     * @return StatusResource|Response
     */
    public function show(int $id): StatusResource|Response {
        return new StatusResource(StatusBackend::getStatus($id));
    }

    /**
     * @param string $parameters
     * @return AnonymousResourceCollection|JsonResponse
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
    public function getPolyline(string $parameters): JsonResponse {
        $ids      = explode(',', $parameters, 50);
        $mapLines = Status::whereIn('id', $ids)
                          ->with('trainCheckin.HafasTrip.getPolyLine')
                          ->get()
                          ->reject(function($status) {
                              return $status->user->userInvisibleToMe;
                          })
                          ->map(function($status) {
                              return [
                                  "id"               => (int) $status->id,
                                  "coordinatesArray" => $status->trainCheckin->getMapLines()
                              ];
                          });
        return $ids ? $this->sendv1Response($mapLines) : $this->sendError("");
    }

    /**
     * @param string $parameters
     * @return JsonResponse
     */
    public function getStopovers(string $parameters): JsonResponse {
        $tripIds = explode(',', $parameters, 50);
        $trips   = HafasTrip::whereIn('id', $tripIds)->get()->mapWithKeys(function($trip) {
            return [$trip->id => StopoverResource::collection($trip->stopoversNEW)];
        });
        return $this->sendv1Response($trips);
    }
}
