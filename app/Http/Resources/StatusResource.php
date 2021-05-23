<?php

namespace App\Http\Resources;

use App\Models\TrainStopover;
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
    public function toArray($request) : array {
        // Create temporary stopover models for old or broken trains
        $originStopover      = $this->trainCheckin->origin_stopover;
        $destinationStopover = $this->trainCheckin->destination_stopover;
        return [
            "id"         => (int) $this->id,
            "body"       => (string) $this->body,
            "type"       => (string) $this->type,
            "createdAt" => (string) $this->created_at,
            "user"       => (int) $this->user->id,
            "username"   => (string) $this->user->username,
            "business"   => (int) $this->business,
            "train"      => [
                "trip"                => (int) $this->trainCheckin->HafasTrip->id,
                "category"            => (string) $this->trainCheckin->HafasTrip->category,
                "number"              => (string) $this->trainCheckin->HafasTrip->number,
                "lineName"            => (string) $this->trainCheckin->HafasTrip->linename,
                "distance"            => (float) $this->trainCheckin->distance,
                "points"              => (int) $this->trainCheckin->points,
                "delay"               => (float) $this->trainCheckin->delay,
                "duration"            => (int) $this->trainCheckin->duration,
                "speed"               => (float) $this->trainCheckin->speed,
                "origin"            => new StopoverResource($originStopover),
                "destination" => new StopoverResource($destinationStopover)
            ],
            //ToDo: Custom Resource for event
            "event"      => empty($this->event) ? null : $this->event
        ];
    }
}
