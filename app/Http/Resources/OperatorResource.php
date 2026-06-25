<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Operator;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OperatorResource',
    required: ['type', 'id', 'uuid', 'identifier', 'name', 'identifiers'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'operator'),
        new OA\Property(
            property: 'id',
            description: 'Numeric legacy ID. Deprecated: will become a UUID after 2026-09-30.',
            type: 'integer',
            example: 1,
            deprecated: true
        ),
        new OA\Property(property: 'uuid', description: 'Stable UUID identifier for this operator.', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
        new OA\Property(
            property: 'identifier',
            description: 'Legacy HAFAS operator ID. Always NULL for new operators. Will be removed soon.',
            type: 'string',
            example: 'db-regio-ag-nord',
            nullable: true,
            deprecated: true
        ),
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
            'type' => 'operator', // @see https://github.com/public-transport/friendly-public-transport-format/tree/master/spec
            'id' => $this->legacy_id, // @deprecated: will become uuid after 2026-09-30
            'uuid' => $this->id, // currently NOT deprecated. But deprecate it, when ID is available -> migration uuid to id
            'identifier' => $this->when( // @deprecated: remove after 2026-09
                $this->relationLoaded('identifiers'),
                fn () => $this->identifiers->firstWhere('type', 'hafas')?->identifier
            ),
            'name' => $this->name,
            'identifiers' => $this->whenLoaded('identifiers', fn () => OperatorIdentifierResource::collection($this->identifiers)),
        ];
    }
}
