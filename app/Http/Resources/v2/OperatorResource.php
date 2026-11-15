<?php

declare(strict_types=1);

namespace App\Http\Resources\v2;

use App\Http\Resources\OperatorIdentifierResource;
use App\Models\Operator;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OperatorResourceV2',
    required: ['type', 'id', 'name', 'identifiers'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'operator'),
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
        new OA\Property(property: 'name', type: 'string', example: 'DB Regio AG Nord'),
        new OA\Property(
            property: 'identifiers',
            type: 'array',
            items: new OA\Items(ref: OperatorIdentifierResource::class),
        ),
    ],
)]
class OperatorResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Operator $this */
        return [
            'type' => 'operator',
            'id' => $this->id,
            'name' => $this->name,
            'identifiers' => $this->whenLoaded('identifiers', fn () => OperatorIdentifierResource::collection($this->identifiers)),
        ];
    }
}
