<?php

namespace App\Http\Resources;

use App\Models\Operator;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OperatorResource',
    required: ['type', 'id', 'identifier', 'name'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'operator'),
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(
            property: 'identifier',
            description: 'Legacy HAFAS operator ID. Always NULL for new operators. Will be removed soon.',
            type: 'string',
            example: 'db-regio-ag-nord',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(property: 'name', type: 'string', example: 'DB Regio AG Nord'),
    ],
)]
class OperatorResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Operator $this */
        return [
            'type' => 'operator', // @see https://github.com/public-transport/friendly-public-transport-format/tree/master/spec
            'id' => $this->id,
            'identifier' => $this->identifiers()->where('type', 'hafas')->first()?->identifier, // @deprecated: remove after 2026-09
            'name' => $this->name,
        ];
    }
}
