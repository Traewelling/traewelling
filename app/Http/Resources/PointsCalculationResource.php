<?php

namespace App\Http\Resources;

use App\Enum\PointReason;
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
                'base'     => $this['calculation']['basePoints'],
                'distance' => $this['calculation']['distancePoints'], //TODO: This isn't the distance! Should be renamed.
                'factor'   => $this['calculation']['factor'] ?? 1,
                'reason'   => $this['calculation']['reason'] ?? PointReason::IN_TIME->value,
            ],
            'additional'  => AdditionalPointsResource::collection($this['additional'])
        ];
    }
}
