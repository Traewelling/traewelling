<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaderBoardUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'username'      => $this->user->username,
            'trainDuration' => (int) $this->duration,
            'trainDistance' => (float) $this->distance,
            'trainSpeed'    => (float) $this->speed,
            'points'        => (int) $this->points
        ];
    }
}
