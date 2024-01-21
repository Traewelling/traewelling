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
            'client'         => new ClientResource($this->client),
            'createdAt'      => $this->created_at->toIso8601String(),
            'train'          => [
                'trip'            => (int) $this->checkin->trip->id,
                'hafasId'         => (string) $this->checkin->trip->trip_id,
                'category'        => (string) $this->checkin->trip->category->value,
                'number'          => (string) $this->checkin->trip->number,
                'lineName'        => (string) $this->checkin->trip->linename,
                'journeyNumber'   => $this->checkin->trip->journey_number,
                'distance'        => (int) $this->checkin->distance,
                'points'          => (int) $this->checkin->points,
                'duration'        => (int) $this->checkin->duration,
                'manualDeparture' => $this->checkin->manual_departure?->toIso8601String(),
                'manualArrival'   => $this->checkin->manual_arrival?->toIso8601String(),
                'origin'          => new StopoverResource($this->checkin->originStopover),
                'destination'     => new StopoverResource($this->checkin->destinationStopover),
                'operator'        => new OperatorResource($this?->checkin->trip->operator)
            ],
            'event'          => new EventResource($this?->event),
        ];
    }
}
