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
            'id' => (int) $this->id,
            'body' => (string) $this->body,
            'type' => (string) $this->type,
            'created_at' => (string) $this->created_at,
            'user' => (int) $this->user->id,
            'username' => (string) $this->user->username,
            'business' => (int) $this->business,
            'train' => [
                'trip' => (int) $this->trainCheckin->HafasTrip->id,
                'category' => (string) $this->trainCheckin->HafasTrip->category,
                'number' => (string) $this->trainCheckin->HafasTrip->number,
                'linenumber' => (string) $this->trainCheckin->HafasTrip->linenumber,
                'distance' => (float) $this->trainCheckin->distance,
                'points' => (int) $this->trainCheckin->points,
                'departure' => (string) $this->trainCheckin->departure,
                'arrival' => (string) $this->trainCheckin->arrival,
                'delay' => (float) $this->trainCheckin->delay,
                'duration' => (int) $this->trainCheckin->duration,
                'speed' => (float) $this->trainCheckin->speed,
                'origin' => (int) $this->trainCheckin->Origin->id,
                'origin_name' => (string) $this->trainCheckin->Origin->name,
                'destination' => (int) $this->trainCheckin->Destination->id,
                'destination_name' => (string) $this->trainCheckin->Destination->name,
            ],
            'event' => $this->event
        ];
    }
}
