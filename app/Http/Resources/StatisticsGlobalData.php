<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsGlobalData extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array {
        return [
            'distance'    => $this->distance,
            'duration'    => $this->duration,
            'activeUsers' => $this->user_count
        ];
    }
}
