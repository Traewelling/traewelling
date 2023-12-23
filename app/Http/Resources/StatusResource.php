<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array {
        return [
            'id'             => (int) $this->id,
            'body'           => (string) $this->body,
            'type'           => '', //TODO: deprecated: remove after 2024-02
            'user'           => (int) $this->user->id,
            'username'       => (string) $this->user->username,
            'profilePicture' => ProfilePictureController::getUrl($this->user),
            'preventIndex'   => (bool) $this->user->prevent_index,
            'business'       => (int) $this->business->value,
            'visibility'     => (int) $this->visibility->value,
            'likes'          => (int) $this->likes->count(),
            'liked'          => (bool) $this->favorited,
            'isLikable'      => Gate::allows('like', $this->resource),
            'createdAt'      => $this->created_at->toIso8601String(),
            'train'          => [
                'trip'                => (int) $this->checkin->HafasTrip->id,
                'hafasId'             => (string) $this->checkin->HafasTrip->trip_id,
                'category'            => (string) $this->checkin->HafasTrip->category->value,
                'number'              => (string) $this->checkin->HafasTrip->number,
                'lineName'            => (string) $this->checkin->HafasTrip->linename,
                'journeyNumber'       => $this->checkin->HafasTrip->journey_number,
                'distance'            => (int) $this->checkin->distance,
                'points'              => (int) $this->checkin->points,
                'duration'            => (int) $this->checkin->duration,
                'speed'               => 0.0, //deprecated: TODO: remove after 2023-12-31
                'overriddenDeparture' => $this->checkin->manual_departure?->toIso8601String(), //TODO: deprecated: remove after 2023-10 (#1809)
                'manualDeparture'     => $this->checkin->manual_departure?->toIso8601String(),
                'overriddenArrival'   => $this->checkin->manual_arrival?->toIso8601String(), //TODO: deprecated: remove after 2023-10 (#1809)
                'manualArrival'       => $this->checkin->manual_arrival?->toIso8601String(),
                'origin'              => new StopoverResource($this->checkin->originStopover),
                'destination'         => new StopoverResource($this->checkin->destinationStopover),
                'operator'            => new OperatorResource($this?->checkin->HafasTrip->operator)
            ],
            'event'          => new EventResource($this?->event),
        ];
    }
}
