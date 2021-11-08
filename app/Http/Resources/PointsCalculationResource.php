<?php

namespace App\Http\Resources;

use App\Enum\PointReasons;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointsCalculationResource extends JsonResource
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
            'points'      => $this->point,
            'calculation' => [
                'base'     => $this->base,
                'distance' => $this->distance,
                'factor'   => $this->factor ?? 1,
                'reason'   => $this->reason ?? PointReasons::IN_TIME
            ],
            'additional'  => AdditionalPointsResource::collection($this->additional)
        ];
    }
}
