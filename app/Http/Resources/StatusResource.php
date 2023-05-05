<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'createdAt'      => $this->created_at->toIso8601String(),
            'train'          => [
                'trip'        => (int) $this->trainCheckin->HafasTrip->id,
                'hafasId'     => (string) $this->trainCheckin->HafasTrip->trip_id,
                'category'    => (string) $this->trainCheckin->HafasTrip->category->value,
                'number'      => (string) $this->trainCheckin->HafasTrip->number,
                'lineName'    => (string) $this->trainCheckin->HafasTrip->linename,
                'distance'    => (int) $this->trainCheckin->distance,
                'points'      => (int) $this->trainCheckin->points,
                'duration'    => (int) $this->trainCheckin->duration,
                'speed'       => (float) $this->trainCheckin->speed,
                'origin'      => new StopoverResource($this->trainCheckin->origin_stopover),
                'destination' => new StopoverResource($this->trainCheckin->destination_stopover),
            ],
            'event'          => new EventResource($this?->event),
        ];
    }
}
