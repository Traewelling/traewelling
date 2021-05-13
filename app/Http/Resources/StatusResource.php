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
    public function toArray($request) {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'user' => $this->user->id,
            'username' => $this->user->username,
            'train' => [
                'trip' => $this->trainCheckin->HafasTrip->id,
                'category' => $this->trainCheckin->HafasTrip->category,
                'number' => $this->trainCheckin->HafasTrip->number,
                'linenumber' => $this->trainCheckin->HafasTrip->linenumber,
                'distance' => $this->trainCheckin->distance,
                'points' => $this->trainCheckin->points,
                'departure' => $this->trainCheckin->departure,
                'arrival' => $this->trainCheckin->arrival,
                'delay' => $this->trainCheckin->delay,
                'duration' => $this->trainCheckin->duration,
                'speed' => $this->trainCheckin->speed,
                'origin' => $this->trainCheckin->Origin->id,
                'origin_name' => $this->trainCheckin->Origin->name,
                'destination' => $this->trainCheckin->Destination->id,
                'destination_name' => $this->trainCheckin->Destination->name,
            ],
            'event' => $this->event
        ];
    }
}
