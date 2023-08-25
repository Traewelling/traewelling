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
            'type'           => (string) $this->type,
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
                'trip'                => (int) $this->trainCheckin->HafasTrip->id,
                'hafasId'             => (string) $this->trainCheckin->HafasTrip->trip_id,
                'category'            => (string) $this->trainCheckin->HafasTrip->category->value,
                'number'              => (string) $this->trainCheckin->HafasTrip->number,
                'lineName'            => (string) $this->trainCheckin->HafasTrip->linename,
                'journeyNumber'       => $this->trainCheckin->HafasTrip->journey_number,
                'distance'            => (int) $this->trainCheckin->distance,
                'points'              => (int) $this->trainCheckin->points,
                'duration'            => (int) $this->trainCheckin->duration,
                'speed'               => (float) $this->trainCheckin->speed,
                'overriddenDeparture' => $this->trainCheckin->manual_departure?->toIso8601String(), //TODO: deprecated: remove after 2023-10 (#1809)
                'manualDeparture'     => $this->trainCheckin->manual_departure?->toIso8601String(),
                'overriddenArrival'   => $this->trainCheckin->manual_arrival?->toIso8601String(), //TODO: deprecated: remove after 2023-10 (#1809)
                'manualArrival'       => $this->trainCheckin->manual_arrival?->toIso8601String(),
                'origin'              => new StopoverResource($this->trainCheckin->origin_stopover),
                'destination'         => new StopoverResource($this->trainCheckin->destination_stopover),
                'operator'            => new OperatorResource($this?->trainCheckin->operator)
            ],
            'event'          => new EventResource($this?->event),
        ];
    }
}
