<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "slug"          => $this->slug,
            "hashtag"       => $this->hashtag,
            "host"          => $this->host,
            "url"           => $this->url,
            "begin"         => $this->begin,
            "end"           => $this->end,
            "trainDistance" => $this->trainDistance,
            "trainDuration" => $this->trainDuration,
            "station"       => new TrainstationResource($this->station)
        ];
    }
}
