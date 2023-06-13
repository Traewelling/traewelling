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
     *
     * @return array
     */
    public function toArray($request): array {
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "slug"          => $this->slug,
            "hashtag"       => $this->hashtag,
            "host"          => $this->host,
            "url"           => $this->url,
            "begin"         => ($this->event_start ?? $this->begin)->toIso8601String(),
            "end"           => ($this->event_end ?? $this->end)->toIso8601String(),
            "station"       => new TrainStationResource($this->station)
        ];
    }
}
