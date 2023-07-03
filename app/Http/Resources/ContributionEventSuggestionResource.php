<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ContributionEventSuggestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "hashtag" => $this->hashtag,
            "host" => $this->host,
            "nearestStation" => $this->station,
            "url" => $this->url,
            "begin" => $this->begin,
            "end" => $this->end,
            "checkinBegin" => $this->checkinBegin,
            "checkinEnd" => $this->checkinEnd,
        ];
    }
}
