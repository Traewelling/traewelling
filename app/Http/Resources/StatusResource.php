<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array {
        return [
            "id"           => (int) $this->id,
            "body"         => (string) $this->body,
            "type"         => (string) $this->type,
            "createdAt"    => (string) $this->created_at,
            "user"         => (int) $this->user->id,
            "username"     => (string) $this->user->username,
            "preventIndex" => (bool) $this->user->prevent_index,
            "business"     => (int) $this->business,
            "visibility"   => (int) $this->visibility,
            "likes"        => (int) $this->likes->count(),
            "liked"        => (bool) $this->favorited,
            "train"        => [
                "trip"        => (int) $this->trainCheckin->HafasTrip->id,
                "category"    => (string) $this->trainCheckin->HafasTrip->category,
                "number"      => (string) $this->trainCheckin->HafasTrip->number,
                "lineName"    => (string) $this->trainCheckin->HafasTrip->linename,
                "distance"    => (int) $this->trainCheckin->distance, //TODO: Levin, das hier sind jetzt Meter (vorher km). Das muss in Vue angepasst werden.
                "points"      => (int) $this->trainCheckin->points,
                "delay"       => (float) $this->trainCheckin->delay,
                "duration"    => (int) $this->trainCheckin->duration,
                "speed"       => (float) $this->trainCheckin->speed,
                "origin"      => new StopoverResource($this->trainCheckin->origin_stopover),
                "destination" => new StopoverResource($this->trainCheckin->destination_stopover)
            ],
            //ToDo: Custom Resource for event
            "event"        => empty($this->event) ? null : $this->event
        ];
    }
}
