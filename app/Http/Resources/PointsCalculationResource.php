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
            'points'      => $this['points'],
            'calculation' => [
                'base'     => $this['calculation']['base'],
                'distance' => $this['calculation']['distance'],
                'factor'   => $this['calculation']['factor'] ?? 1,
                'reason'   => $this['calculation']['reason'] ?? PointReasons::IN_TIME
            ],
            'additional'  => AdditionalPointsResource::collection($this['additional'])
        ];
    }
}
